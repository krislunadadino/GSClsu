<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$username = $_SESSION['username'];

// Get AccountID of logged-in user
$userQuery = "SELECT AccountID FROM Accounts WHERE Username = '$username'";
$userResult = mysqli_query($conn, $userQuery);
$userRow = mysqli_fetch_assoc($userResult);

if (!$userRow) {
    echo json_encode(['error' => 'User not found']);
    exit();
}

$accountID = $userRow['AccountID'];

// Count total concerns
$totalQuery = "SELECT COUNT(*) AS total FROM Concerns WHERE AccountID = '$accountID'";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$total = $totalRow['total'] ?? 0;

// Count pending
$pendingQuery = "SELECT COUNT(*) AS pending FROM Concerns WHERE AccountID = '$accountID' AND Status = 'Pending'";
$pendingResult = mysqli_query($conn, $pendingQuery);
$pendingRow = mysqli_fetch_assoc($pendingResult);
$pending = $pendingRow['pending'] ?? 0;

// Count in progress
$inProgressQuery = "SELECT COUNT(*) AS inProgress FROM Concerns WHERE AccountID = '$accountID' AND Status = 'In Progress'";
$inProgressResult = mysqli_query($conn, $inProgressQuery);
$inProgressRow = mysqli_fetch_assoc($inProgressResult);
$inProgress = $inProgressRow['inProgress'] ?? 0;

// Return JSON
echo json_encode([
    'total' => (int)$total,
    'pending' => (int)$pending,
    'inProgress' => (int)$inProgress
]);
?>
