<?php
include '../includes/auth.php';
include '../includes/db.php';

requireLogin();
requireRole('admin');

//php

$error = "";
$success = "";

/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tenant'])) {

    $id = (int)$_POST['id'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    $unit_id = isset($_POST['unit_id']) && $_POST['unit_id'] !== ""
        ? (int)$_POST['unit_id']
        : null;

    $check = $conn->prepare("SELECT id FROM tenants WHERE id=?");
    $check->bind_param("i", $id);
    $check->execute();

    if ($check->get_result()->num_rows == 0) {
        $error = "❌ Tenant not found.";
    } else {

        if ($unit_id !== null) {

            $unitCheck = $conn->prepare("SELECT id, status FROM property_units WHERE id=?");
            $unitCheck->bind_param("i", $unit_id);
            $unitCheck->execute();
            $unit = $unitCheck->get_result()->fetch_assoc();

            if (!$unit) {
                $error = "❌ Selected unit does not exist.";
            } elseif ($unit['status'] == 'occupied') {
                $error = "❌ This unit is already occupied.";
            } else {

                $stmt = $conn->prepare("
                    UPDATE tenants 
                    SET full_name=?, phone=?, email=?, unit_id=? 
                    WHERE id=?
                ");

                $stmt->bind_param("sssii", $full_name, $phone, $email, $unit_id, $id);
                $stmt->execute();

                $success = "✅ Tenant updated successfully.";
            }

        } else {

            $stmt = $conn->prepare("
                UPDATE tenants 
                SET full_name=?, phone=?, email=?, unit_id=NULL 
                WHERE id=?
            ");

            $stmt->bind_param("sssi", $full_name, $phone, $email, $id);
            $stmt->execute();

            $success = "✅ Tenant updated.";
        }
    }
}

/* DELETE */

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tenants WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $success = "🗑 Tenant deleted.";
}

/* STATUS */

if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $stmt = $conn->prepare("
        UPDATE tenants 
        SET status = IF(status=1,0,1) 
        WHERE id=?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $success = "🔁 Status updated.";
}

/* LOGIN AS */

if (isset($_GET['login_as'])) {

    $tenant_id = (int)$_GET['login_as'];

    $stmt = $conn->prepare("SELECT * FROM tenants WHERE id=?");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();

    if ($tenant && !empty($tenant['user_id'])) {

        session_regenerate_id(true);
        $_SESSION['user_id'] = $tenant['user_id'];
        $_SESSION['tenant_id'] = $tenant['id'];
        $_SESSION['role'] = 'tenant';

        header("Location: ../tenant/dashboard.php");
        exit();
    }
}

/* FETCH */

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "
SELECT 
t.id,t.full_name,t.phone,t.email,t.status,t.unit_id,
u.unit_code,u.unit_type,p.property_name
FROM tenants t
LEFT JOIN property_units u ON t.unit_id=u.id
LEFT JOIN properties p ON u.property_id=p.id
ORDER BY t.id DESC
LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Tenants</title>

<style>

:root{
    --bg:#eef1f5;
    --card:#fff;
    --text:#0f172a;
    --muted:#64748b;
    --primary:#2563eb;
    --green:#16a34a;
    --red:#dc2626;
    --shadow:0 10px 25px rgba(0,0,0,0.08);
}

body.dark{
    --bg:#0f172a;
    --card:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Segoe UI;
}

body{
    background:var(--bg);
    color:var(--text);
    display:flex;
}

/* SIDEBAR */

.sidebar{
    width:240px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    background:linear-gradient(180deg,#0f172a,#1e3a8a);
    color:#fff;
    padding:20px;
}

.sidebar h2{
    color:#fff;
    margin-bottom:20px;
}

.sidebar a{
    display:block;
    color:#cbd5e1;
    padding:12px;
    text-decoration:none;
    border-radius:10px;
    margin:6px 0;
}

.sidebar a:hover{
    background:#1e293b;
}

/* MAIN */

.container{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:20px;
}

/* HEADER */

.header{
    background:var(--card);
    padding:20px;
    border-radius:16px;
    box-shadow:var(--shadow);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* ALERTS */

.alert{
    padding:12px;
    border-radius:12px;
    margin:10px 0;
}

.success{background:#dcfce7;color:#166534;}
.error{background:#fee2e2;color:#991b1b;}

/* TABLE BOX */

.table-box{
    background:var(--card);
    margin-top:20px;
    padding:20px;
    border-radius:16px;
    box-shadow:var(--shadow);
    overflow:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f1f5f9;
    padding:12px;
    text-align:left;
    font-size:14px;
}

td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}

/* BADGES */

.badge{
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
}

.active{background:#dcfce7;color:#166534;}
.inactive{background:#fee2e2;color:#991b1b;}

/* ACTION MENU */

.action-btn{
    padding:6px 10px;
    cursor:pointer;
    border:none;
    background:#2563eb;
    color:#fff;
    border-radius:8px;
}

.dropdown{
    display:none;
    position:absolute;
    background:#fff;
    padding:10px;
    border-radius:10px;
    box-shadow:0 10px 20px rgba(0,0,0,0.15);
}

.dropdown a{
    display:block;
    padding:8px;
    text-decoration:none;
    color:#111;
}

.dropdown a:hover{
    background:#f1f5f9;
}

/* MODAL */

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
}

.modal-content{
    background:var(--card);
    padding:20px;
    border-radius:16px;
    width:350px;
}

.modal-content input{
    width:100%;
    padding:10px;
    margin:8px 0;
    border:1px solid #ddd;
    border-radius:10px;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
 
<div class="sidebar">
    <h2>TenantCore</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="properties.php">Properties</a>
    <a href="tenants.php">Tenants</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<!-- MAIN -->
 
<div class="container">

<div class="header">
    <h2>Tenants Management</h2>
</div>

<?php if($error): ?>
<div class="alert error"><?= $error ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert success"><?= $success ?></div>
<?php endif; ?>

<div class="table-box">

<table>
<tr>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>Unit</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>
<td><?= $row['full_name'] ?></td>
<td><?= $row['phone'] ?></td>
<td><?= $row['email'] ?></td>

<td>
<?= $row['property_name'] ?? 'No Property' ?> -
<?= $row['unit_code'] ?? 'No Unit' ?>
</td>

<td>
<span class="badge <?= $row['status']==1?'active':'inactive' ?>">
<?= $row['status']==1?'Active':'Inactive' ?>
</span>
</td>

<td>
<button class="action-btn" onclick="toggleMenu(<?= $row['id'] ?>)">⋮</button>

<div class="dropdown" id="menu-<?= $row['id'] ?>">

<a href="tenants.php?toggle_status=<?= $row['id'] ?>">Toggle</a>
<a href="tenants.php?delete=<?= $row['id'] ?>">Delete</a>
<a href="tenants.php?login_as=<?= $row['id'] ?>">Login</a>

</div>
</td>
</tr>

<?php endwhile; ?>

</table>

</div>

</div>

<script>
function toggleMenu(id){
    let menu=document.getElementById("menu-"+id);
    menu.style.display = menu.style.display==="block"?"none":"block";
}
</script>

</body>
</html>