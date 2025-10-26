<?php
session_start();
include("config.php");

// Check if role is set in URL parameter
$role = isset($_GET['role']) ? $_GET['role'] : null;

// Variables for alerts
$alert_type = '';
$alert_message = '';

// Handle login form submission
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Correct table name and column names
    $stmt = $conn->prepare("SELECT * FROM Accounts WHERE Username = ? AND Role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ FIXED: use password_verify for hashed passwords
        if (password_verify($password, $user['Password'])) {
            $_SESSION['username'] = $user['Username'];
            $_SESSION['name'] = $user['Name'];
            $_SESSION['role'] = $user['Role'];

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: " . $_SERVER['PHP_SELF'] . "?role=admin&verify=1");
                exit();
            } else {
                header("Location: userdb.php");
                exit();
            }
        } else {
            $alert_type = 'error';
            $alert_message = 'You have entered a wrong password. Please try again.';
        }
    } else {
        $alert_type = 'error';
        $alert_message = 'Username not found for the selected role.';
    }
    $stmt->close();
}

if (isset($_POST['verify'])) {
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM Accounts WHERE Username = ? AND Role = 'admin'");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        $masterKey = "GSC"; // SET PW HERE
        if ($password === $masterKey) {
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'You have successfully logged in as Admin!';
            header("Location: admindb.php");
            exit();
        } else {
            $alert_type = 'error';
            $alert_message = 'Incorrect Master Password!';
        }
    } else {
        $alert_type = 'error';
        $alert_message = 'Admin user not found!';
    }
    $stmt->close();
}

// check for logout notification
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $alert_type = 'success';
    $alert_message = 'You have successfully logged out!';
}

// check for session alerts
if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
    $alert_type = $_SESSION['alert_type'];
    $alert_message = $_SESSION['alert_message'];
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
}

// clear any existing session alerts when accessing the login page directly
if (!isset($_POST['login']) && !isset($_POST['verify']) && !isset($_GET['logout'])) {
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GSC HelpDesk</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
body { 
    font-family: Arial, sans-serif; 
    background: linear-gradient(135deg, #1f9158, #275850, #1c4440, #163a37);
    display: flex; 
    justify-content: center; 
    align-items: center; 
    flex-direction: column;
    height: 100vh; 
    margin: 0; 
    text-align: center;
    color: white;
}

img {
    width: 200px;
    margin-bottom: 30px;
}

button {
    width: 200px;
    margin: 10px 0;
    padding: 12px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background: white;
    color: #1f9158;
    font-size: 16px;
    transition: 0.3s;
}

button:hover {
    opacity: 0.8;
}

.container {
    width: 350px;
    padding: 40px;
    background: linear-gradient(135deg, #1f9158, #275850, #1c4440, #163a37);
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    text-align: center;
    position: relative;
    color: white;
}

.back-btn {
    position: absolute;
    top: 15px;
    left: 15px;
    font-size: 24px;
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    width: auto;
    padding: 5px 10px;
}

.back-btn:hover {
    opacity: 0.7;
}

.container img {
    width: 200px;
    margin-bottom: 30px;
}

h2 {
    font-size: 28px;
    margin-bottom: 20px;
    font-weight: bold;
}

.input-container {
    position: relative;
    width: 100%;
    margin-bottom: 15px;
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
    box-sizing: border-box;
    background-color: #d9d9d9;
    color: #000;
    font-size: 16px;
}

button.login-btn {
    width: 100%;
    padding: 12px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    background: white;
    color: #1f9158;
    margin-top: 10px;
    font-size: 16px;
    transition: 0.3s;
}

button.login-btn:hover {
    opacity: 0.9;
}

.toggle {
    margin-top: 10px;
    font-size: 14px;
}

.toggle a {
    color: white;
    text-decoration: underline;
    font-weight: bold;
}

.error {
    color: red;
    font-size: 14px;
    margin-bottom: 15px;
}

.swal2-popup {
    font-size: 1rem !important;
    border-radius: 10px !important;
    padding: 1.5rem !important;
}

.swal2-title {
    font-size: 1.5rem !important;
    margin-bottom: 1rem !important;
}

.swal2-html-container {
    font-size: 1rem !important;
    margin: 1rem 0 !important;
}

.swal2-confirm {
    padding: 0.7rem 2rem !important;
    font-size: 1rem !important;
    border-radius: 5px !important;
}
</style>
</head>
<body>

<?php
if (!$role) {
?>
    <img src="img/LSULogo.png" alt="LSU Logo">
    <button onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?role=student_staff'">Student/Staff</button>
    <button onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?role=admin'">Admin</button>
<?php
} elseif ($role === 'admin' && isset($_GET['verify'])) {
?>
    <div class="container">
        <img src="img/LSULogo.png" alt="Logo">
        <h2>Admin Verification</h2>
        <p>For added security, please input your Master Password</p>
        <form method="POST">
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Enter Master Password" required>
            </div>
            <button type="submit" name="verify">Verify</button>
        </form>
    </div>
<?php
} else {
?>
    <div class="container">
        <button class="back-btn" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'"><</button>
        <img src="img/LSULogo.png" alt="Logo">
        <h2><?php echo $role === 'admin' ? 'Admin Login' : 'Student/Staff Login'; ?></h2>
        <form method="POST">
            <div class="input-container">
                <i class="fa-solid fa-circle-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="login-btn">Login</button>
        </form>
        <?php if ($role === 'student_staff') { ?>
        <div class="toggle">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
        <?php } ?>
    </div>
<?php
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
<?php if ($alert_type && $alert_message): ?>
    Swal.fire({
        icon: '<?php echo $alert_type == "success" ? "success" : "error"; ?>',
        title: '<?php echo $alert_type == "success" ? "Success!" : "Error!"; ?>',
        text: '<?php echo $alert_message; ?>',
        confirmButtonColor: '<?php echo $alert_type == "success" ? "#1f9158" : "#d33"; ?>',
        confirmButtonText: 'OK'
    });
<?php endif; ?>
</script>

</body>
</html>
