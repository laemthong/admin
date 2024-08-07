<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next pro_id
function getNextProId($conn) {
    $sql = "SELECT pro_id FROM profile ORDER BY pro_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['pro_id'], 1) + 1;
        return 'P' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 'P001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pro_id = $_POST["pro_id"] ?? '';
    $pro_name = $_POST["pro_name"] ?? '';
    $pro_username = $_POST["pro_username"] ?? '';
    $pro_brief = $_POST["pro_brief"] ?? '';

    if (!empty($pro_id)) {
        // Update existing record
        $sql = "UPDATE profile SET pro_name='$pro_name', pro_username='$pro_username', pro_brief='$pro_brief' WHERE pro_id='$pro_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "แก้ไขข้อมูลสำเร็จ";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Check for duplicate pro_name or pro_username
        $sql = "SELECT * FROM profile WHERE pro_name='$pro_name' OR pro_username='$pro_username'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $error = "ข้อมูลซ้ำกรุณากรอกใหม่";
        } else {
            // Generate new pro_id
            $pro_id = getNextProId($conn);

            // Insert new record
            $sql = "INSERT INTO profile (pro_id, pro_name, pro_username, pro_brief) VALUES ('$pro_id', '$pro_name', '$pro_username', '$pro_brief')";
            if ($conn->query($sql) === TRUE) {
                $message = "เพิ่มข้อมูลสำเร็จ";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $pro_id = $_GET['delete'];
    $sql = "DELETE FROM profile WHERE pro_id='$pro_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "ลบข้อมูลสำเร็จ";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลโปรไฟล์</title>
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
        .form-group input {
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
    <h2>ข้อมูลโปรไฟล์</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="profile.php">
        <input type="hidden" id="pro_id" name="pro_id">
        <div class="form-group">
            <label for="pro_name">ชื่อ:</label>
            <input type="text" id="pro_name" name="pro_name" required>
        </div>
        <div class="form-group">
            <label for="pro_username">ชื่อผู้ใช้งาน:</label>
            <input type="text" id="pro_username" name="pro_username" required>
        </div>
        <div class="form-group">
            <label for="pro_brief">คำอธิบาย:</label>
            <input type="text" id="pro_brief" name="pro_brief">
        </div>
        <button type="submit" class="btn-submit">บันทึก</button>
    </form>

    <h2>รายการ</h2>

    <?php
    $sql = "SELECT pro_id, pro_name, pro_username, pro_brief FROM profile";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>รหัสโปรไฟล์</th><th>ชื่อ</th><th>ชื่อผู้ใช้งาน</th><th>คำอธิบาย</th><th>การดำเนินการ</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["pro_id"]."</td><td>".$row["pro_name"]."</td><td>".$row["pro_username"]."</td><td>".$row["pro_brief"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editProfile(\"".$row["pro_id"]."\", \"".$row["pro_name"]."\", \"".$row["pro_username"]."\", \"".$row["pro_brief"]."\")'>แก้ไข</button>
                <a class='btn btn-delete' href='profile.php?delete=".$row["pro_id"]."'>ลบ</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editProfile(pro_id, pro_name, pro_username, pro_brief) {
        document.getElementById('pro_id').value = pro_id;
        document.getElementById('pro_name').value = pro_name;
        document.getElementById('pro_username').value = pro_username;
        document.getElementById('pro_brief').value = pro_brief;
    }
    </script>

</div>

</body>
</html>
