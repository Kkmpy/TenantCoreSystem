<?php
include '../includes/auth.php';
include '../includes/db.php';

requireLogin();
requireRole('admin');

/* =========================
   UPDATE TENANT
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tenant'])) {

    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $property_id = $_POST['property_id'];

    $sql = "UPDATE tenants 
            SET full_name=?, phone=?, email=?, property_id=? 
            WHERE ID=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $full_name, $phone, $email, $property_id, $id);
    $stmt->execute();

    header("Location: tenants.php?updated=1");
    exit();
}

/* =========================
   DELETE TENANT
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM tenants WHERE ID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: tenants.php?deleted=1");
    exit();
}

/* =========================
   LOGIN AS TENANT (IMPERSIONATION)
========================= */
if (isset($_GET['login_as'])) {

    $tenant_id = (int) $_GET['login_as'];

    $stmt = $conn->prepare("SELECT * FROM tenants WHERE ID=?");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $tenant = $stmt->get_result()->fetch_assoc();

    if ($tenant && !empty($tenant['user_id'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $tenant['user_id'];
        $_SESSION['tenant_id'] = $tenant['ID'];
        $_SESSION['role'] = 'tenant';

        header("Location: ../tenant/dashboard.php");
        exit();
    }
}

/* =========================
   SEARCH + PAGINATION
========================= */

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = "";
$where = "";

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where = "WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ?";
}

/* COUNT */
$count_sql = "SELECT COUNT(*) as total FROM tenants $where";
$count_stmt = $conn->prepare($count_sql);

if (!empty($where)) {
    $count_stmt->bind_param("sss", $search, $search, $search);
}

$count_stmt->execute();
$total_result = $count_stmt->get_result()->fetch_assoc();
$total_pages = ceil($total_result['total'] / $limit);

/* FETCH */
$sql = "SELECT * FROM tenants 
        $where 
        ORDER BY ID DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if (!empty($where)) {
    $stmt->bind_param("sssii", $search, $search, $search, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Tenants</title>

<style>
body{
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background:#f4f6f9;
    margin:0;
    padding:25px;
    color:#333;
}

.container{
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 10px 30px rgba(0,0,0,0.06);
    max-width:1200px;
    margin:auto;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.header h2{
    font-size:22px;
}

.back-btn{
    background:linear-gradient(135deg,#1565c0,#1e88e5);
    color:#fff;
    padding:10px 14px;
    border-radius:8px;
    text-decoration:none;
    font-weight:500;
}

/* SEARCH */
.search-box input{
    padding:12px;
    width:300px;
    border:1px solid #ddd;
    border-radius:10px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 10px;
    margin-top:10px;
}

th{
    text-align:left;
    font-size:13px;
    color:#666;
    padding:12px;
}

td{
    background:#fff;
    padding:14px 12px;
    border-top:1px solid #f0f0f0;
    border-bottom:1px solid #f0f0f0;
}

tr:hover td{
    background:#fafcff;
}

/* BADGES */
.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.active{background:#e8f5e9;color:#2e7d32;}
.inactive{background:#ffebee;color:#c62828;}

/* ACTION */
.action-wrapper{
    position:relative;
    display:inline-block;
}

.action-btn{
    background:#f5f7fa;
    border:1px solid #ddd;
    padding:6px 10px;
    border-radius:8px;
    cursor:pointer;
}

.dropdown{
    display:none;
    position:absolute;
    right:0;
    top:40px;
    background:#fff;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    border-radius:10px;
    min-width:180px;
    z-index:999;
}

.dropdown a,
.dropdown button{
    display:block;
    padding:11px;
    text-decoration:none;
    color:#333;
    width:100%;
    border:none;
    background:none;
    text-align:left;
    cursor:pointer;
}

.dropdown a:hover,
.dropdown button:hover{
    background:#f4f6f9;
}

/* COLORS */
.edit{color:#1976d2;}
.delete{color:#e53935;}
.activate{color:#2e7d32;}
.deactivate{color:#e53935;}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
}

.modal-content{
    background:#fff;
    padding:25px;
    border-radius:12px;
    width:340px;
    display:flex;
    flex-direction:column;
    gap:12px;
}

/* INPUTS */
.modal-content input{
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
}

/* PAGINATION */
.pagination a{
    padding:8px 12px;
    margin-right:5px;
    background:#eee;
    text-decoration:none;
    border-radius:6px;
}

.pagination a.active-page{
    background:#1565c0;
    color:#fff;
}
</style>
</head>

<body>

<div class="container">

<div class="header">
    <h2>Tenants</h2>
    <a class="back-btn" href="dashboard.php">← Back</a>
</div>

<form class="search-box" method="GET">
    <input type="text" name="search" placeholder="Search tenant...">
</form>

<table>

<tr>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>Property</th>
<th>Status</th>
<th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>

<td><?= $row['full_name'] ?></td>
<td><?= $row['phone'] ?></td>
<td><?= $row['email'] ?></td>
<td><?= $row['property_id'] ?></td>

<td>
<span class="badge <?= $row['status']==1?'active':'inactive' ?>">
<?= $row['status']==1?'Active':'Inactive' ?>
</span>
</td>

<td>

<div class="action-wrapper">
<button class="action-btn" onclick="toggleMenu(<?= $row['ID'] ?>)">⋮</button>

<div class="dropdown" id="menu-<?= $row['ID'] ?>">

<a class="activate" href="tenants.php?login_as=<?= $row['ID'] ?>">
👤 Login as Tenant
</a>

<button class="edit"
onclick="openEditModal(
<?= $row['ID'] ?>,
'<?= $row['full_name'] ?>',
'<?= $row['phone'] ?>',
'<?= $row['email'] ?>',
<?= $row['property_id'] ?>
)">
Edit
</button>

<?php if ($row['status']==1): ?>
<a class="deactivate" href="tenant_status.php?id=<?= $row['ID'] ?>&status=0">Deactivate</a>
<?php else: ?>
<a class="activate" href="tenant_status.php?id=<?= $row['ID'] ?>&status=1">Activate</a>
<?php endif; ?>

<a class="delete"
href="tenants.php?delete=<?= $row['ID'] ?>"
onclick="return confirm('Delete tenant?')">
Delete
</a>

</div>
</div>

</td>
</tr>
<?php endwhile; ?>

</table>

</div>

<!-- MODAL -->
<div id="editModal" class="modal">
<div class="modal-content">

<h3>Edit Tenant</h3>

<form method="POST">

<input type="hidden" name="id" id="edit_id">

<input type="text" name="full_name" id="edit_name" required>
<input type="text" name="phone" id="edit_phone" required>
<input type="email" name="email" id="edit_email">
<input type="number" name="property_id" id="edit_property">

<button type="submit" name="update_tenant">Update</button>
<button type="button" onclick="closeEditModal()">Cancel</button>

</form>

</div>
</div>

<script>
function toggleMenu(id){
    let menu = document.getElementById("menu-"+id);
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

function openEditModal(id,name,phone,email,property_id){
    document.getElementById("edit_id").value=id;
    document.getElementById("edit_name").value=name;
    document.getElementById("edit_phone").value=phone;
    document.getElementById("edit_email").value=email;
    document.getElementById("edit_property").value=property_id;
    document.getElementById("editModal").style.display="flex";
}

function closeEditModal(){
    document.getElementById("editModal").style.display="none";
}
</script>

</body>
</html>