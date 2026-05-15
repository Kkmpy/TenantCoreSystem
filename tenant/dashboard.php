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

if (isset($_SESSION['tenant_id'])) {

    $tenant_id = $_SESSION['tenant_id'];

    $stmt = $conn->prepare("
        SELECT
            t.*,
            u.unit_code,
            u.unit_type,
            p.property_name,
            p.location
        FROM tenants t
        LEFT JOIN property_units u ON t.unit_id = u.id
        LEFT JOIN properties p ON u.property_id = p.id
        WHERE t.id=?
    ");

    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();
}

if (!$tenant && isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT
            t.*,
            u.unit_code,
            u.unit_type,
            p.property_name,
            p.location
        FROM tenants t
        LEFT JOIN property_units u ON t.unit_id = u.id
        LEFT JOIN properties p ON u.property_id = p.id
        WHERE t.user_id=?
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();

    if ($tenant) {
        $_SESSION['tenant_id'] = $tenant['id'];
    }
}

if (!$tenant) {
    $tenant = [
        'full_name' => 'Tenant',
        'email' => 'No Email',
        'property_name' => 'Not Assigned',
        'unit_code' => 'N/A',
        'unit_type' => '-',
        'location' => '-'
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tenant Dashboard</title>

<style>

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root{
    --bg:#eef2f7;
    --card:#fff;
    --text:#0f172a;
    --muted:#64748b;
    --primary:#2563eb;
    --shadow:0 10px 30px rgba(0,0,0,0.08);
    --border:#e2e8f0;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    background:var(--bg);
    display:flex;
    color:var(--text);
}

/* SIDEBAR */
.sidebar{
    width:250px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    background:linear-gradient(180deg,#0f172a,#1e293b);
    padding:25px;
    color:#fff;
}

.sidebar h2{
    margin-bottom:25px;
}

.sidebar a{
    display:block;
    padding:14px;
    margin-bottom:10px;
    border-radius:12px;
    text-decoration:none;
    color:#cbd5e1;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.08);
}

.sidebar a.active{
    background:linear-gradient(90deg,#2563eb,#1d4ed8);
    color:#fff;
}

/* MAIN */
.main{
    margin-left:250px;
    width:calc(100% - 250px);
    padding:25px;
}

/* TOPBAR */
.topbar{
    background:var(--card);
    padding:20px;
    border-radius:18px;
    box-shadow:var(--shadow);
    display:flex;
    justify-content:space-between;
    align-items:center;
    border:1px solid var(--border);
}

.user-info small{
    display:block;
    color:var(--muted);
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:18px;
    margin-top:20px;
}

/* CARD */
.card{
    background:var(--card);
    padding:20px;
    border-radius:18px;
    box-shadow:var(--shadow);
    border:1px solid var(--border);
}

/* BIG FEATURE CARD */
.feature-card{
    grid-column:1 / -1;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    padding:25px;
    border-radius:20px;
    box-shadow:var(--shadow);
}

.feature-card p{
    opacity:0.9;
    margin-top:6px;
}

/* TITLES */
.card h3,
.feature-card h3{
    font-size:15px;
    margin-bottom:10px;
}

/* BADGES */
.badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    color:#fff;
    background:#16a34a;
}

/* ACTION BUTTONS */
.actions{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:15px;
}

.actions button{
    flex:1;
    min-width:150px;
    padding:12px;
    border:none;
    border-radius:12px;
    cursor:pointer;
    background:#fff;
    color:#2563eb;
    font-weight:600;
    transition:0.3s;
}

.actions button:hover{
    transform:translateY(-3px);
    background:#f1f5f9;
}

/* RESPONSIVE */
@media(max-width:900px){
    .sidebar{
        width:80px;
    }

    .sidebar h2{
        display:none;
    }

    .main{
        margin-left:80px;
        width:calc(100% - 80px);
    }

    .topbar{
        flex-direction:column;
        text-align:center;
        gap:10px;
    }
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h2>Tenant Portal</h2>

    <a href="?view=home" class="<?= $view=='home'?'active':'' ?>">🏠 Dashboard</a>
    <a href="?view=payments" class="<?= $view=='payments'?'active':'' ?>">💳 Payments</a>
    <a href="?view=profile" class="<?= $view=='profile'?'active':'' ?>">👤 Profile</a>
    <a href="?view=maintenance" class="<?= $view=='maintenance'?'active':'' ?>">🛠 Maintenance</a>
    <a href="?view=password" class="<?= $view=='password'?'active':'' ?>">🔐 Password</a>

    <!-- ✅ ADDED ONLY THIS -->
    <a href="../logout.php" style="color:#f87171;">
        🚪 Logout
    </a>

</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">

        <h3>Welcome back 👋</h3>

        <div class="user-info">
            <strong><?= htmlspecialchars($tenant['full_name']) ?></strong>
            <small><?= htmlspecialchars($tenant['email']) ?></small>
        </div>

    </div>

    <!-- HOME -->
    <?php if ($view == 'home'): ?>

    <div class="grid">

        <div class="card">
            <h3>Rent Status</h3>
            <span class="badge">Paid</span>
        </div>

        <div class="card">
            <h3>Balance</h3>
            <h2>KES 0</h2>
        </div>

        <div class="card">
            <h3>Maintenance</h3>
            <h2>0</h2>
        </div>

        <div class="card">
            <h3>Next Due</h3>
            <p>05 June 2026</p>
        </div>

        <div class="feature-card">

            <h3>🏠 Allocated House</h3>

            <h2><?= htmlspecialchars($tenant['property_name']) ?></h2>

            <p>
                Unit: <?= htmlspecialchars($tenant['unit_code']) ?> |
                Type: <?= htmlspecialchars($tenant['unit_type']) ?>
            </p>

            <p>
                Location: <?= htmlspecialchars($tenant['location']) ?>
            </p>

        </div>

        <div class="card" style="grid-column:1/-1;">
            <h3>Quick Actions</h3>

            <div class="actions">
                <button>Pay Rent</button>
                <button>Download Receipt</button>
                <button>Maintenance Request</button>
            </div>
        </div>

    </div>

    <?php endif; ?>

</div>

</body>
</html>