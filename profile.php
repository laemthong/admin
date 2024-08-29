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
            background: #f4f7f6;
        }
        .sidebar {
    position: fixed; /* ล็อคแถบด้านข้าง */
    top: 0;
    left: 0;
    height: 100%; /* ทำให้แถบด้านข้างสูงเต็มหน้าจอ */
    width: 250px;
    background: #2c3e50;
    color: white;
    padding: 20px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    overflow-y: auto; /* ถ้ามีเนื้อหาในแถบด้านข้างมาก จะสามารถเลื่อนลงได้ */
}
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        .sidebar .menu-group {
            margin-bottom: 20px;
            border-bottom: 2px solid #1abc9c;
            padding-bottom: 0;
        }
        .sidebar p {
            margin-bottom: 0;
            padding-bottom: 5px;
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
            margin-left: 290px;
            padding: 20px;
            background: #ecf0f1;
            flex: 1;
            height: auto;
        }
        h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #fff;
            border: 1px solid #bdc3c7;
            padding: 5px 10px;
            border-radius: 5px;
            white-space: nowrap;
        }
        .btn-submit, .btn-select-all {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background: #2ecc71;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-select-all {
            background: #3498db;
            margin-bottom: 10px;
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
            background:  #ecf0f1;
        }
        table, th, td {
            border: 1px solid #bdc3c7;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background: #ecf0f1;
            color: #2c3e50;
        }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .btn-edit {
            background: #f1c40f;
        }
        .btn-delete {
            background: #e74c3c;
        }
        .btn-container {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .sidebar a.btn-logout {
    background: #e74c3c; /* สีแดง */
    color: white; 
    padding: 15px 20px;
    text-decoration: none;
    display: block;
    border-radius: 5px;
    margin-bottom: 10px;
    text-align: center;
}

.sidebar a.btn-logout:hover {
    background: #c0392b; /* สีแดงเข้มขึ้นเมื่อเมาส์อยู่เหนือ */
}
    </style>
</head>
<body>

<div class="sidebar">
    <h2>เมนู</h2>
    <br>
    <div class="menu-group">
        <p>จัดการข้อมูลพื้นฐาน</p>
    </div>
    
    <div class="menu-group">
        <a href="user.php">ข้อมูลผู้ใช้งาน</a>
        <a href="sport.php">ข้อมูลกีฬา</a>
        <a href="location.php">ข้อมูลสถานที่เล่นกีฬา</a>
        <a href="sport_type.php">ข้อมูลประเภทสนามกีฬา</a>
        <a href="hashtag.php">ข้อมูลแฮชเเท็ก</a>
        <a href="approve.php">อนุมัติสถานที่</a>
        <br>
        <p>ข้อมูลทั่วไป</p>
    </div>
    
    <div class="menu-group">
        
        <a href="sport_type_in_location.php">ข้อมูลสนามกีฬา</a>
        <a href="activity.php">ข้อมูลกิจกรรม</a>
        <a href="member_in_activity.php">ข้อมูลสมาชิกกิจกรรม</a>
       
        <a href="profile.php">ข้อมูลโปรไฟล์</a>
    </div>
    
    <a href="index.php" class="btn-logout" onclick="return confirm('คุณแน่ใจว่าต้องการออกจากระบบหรือไม่?');">ออกจากระบบ</a>
    
</div>

<div class="container">
    <h2>ข้อมูลโปรไฟล์</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="profile.php">
        <input type="hidden" id="pro_id" name="pro_id">
        <div class="form-group">
            <label for="pro_name">ชื่อ - สกุล:</label>
            <input type="text" id="pro_name" name="pro_name" required>
        </div>
        <div class="form-group">
            <label for="pro_username">ชื่อสมาชิก:</label>
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
        echo "<table><tr><th>ชื่อ - สกุล</th><th>ชื่อสมาชิก</th><th>คำอธิบาย</th><th>การดำเนินการ</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["pro_name"]."</td><td>".$row["pro_username"]."</td><td>".$row["pro_brief"]."</td>
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
