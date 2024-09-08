<?php
include 'config.php';

$message = '';
$error = '';

function getNextLocationId($conn) {
    $sql = "SELECT location_id FROM location ORDER BY location_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        return (int)$lastId['location_id'] + 1;
    } else {
        return 1;
    }
}

if (isset($_GET['delete'])) {
    $location_id = $_GET['delete'];
    
    // ลบข้อมูลจากตาราง sport_type_in_location ที่อ้างอิง location_id นี้
    $stmt = $conn->prepare("DELETE FROM sport_type_in_location WHERE location_id=?");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $stmt->close();

    // ลบข้อมูลจากตาราง member_in_activity ที่อ้างอิง activity_id ที่เกี่ยวข้องกับ location นี้
    $stmt = $conn->prepare("DELETE FROM member_in_activity WHERE activity_id IN (SELECT activity_id FROM activity WHERE location_id=?)");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $stmt->close();

    // ลบข้อมูลจากตาราง hashtags_in_activities ที่อ้างอิง activity_id ที่เกี่ยวข้องกับ location นี้
    $stmt = $conn->prepare("DELETE FROM hashtags_in_activities WHERE activity_id IN (SELECT activity_id FROM activity WHERE location_id=?)");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $stmt->close();

    // ลบข้อมูลจากตาราง creator ที่อ้างอิง activity_id ที่เกี่ยวข้องกับ location นี้
    $stmt = $conn->prepare("DELETE FROM creator WHERE activity_id IN (SELECT activity_id FROM activity WHERE location_id=?)");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $stmt->close();
    
    // ลบข้อมูลจากตาราง activity ที่อ้างอิง location_id นี้
    $stmt = $conn->prepare("DELETE FROM activity WHERE location_id=?");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $stmt->close();

    // ลบข้อมูลจากตาราง location
    $stmt = $conn->prepare("DELETE FROM location WHERE location_id=?");
    $stmt->bind_param("i", $location_id);
    if ($stmt->execute()) {
        $message = "ลบข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}


if (isset($_GET['suspend'])) {
    $location_id = $_GET['suspend'];
    $stmt = $conn->prepare("UPDATE location SET status='inactive' WHERE location_id=?");
    $stmt->bind_param("i", $location_id);
    if ($stmt->execute()) {
        $message = "ระงับข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_GET['activate'])) {
    $location_id = $_GET['activate'];
    $stmt = $conn->prepare("UPDATE location SET status='approved' WHERE location_id=?");
    $stmt->bind_param("i", $location_id);
    if ($stmt->execute()) {
        $message = "เปิดใช้งานข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_GET['suspend_sport_type'])) {
    $type_id = $_GET['suspend_sport_type'];
    $stmt = $conn->prepare("UPDATE sport_type SET status='inactive' WHERE type_id=?");
    $stmt->bind_param("i", $type_id);
    if ($stmt->execute()) {
        $message = "ระงับข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_GET['reactivate_sport_type'])) {
    $type_id = $_GET['reactivate_sport_type'];
    $stmt = $conn->prepare("UPDATE sport_type SET status='active' WHERE type_id=?");
    $stmt->bind_param("i", $type_id);
    if ($stmt->execute()) {
        $message = "เปิดใช้งานข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}
// ฟังก์ชันนี้ใช้ในการดึงประเภทกีฬาที่มีสถานะ active จากตาราง sport_type
function getActiveSportTypes($conn) {
    $sql = "SELECT type_id, type_name FROM sport_type WHERE status='active'";
    return $conn->query($sql);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location_id = $_POST["location_id"] ?? '';
    $location_name = $_POST["location_name"] ?? '';
    $location_time = $_POST["opening_time"] . ' - ' . $_POST["closing_time"];
    $latitude = !empty($_POST["latitude"]) ? $_POST["latitude"] : null;
    $longitude = !empty($_POST["longitude"]) ? $_POST["longitude"] : null;
    $type_ids = $_POST["type_id"] ?? [];
    $type_ids_str = implode(',', $type_ids);
    $selected_days = $_POST["day_selection"] ?? [];
    $days_str = implode(',', $selected_days);

    $location_photo = $_FILES["location_photo"]["name"] ?? ''; //$location_photo: ชื่อของไฟล์ที่ผู้ใช้เลือกอัปโหลด
    $target_dir = "uploads/"; //$target_dir: โฟลเดอร์ปลายทางที่ไฟล์จะถูกบันทึกในเซิร์ฟเวอร์
    $target_file = $target_dir . basename($location_photo); //$target_file: ที่อยู่เต็มของไฟล์รวมถึงโฟลเดอร์ปลายทางและชื่อไฟล์
    
    if (!empty($location_photo)) {
        if (!move_uploaded_file($_FILES["location_photo"]["tmp_name"], $target_file)) {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
        }
    }

    if (!$error && !is_null($latitude) && !is_null($longitude)) {
        if (!empty($location_id)) {
            $stmt = $conn->prepare("UPDATE location SET location_name=?, location_time=?, location_photo=?, latitude=?, longitude=?, type_id=?, location_day=? WHERE location_id=?");
            $stmt->bind_param("sssssssi", $location_name, $location_time, $target_file, $latitude, $longitude, $type_ids_str, $days_str, $location_id);
            $stmt->execute();
        } else {
            $location_id = getNextLocationId($conn);
            $stmt = $conn->prepare("INSERT INTO location (location_id, location_name, location_time, location_photo, latitude, longitude, type_id, location_day, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssssssss", $location_id, $location_name, $location_time, $target_file, $latitude, $longitude, $type_ids_str, $days_str);
            $stmt->execute();
        }
        if ($stmt->error) {
            $error = $stmt->error;
        } else {
            $message = "เพิ่มข้อมูลสำเร็จ";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    } else {
        $error = "Latitude or Longitude cannot be empty.";
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
        .btn-reactivate {
            background: #2ecc71;
        }
        .btn-container {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        #location_photo_preview_container {
            display: none;
            margin-top: 10px;
            position: relative;
        }

        #location_photo_preview {
            max-width: 100px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        #remove_photo_btn {
            position: absolute;
            top: 0;
            right: 0;
            background: transparent;
            border: none;
            cursor: pointer;
        }

        #remove_photo_btn img {
            width: 20px;
            height: 20px;
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
        .time-inputs {
    display: flex;
    align-items: center;
    gap: 5px;
}

.time-inputs input[type="time"] {
    width: auto; /* Adjusts the width based on content */
}

    </style>
    <script>
         //ฟังก์ชันนี้ใช้สำหรับสลับสถานะของกลุ่มเช็คบ็อกซ์ทั้งหมดในกลุ่มเดียวกัน
        function toggleCheckboxes() {
            const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');

            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked); //ตรวจสอบว่าเช็คบ็อกซ์ทั้งหมดถูกเลือก (checked) หรือไม่ ถ้าทุกเช็คบ็อกซ์ถูกเลือกจะคืนค่า true ไม่เช่นนั้นจะคืนค่า false
            checkboxes.forEach(checkbox => checkbox.checked = !allChecked); //วนลูปผ่านเช็คบ็อกซ์ทุกตัว และสลับสถานะการเลือกเช็คบ็อกซ์ (ถ้าทุกเช็คบ็อกซ์ถูกเลือก ก็จะยกเลิกการเลือกทั้งหมด, ถ้าไม่ถูกเลือกทั้งหมด ก็จะเลือกทั้งหมด)
        }

        function editLocation(location_id, location_name, location_time, location_photo, latitude, longitude, type_ids) {
            document.getElementById('location_id').value = location_id;
            document.getElementById('location_name').value = location_name;
            document.getElementById('location_time').value = location_time;
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
            
            const checkboxes = document.querySelectorAll('input[name="type_id[]"]');
            const typeIdArray = type_ids.split(','); // Convert the string to an array
            checkboxes.forEach(checkbox => {
                checkbox.checked = typeIdArray.includes(checkbox.value);
            });
        }
    </script>
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
        <br>
        <p>ข้อมูลทั่วไป</p>
    </div>
    
    <div class="menu-group">
        
        <a href="sport_type_in_location.php">ข้อมูลสนามกีฬา</a>
        <a href="activity.php">ข้อมูลกิจกรรม</a>
        <a href="member_in_activity.php">ข้อมูลสมาชิกกิจกรรม</a>
        <a href="profile.php">ข้อมูลโปรไฟล์</a>
    </br>
    <p>การอนุมัติ</p>
    </div>
    <div class="menu-group">
        
    <a href="approve.php">อนุมัติสถานที่</a>
    </div>
    <a href="index.php" class="btn-logout" onclick="return confirm('คุณแน่ใจว่าต้องการออกจากระบบหรือไม่?');">ออกจากระบบ</a>
    
</div>
<div class="container">
    <h2>ข้อมูลสถานที่เล่นกีฬา</h2>

    <?php if ($message) { echo "<div class='message'>".htmlspecialchars($message)."</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>".htmlspecialchars($error)."</div>"; } ?>

    <form method="POST" action="location.php" enctype="multipart/form-data">
        <input type="hidden" id="location_id" name="location_id">
        <div class="form-group">
            <label for="location_name">ชื่อสถานที่:</label>
            <input type="text" id="location_name" name="location_name" required>
        </div>
        <div class="form-group">
    <label for="day_selection">วัน:</label>
    <div class="checkbox-group">
        <label><input type="checkbox" name="day_selection[]" value="1" > จันทร์</label>
        <label><input type="checkbox" name="day_selection[]" value="2" > อังคาร</label>
        <label><input type="checkbox" name="day_selection[]" value="3" > พุธ</label>
        <label><input type="checkbox" name="day_selection[]" value="4" > พฤหัสบดี</label>
        <label><input type="checkbox" name="day_selection[]" value="5" > ศุกร์</label>
        <label><input type="checkbox" name="day_selection[]" value="6" > เสาร์</label>
        <label><input type="checkbox" name="day_selection[]" value="0" > อาทิตย์</label>
    </div>
</div>

<div class="form-group">
    <label for="location_time">เวลาเปิด - ปิด:</label>
    <div class="time-inputs">
        <input type="time" id="opening_time" name="opening_time" required>
        <span> - </span>
        <input type="time" id="closing_time" name="closing_time" required>
    </div>
</div>

        <div class="form-group">
            <label for="location_photo">รูปภาพ:</label>
            <input type="file" id="location_photo" name="location_photo" accept="image/*" onchange="previewFile()">
            <div id="location_photo_preview_container" style="display: none; position: relative;">
                <img id="location_photo_preview" src="" alt="รูปภาพ" width="100">
                <button type="button" id="remove_photo_btn" onclick="removePhoto()" style="position: absolute; top: 0; right: 0; background: transparent; border: none;">
                    <img src="./images/close.png" alt="ลบรูป" style="width: 10px; height: 10px;">
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="latitude">ละติจูด:</label>
            <input type="text" id="latitude" name="latitude" required>
        </div>
        <div class="form-group">
            <label for="longitude">ลองจิจูด:</label>
            <input type="text" id="longitude" name="longitude" required>
        </div>
        <div class="form-group">
            <label for="type_id">ประเภทสนามกีฬา:</label>
            <button type="button" class="btn-select-all" onclick="toggleCheckboxes()">เลือกทั้งหมด</button>
            <div class="checkbox-group">
                <?php
                // Get only active sport types
                $result = getActiveSportTypes($conn);
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
$sql = "SELECT l.location_id, l.location_name, l.location_time, l.location_photo, l.latitude, l.longitude, l.status, l.location_day, l.type_id as type_ids, GROUP_CONCAT(s.type_name SEPARATOR ', ') as type_names 
FROM location l 
LEFT JOIN sport_type s ON FIND_IN_SET(s.type_id, l.type_id)
WHERE l.status != 'pending'
GROUP BY l.location_id";



$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ชื่อ</th><th>วันและเวลาเปิด - ปิด</th><th>รูปภาพ</th><th>ละติจูด</th><th>ลองจิจูด</th><th>ประเภทสนามกีฬา</th><th>การดำเนินการ</th></tr>";
    while($row = $result->fetch_assoc()) {

        //สร้างลิงก์ไปยัง Google Maps โดยใช้ค่าละติจูดและลองจิจูดที่ดึงมาจากฐานข้อมูล
        $latitude = htmlspecialchars($row["latitude"]);
        $longitude = htmlspecialchars($row["longitude"]);
        $mapsLink = "https://www.google.com/maps/place/$latitude,$longitude";


        //แปลงรหัสวัน (0-6) ที่เก็บในฐานข้อมูลให้เป็นชื่อวันภาษาไทย (เช่น 0 แปลงเป็น อาทิตย์) แล้วรวมเข้ากับเวลาที่เปิด-ปิด
        $days = htmlspecialchars($row["location_day"]);
        $daysArray = explode(',', $days);
        $daysReadable = array_map(function($day) {
            switch($day) {
                case '0': return 'อาทิตย์';
                case '1': return 'จันทร์';
                case '2': return 'อังคาร';
                case '3': return 'พุธ';
                case '4': return 'พฤหัสบดี';
                case '5': return 'ศุกร์';
                case '6': return 'เสาร์';
                default: return '';
            }
        }, $daysArray);
        $daysStr = implode(', ', $daysReadable);

        $dayTimeStr = $daysStr . " " . htmlspecialchars($row["location_time"]);

        echo "<tr><td>".htmlspecialchars($row["location_name"])."</td> <td>".$dayTimeStr."</td><td><img src='".htmlspecialchars($row["location_photo"])."' alt='รูปภาพ' width='100'></td><td><a href='$mapsLink' target='_blank'>".htmlspecialchars($latitude)."</a></td><td><a href='$mapsLink' target='_blank'>".htmlspecialchars($longitude)."</a></td><td>".htmlspecialchars($row["type_names"])."</td><td>";
        
        echo "<a class='btn btn-edit' href='#' onclick='editLocation(\"".htmlspecialchars($row["location_id"])."\", \"".htmlspecialchars($row["location_name"])."\", \"".htmlspecialchars($row["location_time"])."\", \"".htmlspecialchars($row["location_photo"])."\", \"".htmlspecialchars($row["latitude"])."\", \"".htmlspecialchars($row["longitude"])."\", \"".htmlspecialchars($row["type_ids"])."\", \"".htmlspecialchars($row["location_day"])."\")'>แก้ไข</a>";

        echo "<a class='btn btn-delete' href='location.php?delete=".htmlspecialchars($row["location_id"])."'>ลบ</a>";
        
        if ($row["status"] == 'inactive') {
            echo "<a class='btn btn-reactivate' href='location.php?activate=".htmlspecialchars($row["location_id"])."'>เปิดใช้งาน</a>";
        } else {
            echo "<a class='btn btn-suspend' href='location.php?suspend=".htmlspecialchars($row["location_id"])."'>ระงับ</a>";
        }
        
        echo "</td></tr>";
    }
    echo "</table>";
} else {
    echo "ไม่มีข้อมูล";
}

    
    $conn->close();
    ?>

    <script>
        //ฟังก์ชัน previewFile() ใช้สำหรับแสดงตัวอย่างรูปภาพที่เลือกอัปโหลด
        function previewFile() {
            const preview = document.getElementById('location_photo_preview');
            const previewContainer = document.getElementById('location_photo_preview_container');
            const file = document.getElementById('location_photo').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
                previewContainer.style.display = 'block';
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
            //ฟังก์ชัน removePhoto() ใช้สำหรับลบรูปภาพที่แสดงในตัวอย่างและรีเซ็ตการเลือกไฟล์
        function removePhoto() {
            const preview = document.getElementById('location_photo_preview');
            const previewContainer = document.getElementById('location_photo_preview_container');
            const input = document.getElementById('location_photo');

            // Clear the input field
            input.value = '';
            // Hide the preview container
            previewContainer.style.display = 'none';
            // Clear the src of the image
            preview.src = '';
        }
                //นี้ใช้ในการสลับสถานะการเลือกเช็คบ็อกซ์ทั้งหมดภายในกลุ่มที่มีชื่อ type_id[] โดยถ้าเช็คบ็อกซ์ทั้งหมดถูกเลือกอยู่แล้ว จะทำการยกเลิกการเลือกทั้งหมด แต่ถ้ามีเช็คบ็อกซ์บางอันหรือทั้งหมดไม่ถูกเลือก จะทำการเลือกเช็คบ็อกซ์ทั้งหมด.
        function toggleCheckboxes() {
        const checkboxes = document.querySelectorAll('.checkbox-group input[name="type_id[]"]'); 
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
    }

    function toggleAllDays() {
        const checkboxes = document.querySelectorAll('.checkbox-group input[name="day_selection[]"]'); //เลือกเช็คบ็อกซ์ทั้งหมดที่อยู่ในกลุ่มที่มีชื่อ day_selection
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);  //ตรวจสอบว่าเช็คบ็อกซ์ทุกตัวถูกเลือก
        checkboxes.forEach(checkbox => checkbox.checked = !allChecked); //สลับสถานะการเลือกของเช็คบ็อกซ์ทั้งหมด
    }


    //ฟังก์ชัน editLocation() ใช้ในการตั้งค่าฟิลด์ต่าง ๆ ในฟอร์มแก้ไขข้อมูลสถานที่ โดยจะแยกเวลาเปิด-ปิดจากสตริง, ตั้งค่าละติจูดและลองจิจูด, ตั้งค่าสถานะเช็คบ็อกซ์สำหรับประเภทสนามกีฬาและวันที่เปิดทำการ และจัดการการแสดงผลของรูปภาพที่เกี่ยวข้อง.
    function editLocation(location_id, location_name, location_time, location_photo, latitude, longitude, type_ids, location_day) {
    document.getElementById('location_id').value = location_id;
    document.getElementById('location_name').value = location_name;

    // แยกเวลาเปิด-ปิด
    const [opening_time, closing_time] = location_time.split(' - ');
    document.getElementById('opening_time').value = opening_time;
    document.getElementById('closing_time').value = closing_time;

    document.getElementById('latitude').value = latitude;
    document.getElementById('longitude').value = longitude;

    const checkboxes = document.querySelectorAll('input[name="type_id[]"]');
    const typeIdArray = type_ids.split(','); // แปลง string ของประเภทเป็น array
    checkboxes.forEach(checkbox => {
        checkbox.checked = typeIdArray.includes(checkbox.value);
    });

    const daysCheckboxes = document.querySelectorAll('input[name="day_selection[]"]');
    const daysArray = location_day.split(','); // แปลง string ของวันเป็น array
    daysCheckboxes.forEach(checkbox => {
        checkbox.checked = daysArray.includes(checkbox.value);
    });

    if (location_photo) {
        document.getElementById('location_photo_preview').src = location_photo;
        document.getElementById('location_photo_preview_container').style.display = 'block'; // แสดงรูปที่มีอยู่ก่อน
    } else {
        document.getElementById('location_photo_preview_container').style.display = 'none'; // ซ่อนหากไม่มีรูปภาพ
    }
}

    </script>

</div>

</body>
</html>