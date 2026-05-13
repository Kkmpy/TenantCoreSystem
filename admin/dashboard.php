<?php
include_once __DIR__ . '/../includes/db.php';
include '../includes/auth.php';

requireLogin();
requireRole('admin');
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
:root{
    --bg:#eef1f5;
    --card:#ffffff;
    --text:#111;
    --muted:#666;
    --primary:#1565c0;
    --shadow:0 2px 10px rgba(0,0,0,0.06);
}

body.dark{
    --bg:#0f172a;
    --card:#1e293b;
    --text:#fff;
    --muted:#cbd5e1;
    --shadow:0 2px 10px rgba(0,0,0,0.4);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    display:flex;
    background:var(--bg);
    color:var(--text);
    transition: all 0.3s ease;
}

/* SIDEBAR */
.sidebar{
    width:240px;
    min-height:100vh;
    background:linear-gradient(to bottom,#1565c0,#0d47a1);
    color:#fff;
    position:fixed;
    left:0;
    top:0;
    padding-top:20px;
    transition:0.3s ease;
}

.sidebar.collapsed{
    width:70px;
}

/* FIXED COLLAPSE BEHAVIOR */
.sidebar.collapsed .text,
.sidebar.collapsed .logo p,
.sidebar.collapsed .logo h2{
    opacity:0;
    visibility:hidden;
    width:0;
    height:0;
    overflow:hidden;
}

/* center icons when collapsed */
.sidebar.collapsed a{
    justify-content:center;
    padding:14px 0;
}

.sidebar.collapsed a .icon{
    margin:0;
}

.logo{
    text-align:center;
    margin-bottom:30px;
}

.logo h2{
    font-size:20px;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    color:#fff;
    text-decoration:none;
    padding:14px 20px;
    margin:6px 10px;
    border-radius:10px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.15);
}

.sidebar .icon{
    width:25px;
    text-align:center;
}

/* MAIN */
.main-content{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:20px;
    transition:0.3s ease;
}

.sidebar.collapsed ~ .main-content{
    margin-left:70px;
    width:calc(100% - 70px);
}

/* TOPBAR */
.topbar{
    background:var(--card);
    padding:15px 20px;
    border-radius:10px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:var(--shadow);
}

.top-left{
    display:flex;
    gap:10px;
    align-items:center;
}

.welcome{
    font-size:14px;
    color:var(--muted);
}

/* BUTTONS */
button{
    border:none;
    padding:8px 12px;
    border-radius:6px;
    cursor:pointer;
}

.toggle-btn{
    background:var(--primary);
    color:#fff;
}

.dark-btn{
    background:#333;
    color:#fff;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
    margin-top:20px;
}

.card{
    background:var(--card);
    padding:18px;
    border-radius:12px;
    box-shadow:var(--shadow);
    position:relative;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h4{
    color:var(--muted);
}

.card h2{
    margin-top:10px;
    font-size:26px;
}

.card .icon{
    position:absolute;
    right:15px;
    top:15px;
    font-size:22px;
}

/* COLORS */
.blue{color:#1976d2;}
.green{color:#2e7d32;}
.red{color:#e53935;}
.teal{color:#00897b;}

/* GRID */
.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:15px;
    margin-top:20px;
}

.box{
    background:var(--card);
    padding:18px;
    border-radius:10px;
    box-shadow:var(--shadow);
}

/* ACTIONS */
.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:20px;
}

.actions button{
    background:var(--primary);
    color:#fff;
    flex:1;
    min-width:150px;
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
    border-radius:10px;
    width:320px;
    display:flex;
    flex-direction:column;
    gap:10px;
}

.modal-content input{
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
}

.modal-content button{
    background:var(--primary);
    color:#fff;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <div class="logo">
        <h2>TenantCore</h2>
        <p>System</p>
    </div>

    <a href="#"><span class="icon">🏠</span><span class="text">Dashboard</span></a>
    <a href="tenants.php"><span class="icon">👥</span><span class="text">Tenants</span></a>
    <a href="#"><span class="icon">💳</span><span class="text">Payments</span></a>
    <a href="#"><span class="icon">🏢</span><span class="text">Properties</span></a>
    <a href="#"><span class="icon">📊</span><span class="text">Reports</span></a>
    <a href="#"><span class="icon">🛠</span><span class="text">Maintenance</span></a>
    <a href="../logout.php"><span class="icon">🚪</span><span class="text">Logout</span></a>

</div>

<!-- MAIN -->
<div class="main-content">

    <div class="topbar">

        <div class="top-left">
            <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            <button class="dark-btn" onclick="toggleDark()">🌙</button>
        </div>

        <div>
            <h2>Admin Dashboard</h2>
            <div class="welcome">
                Welcome, <?php echo $_SESSION['email']; ?>
            </div>
        </div>

    </div>

    <!-- CARDS -->
    <div class="cards">

        <div class="card blue">
            <span class="icon">🏢</span>
            <h4>Total Properties</h4>
            <h2>0</h2>
        </div>

        <div class="card green">
            <span class="icon">🏠</span>
            <h4>Occupied Units</h4>
            <h2>0</h2>
        </div>

        <div class="card red">
            <span class="icon">💰</span>
            <h4>Rent Due</h4>
            <h2>0</h2>
        </div>

        <div class="card teal">
            <span class="icon">📈</span>
            <h4>Income</h4>
            <h2>0</h2>
        </div>

    </div>

    <!-- ACTIONS -->
    <div class="actions">
        <button onclick="openModal()">Add Tenant</button>
        <button>Add Property</button>
        <button>Record Payment</button>
    </div>

    <!-- GRID -->
    <div class="dashboard-grid">

        <div class="box">
            <h3>Recent Payments</h3>
            <div>No payments yet</div>
        </div>

        <div class="box">
            <h3>Arrears</h3>
            <div>No data yet</div>
        </div>

    </div>

</div>

<!-- MODAL -->
<div class="modal" id="tenantModal">
    <div class="modal-content">
        <h3>Add Tenant</h3>

        <form method="POST" action="add_tenant.php">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="email" name="email" placeholder="Email">
            <input type="number" name="property_id" placeholder="Property ID">

            <button type="submit">Save Tenant</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function toggleDark(){
    document.body.classList.toggle("dark");
    localStorage.setItem("dark", document.body.classList.contains("dark"));
}

function openModal(){
    document.getElementById("tenantModal").style.display = "flex";
}

function closeModal(){
    document.getElementById("tenantModal").style.display = "none";
}

window.onload = function(){
    if(localStorage.getItem("dark") === "true"){
        document.body.classList.add("dark");
    }
};
</script>

</body>
</html>