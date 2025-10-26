<?php
include("config.php");

$name = "Admin User";
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

$sql = "INSERT INTO Accounts (Name, Username, Password, Role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $username, $password, $role);

if ($stmt->execute()) {
    echo "✅ Admin account created successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
