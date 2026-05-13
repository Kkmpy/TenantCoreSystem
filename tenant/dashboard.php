<?php
include '../includes/auth.php';
include '../includes/db.php';

requireLogin();
requireRole('tenant');

$view = $_GET['view'] ?? 'home';

/* =========================
   GET LOGGED IN TENANT
========================= */

$tenant = null;

/* when admin logs in as tenant */
if (isset($_SESSION['tenant_id'])) {

    $tenant_id = $_SESSION['tenant_id'];

    $stmt = $conn->prepare("SELECT * FROM tenants WHERE ID=?");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();

}

/* when tenant logs in directly */
if (!$tenant && isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM tenants WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();

    /* create tenant session automatically */
    if ($tenant) {
        $_SESSION['tenant_id'] = $tenant['ID'];
    }
}

/* fallback values to avoid errors */
if (!$tenant) {
    $tenant = [
        'full_name' => 'Tenant',
        'email' => 'No Email'
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tenant Dashboard</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

body{
    background:linear-gradient(135deg,#eef2f7,#f8fafc);
    display:flex;
    color:#1f2937;
}

/* ================= SIDEBAR ================= */
.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    padding:25px;
    background:linear-gradient(180deg,#0f172a,#1e293b);
    color:#fff;
    box-shadow:10px 0 30px rgba(0,0,0,0.2);
}

.sidebar h2{
    font-size:20px;
    margin-bottom:25px;
    letter-spacing:1px;
    color:#fff;
}

.sidebar a{
    display:block;
    padding:12px 14px;
    margin-bottom:10px;
    color:#cbd5e1;
    text-decoration:none;
    border-radius:10px;
    transition:0.3s;
    font-size:14px;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.08);
    transform:translateX(5px);
}

.sidebar a.active{
    background:linear-gradient(90deg,#2563eb,#1d4ed8);
    color:#fff;
    box-shadow:0 5px 15px rgba(37,99,235,0.3);
}

/* ================= MAIN ================= */
.main{
    margin-left:260px;
    padding:25px;
    width:100%;
}

/* ================= TOPBAR ================= */
.topbar{
    background:#fff;
    padding:18px 22px;
    border-radius:16px;
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 10px 25px rgba(0,0,0,0.06);
}

.topbar h3{
    font-size:18px;
    font-weight:600;
}

.user-info{
    text-align:right;
}

.user-info small{
    display:block;
    color:#6b7280;
}

/* ================= GRID ================= */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
}

/* ================= CARD ================= */
.card{
    background:#fff;
    padding:22px;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
    transition:0.3s;
    border:1px solid #f1f5f9;
}

.card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 30px rgba(0,0,0,0.1);
}

.card h3{
    font-size:15px;
    margin-bottom:10px;
    color:#374151;
}

.card p{
    font-size:14px;
    color:#6b7280;
}

/* ================= FORM ================= */
input, textarea{
    width:100%;
    padding:12px;
    margin-top:8px;
    margin-bottom:15px;
    border:1px solid #e5e7eb;
    border-radius:10px;
    outline:none;
    transition:0.3s;
    background:#f9fafb;
}

input:focus, textarea:focus{
    border-color:#3b82f6;
    background:#fff;
    box-shadow:0 0 0 3px rgba(59,130,246,0.15);
}

button{
    padding:12px 16px;
    background:linear-gradient(90deg,#2563eb,#1d4ed8);
    color:#fff;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:500;
    transition:0.3s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(37,99,235,0.3);
}

/* ================= BADGE ================= */
.badge{
    padding:6px 12px;
    border-radius:20px;
    color:#fff;
    font-size:12px;
    font-weight:500;
}

.green{
    background:linear-gradient(90deg,#22c55e,#16a34a);
}

.red{
    background:linear-gradient(90deg,#ef4444,#dc2626);
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Tenant Portal</h2>

    <a href="?view=home" class="<?= $view=='home'?'active':'' ?>">🏠 Dashboard</a>
    <a href="?view=profile" class="<?= $view=='profile'?'active':'' ?>">👤 Profile</a>
    <a href="?view=maintenance" class="<?= $view=='maintenance'?'active':'' ?>">🛠 Maintenance</a>
    <a href="?view=password" class="<?= $view=='password'?'active':'' ?>">🔐 Password</a>

    <a href="../logout.php" style="color:#f87171;">🚪 Logout</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="topbar">
        <h3>Welcome back 👋</h3>

        <div class="user-info">
            <strong><?= htmlspecialchars($tenant['full_name']) ?></strong>
            <small><?= htmlspecialchars($tenant['email']) ?></small>
        </div>
    </div>

    <?php if ($view == 'home'): ?>
        <div class="grid">

            <div class="card">
                <h3>Rent Status</h3>
                <p><span class="badge green">Paid</span></p>
            </div>

            <div class="card">
                <h3>Balance</h3>
                <p>KES 0</p>
            </div>

            <div class="card">
                <h3>Maintenance Requests</h3>
                <p>0 Active</p>
            </div>

        </div>
    <?php endif; ?>

    <?php if ($view == 'profile'): ?>
        <div class="card">
            <h3>Update Profile</h3>

            <form method="POST" action="update_profile.php">
                <label>Name</label>
                <input type="text" name="name">

                <label>Phone</label>
                <input type="text" name="phone">

                <label>Email</label>
                <input type="email" name="email">

                <button type="submit">Update Profile</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($view == 'maintenance'): ?>
        <div class="card">
            <h3>Report Maintenance Issue</h3>

            <form method="POST" action="submit_maintenance.php">
                <label>Issue Title</label>
                <input type="text" name="title" required>

                <label>Description</label>
                <textarea name="description" rows="4"></textarea>

                <button type="submit">Submit Request</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($view == 'password'): ?>
        <div class="card">
            <h3>Change Password</h3>

            <form method="POST" action="change_password.php">
                <label>Old Password</label>
                <input type="password" name="old_password">

                <label>New Password</label>
                <input type="password" name="new_password">

                <label>Confirm Password</label>
                <input type="password" name="confirm_password">

                <button type="submit">Update Password</button>
            </form>
        </div>
    <?php endif; ?>

</div>

</body>
</html>