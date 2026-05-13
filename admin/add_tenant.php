<?php
include '../includes/auth.php';
include '../includes/db.php';

requireLogin();
requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name   = trim($_POST['full_name']);
    $phone       = trim($_POST['phone']);
    $email       = trim($_POST['email']);
    $property_id = trim($_POST['property_id']);

    /* DEFAULT PASSWORD (YOU CAN CHANGE THIS) */
    $default_password = "123456";

    $role = "tenant";

    /* =========================
       STEP 1: CREATE USER LOGIN
    ========================= */
    $stmt = $conn->prepare("
        INSERT INTO users (fullname, email, password, role)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("ssss", $full_name, $email, $default_password, $role);
    $stmt->execute();

    $user_id = $conn->insert_id; // IMPORTANT LINK ID

    /* =========================
       STEP 2: CREATE TENANT PROFILE
    ========================= */
    $status = 1;

    $stmt2 = $conn->prepare("
        INSERT INTO tenants (full_name, phone, email, property_id, status, user_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt2->bind_param(
        "sssiii",
        $full_name,
        $phone,
        $email,
        $property_id,
        $status,
        $user_id
    );

    $stmt2->execute();

    header("Location: tenants.php?success=1");
    exit();
}
?>