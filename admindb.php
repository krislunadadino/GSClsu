<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "dashboard";

// Total concerns
$totalQuery = "SELECT COUNT(*) AS total FROM Concerns";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$total = $totalRow['total'] ?? 0;

// Pending concerns
$pendingQuery = "SELECT COUNT(*) AS pending FROM Concerns WHERE Status = 'Pending'";
$pendingResult = mysqli_query($conn, $pendingQuery);
$pendingRow = mysqli_fetch_assoc($pendingResult);
$pending = $pendingRow['pending'] ?? 0;

// In Progress concerns
$inProgressQuery = "SELECT COUNT(*) AS inProgress FROM Concerns WHERE Status = 'In Progress'";
$inProgressResult = mysqli_query($conn, $inProgressQuery);
$inProgressRow = mysqli_fetch_assoc($inProgressResult);
$inProgress = $inProgressRow['inProgress'] ?? 0;

// Fetch recent concerns
$recentConcernsQuery = "
    SELECT 
        c.ConcernID, 
        c.Concern_Title, 
        c.Room, 
        c.Problem_Type, 
        c.Priority, 
        c.Status, 
        c.Assigned_to, 
        a.Username AS ReportedBy
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    ORDER BY c.ConcernID DESC
    LIMIT 10";
$recentResult = mysqli_query($conn, $recentConcernsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
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
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 16px;
    padding: 6px 12px;
    border-radius: 5px;
    transition: all 0.3s ease;
}
.navbar .links a.active {
    background: #4ba06f;
    border: 1px solid #07491f;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4);
}
.navbar .links a:hover {
    background: #107040;
}
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
.dropdown-toggle:hover .dropdown-menu {
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
.container {
    display: flex;
    flex-direction: column;
    padding: 40px;
    gap: 25px;
    justify-content: center;
}
.status-section {
    display: flex;
    gap: 20px;
}
.concerns-panel {
    flex: 2.5;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-width: 650px;
    overflow: hidden;
    border: 1px solid black;
}
.concerns-header {
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    color: white;
    padding: 12px 0;
    text-align: center;
}
.cards {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 20px;
}
.card {
    flex: 1;
    background: #f0f0f0;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    border: 1px solid black;
    box-shadow: 1px 2px 4px rgba(0,0,0,0.2);
}
.card h1 {
    margin: 0;
    font-size: 48px;
}
.card.total { color: #d32f2f; }
.card.pending { color: #fbc02d; }
.card.inprogress { color: #4caf50; }
.card p {
    margin: 10px 0 0 0;
    font-weight: bold;
    color: #333;
}
.announcements-panel {
    flex: 1;
    background: #e6e6e6;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    height: fit-content;
    max-width: 250px;
    border: 1px solid gray;
}
.announcement {
    background: white;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
    text-align: left;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.recent-table {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid black;
}
.recent-table h4 {
    margin-bottom: 15px;
    font-weight: bold;
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

<div class="container">
    <div class="status-section">
        <div class="concerns-panel">
            <div class="concerns-header">
                <h2>Concerns Status</h2>
            </div>
            <div class="cards">
                <div class="card total">
                    <h1><?php echo $total; ?></h1>
                    <p>Total Complaints</p>
                </div>
                <div class="card pending">
                    <h1><?php echo $pending; ?></h1>
                    <p>Pending</p>
                </div>
                <div class="card inprogress">
                    <h1><?php echo $inProgress; ?></h1>
                    <p>In Progress</p>
                </div>
            </div>
        </div>

        <div class="announcements-panel">
            <h3>Announcements</h3>
            <div id="announcementsContainer">
                <div class="announcement">Loading announcements...</div>
            </div>
        </div>
    </div>

    <div class="recent-table">
    <h4>Recent Concerns</h4>
    <div class="table-responsive mt-2">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Reported By</th>
                    <th>Assigned To</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($recentResult)): ?>
                <tr>
                    <td><?php echo $row['ConcernID']; ?></td>
                    <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                    <td><?php echo htmlspecialchars($row['Room']); ?></td>
                    <td><?php echo htmlspecialchars($row['Problem_Type']); ?></td>
                    <td><?php echo htmlspecialchars($row['Priority']); ?></td>
                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                    <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                    <td><?php echo htmlspecialchars($row['Assigned_to']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="text-align: center; margin-top: 15px;">
        <a href="adminconcerns.php" 
           class="btn" 
           style="background-color: white; color: green; font-weight: 600; border: 1px solid green; 
                  box-shadow: 0 3px 6px rgba(0, 128, 0, 0.3); transition: 0.3s;">
            View All Concerns
        </a>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
function loadAnnouncements() {
    fetch('get_announcement.php')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('announcementsContainer');
            container.innerHTML = '';
            if (!data.length) {
                container.innerHTML = '<div class="announcement">No announcements yet.</div>';
                return;
            }
            data.forEach(a => {
                const div = document.createElement('div');
                div.className = 'announcement';
                div.innerHTML = `
                    <div class="fw-bold">${a.title}</div>
                    <div class="text-muted small">${a.date}</div>
                    <div>${a.details}</div>
                `;
                container.appendChild(div);
            });
        })
        .catch(() => {
            document.getElementById('announcementsContainer').innerHTML =
                '<div class="announcement text-danger">Error loading announcements.</div>';
        });
}
loadAnnouncements();
setInterval(loadAnnouncements, 30000);
</script>
</body>
</html>