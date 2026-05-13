<?php
session_start();
include __DIR__ . '/includes/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];

/* GET USER BY EMAIL (both admin + tenant) */
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

/* CHECK USER EXISTS */
if ($result->num_rows === 0) {
    die("Invalid login details");
}

$user = $result->fetch_assoc();

/* CHECK PASSWORD */
if ($user['password'] !== $password) {
    die("Wrong password");
}

/* SET COMMON SESSION */
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $user['email'];

/* ROLE REDIRECT */
if ($user['role'] === 'admin') {

    header("Location: admin/dashboard.php");
    exit();

} elseif ($user['role'] === 'tenant') {

    $_SESSION['tenant_id'] = $user['tenant_id'];

    header("Location: tenant/dashboard.php");
    exit();

} else {
    die("Unknown role");
}
?>