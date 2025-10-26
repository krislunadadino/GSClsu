<?php
session_start();
include 'config.php';

$role = isset($_GET['role']) ? $_GET['role'] : 'student_staff';
$roleTitle = ucfirst(str_replace("_", " ", $role));

if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match');</script>"; 
    } else {
        $check = $conn->query("SELECT * FROM Accounts WHERE Username='$username'");
        if ($check->num_rows > 0) {
            echo "<script>alert('Username already exists');</script>"; 
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT); // keep hashed passwords
            $conn->query("INSERT INTO Accounts (Name, Username, Password, Role) 
                          VALUES ('$name','$username','$hashed','$role')");
            echo "<script>alert('Registration successful! You can now login'); window.location='login.php?role=$role';</script>"; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student/Staff Sign Up</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>

body {
    font-family: Arial, sans-serif;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    width: 350px; 
    height: 450px; 
    padding: 20px 30px; 
    background: linear-gradient(135deg, #1f9158, #275850, #1c4440, #163a37);
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    text-align: center;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.container img {
    width: 150px; 
    height: auto;
    margin: 0 auto 15px auto;
}

h2 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 15px;
}

.form-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.input-container {
    position: relative;
    width: 100%;
    margin-bottom: 12px;
}

.input-container i {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    color: #555;
    font-size: 18px;
}

.input-container input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #d9d9d9;
    color: #000;
    font-size: 16px;
    box-sizing: border-box;
}

button.signup-btn {
    width: 100%;
    padding: 12px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background: white;
    color: #1f9158;
    font-size: 16px;
    margin-top: 10px;
    transition: 0.3s;
}

button.signup-btn:hover {
    opacity: 0.9;
}

.toggle {
    margin-top: 5px;
    font-size: 14px;
}
.toggle a {
    color: white;
    text-decoration: underline;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
    <div>
        <img src="img/LSULogo.png" alt="Logo">
        <h2>Student/Staff Sign Up</h2>
    </div>
    <div class="form-wrapper">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?role=" . urlencode($role); ?>">
            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="name" placeholder="Name" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-circle-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <input type="hidden" name="role" value="<?php echo $role; ?>">
            <button type="submit" name="signup" class="signup-btn">Sign Up</button>
        </form>
    </div>
    <div class="toggle">
        Already have an account? <a href="login.php?role=<?php echo $role; ?>">Login</a>
    </div>
</div>
</body>
</html>
