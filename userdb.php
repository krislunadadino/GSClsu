<?php
session_start();
include("config.php");

if(!isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}
$username = $_SESSION['username'];
$activePage = "dashboard";
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

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px; 
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}

.navbar .logo {
    display: flex;
    align-items: center;
    margin-right: 25px; 
}
.navbar .logo img {
    height: 40px; 
    width: auto;
    object-fit: contain;
}

/* Links */
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

/* Dropdown */
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
    font-size: 16px;
    padding: 6px 8px;
    border-radius: 5px;
    position: relative;
    display: inline-block;
    color: white;
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

/* Main container */
.container {
    display: flex;
    padding: 40px;
    gap: 20px;
    justify-content: center;
}

/* Concerns Status panel */
.concerns-panel {
    flex: 2.5;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-width: 650px;
    overflow: hidden;
    border: 1px solid black;
}

/* Header */
.concerns-header {
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    color: white;
    padding: 12px 0;
    text-align: center;
}

/* Cards below status */
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

/* Announcements panel */
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
.announcements-panel h3 {
    margin-top: 0;
    font-size: 18px;
    margin-bottom: 15px;
}
.announcement {
    background: white;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
    text-align: left;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-size: 14px;
}
.announcement-title {
    font-weight: bold;
    color: #163a37;
    margin-bottom: 5px;
}
.announcement-date {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}
.announcement-details {
    color: #333;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
    </div>
    <div class="links">
        <a href="userdb.php" class="<?php echo ($activePage=="dashboard")?"active":""; ?>">Dashboard</a>
        <a href="usersubmit.php" class="<?php echo ($activePage=="newconcerns")?"active":""; ?>">Submit New Concerns</a>
        <a href="userconcerns.php" class="<?php echo ($activePage=="concerns")?"active":""; ?>">Concerns</a>
    </div>
    <div class="dropdown">
        <span class="username"><?php echo htmlspecialchars($username); ?></span> 
        <span class="dropdown-toggle">
            <div class="dropdown-menu">
                <a href="#">Change Password</a>
                <a href="archived_concerns.php">Archived Concerns</a>
                <a href="#">Help & Support</a>
                <a href="login.php">Logout</a>
            </div>
        </span>
    </div>
</div>

<div class="container">
    <div class="concerns-panel">
        <div class="concerns-header">
            <h2>Concerns Status</h2>
        </div>
        <div class="cards">
            <div class="card total">
                <h1 id="totalComplaints">0</h1>
                <p>Total Complaints</p>
            </div>
            <div class="card pending">
                <h1 id="pendingComplaints">0</h1>
                <p>Pending</p>
            </div>
            <div class="card inprogress">
                <h1 id="inProgressComplaints">0</h1>
                <p>In Progress</p>
            </div>
        </div>
    </div>

    <div class="announcements-panel">
        <h3>Announcements</h3>
        <div id="announcementsContainer">
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
// Function to fetch and update concerns data
function updateConcernsData() {
    fetch('get_concerns_data.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalComplaints').textContent = data.total || 0;
            document.getElementById('pendingComplaints').textContent = data.pending || 0;
            document.getElementById('inProgressComplaints').textContent = data.inProgress || 0;
        })
        .catch(error => {
            console.error('Error fetching concerns data:', error);
        });
}

// Function to fetch and display announcements
function loadAnnouncements() {
    fetch('get_announcement.php')
        .then(response => response.json())
        .then(announcements => {
            const container = document.getElementById('announcementsContainer');
            container.innerHTML = '';
            
            if (announcements.length === 0) {
                container.innerHTML = '<div class="announcement">No announcements yet.</div>';
                return;
            }
            
            announcements.forEach(announcement => {
                const announcementDiv = document.createElement('div');
                announcementDiv.className = 'announcement';
                announcementDiv.innerHTML = `
                    <div class="announcement-title">${announcement.title}</div>
                    <div class="announcement-date">${announcement.date}</div>
                    <div class="announcement-details">${announcement.details}</div>
                `;
                container.appendChild(announcementDiv);
            });
        })
        .catch(error => {
            console.error('Error loading announcements:', error);
            document.getElementById('announcementsContainer').innerHTML = 
                '<div class="announcement">Error loading announcements.</div>';
        });
}

// Update data immediately when page loads
updateConcernsData();
loadAnnouncements();

// Update data every 30 seconds
setInterval(updateConcernsData, 30000);
setInterval(loadAnnouncements, 30000);
</script>

</body>
</html>