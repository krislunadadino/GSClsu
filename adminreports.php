<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "reports";

$filterRoom = isset($_GET['room']) ? mysqli_real_escape_string($conn, $_GET['room']) : '';
$filterAssignedTo = isset($_GET['assigned']) ? mysqli_real_escape_string($conn, $_GET['assigned']) : '';
$filterStatus = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Fetch unique room numbers for the dropdown
$roomsQuery = "SELECT DISTINCT Room FROM Concerns WHERE Room IS NOT NULL AND Room != '' ORDER BY Room ASC";
$roomsResult = mysqli_query($conn, $roomsQuery);
$roomOptions = [];
if ($roomsResult) {
    while ($row = mysqli_fetch_assoc($roomsResult)) {
        $roomOptions[] = $row['Room'];
    }
} else {
    echo "Error fetching rooms: " . mysqli_error($conn);
}

$assignedToQuery = "SELECT DISTINCT Assigned_to FROM Concerns WHERE Assigned_to IS NOT NULL AND Assigned_to != '' ORDER BY Assigned_to ASC";
$assignedToResult = mysqli_query($conn, $assignedToQuery);
$assignedOptions = [];
if ($assignedToResult) {
    while ($row = mysqli_fetch_assoc($assignedToResult)) {
        $assignedOptions[] = $row['Assigned_to'];
    }
} else {
    echo "Error fetching personnel: " . mysqli_error($conn);
}

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
    WHERE 1=1 
";

if (!empty($filterRoom) && $filterRoom !== 'All Rooms') {
    $query .= " AND c.Room = '$filterRoom'";
}
if (!empty($filterAssignedTo) && $filterAssignedTo !== 'All Personnel') {
    $query .= " AND c.Assigned_to = '$filterAssignedTo'";
}
if (!empty($filterStatus) && $filterStatus !== 'All Statuses') {
    $query .= " AND c.Status = '$filterStatus'";
}

$query .= " ORDER BY c.ConcernID DESC";

$result = mysqli_query($conn, $query);
$concernsData = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $concernsData[] = $row;
    }
} else {
    echo "Error fetching concerns: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports</title>
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

/* Page Specific Styles */
.page-container {
    padding: 30px 40px;
}

.report-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.input-filter-width {
    width: 150px !important;
    padding: 10px 15px;
    font-weight: bold;
    border-radius: 8px;
    border: 1px solid #ced4da;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    background-color: white;
    font-size: 16px;
}

.report-controls .input-search {
    width: 160px;
    padding: 10px 15px;
    font-weight: normal;
    border-radius: 8px;
    border: 1px solid #ced4da;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.report-controls .btn-generate {
    background-color: #198754; 
    color: white;
    padding: 10px 20px;
    font-weight: bold;
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: background-color 0.3s;
    margin-left: auto; 
}
.report-controls .btn-generate:hover {
    background-color: #146c43;
}

.table thead {
    background: #198754;
    color: white;
}
.table-container {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
.table-bordered {
    border: 1px solid #dee2e6;
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

<div class="page-container">
    <form method="GET" action="adminreports.php">
        <div class="report-controls">
            
            <!-- Room Dropdown -->
            <select class="form-select input-filter-width" name="room" aria-label="Room filter">
                <option value="All Rooms" <?php echo ($filterRoom == 'All Rooms' || $filterRoom == '') ? 'selected' : ''; ?>>Room</option>
                <?php foreach ($roomOptions as $room): ?>
                    <option value="<?php echo htmlspecialchars($room); ?>" 
                            <?php echo ($filterRoom == $room) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($room); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Assigned To dd -->
            <select class="form-select input-filter-width" name="assigned" aria-label="Assigned To filter">
                <option value="All Personnel" <?php echo ($filterAssignedTo == 'All Personnel' || $filterAssignedTo == '') ? 'selected' : ''; ?>>Assigned To</option>
                <?php foreach ($assignedOptions as $person): ?>
                    <option value="<?php echo htmlspecialchars($person); ?>" 
                            <?php echo ($filterAssignedTo == $person) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($person); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!-- Status dd -->
            <select class="form-select input-filter-width" name="status" aria-label="Status filter">
                <option value="All Statuses" <?php echo ($filterStatus == 'All Statuses' || $filterStatus == '') ? 'selected' : ''; ?>>Status</option>
                <option value="Pending" <?php echo ($filterStatus == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?php echo ($filterStatus == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?php echo ($filterStatus == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?php echo ($filterStatus == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>

            <button class="btn-generate" type="submit">Generate</button>
        </div>
    </form>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
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
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($concernsData)): ?>
                        <?php foreach ($concernsData as $row): ?>
                        <tr>
                            <td><?php echo $row['ConcernID']; ?></td>
                            <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                            <td><?php echo htmlspecialchars($row['Room']); ?></td>
                            <td><?php echo htmlspecialchars($row['Problem_Type']); ?></td>
                            <td><?php echo htmlspecialchars($row['Priority']); ?></td>
                            <td><?php echo htmlspecialchars($row['Concern_Date']); ?></td>
                            <td>
                                <?php 
                                    $statusClass = '';
                                    switch ($row['Status']) {
                                        case 'Completed': $statusClass = 'bg-success'; break;
                                        case 'In Progress': $statusClass = 'bg-warning text-dark'; break;
                                        case 'Pending': $statusClass = 'bg-danger'; break;
                                        case 'Cancelled': $statusClass = 'bg-secondary'; break;
                                        default: $statusClass = 'bg-info';
                                    }
                                ?>
                                <span class="badge <?php echo $statusClass; ?> rounded-pill px-2 py-1"><?php echo htmlspecialchars($row['Status']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                            <td><?php echo htmlspecialchars($row['Assigned_to']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No concerns found matching the current filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</body>
</html>
