<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit New Concerns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
}
.navbar h2 { margin: 0; font-size: 20px; }
.return-btn {
    background: #107040;
    color: white;
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.return-btn:hover { background: #07532e; }
.date-display {
    text-align: right;
    margin: 10px 30px;
    font-weight: bold;
}
.date-display span {
    background: white;
    border: 1px solid black;
    padding: 5px 10px;
    border-radius: 4px;
}
.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin: 20px auto;
}
.submit-btn {
    width: 100%;
    padding: 12px;
    background: #1f9158;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
}
.submit-btn:hover { background: #107040; }
</style>
</head>
<body>

<div class="navbar">
    <h2>Submit New Concerns</h2>
    <button class="return-btn" onclick="window.location.href='userdb.php'">Return</button>
</div>

<div class="date-display">
    <span id="currentDateTime"><?php echo date("F j, Y"); ?></span>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="form-card">
                <form action="usersubmit_process.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Concern Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="room" class="form-label">Room</label>
                        <select class="form-select" id="room" name="room" required>
                            <option value="">Select a room</option>
                            <option value="LS 211">LS 211</option>
                            <option value="LS 212">LS 212</option>
                            <option value="LS 213">LS 213</option>
                            <option value="SB 311">SB 311</option>
                            <option value="SB 312">SB 312</option>
                            <option value="SB 313">SB 313</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="equipment" class="form-label">Equipment / Facility</label>
                        <select class="form-select" id="equipment" name="equipment" required>
                            <option value="">Select equipment/facility</option>
                            <option value="AC">Air Conditioner</option>
                            <option value="Electric Fan">Electric Fan</option>
                            <option value="Chair">Chair</option>
                            <option value="Lights">Lights</option>
                            <option value="Outlet">Outlet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="problem_type" class="form-label">Problem Type</label>
                        <select class="form-select" id="problem_type" name="problem_type">
                            <option value="Equipment">Equipment</option>
                            <option value="Facility">Facility</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="">Select priority</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="attachment" class="form-label">Attachment (Photo/Video)</label>
                        <input type="file" class="form-control" id="attachment" name="attachment">
                    </div>

                    <button type="submit" class="submit-btn">Submit Concern</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateDateTime() {
    const now = new Date();
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
}
updateDateTime();
setInterval(updateDateTime, 1000);
</script>

</body>
</html>
