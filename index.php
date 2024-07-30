<?php
include 'config.php';

$message = '';
$error = '';

// Insert or Update user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $edit_user_id = $_POST['edit_user_id'] ?? '';
    $user_id = $_POST['user_id'];
    $user_email = $_POST['user_email'];
    $user_pass = $_POST['user_pass'];
    $user_name = $_POST['user_name'];
    $user_age = $_POST['user_age'];
    $user_photo = $_POST['user_photo'];
    $user_token = $_POST['user_token'];

    $conn->begin_transaction();

    try {
        if (!empty($edit_user_id)) {
            // Update user
            $sql = "UPDATE user_information 
                    SET user_email='$user_email', user_pass='$user_pass', user_name='$user_name', user_age='$user_age', user_photo='$user_photo', user_token='$user_token'
                    WHERE user_id='$edit_user_id'";
            $conn->query($sql);

            // Update member_in_activity if user_id is changed
            if ($edit_user_id !== $user_id) {
                // Update member_in_activity first
                $sql = "UPDATE member_in_activity SET user_id='$user_id' WHERE user_id='$edit_user_id'";
                $conn->query($sql);

                // Update user_information last to ensure foreign key integrity
                $sql = "UPDATE user_information SET user_id='$user_id' WHERE user_id='$edit_user_id'";
                $conn->query($sql);
            }
        } else {
        
            // Check for duplicate user_id
            $sql = "SELECT * FROM user_information WHERE user_id='$user_id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                throw new Exception("ข้อมูลซ้ำกรุณากรอกใหม่");
            }

            // Insert user
            $sql = "INSERT INTO user_information (user_id, user_email, user_pass, user_name, user_age, user_photo, user_token)
                    VALUES ('$user_id', '$user_email', '$user_pass', '$user_name', '$user_age', '$user_photo', '$user_token')";
            $conn->query($sql);
        }
        

        $conn->commit();
        $message = "Record saved successfully";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = " " . $e->getMessage();
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];

    $conn->begin_transaction();

    try {
        // Delete related entries in member_in_activity
        $sql = "DELETE FROM member_in_activity WHERE user_id='$user_id'";
        $conn->query($sql);

        // Delete the user
        $sql = "DELETE FROM user_information WHERE user_id='$user_id'";
        $conn->query($sql);

        $conn->commit();
        $message = "Record deleted successfully";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลผู้ใช้งาน</title>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #34495e;
            text-align: center;
        }
        .sidebar a:hover {
            background: #1abc9c;
        }
        .container {
            flex: 1;
            padding: 20px;
            background: #ecf0f1;
        }
        h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
        }
        .btn-submit {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background: #2ecc71;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .message, .error {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .message {
            background: #2ecc71;
            color: white;
        }
        .error {
            background: #e74c3c;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #bdc3c7;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 5px;
            background: #3498db;
            text-align: center;
        }
        .btn-edit {
            background: #f1c40f;
        }
        .btn-delete {
            background: #e74c3c;
        }
    </style>
    <script>
        function validateForm() {
            var userId = document.getElementById("user_id").value;
            if (userId.length > 10) {
                alert("กรุณากรอก user ไม่เกิน 10 หลัก");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="sidebar">
<h2>เมนู</h2>
    <a href="index.php">ข้อมูลผู้ใช้งาน</a>
    <a href="sport.php">ข้อมูลกีฬา</a>
    <a href="sport_type_in_location.php">ข้อมูลประเภทสนามกีฬา</a>
    <a href="sport_type.php">ข้อมูลประเภทกีฬา</a>
    <a href="location.php">ข้อมูลสถานที่เล่นกีฬา</a>
    <a href="activity.php">ข้อมูลกิจกรรม</a>
    <a href="member_in_activity.php">ข้อมูลสมาชิกกิจกรรม</a>
    <a href="hashtag.php">ข้อมูลแฮชเเท็ก</a>
    <a href="profile.php">ข้อมูลโปรไฟล์</a>
    <a href="approve.php">อนุมัติสถานที่</a>
    </div>

<div class="container">
    <h2>ข้อมูลผู้ใช้งาน</h2>

    <?php
    if ($message) { echo "<div class='message'>$message</div>"; }
    if ($error) { echo "<div class='error'>$error</div>"; }
    ?>

    <form method="POST" action="index.php" onsubmit="return validateForm()">
        <input type="hidden" id="edit_user_id" name="edit_user_id">
        <div class="form-group">
            <label for="user_id">รหัสผู้ใช้งาน:</label>
            <input type="text" id="user_id" name="user_id" required>
        </div>
        <div class="form-group">
            <label for="user_email">อีเมล:</label>
            <input type="text" id="user_email" name="user_email" required>
        </div>
        <div class="form-group">
            <label for="user_pass">รหัสผ่าน:</label>
            <input type="text" id="user_pass" name="user_pass" required>
        </div>
        <div class="form-group">
            <label for="user_name">ชื่อ:</label>
            <input type="text" id="user_name" name="user_name" required>
        </div>
        <div class="form-group">
            <label for="user_age">อายุ:</label>
            <input type="number" id="user_age" name="user_age" required>
        </div>
        <div class="form-group">
            <label for="user_photo">รูปภาพ:</label>
            <input type="text" id="user_photo" name="user_photo" required>
        </div>
        <div class="form-group">
            <label for="user_token">โทเค็น:</label>
            <input type="text" id="user_token" name="user_token" required>
        </div>
        <button type="submit" class="btn-submit">บันทึก</button>
    </form>

    <h2>รายการ</h2>

    <?php
    $sql = "SELECT user_id, user_email, user_name, user_age, user_photo, user_token FROM user_information";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>รหัสผู้ใช้งาน</th><th>อีเมล</th><th>ชื่อ</th><th>อายุ</th><th>รูปภาพ</th><th>โทเค็น</th><th>การดำเนินการ</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["user_id"]."</td><td>".$row["user_email"]."</td><td>".$row["user_name"]."</td><td>".$row["user_age"]."</td><td>".$row["user_photo"]."</td><td>".$row["user_token"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editUser(\"".$row["user_id"]."\")'>แก้ไข</button>
                <a class='btn btn-delete' href='?delete=".$row["user_id"]."'>ลบ</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

</div>

<script>
function editUser(user_id) {
    fetch('get_user.php?user_id=' + user_id)
    .then(response => response.json())
    .then(data => {
        document.getElementById('edit_user_id').value = data.user_id;
        document.getElementById('user_id').value = data.user_id;
        document.getElementById('user_email').value = data.user_email;
        document.getElementById('user_pass').value = data.user_pass;
        document.getElementById('user_name').value = data.user_name;
        document.getElementById('user_age').value = data.user_age;
        document.getElementById('user_photo').value = data.user_photo;
        document.getElementById('user_token').value = data.user_token;
    });
}
</script>

</body>
</html>
