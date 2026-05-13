<?php
include '../includes/auth.php';
include '../includes/db.php';

requireLogin();
requireRole('admin');

if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = (int) $_GET['id'];
    $status = (int) $_GET['status'];

    $sql = "UPDATE tenants SET status=? WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $status, $id);

    $stmt->execute();
}

header("Location: tenants.php");
exit();
?>