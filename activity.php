<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next activity_id
function getNextActivityId($conn) {
    $sql = "SELECT activity_id FROM activity ORDER BY activity_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['activity_id'], 1) + 1;
        return 'a' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 'a001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity_id = $_POST["activity_id"] ?? '';
    $activity_name = $_POST["activity_name"] ?? '';
    $activity_date = $_POST["activity_date"] ?? '';
    $location_id = $_POST["location_id"] ?? '';
    $sport_id = $_POST["sport_id"] ?? '';
    $user_id = $_POST["user_id"] ?? '';

    if (!empty($activity_id)) {
        // Update existing record
        $sql = "UPDATE activity SET activity_name='$activity_name', activity_date='$activity_date', location_id='$location_id', sport_id='$sport_id', user_id='$user_id' WHERE activity_id='$activity_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "แก้ไขข้อมูลสำเร็จ";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Generate new activity_id
        $activity_id = getNextActivityId($conn);

        // Insert new record
        $sql = "INSERT INTO activity (activity_id, activity_name, activity_date, location_id, sport_id, user_id, status) 
                VALUES ('$activity_id', '$activity_name', '$activity_date', '$location_id', '$sport_id', '$user_id', 'active')";
        if ($conn->query($sql) === TRUE) {
            $message = "เพิ่มข้อมูลสำเร็จ";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if (isset($_GET['delete'])) {
    $activity_id = $_GET['delete'];
    $sql = "DELETE FROM activity WHERE activity_id='$activity_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "ลบข้อมูลสำเร็จ";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_GET['suspend'])) {
    $activity_id = $_GET['suspend'];
    $sql = "UPDATE activity SET status='inactive' WHERE activity_id='$activity_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "ระงับข้อมูลสำเร็จ";
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
    <title>ข้อมูลกิจกรรม</title>
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
        .btn-suspend {
            background: #e67e22;
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
    <h2>ข้อมูลกิจกรรม</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="activity.php">
        <input type="hidden" id="activity_id" name="activity_id">
        <div class="form-group">
            <label for="activity_name">ชื่อกิจกรรม:</label>
            <input type="text" id="activity_name" name="activity_name" required>
        </div>
        <div class="form-group">
            <label for="activity_date">วันที่:</label>
            <input type="datetime-local" id="activity_date" name="activity_date" required>
        </div>
        <div class="form-group">
            <label for="location_id">สถานที่เล่นกีฬา:</label>
            <select id="location_id" name="location_id" required>
                <option value="">กรุณาเลือกสถานที่เล่นกีฬา</option>
                <?php
                $sql = "SELECT location_id, location_name FROM location";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='".$row['location_id']."'>".$row['location_name']."</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sport_id">กีฬา:</label>
            <select id="sport_id" name="sport_id" required>
                <option value="">กรุณาเลือกข้อมูลกีฬา</option>
                <?php
                $sql = "SELECT sport_id, sport_name FROM sport";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='".$row['sport_id']."'>".$row['sport_name']."</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="user_id">ผู้ใช้งาน:</label>
            <select id="user_id" name="user_id" required>
                <option value="">กรุณาเลือกข้อมูลผู้ใช้งาน</option>
                <?php
                $sql = "SELECT user_id, user_name FROM user_information";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='".$row['user_id']."'>".$row['user_name']."</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn-submit">บันทึก</button>
    </form>

    <h2>รายการ</h2>

    <?php
    $sql = "SELECT a.activity_id, a.activity_name, a.activity_date, l.location_name, s.sport_name, u.user_name, a.location_id, a.sport_id, a.user_id
            FROM activity a
            LEFT JOIN location l ON a.location_id = l.location_id
            LEFT JOIN sport s ON a.sport_id = s.sport_id
            LEFT JOIN user_information u ON a.user_id = u.user_id
            WHERE a.status = 'active'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>รหัสกิจกรรม</th><th>ชื่อ</th><th>วันที่</th><th>สถานที่เล่นกีฬา</th><th>กีฬา</th><th>ผู้ใช้งาน</th><th>การดำเนินการ</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["activity_id"]."</td><td>".$row["activity_name"]."</td><td>".$row["activity_date"]."</td><td>".$row["location_name"]."</td><td>".$row["sport_name"]."</td><td>".$row["user_name"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editActivity(\"".$row["activity_id"]."\", \"".$row["activity_name"]."\", \"".$row["activity_date"]."\", \"".$row["location_id"]."\", \"".$row["sport_id"]."\", \"".$row["user_id"]."\")'>แก้ไข</button>
                <a class='btn btn-delete' href='activity.php?delete=".$row["activity_id"]."'>ลบ</a>
                <a class='btn btn-suspend' href='activity.php?suspend=".$row["activity_id"]."'>ระงับ</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editActivity(activity_id, activity_name, activity_date, location_id, sport_id, user_id) {
        document.getElementById('activity_id').value = activity_id;
        document.getElementById('activity_name').value = activity_name;
        document.getElementById('activity_date').value = activity_date;
        document.getElementById('location_id').value = location_id;
        document.getElementById('sport_id').value = sport_id;
        document.getElementById('user_id').value = user_id;
    }
    </script>

</div>

</body>
</html>
