<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next type_in_location_id
function getNextTypeInLocationId($conn) {
    $sql = "SELECT type_in_location_id FROM sport_type_in_location ORDER BY type_in_location_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['type_in_location_id'], 1) + 1;
        return 'L' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 'L001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_in_location_id = $_POST["type_in_location_id"] ?? '';
    $location_id = $_POST["location_id"] ?? '';
    $type_id = $_POST["type_id"] ?? '';

    if (!empty($type_in_location_id)) {
        // Update existing record
        $sql = "UPDATE sport_type_in_location SET location_id='$location_id', type_id='$type_id' WHERE type_in_location_id='$type_in_location_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "แก้ไขข้อมูลสำเร็จ";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Check for duplicate location_id and type_id
        $sql = "SELECT * FROM sport_type_in_location WHERE location_id='$location_id' AND type_id='$type_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $error = "ข้อมูลซ้ำกรุณาเลือกข้อมูลใหม่";
        } else {
            // Generate new type_in_location_id
            $type_in_location_id = getNextTypeInLocationId($conn);

            // Insert new record
            $sql = "INSERT INTO sport_type_in_location (type_in_location_id, location_id, type_id) 
                    VALUES ('$type_in_location_id', '$location_id', '$type_id')";
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
    $type_in_location_id = $_GET['delete'];
    $sql = "DELETE FROM sport_type_in_location WHERE type_in_location_id='$type_in_location_id'";
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
    <title>ข้อมูลประเภทสนามกีฬา</title>
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
    <a href="sport_in_type.php">ข้อมูลกีฬาในสนาม</a>
</div>
</div>

<div class="container">
    <h2>ข้อมูลประเภทสนามกีฬา</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="sport_type_in_location.php">
        <input type="hidden" id="type_in_location_id" name="type_in_location_id">
        <div class="form-group">
            <label for="location_id">สถานที่เล่นกีฬา:</label>
            <select id="location_id" name="location_id" required>
                <option value="">กรุณาเลือกสถานที่เล่นกีฬา</option>
                <?php
                $sql = "SELECT location_id, location_name FROM location";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['location_id'] . "'>" . $row['location_name'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="type_id">ประเภทกีฬา:</label>
            <select id="type_id" name="type_id" required>
                <option value="">กรุณาเลือกประเภทกีฬา</option>
                <?php
                $sql = "SELECT type_id, type_name FROM sport_type";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['type_id'] . "'>" . $row['type_name'] . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn-submit">บันทึก</button>
    </form>

    <h2>รายการ</h2>

    <?php
    $sql = "SELECT s.type_in_location_id, l.location_name, t.type_name 
            FROM sport_type_in_location s
            JOIN location l ON s.location_id = l.location_id
            JOIN sport_type t ON s.type_id = t.type_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>รหัสประเภทสนามกีฬา</th><th>สถานที่เล่นกีฬา</th><th>ประเภทกีฬา</th><th>การดำเนินการ</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["type_in_location_id"]."</td><td>".$row["location_name"]."</td><td>".$row["type_name"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editTypeInLocation(\"".$row["type_in_location_id"]."\", \"".$row["location_name"]."\", \"".$row["type_name"]."\")'>แก้ไข</button>
                <a class='btn btn-delete' href='sport_type_in_location.php?delete=".$row["type_in_location_id"]."'>ลบ</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editTypeInLocation(type_in_location_id, location_id, type_id) {
        document.getElementById('type_in_location_id').value = type_in_location_id;
        document.getElementById('location_id').value = location_id;
        document.getElementById('type_id').value = type_id;
    }
    </script>

</div>

</body>
</html>
