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
   LOGIN AS TENANT
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    background:
    radial-gradient(circle at top left,#dbeafe 0%,transparent 25%),
    radial-gradient(circle at bottom right,#e0e7ff 0%,transparent 25%),
    #f4f7fb;
    padding:30px;
    color:#1e293b;
}

/* ================= CONTAINER ================= */
.container{
    background:rgba(255,255,255,0.88);
    backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,0.5);
    border-radius:24px;
    padding:30px;
    max-width:1300px;
    margin:auto;
    box-shadow:
    0 20px 40px rgba(15,23,42,0.08),
    0 4px 10px rgba(15,23,42,0.04);
}

/* ================= HEADER ================= */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.header h2{
    font-size:28px;
    font-weight:700;
    color:#0f172a;
}

.back-btn{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    padding:12px 18px;
    border-radius:14px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    transition:0.3s ease;
    box-shadow:0 10px 20px rgba(37,99,235,0.25);
}

.back-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 14px 25px rgba(37,99,235,0.35);
}

/* ================= SEARCH ================= */
.search-box{
    margin-bottom:25px;
}

.search-box input{
    width:340px;
    padding:14px 18px;
    border-radius:14px;
    border:1px solid #dbe2ea;
    background:#fff;
    font-size:14px;
    outline:none;
    transition:0.3s;
    box-shadow:0 4px 10px rgba(0,0,0,0.03);
}

.search-box input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,0.12);
}

/* ================= TABLE ================= */
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0 14px;
}

th{
    text-align:left;
    padding:0 16px 12px;
    color:#64748b;
    font-size:13px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:0.5px;
}

td{
    background:#fff;
    padding:18px 16px;
    font-size:14px;
    border-top:1px solid #f1f5f9;
    border-bottom:1px solid #f1f5f9;
}

tr td:first-child{
    border-radius:16px 0 0 16px;
}

tr td:last-child{
    border-radius:0 16px 16px 0;
}

tr:hover td{
    background:#f8fbff;
}

/* ================= BADGES ================= */
.badge{
    padding:8px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:700;
    display:inline-flex;
    align-items:center;
}

.active{
    background:#dcfce7;
    color:#166534;
}

.inactive{
    background:#fee2e2;
    color:#b91c1c;
}

/* ================= ACTION ================= */
.action-wrapper{
    position:relative;
    display:inline-block;
}

.action-btn{
    width:40px;
    height:40px;
    border:none;
    border-radius:12px;
    background:#f8fafc;
    color:#334155;
    cursor:pointer;
    font-size:18px;
    transition:0.3s;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

.action-btn:hover{
    background:#2563eb;
    color:#fff;
    transform:translateY(-2px);
}

.dropdown{
    display:none;
    position:absolute;
    right:0;
    top:50px;
    width:220px;
    background:#fff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
    z-index:999;
    border:1px solid #f1f5f9;
}

.dropdown a,
.dropdown button{
    width:100%;
    border:none;
    background:none;
    padding:14px 16px;
    text-align:left;
    cursor:pointer;
    font-size:14px;
    transition:0.2s;
    text-decoration:none;
    display:block;
}

.dropdown a:hover,
.dropdown button:hover{
    background:#f8fafc;
}

.edit{
    color:#2563eb;
}

.activate{
    color:#16a34a;
}

.deactivate{
    color:#dc2626;
}

.delete{
    color:#dc2626;
}

/* ================= MODAL ================= */
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(15,23,42,0.55);
    backdrop-filter:blur(5px);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content{
    background:#fff;
    width:400px;
    border-radius:24px;
    padding:28px;
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
}

.modal-content h3{
    margin-bottom:18px;
    font-size:22px;
    color:#0f172a;
}

/* ================= INPUTS ================= */
.modal-content input{
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border-radius:14px;
    border:1px solid #dbe2ea;
    outline:none;
    background:#f8fafc;
    transition:0.3s;
}

.modal-content input:focus{
    border-color:#2563eb;
    background:#fff;
    box-shadow:0 0 0 4px rgba(37,99,235,0.1);
}

/* ================= BUTTONS ================= */
.modal-content button{
    padding:13px 18px;
    border:none;
    border-radius:14px;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
}

.modal-content button[name="update_tenant"]{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    margin-right:8px;
}

.modal-content button[name="update_tenant"]:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 20px rgba(37,99,235,0.3);
}

