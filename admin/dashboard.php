<?php
include '../includes/auth.php';

requireLogin();
requireRole('admin');
?>

<h1>Admin Dashboard</h1>