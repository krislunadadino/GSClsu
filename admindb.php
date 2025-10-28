<?php
session_start();
// NOTE: Assuming config.php and necessary PHP environment is correctly configured.
// For demonstration, the database connection functions (mysqli_query, mysqli_fetch_assoc) 
// are assumed to be working correctly with the included config.php.
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
// Check if $conn is defined and connected before running query
$totalResult = isset($conn) ? mysqli_query($conn, $totalQuery) : false;
$totalRow = ($totalResult && $totalResult !== true) ? mysqli_fetch_assoc($totalResult) : ['total' => 0];
$total = $totalRow['total'] ?? 0;

// Pending concerns
$pendingQuery = "SELECT COUNT(*) AS pending FROM Concerns WHERE Status = 'Pending'";
$pendingResult = isset($conn) ? mysqli_query($conn, $pendingQuery) : false;
$pendingRow = ($pendingResult && $pendingResult !== true) ? mysqli_fetch_assoc($pendingResult) : ['pending' => 0];
$pending = $pendingRow['pending'] ?? 0;

// In Progress concerns
$inProgressQuery = "SELECT COUNT(*) AS inProgress FROM Concerns WHERE Status = 'In Progress'";
$inProgressResult = isset($conn) ? mysqli_query($conn, $inProgressQuery) : false;
$inProgressRow = ($inProgressResult && $inProgressResult !== true) ? mysqli_fetch_assoc($inProgressResult) : ['inProgress' => 0];
$inProgress = $inProgressRow['inProgress'] ?? 0;

// Fetch recent concerns
$recentConcernsQuery = "
    SELECT 
        c.ConcernID, 
        c.Concern_Title, 
        c.Room, 
        c.Problem_Type, 
        c.Priority, 
        c.Concern_Date,
        c.Status, 
        c.Assigned_to, 
        a.Username AS ReportedBy
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    ORDER BY c.ConcernID DESC
    LIMIT 3";
