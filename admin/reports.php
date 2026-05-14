<?php
include_once __DIR__ . '/../includes/db.php';
include '../includes/auth.php';

requireLogin();
requireRole('admin');

/* BASIC STATS */

$properties = $conn->query("SELECT COUNT(*) AS total FROM properties")
    ?->fetch_assoc()['total'] ?? 0;

$units = $conn->query("SELECT COUNT(*) AS total FROM property_units")
    ?->fetch_assoc()['total'] ?? 0;

$occupied = $conn->query("
    SELECT COUNT(*) AS total
    FROM property_units
    WHERE status='occupied'
")?->fetch_assoc()['total'] ?? 0;

$vacant = $conn->query("
    SELECT COUNT(*) AS total
    FROM property_units
    WHERE status='vacant'
")?->fetch_assoc()['total'] ?? 0;

/*PROPERTY PERFORMANCE*/

$propData = $conn->query("
    SELECT p.property_name,
           COUNT(u.id) AS units
    FROM properties p
    LEFT JOIN property_units u ON p.id = u.property_id
    GROUP BY p.id
");

$propNames = [];
$propUnits = [];

while ($row = $propData->fetch_assoc()) {
    $propNames[] = $row['property_name'];
    $propUnits[] = (int)$row['units'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

:root{
    --bg:#eef1f5;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --primary:#2563eb;
    --green:#16a34a;
    --red:#dc2626;
    --yellow:#f59e0b;
    --shadow:0 10px 25px rgba(0,0,0,0.08);
}

/* DARK MODE */

body.dark{
    --bg:#0f172a;
    --card:#111827;
    --text:#f8fafc;
    --muted:#94a3b8;
    --shadow:0 10px 25px rgba(0,0,0,0.4);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Segoe UI, sans-serif;
}

body{
    background:var(--bg);
    color:var(--text);
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
    text-decoration:none;
    padding:12px;
    border-radius:10px;
    margin:6px 0;
}

.sidebar a:hover{
    background:#1e293b;
}

/* MAIN */

.main{
    margin-left:240px;
    padding:20px;
    max-width:1200px;
}

/* HEADER */
.header{
    background:var(--card);
    padding:20px;
    border-radius:16px;
    box-shadow:var(--shadow);
}

.header h1{
    font-size:24px;
}

/* CARDS */

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-top:20px;
}

.card{
    background:var(--card);
    padding:18px;
    border-radius:14px;
    box-shadow:var(--shadow);
    text-align:center;
}

.card h2{
    font-size:26px;
    margin-bottom:5px;
}

.card p{
    color:var(--muted);
    font-size:14px;
}

/* COLORS */

.green{color:var(--green);}
.red{color:var(--red);}
.yellow{color:var(--yellow);}
.blue{color:var(--primary);}

/* CHART GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
    margin-top:20px;
}

/* BOX */

.box{
    background:var(--card);
    padding:18px;
    border-radius:14px;
    box-shadow:var(--shadow);

    /* FIX OVERLAP */
    min-height:320px;
    display:flex;
    flex-direction:column;
}

.box h3{
    margin-bottom:10px;
}

/* FIX CHART SIZE */
canvas{
    max-height:260px !important;
    width:100% !important;
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
<div class="main">

    <div class="header">
        <h1>Reports Dashboard</h1>
        <p>System analytics overview</p>
    </div>

    <!-- CARDS -->
    <div class="cards">

        <div class="card">
            <h2 class="blue"><?= $properties ?></h2>
            <p>Total Properties</p>
        </div>

        <div class="card">
            <h2 class="yellow"><?= $units ?></h2>
            <p>Total Units</p>
        </div>

        <div class="card">
            <h2 class="green"><?= $occupied ?></h2>
            <p>Occupied Units</p>
        </div>

        <div class="card">
            <h2 class="red"><?= $vacant ?></h2>
            <p>Vacant Units</p>
        </div>

    </div>

    <!-- CHARTS -->
    <div class="grid">

        <div class="box">
            <h3>Occupancy Overview</h3>
            <canvas id="occupancyChart"></canvas>
        </div>

        <div class="box">
            <h3>Property Performance</h3>
            <canvas id="propertyChart"></canvas>
        </div>

    </div>

</div>

<script>

/* OCCUPANCY */
new Chart(document.getElementById('occupancyChart'), {
    type: 'doughnut',
    data: {
        labels: ['Occupied', 'Vacant'],
        datasets: [{
            data: [<?= $occupied ?>, <?= $vacant ?>],
            backgroundColor: ['#16a34a', '#dc2626']
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false
    }
});

/* PROPERTY */
new Chart(document.getElementById('propertyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($propNames) ?>,
        datasets: [{
            label: 'Units',
            data: <?= json_encode($propUnits) ?>,
            backgroundColor: '#2563eb'
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false
    }
});

</script>

</body>
</html>