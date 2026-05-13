<?php
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
    transition:0.3s;
    overflow:hidden;
}

.sidebar.collapsed{
    width:70px;
}

.logo{
    text-align:center;
    margin-bottom:30px;
}

.logo h2{
    font-size:20px;
}

.sidebar.collapsed .logo p,
.sidebar.collapsed a span.text{
    display:none;
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

.sidebar span.icon{
    width:25px;
    text-align:center;
}

/* MAIN CONTENT */
.main-content{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:20px;
    transition:0.3s;
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

.topbar h1{
    font-size:22px;
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

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

table th,table td{
    padding:10px;
    border-bottom:1px solid #ddd;
}

.empty{
    text-align:center;
    padding:20px;
    color:var(--muted);
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">

    <div class="logo">
        <h2>TenantCore</h2>
        <p>System</p>
    </div>

    <a href="#"><span class="icon">🏠</span><span class="text">Dashboard</span></a>
    <a href="#"><span class="icon">👥</span><span class="text">Tenants</span></a>
    <a href="#"><span class="icon">💳</span><span class="text">Payments</span></a>
    <a href="#"><span class="icon">🏢</span><span class="text">Properties</span></a>
    <a href="#"><span class="icon">📊</span><span class="text">Reports</span></a>
    <a href="#"><span class="icon">🛠</span><span class="text">Maintenance</span></a>
    <a href="../logout.php"><span class="icon">🚪</span><span class="text">Logout</span></a>

</div>

<div class="main-content">

    <div class="topbar">

        <div class="top-left">
            <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            <button class="dark-btn" onclick="toggleDark()">🌙</button>
        </div>

        <h1>Admin Dashboard</h1>

        <div class="welcome">
            Welcome, <?php echo $_SESSION['email']; ?>
        </div>

    </div>

    <div class="cards">

        <div class="card blue">
            <span class="icon">🏢</span>
            <h4>Total Properties</h4>
            <h2 class="count" data-value="0">0</h2>
        </div>

        <div class="card green">
            <span class="icon">🏠</span>
            <h4>Occupied Units</h4>
            <h2 class="count" data-value="0">0</h2>
        </div>

        <div class="card red">
            <span class="icon">💰</span>
            <h4>Rent Due</h4>
            <h2 class="count" data-value="0">0</h2>
        </div>

        <div class="card teal">
            <span class="icon">📈</span>
            <h4>Income</h4>
            <h2 class="count" data-value="0">0</h2>
        </div>

    </div>

    <div class="actions">
        <button>Add Tenant</button>
        <button>Add Property</button>
        <button>Record Payment</button>
    </div>

    <div class="dashboard-grid">

        <div class="box">
            <h3>Recent Payments</h3>
            <div class="empty">No payments yet</div>
        </div>

        <div class="box">
            <h3>Arrears</h3>
            <div class="empty">No data yet</div>
        </div>

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

window.onload = function(){
    if(localStorage.getItem("dark") === "true"){
        document.body.classList.add("dark");
    }

    document.querySelectorAll(".count").forEach(c=>{
        c.innerText = 0;
    });
};
</script>

</body>
</html>