.modal-content button[type="button"]{
    background:#eef2f7;
    color:#334155;
}

/* ================= PAGINATION ================= */
.pagination{
    margin-top:30px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.pagination a{
    width:42px;
    height:42px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#fff;
    color:#334155;
    border-radius:12px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

.pagination a:hover{
    transform:translateY(-2px);
    background:#eff6ff;
}

.pagination a.active-page{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    box-shadow:0 10px 20px rgba(37,99,235,0.3);
}

/* ================= RESPONSIVE ================= */
@media(max-width:900px){

    body{
        padding:15px;
    }

    .container{
        padding:20px;
    }

    .header{
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }

    .search-box input{
        width:100%;
    }

    table{
        display:block;
        overflow-x:auto;
    }
}
</style>
</head>

<body>

<div class="container">

<div class="header">
    <h2>Tenants Management</h2>
    <a class="back-btn" href="dashboard.php">← Back Dashboard</a>
</div>

<form class="search-box" method="GET">
    <input type="text" name="search" placeholder="Search tenant..." value="<?= $_GET['search'] ?? '' ?>">
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
✏️ Edit
</button>

<?php if ($row['status']==1): ?>
<a class="deactivate" href="tenant_status.php?id=<?= $row['ID'] ?>&status=0">
⛔ Deactivate
</a>
<?php else: ?>
<a class="activate" href="tenant_status.php?id=<?= $row['ID'] ?>&status=1">
✅ Activate
</a>
<?php endif; ?>

<a class="delete"
href="tenants.php?delete=<?= $row['ID'] ?>"
onclick="return confirm('Delete tenant?')">
🗑 Delete
</a>

</div>
</div>

</td>
</tr>
<?php endwhile; ?>

</table>

<!-- PAGINATION -->
<div class="pagination">
<?php for($i = 1; $i <= $total_pages; $i++): ?>
<a class="<?= ($i == $page) ? 'active-page' : '' ?>"
href="?page=<?= $i ?>&search=<?= $_GET['search'] ?? '' ?>">
<?= $i ?>
</a>
<?php endfor; ?>
</div>

</div>

<!-- MODAL -->
<div id="editModal" class="modal">
<div class="modal-content">

<h3>Edit Tenant</h3>

<form method="POST">

<input type="hidden" name="id" id="edit_id">

<input type="text" name="full_name" id="edit_name" required placeholder="Full Name">

<input type="text" name="phone" id="edit_phone" required placeholder="Phone Number">

<input type="email" name="email" id="edit_email" placeholder="Email">

<input type="number" name="property_id" id="edit_property" placeholder="Property ID">

<button type="submit" name="update_tenant">Update Tenant</button>

<button type="button" onclick="closeEditModal()">Cancel</button>

</form>

</div>
</div>

<script>
function toggleMenu(id){

    document.querySelectorAll(".dropdown").forEach(menu => {
        if(menu.id !== "menu-" + id){
            menu.style.display = "none";
        }
    });

    let menu = document.getElementById("menu-"+id);

    menu.style.display =
    menu.style.display === "block" ? "none" : "block";
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

window.onclick = function(e){

    if(!e.target.matches('.action-btn')){

        document.querySelectorAll(".dropdown").forEach(menu => {
            menu.style.display = "none";
        });
    }

    if(e.target == document.getElementById("editModal")){
        closeEditModal();
    }
}
</script>

</body>
</html>