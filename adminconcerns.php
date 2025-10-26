<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "concerns";

$query = "
    SELECT 
        c.ConcernID,
        c.Concern_Title,
        c.Room,
        c.Problem_Type,
        c.Priority,
        c.Concern_Date,
        c.Status,
        a.Name AS ReportedBy,
        c.Assigned_to
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    ORDER BY c.ConcernID DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Concerns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: white;
    font-family: Arial, sans-serif;
    margin: 0;
}
.navbar {
    display: flex; 
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px; 
    color: white;
}
.logo {
    display: flex;
    align-items: center;
    margin-right: 25px; 
}
.logo img {
    height: 40px; 
    width: auto; 
}
.navbar .links {
    display: flex;
    gap: 20px;
    margin-right: auto;
}
.navbar .links a {
    color: white; text-decoration: none; font-weight: bold; padding: 6px 12px;
    border-radius: 5px; transition: 0.3s;
}
.navbar .links a.active { background: #4ba06f; }
.navbar .links a:hover { background: #107040; }
.dropdown {
    position: relative;
    display: flex;
    align-items: center;
    gap: 5px;
}
.dropdown .username {
    font-weight: bold;
    font-size: 16px;
    padding: 6px 12px;
}

.dropdown:hover .dropdown-menu {
    display: block;
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 180px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    border-radius: 5px;
    overflow: hidden;
    z-index: 10;
}
.dropdown-menu a {
    display: block;
    padding: 12px 16px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
}
.dropdown-menu a:hover {
    background: #f1f1f1;
}

.table thead {
    background: #198754;
    color: white;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 40px 10px 40px;
}
.page-header h3 {
    color: #198754;
    font-weight: bold;
}
.realtime-clock {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
}
.table-container {
    margin: 0 40px 40px 40px;
}

.concern-row {
    cursor: pointer; 
    transition: background-color 0.2s ease;
}

.concern-row:hover {
    background-color: #e9ecef;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>
    <div class="links">
        <a href="admindb.php" class="<?php echo ($activePage=="dashboard")?"active":""; ?>">Dashboard</a>
        <a href="adminconcerns.php" class="<?php echo ($activePage=="concerns")?"active":""; ?>">Concerns</a>
        <a href="adminreports.php" class="<?php echo ($activePage=="reports")?"active":""; ?>">Reports</a>
        <a href="adminfeedback.php" class="<?php echo ($activePage=="feedback")?"active":""; ?>">Feedback</a>
        <a href="adminannounce.php" class="<?php echo ($activePage=="announcements")?"active":""; ?>">Announcements</a>
    </div>
    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($name); ?></span>
        <span class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#">Change Password</a>
                <a href="#">Help & Support</a>
                <a href="login.php">Logout</a>
            </div>
        </span>
    </div>
</div>

<div class="page-header">
    <h3>All Concerns</h3>
    <div class="realtime-clock" id="currentDateTime"></div>
</div>

<div class="table-container">
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Concern Date</th>
                    <th>Status</th>
                    <th>Reported By</th>
                    <th>Assigned To</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <!-- data-id attribute holds the ConcernID for js to use -->
                <tr class="concern-row" data-id="<?php echo $row['ConcernID']; ?>">
                    <td><?php echo $row['ConcernID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                    <td><?php echo htmlspecialchars($row['Room']); ?></td>
                    <td><?php echo htmlspecialchars($row['Problem_Type']); ?></td>
                    <td><?php echo htmlspecialchars($row['Priority']); ?></td>
                    <td><?php echo htmlspecialchars($row['Concern_Date']); ?></td>
                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                    <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                    <td><?php echo htmlspecialchars($row['Assigned_to']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateDateTime(){
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    document.getElementById('currentDateTime').textContent = now.toLocaleString('en-US', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();

document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('.concern-row');
    rows.forEach(row => {
        row.addEventListener('click', () => {
            const concernId = row.getAttribute('data-id');
            if (concernId) {
                window.location.href = `view_concern.php?id=${concernId}`;
            }
        });
    });
});
</script>

</body>
</html>
