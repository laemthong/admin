<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next numeric location_id
function getNextLocationId($conn) {
    $sql = "SELECT location_id FROM location ORDER BY location_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)$lastId['location_id'] + 1;
        return $num;
    } else {
        return 1;
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $location_id = $_GET['delete'];
    $sql = "DELETE FROM location WHERE location_id='$location_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "ลบข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location_id = $_POST["location_id"] ?? '';
    $location_name = $_POST["location_name"] ?? '';
    $location_time = $_POST["location_time"] ?? '';
    $location_photo = $_POST["location_photo"] ?? '';
    $location_map = $_POST["location_map"] ?? '';
    $type_ids = $_POST["type_id"] ?? [];
    $type_ids_str = implode(',', $type_ids); // Convert array to comma-separated string

    if (!empty($location_id)) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE location SET location_name=?, location_time=?, location_photo=?, location_map=?, type_id=? WHERE location_id=?");
        $stmt->bind_param("ssssss", $location_name, $location_time, $location_photo, $location_map, $type_ids_str, $location_id);

        if ($stmt->execute()) {
            $message = "แก้ไขข้อมูลสำเร็จ";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Generate new location_id
        $location_id = getNextLocationId($conn);

        // Insert new record
        $stmt = $conn->prepare("INSERT INTO location (location_id, location_name, location_time, location_photo, location_map, type_id, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssss", $location_id, $location_name, $location_time, $location_photo, $location_map, $type_ids_str);

        if ($stmt->execute()) {
            $message = "เพิ่มข้อมูลสำเร็จ";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลสถานที่เล่นกีฬา</title>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f7f6;
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
            color: white;
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
    </style>
    <script>
        function toggleCheckboxes() {
            const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
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
    <h2>ข้อมูลสถานที่เล่นกีฬา</h2>

    <?php if ($message) { echo "<div class='message'>".htmlspecialchars($message)."</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>".htmlspecialchars($error)."</div>"; } ?>

    <form method="POST" action="location.php">
        <input type="hidden" id="location_id" name="location_id">
        <div class="form-group">
            <label for="location_name">ชื่อสถานที่:</label>
            <input type="text" id="location_name" name="location_name" required>
        </div>
        <div class="form-group">
            <label for="location_time">เวลาเปิด - ปิด:</label>
            <input type="text" id="location_time" name="location_time" required>
        </div>
        <div class="form-group">
            <label for="location_photo">รูปภาพ:</label>
            <input type="text" id="location_photo" name="location_photo" required>
        </div>
        <div class="form-group">
            <label for="location_map">ตำแหน่ง:</label>
            <input type="text" id="location_map" name="location_map" required>
        </div>
        <div class="form-group">
            <label for="type_id">ประเภทกีฬา:</label>
            <button type="button" class="btn-select-all" onclick="toggleCheckboxes()">เลือกทั้งหมด</button>
            <div class="checkbox-group">
                <?php
                $sql = "SELECT type_id, type_name FROM sport_type";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<label><input type='checkbox' name='type_id[]' value='" . htmlspecialchars($row['type_id']) . "'>" . htmlspecialchars($row['type_name']) . "</label>";
                }
                ?>
            </div>
        </div>
        <button type="submit" class="btn-submit">บันทึก</button>
    </form>

    <h2>รายการ</h2>

    <?php
    $sql = "SELECT l.location_id, l.location_name, l.location_time, l.location_photo, l.location_map, GROUP_CONCAT(s.type_name SEPARATOR ', ') as type_names 
            FROM location l 
            LEFT JOIN sport_type s ON FIND_IN_SET(s.type_id, l.type_id)
            WHERE l.status = 'approved'
            GROUP BY l.location_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>รหัสสถานที่เล่นกีฬา</th><th>ชื่อ</th><th>เวลาเปิด - ปิด</th><th>รูปภาพ</th><th>ตำแหน่ง</th><th>ประเภทกีฬา</th><th>การดำเนินการ</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".htmlspecialchars($row["location_id"])."</td><td>".htmlspecialchars($row["location_name"])."</td><td>".htmlspecialchars($row["location_time"])."</td><td>".htmlspecialchars($row["location_photo"])."</td><td>".htmlspecialchars($row["location_map"])."</td><td>".htmlspecialchars($row["type_names"])."</td>
            <td class='btn-container'>
                <button class='btn btn-edit' onclick='editLocation(\"".htmlspecialchars($row["location_id"])."\", \"".htmlspecialchars($row["location_name"])."\", \"".htmlspecialchars($row["location_time"])."\", \"".htmlspecialchars($row["location_photo"])."\", \"".htmlspecialchars($row["location_map"])."\", \"".htmlspecialchars($row["type_id"] ?? '')."\")'>แก้ไข</button>
                <a class='btn btn-delete' href='location.php?delete=".htmlspecialchars($row["location_id"])."'>ลบ</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "ไม่มีข้อมูล";
    }
    $conn->close();
    ?>

    <script>
    function editLocation(location_id, location_name, location_time, location_photo, location_map, type_id) {
        document.getElementById('location_id').value = location_id;
        document.getElementById('location_name').value = location_name;
        document.getElementById('location_time').value = location_time;
        document.getElementById('location_photo').value = location_photo;
        document.getElementById('location_map').value = location_map;
        // ดึงค่าที่เลือกให้กลับมาแสดงผลใน checkbox
        const checkboxes = document.querySelectorAll('input[name="type_id[]"]');
        checkboxes.forEach(checkbox => {
            if (type_id.split(',').includes(checkbox.value)) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
    }
    </script>

</div>

</body>
</html>
