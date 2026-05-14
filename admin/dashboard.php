<?php
include_once __DIR__ . '/../includes/db.php';
include '../includes/auth.php';

requireLogin();
requireRole('admin');

//LOAD VACANT UNITS
$units = $conn->query("
    SELECT 
        u.id,
        u.unit_code,
        u.unit_type,
        p.property_name
    FROM property_units u
    LEFT JOIN properties p 
        ON u.property_id = p.id
    WHERE u.status = 'vacant'
    ORDER BY p.property_name ASC
");

//ADD PROPERTY
if (isset($_POST['add_property'])) {

    $property_name = trim($_POST['property_name']);
    $location      = trim($_POST['location']);
    $total_units   = (int) $_POST['total_units'];

    if (
        empty($property_name) ||
        empty($location) ||
        $total_units <= 0
    ) {

        echo "<script>alert('Please fill all property fields correctly.');</script>";

    } else {

        /* CHECK DUPLICATE PROPERTY */
        $check = $conn->prepare("
            SELECT id
            FROM properties
            WHERE property_name = ?
        ");

        $check->bind_param("s", $property_name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            echo "<script>alert('Property already exists!');</script>";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO properties
                (property_name, location, total_units)
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param(
                "ssi",
                $property_name,
                $location,
                $total_units
            );

            if ($stmt->execute()) {

                echo "<script>alert('Property added successfully!');</script>";

            } else {

                echo "<script>alert('Failed to add property!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>

:root{
    --bg:#eef1f5;
    --card:rgba(255,255,255,0.75);
    --text:#111;
    --muted:#666;
    --primary:#1565c0;
    --shadow:0 10px 25px rgba(0,0,0,0.08);
}

body.dark{
    --bg:#0f172a;
    --card:rgba(30,41,59,0.75);
    --text:#fff;
    --muted:#cbd5e1;
    --shadow:0 10px 25px rgba(0,0,0,0.4);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', sans-serif;
}

body{
    display:flex;
    background:var(--bg);
    color:var(--text);
    transition:0.3s ease;
}

/* SIDEBAR */

.sidebar{
    width:240px;
    min-height:100vh;
    background:linear-gradient(180deg,#0f172a,#1e3a8a);
    color:#fff;
    position:fixed;
    left:0;
    top:0;
    padding-top:20px;
    transition:0.3s ease;
    box-shadow:10px 0 30px rgba(0,0,0,0.2);
}

.sidebar.collapsed{
    width:70px;
}

.sidebar.collapsed .text,
.sidebar.collapsed .logo p,
.sidebar.collapsed .logo h2{
    opacity:0;
    visibility:hidden;
    height:0;
    overflow:hidden;
}

.sidebar.collapsed a{
    justify-content:center;
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
    color:#cbd5e1;
    text-decoration:none;
    padding:14px 20px;
    margin:6px 10px;
    border-radius:12px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.12);
    transform:translateX(4px);
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
    backdrop-filter:blur(12px);
    padding:15px 20px;
    border-radius:14px;
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
    padding:10px 14px;
    border-radius:10px;
    cursor:pointer;
    transition:0.2s;
    font-weight:600;
}

button:hover{
    transform:translateY(-2px);
}

.toggle-btn{
    background:var(--primary);
    color:#fff;
}

.dark-btn{
    background:#1f2937;
    color:#fff;
}

/* CARDS */

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-top:20px;
}

.card{
    background:var(--card);
    backdrop-filter:blur(12px);
    padding:20px;
    border-radius:18px;
    box-shadow:var(--shadow);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card-icon{
    font-size:28px;
}

.card h4{
    color:var(--muted);
    margin-top:10px;
    font-size:14px;
}

.card h2{
    margin-top:10px;
    font-size:30px;
}

/* ACTIONS */

.actions{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:20px;
}

.actions button{
    background:linear-gradient(135deg,#1565c0,#1e88e5);
    color:#fff;
    flex:1;
    min-width:170px;
}

/* GRID */

.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:15px;
    margin-top:20px;
}

.box{
    background:var(--card);
    backdrop-filter:blur(12px);
    padding:20px;
    border-radius:16px;
    box-shadow:var(--shadow);
}

/* PROPERTY ITEM */

.property-item{
    padding:12px;
    background:#f8fafc;
    border-radius:12px;
    margin-bottom:10px;
}

body.dark .property-item{
    background:#1e293b;
}
.property-list{
    display:flex;
    flex-direction:column;
    gap:10px;
    max-height:260px;
    overflow-y:auto;
    padding-right:5px;
}

.property-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 12px;
    border-radius:12px;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.08);
    transition:0.2s;
}

.property-row:hover{
    transform:translateY(-2px);
    background:rgba(255,255,255,0.1);
}

.property-row .left{
    display:flex;
    flex-direction:column;
}

.property-row .left span{
    font-size:12px;
    color:var(--muted);
}

.property-row .right{
    font-weight:600;
    color:var(--primary);
    font-size:13px;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
    z-index:999;
}

.modal-content{
    background:var(--card);
    backdrop-filter:blur(12px);
    padding:25px;
    border-radius:16px;
    width:350px;
    box-shadow:var(--shadow);
}

.modal-content h3{
    margin-bottom:15px;
}

.modal-content input,
.modal-content select{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    margin-bottom:12px;
    outline:none;
}

.modal-buttons{
    display:flex;
    gap:10px;
}

.cancel-btn{
    background:#dc2626 !important;
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

    <a href="#">
        <span class="icon">🏠</span>
        <span class="text">Dashboard</span>
    </a>

    <a href="tenants.php">
        <span class="icon">👥</span>
        <span class="text">Tenants</span>
    </a>

    <a href="#">
        <span class="icon">💳</span>
        <span class="text">Payments</span>
    </a>

    <a href="properties.php">
        <span class="icon">🏢</span>
        <span class="text">Properties</span>
    </a>

    <a href="reports.php">
        <span class="icon">📊</span>
        <span class="text">Reports</span>
    </a>

    <a href="#">
        <span class="icon">🛠</span>
        <span class="text">Maintenance</span>
    </a>

    <a href="../logout.php">
        <span class="icon">🚪</span>
        <span class="text">Logout</span>
    </a>

</div>

<!-- MAIN -->
<div class="main-content">

    <!-- TOPBAR -->
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

        <div class="card">

            <div class="card-icon">🏢</div>

            <h4>Total Properties</h4>

            <h2>
                <?php
                $res = $conn->query("
                    SELECT COUNT(*) AS total
                    FROM properties
                ");

                echo $res->fetch_assoc()['total'];
                ?>
            </h2>

        </div>

        <div class="card">

            <div class="card-icon">🏠</div>

            <h4>Occupied Units</h4>

            <h2>
                <?php
                $res = $conn->query("
                    SELECT COUNT(*) AS total
                    FROM property_units
                    WHERE status='occupied'
                ");

                echo $res->fetch_assoc()['total'];
                ?>
            </h2>

        </div>

        <div class="card">

            <div class="card-icon">📂</div>

            <h4>Vacant Units</h4>

            <h2>
                <?php
                $res = $conn->query("
                    SELECT COUNT(*) AS total
                    FROM property_units
                    WHERE status='vacant'
                ");

                echo $res->fetch_assoc()['total'];
                ?>
            </h2>

        </div>

        <div class="card">

            <div class="card-icon">📈</div>

            <h4>Total Units</h4>

            <h2>
                <?php
                $res = $conn->query("
                    SELECT SUM(total_units) AS total
                    FROM properties
                ");

                $row = $res->fetch_assoc();

                echo $row['total'] ?? 0;
                ?>
            </h2>

        </div>

    </div>

    <!-- ACTIONS -->
    <div class="actions">

        <button type="button" onclick="openTenantModal()">
            Add Tenant
        </button>

        <button type="button" onclick="openPropertyModal()">
            Add Property
        </button>

        <button type="button">
            Record Payment
        </button>

    </div>

    <!-- GRID -->
    <div class="dashboard-grid">

      <div class="box">

    <h3>Property Overview</h3>

    <div class="property-list">

        <?php
        $properties = $conn->query("
            SELECT property_name, location, total_units
            FROM properties
            ORDER BY id DESC
            LIMIT 6
        ");

        if ($properties->num_rows > 0) {

            while($row = $properties->fetch_assoc()) {
        ?>

        <div class="property-row">

            <div class="left">
                <strong><?= htmlspecialchars($row['property_name']) ?></strong>
                <span><?= htmlspecialchars($row['location']) ?></span>
            </div>

            <div class="right">
                <?= (int)$row['total_units'] ?> units
            </div>

        </div>

        <?php
            }
        } else {
            echo "<div style='color:var(--muted)'>No properties added yet</div>";
        }
        ?>

    </div>

</div>

</div>

<!-- TENANT MODAL -->
<div class="modal" id="tenantModal">

    <div class="modal-content">

        <h3>Add Tenant</h3>

        <form method="POST" action="add_tenant.php">

            <input
                type="text"
                name="full_name"
                placeholder="Full Name"
                required
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
            >

            <select name="unit_id" required>

                <option value="">
                    -- Select Vacant Unit --
                </option>

                <?php if($units && $units->num_rows > 0): ?>

                    <?php while($u = $units->fetch_assoc()): ?>

                        <option value="<?= $u['id'] ?>">

                            <?= htmlspecialchars($u['property_name']) ?>
                            -
                            <?= htmlspecialchars($u['unit_code']) ?>
                            (<?= htmlspecialchars($u['unit_type']) ?>)

                        </option>

                    <?php endwhile; ?>

                <?php else: ?>

                    <option value="">
                        No vacant units available
                    </option>

                <?php endif; ?>

            </select>

            <small style="color:gray;font-size:12px;">
                Only vacant units can be assigned
            </small>

            <br><br>

            <div class="modal-buttons">

                <button type="submit">
                    Save Tenant
                </button>

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeTenantModal()"
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>

</div>

<!-- PROPERTY MODAL -->
<div class="modal" id="propertyModal">

    <div class="modal-content">

        <h3>Add Property</h3>

        <form method="POST">

            <input
                type="text"
                name="property_name"
                placeholder="Property Name"
                required
            >

            <input
                type="text"
                name="location"
                placeholder="Location"
                required
            >

            <input
                type="number"
                name="total_units"
                placeholder="Total Units / Houses"
                required
            >

            <div class="modal-buttons">

                <button
                    type="submit"
                    name="add_property"
                >
                    Save Property
                </button>

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closePropertyModal()"
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>

</div>

<script>

function toggleSidebar() {

    document
        .getElementById("sidebar")
        .classList.toggle("collapsed");
}

function toggleDark() {

    document.body.classList.toggle("dark");

    localStorage.setItem(
        "dark",
        document.body.classList.contains("dark")
    );
}

/* TENANT MODAL */

function openTenantModal() {

    document.getElementById("tenantModal").style.display = "flex";
}

function closeTenantModal() {

    document.getElementById("tenantModal").style.display = "none";
}

/* PROPERTY MODAL */

function openPropertyModal() {

    document.getElementById("propertyModal").style.display = "flex";
}

function closePropertyModal() {

    document.getElementById("propertyModal").style.display = "none";
}

/* DARK MODE */

window.onload = function() {

    if(localStorage.getItem("dark") === "true") {

        document.body.classList.add("dark");
    }
};

/* CLOSE MODAL OUTSIDE CLICK */

window.onclick = function(event) {

    let tenantModal = document.getElementById("tenantModal");
    let propertyModal = document.getElementById("propertyModal");

    if(event.target == tenantModal) {

        closeTenantModal();
    }

    if(event.target == propertyModal) {

        closePropertyModal();
    }
};

</script>

</body>
</html>