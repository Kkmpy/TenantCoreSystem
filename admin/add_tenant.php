<?php
include '../includes/auth.php';
include '../includes/db.php';

requireLogin();
requireRole('admin');

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name   = trim($_POST['full_name']);
    $phone       = trim($_POST['phone']);
    $email       = trim($_POST['email']);
    $unit_id     = isset($_POST['unit_id']) && $_POST['unit_id'] !== "" 
                    ? (int)$_POST['unit_id'] 
                    : null;

    $default_password = "123456";
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    $role = "tenant";

    //VALIDATE UNIT (IF PROVIDED)
    if ($unit_id !== null) {

        $checkUnit = $conn->prepare("SELECT id, status FROM property_units WHERE id=?");
        $checkUnit->bind_param("i", $unit_id);
        $checkUnit->execute();
        $unit = $checkUnit->get_result()->fetch_assoc();

        if (!$unit) {
            $error = "❌ Selected unit does not exist.";
        } elseif ($unit['status'] == 'occupied') {
            $error = "❌ This unit is already occupied.";
        }
    }

    //STEP 2: CREATE USER
    if (!$error) {

        $stmt = $conn->prepare("
            INSERT INTO users (fullname, email, password, role)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);
        $stmt->execute();

        $user_id = $conn->insert_id;

        //CREATE TENANT

        if ($unit_id !== null) {

            $stmt2 = $conn->prepare("
                INSERT INTO tenants 
                (full_name, phone, email, unit_id, status, user_id, created_at)
                VALUES (?, ?, ?, ?, 1, ?, NOW())
            ");

            $stmt2->bind_param(
                "sssii",
                $full_name,
                $phone,
                $email,
                $unit_id,
                $user_id
            );

        } else {

            $stmt2 = $conn->prepare("
                INSERT INTO tenants 
                (full_name, phone, email, unit_id, status, user_id, created_at)
                VALUES (?, ?, ?, NULL, 1, ?, NOW())
            ");

            $stmt2->bind_param(
                "sssi",
                $full_name,
                $phone,
                $email,
                $user_id
            );
        }

        $stmt2->execute();

        //MARK UNIT OCCUPIED/
        if ($unit_id !== null) {
            $conn->query("UPDATE property_units SET status='occupied' WHERE id=$unit_id");
        }

        $success = "✅ Tenant added successfully!";
    }
}
?>