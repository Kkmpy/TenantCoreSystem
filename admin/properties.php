<?php
include_once __DIR__ . '/../includes/db.php';
include '../includes/auth.php';

requireLogin();
requireRole('admin');

/* =========================
   ADD PROPERTY
========================= */
if (isset($_POST['add_property'])) {

    $property_name = trim($_POST['property_name']);
    $location      = trim($_POST['location']);

    if (!empty($property_name) && !empty($location)) {

        $check = $conn->prepare("
            SELECT id
            FROM properties
            WHERE property_name = ?
        ");

        $check->bind_param("s", $property_name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            echo "<script>alert('Property already exists');</script>";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO properties
                (property_name, location)
                VALUES (?, ?)
            ");

            $stmt->bind_param(
                "ss",
                $property_name,
                $location
            );

            if ($stmt->execute()) {

                echo "<script>alert('Property added successfully');</script>";

            } else {

                echo "<script>alert('Failed to add property');</script>";
            }
        }
    }
}
/* =========================
   ADD UNIT
========================= */
if (isset($_POST['add_unit'])) {

    $property_id = (int) $_POST['property_id'];
    $unit_code   = trim($_POST['unit_code']);
    $unit_type   = trim($_POST['unit_type']);
    $rent_amount = trim($_POST['rent_amount']);
    $status      = trim($_POST['status']);

    if (
        !empty($property_id) &&
        !empty($unit_code) &&
        !empty($unit_type) &&
        !empty($rent_amount)
    ) {

        $checkUnit = $conn->prepare("
            SELECT id
            FROM property_units
            WHERE unit_code = ?
            AND property_id = ?
        ");

        $checkUnit->bind_param(
            "si",
            $unit_code,
            $property_id
        );

        $checkUnit->execute();
        $checkUnit->store_result();

        if ($checkUnit->num_rows > 0) {

            echo "<script>alert('Unit already exists in this property');</script>";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO property_units
                (
                    property_id,
                    unit_code,
                    unit_type,
                    rent_amount,
                    status
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "issss",
                $property_id,
                $unit_code,
                $unit_type,
                $rent_amount,
                $status
            );

            if ($stmt->execute()) {

                echo "<script>alert('Unit added successfully');</script>";

            } else {

                echo "<script>alert('Failed to add unit');</script>";
            }
        }
    }
}

/* =========================
   FETCH PROPERTIES
========================= */
$properties = $conn->query("
    SELECT *
    FROM properties
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Properties Management</title>

<style>

:root{
    --bg:#eef1f5;
    --card:#fff;
    --text:#111827;
    --muted:#6b7280;
    --primary:#2563eb;
    --danger:#dc2626;
    --success:#16a34a;
    --shadow:0 10px 25px rgba(0,0,0,0.08);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', sans-serif;
}

body{
    background:var(--bg);
    display:flex;
}

/* SIDEBAR */

.sidebar{
    width:240px;
    min-height:100vh;
    background:linear-gradient(180deg,#0f172a,#1e3a8a);
    color:#fff;
    position:fixed;
    top:0;
    left:0;
    padding-top:20px;
}

.logo{
    text-align:center;
    margin-bottom:30px;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    color:#cbd5e1;
    text-decoration:none;
    padding:14px 20px;
    margin:6px 10px;
    border-radius:10px;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.1);
}

/* MAIN */

.main{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:25px;
}

.topbar{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:var(--shadow);
    margin-bottom:20px;
}

.topbar h1{
    font-size:28px;
}

.topbar p{
    color:var(--muted);
    margin-top:5px;
}

/* BUTTONS */

.action-bar{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.btn{
    border:none;
    padding:12px 16px;
    border-radius:10px;
    cursor:pointer;
    color:#fff;
    font-weight:600;
}

.primary{
    background:var(--primary);
}

.success{
    background:var(--success);
}

/* TABLE */

.table-box{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:var(--shadow);
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#f3f4f6;
    text-align:left;
    padding:14px;
}

table td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
}

.view-btn{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:8px 14px;
    border-radius:8px;
    cursor:pointer;
}

/* UNIT DROPDOWN */

.units-dropdown{
    display:none;
    background:#f9fafb;
    padding:15px;
    border-radius:10px;
    margin-top:10px;
}

.unit-item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #e5e7eb;
}

.unit-item:last-child{
    border-bottom:none;
}

.status{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.vacant{
    background:#dcfce7;
    color:#166534;
}

.occupied{
    background:#fee2e2;
    color:#991b1b;
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
    background:#fff;
    width:350px;
    padding:20px;
    border-radius:16px;
}

.modal-content h2{
    margin-bottom:15px;
}

.modal-content input,
.modal-content select{
    width:100%;
    padding:12px;
    margin-bottom:12px;
    border:1px solid #ddd;
    border-radius:8px;
}

.modal-buttons{
    display:flex;
    gap:10px;
}

.save-btn{
    background:#2563eb;
    color:#fff;
}

.cancel-btn{
    background:#dc2626;
    color:#fff;
}

.modal-buttons button{
    flex:1;
    border:none;
    padding:12px;
    border-radius:10px;
    cursor:pointer;
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        <h2>TenantCore</h2>
        <p>System</p>
    </div>

    <a href="dashboard.php">🏠 Dashboard</a>

    <a href="properties.php">🏢 Properties</a>

    <a href="tenants.php">👥 Tenants</a>

    <a href="#">💳 Payments</a>

    <a href="../logout.php">🚪 Logout</a>

</div>

<!-- MAIN -->

<div class="main">

    <div class="topbar">

        <h1>Properties Management</h1>

        <p>
            Manage properties and units/houses
        </p>

    </div>

    <!-- ACTIONS -->

    <div class="action-bar">

        <button
            class="btn primary"
            onclick="openPropertyModal()"
        >
            + Add Property
        </button>

        <button
            class="btn success"
            onclick="openUnitModal()"
        >
            + Add Unit
        </button>

    </div>

    <!-- TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Property Name</th>
                    <th>Location</th>
                    <th>Units</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php
            if ($properties->num_rows > 0):

                while($property = $properties->fetch_assoc()):

                    $property_id = $property['id'];

                    $unitCount = $conn->query("
                        SELECT COUNT(*) AS total
                        FROM property_units
                        WHERE property_id = '$property_id'
                    ");

                    $total_units = $unitCount
                        ->fetch_assoc()['total'];
            ?>

                <tr>

                    <td>
                        <?= $property['id'] ?>
                    </td>

                    <td>
                        <?= $property['property_name'] ?>
                    </td>

                    <td>
                        <?= $property['location'] ?>
                    </td>

                    <td>
                        <?= $total_units ?>
                    </td>

                    <td>

                        <button
                            class="view-btn"
                            onclick="toggleUnits('units<?= $property_id ?>')"
                        >
                            View Units
                        </button>

                    </td>

                </tr>

                <tr>

                    <td colspan="5">

                        <div
                            class="units-dropdown"
                            id="units<?= $property_id ?>"
                        >

                        <?php

                        $units = $conn->query("
                            SELECT *
                            FROM property_units
                            WHERE property_id = '$property_id'
                            ORDER BY unit_code ASC
                        ");

                        if ($units->num_rows > 0):

                            while($unit = $units->fetch_assoc()):
                        ?>

                            <div class="unit-item">

                                <div>

                                    <strong>
                                        <?= $unit['unit_code'] ?>
                                    </strong>

                                    -
                                    <?= $unit['unit_type'] ?>

                                    -
                                    KES <?= number_format($unit['rent_amount']) ?>

                                </div>

                                <div>

                                <?php
                                if ($unit['status'] == 'vacant') {

                                    echo "
                                    <span class='status vacant'>
                                        Vacant
                                    </span>
                                    ";

                                } else {

                                    echo "
                                    <span class='status occupied'>
                                        Occupied
                                    </span>
                                    ";
                                }
                                ?>

                                </div>

                            </div>

                        <?php
                            endwhile;

                        else:
                        ?>

                            No units added yet

                        <?php endif; ?>

                        </div>

                    </td>

                </tr>

            <?php
                endwhile;

            endif;
            ?>

            </tbody>

        </table>

    </div>

</div>

<!-- ADD PROPERTY MODAL -->

<div class="modal" id="propertyModal">

    <div class="modal-content">

        <h2>Add Property</h2>

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

            <div class="modal-buttons">

                <button
                    type="submit"
                    name="add_property"
                    class="save-btn"
                >
                    Save
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

<!-- ADD UNIT MODAL -->

<div class="modal" id="unitModal">

    <div class="modal-content">

        <h2>Add Unit</h2>

        <form method="POST">

            <select
                name="property_id"
                required
            >

                <option value="">
                    Select Property
                </option>

                <?php

                $propertyList = $conn->query("
                    SELECT *
                    FROM properties
                    ORDER BY property_name ASC
                ");

                while($p = $propertyList->fetch_assoc()):
                ?>

                <option value="<?= $p['id'] ?>">

                    <?= $p['property_name'] ?>

                </option>

                <?php endwhile; ?>

            </select>

            <input
                type="text"
                name="unit_code"
                placeholder="Unit Code (A1, D1...)"
                required
            >

            <input
                type="text"
                name="unit_type"
                placeholder="Unit Type"
                required
            >

            <input
                type="number"
                name="rent_amount"
                placeholder="Rent Amount"
                required
            >

            <select
                name="status"
                required
            >

                <option value="vacant">
                    Vacant
                </option>

                <option value="occupied">
                    Occupied
                </option>

            </select>

            <div class="modal-buttons">

                <button
                    type="submit"
                    name="add_unit"
                    class="save-btn"
                >
                    Save Unit
                </button>

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeUnitModal()"
                >
                    Cancel
                </button>

            </div>

        </form>

    </div>

</div>

<script>

function toggleUnits(id) {

    let dropdown = document.getElementById(id);

    if (dropdown.style.display === "block") {

        dropdown.style.display = "none";

    } else {

        dropdown.style.display = "block";
    }
}

/* PROPERTY MODAL */

function openPropertyModal() {

    document
        .getElementById("propertyModal")
        .style.display = "flex";
}

function closePropertyModal() {

    document
        .getElementById("propertyModal")
        .style.display = "none";
}

/* UNIT MODAL */

function openUnitModal() {

    document
        .getElementById("unitModal")
        .style.display = "flex";
}

function closeUnitModal() {

    document
        .getElementById("unitModal")
        .style.display = "none";
}

/* CLOSE OUTSIDE */

window.onclick = function(event) {

    let propertyModal =
        document.getElementById("propertyModal");

    let unitModal =
        document.getElementById("unitModal");

    if (event.target == propertyModal) {

        propertyModal.style.display = "none";
    }

    if (event.target == unitModal) {

        unitModal.style.display = "none";
    }
};

</script>

</body>
</html>