$recentResult = isset($conn) ? mysqli_query($conn, $recentConcernsQuery) : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Optional: Add Lucide or FontAwesome for card icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
/* --- PRESERVED NAVBAR STYLES (AS REQUESTED) --- */
body {
    margin: 0;
    font-family: 'Inter', sans-serif; /* Changed font for better look */
    background: #f9fafb; /* Softer background */
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
.dropdown-toggle {
    cursor: pointer;
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
.dropdown:hover .dropdown-menu {
    display: block; /* Show dropdown on hover */
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
/* --- END PRESERVED NAVBAR STYLES --- */


/* --- NEW/REFACTORED DASHBOARD STYLES --- */
.container {
    padding: 40px 60px;
    gap: 30px;
}

.top-dashboard-grid {
    display: grid;
    grid-template-columns: 3fr 1fr; /* 3/4 for cards, 1/4 for announcements */
    gap: 30px;
    margin-bottom: 30px;
}

/* Card Styling */
.status-cards-wrapper {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* Soft shadow */
    transition: transform 0.2s, box-shadow 0.2s;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 120px;
    border: 1px solid #e5e7eb; /* Light border */
}

.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.card-icon {
    font-size: 24px;
    opacity: 0.7;
    margin-bottom: 10px;
}

.card-value {
    font-size: 44px;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.card-label {
    font-size: 16px;
    font-weight: 500;
    color: #6b7280;
    margin-top: 5px;
}

/* Specific Card Colors (Soft Tints) */
.card-total {
    color: #275850; /* Primary color */
}
.card-total .card-icon { color: #1f9158; }

.card-pending {
    background-color: #fffbeb;
    color: #b45309;
}
.card-pending .card-icon { color: #f59e0b; }

.card-inprogress {
    background-color: #ecfdf5;
    color: #047857;
}
.card-inprogress .card-icon { color: #10b981; }

/* Announcements Panel */
.announcements-panel {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    min-height: 200px; /* Ensure visual balance */
    border: 1px solid #e5e7eb;
}

.announcements-panel h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1f9158;
    margin-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 8px;
}

.announcement-item {
    background: #f9fafb;
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 10px;
    text-align: left;
    font-size: 14px;
    border-left: 3px solid #1f9158;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* Recent Concerns Table */
.recent-concerns-panel {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.recent-concerns-panel h4 {
    margin-bottom: 20px;
    font-weight: 700;
    color: #374151;
    font-size: 20px;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.table thead {
    background-color: #f3f4f6;
    color: #374151;
    font-weight: 600;
}

.table tbody tr:hover {
    background-color: #fefefe;
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
    .top-dashboard-grid {
        grid-template-columns: 2fr 1fr;
    }
}
@media (max-width: 768px) {
    .top-dashboard-grid {
        grid-template-columns: 1fr; /* Stack cards and announcements */
    }
    .status-cards-wrapper {
        grid-template-columns: 1fr; /* Stack cards vertically on mobile */
    }
    .container {
        padding: 20px;
    }
    .recent-concerns-panel {
        padding: 20px;
    }
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
    
    <!-- Top Dashboard Grid: Status Cards & Announcements -->
    <div class="top-dashboard-grid">
        <!-- Status Cards Wrapper -->
        <div class="status-cards-wrapper">
            
            <!-- Card 1: Total Complaints -->
            <div class="dashboard-card card-total">
                <div class="card-icon"><i class="fas fa-boxes"></i></div>
                <h1 class="card-value"><?php echo $total; ?></h1>
                <p class="card-label">Total Complaints</p>
            </div>
            
            <!-- Card 2: Pending -->
            <div class="dashboard-card card-pending">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <h1 class="card-value"><?php echo $pending; ?></h1>
                <p class="card-label">Pending</p>
            </div>
            
            <!-- Card 3: In Progress -->
            <div class="dashboard-card card-inprogress">
                <div class="card-icon"><i class="fas fa-tasks"></i></div>
                <h1 class="card-value"><?php echo $inProgress; ?></h1>
                <p class="card-label">In Progress</p>
            </div>
        </div>

        <!-- Announcements Panel -->
        <div class="announcements-panel">
            <h3>Announcements</h3>
            <div id="announcementsContainer">
                <div class="announcement-item">Loading announcements...</div>
            </div>
        </div>
    </div>

    <!-- Recent Concerns Table -->
    <div class="recent-concerns-panel">
        <h4>Recent Concerns</h4>
        <div class="table-responsive mt-2">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Reported By</th>
                        <th>Assigned To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($recentResult) {
                        while ($row = mysqli_fetch_assoc($recentResult)): 
                    ?>
                        <tr>
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
                    <?php 
                        endwhile; 
                    } else {
                        // Display fallback if no results or connection issue
                        echo '<tr><td colspan="9" class="text-center text-muted">No recent concerns found or database error.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 25px;">
            <a href="adminconcerns.php" 
                class="btn btn-lg" 
                style="background-color: #275850; color: white; font-weight: 600; border: none; 
                       border-radius: 8px; padding: 10px 25px; transition: 0.3s ease-in-out;
                       box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                View All Concerns
            </a>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
function loadAnnouncements() {
    fetch('adminannounce.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            const container = document.getElementById('announcementsContainer');
            container.innerHTML = '';
            if (!data || !data.length) {
                container.innerHTML = '<div class="announcement-item text-muted">No announcements yet.</div>';
                return;
            }
            data.forEach(a => {
                const div = document.createElement('div');
                div.className = 'announcement-item';
                div.innerHTML = `
                    <div class="fw-bold" style="color:#275850;">${a.title || 'No Title'}</div>
                    <div class="text-muted small mb-1" style="font-size:11px;">${a.date || 'Unknown Date'}</div>
                    <div style="color:#6b7280;">${a.details || 'No details provided.'}</div>
                `;
                container.appendChild(div);
            });
        })
        .catch(() => {
            document.getElementById('announcementsContainer').innerHTML =
                '<div class="announcement-item text-danger">Error loading announcements.</div>';
        });
}
loadAnnouncements();
setInterval(loadAnnouncements, 30000);
</script>
</body>
</html>
