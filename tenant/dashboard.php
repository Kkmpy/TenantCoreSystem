<?php
include '../includes/auth.php';

requireLogin();
requireRole('tenant');
?>

<h1>Tenant Dashboard</h1>