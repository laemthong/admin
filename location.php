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

// Handle suspend request
if (isset($_GET['suspend'])) {
    $location_id = $_GET['suspend'];
    $sql = "UPDATE location SET status='inactive' WHERE location_id='$location_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "ระงับข้อมูลสำเร็จ";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle activate request
if (isset($_GET['activate'])) {
    $location_id = $_GET['activate'];
    $sql = "UPDATE location SET status='active' WHERE location_id='$location_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "เปิดใช้งานข้อมูลสำเร็จ";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle sport_type suspend and reactivate requests
if (isset($_GET['suspend_sport_type'])) {
    $type_id = $_GET['suspend_sport_type'];
    $sql = "UPDATE sport_type SET status='inactive' WHERE type_id='$type_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "ระงับข้อมูลสำเร็จ";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

if (isset($_GET['reactivate_sport_type'])) {
    $type_id = $_GET['reactivate_sport_type'];
    $sql = "UPDATE sport_type SET status='active' WHERE type_id='$type_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "เปิดใช้งานข้อมูลสำเร็จ";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Function to get active sport types
function getActiveSportTypes($conn) {
    $sql = "SELECT type_id, type_name FROM sport_type WHERE status='active'";
    $result = $conn->query($sql);
    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location_id = $_POST["location_id"] ?? '';
    $location_name = $_POST["location_name"] ?? '';
    $location_time = $_POST["location_time"] ?? '';
    $latitude = !empty($_POST["latitude"]) ? $_POST["latitude"] : null;
    $longitude = !empty($_POST["longitude"]) ? $_POST["longitude"] : null;
    $type_ids = $_POST["type_id"] ?? [];
    $type_ids_str = implode(',', $type_ids); // Convert array to comma-separated string

    // Upload photo
    $location_photo = $_FILES["location_photo"]["name"] ?? '';
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($location_photo);
    
    if (!empty($location_photo)) {
        if (!move_uploaded_file($_FILES["location_photo"]["tmp_name"], $target_file)) {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
        }
    }

    if (!$error && !is_null($latitude) && !is_null($longitude)) {
        if (!empty($location_id)) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE location SET location_name=?, location_time=?, location_photo=?, latitude=?, longitude=?, type_id=? WHERE location_id=?");
            $stmt->bind_param("sssssss", $location_name, $location_time, $target_file, $latitude, $longitude, $type_ids_str, $location_id);
            $stmt->execute();
        } else {
            // Generate new location_id
            $location_id = getNextLocationId($conn);

            // Insert new record
            $stmt = $conn->prepare("INSERT INTO location (location_id, location_name, location_time, location_photo, latitude, longitude, type_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sssssss", $location_id, $location_name, $location_time, $target_file, $latitude, $longitude, $type_ids_str);
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
    </style>
    <script>
        function toggleCheckboxes() {
            const checkboxes = document.querySelectorAll('.checkbox-group input[type="checkbox"]');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
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
            <select id="day_selection" name="day_selection" onchange="setDefaultTime()">
                <option value="">กรุณาเลือกวัน</option>
                <option value="1">จันทร์</option>
                <option value="2">อังคาร</option>
                <option value="3">พุธ</option>
                <option value="4">พฤหัสบดี</option>
                <option value="5">ศุกร์</option>
                <option value="6">เสาร์</option>
                <option value="0">อาทิตย์</option>
            </select>
        </div>
        <div class="form-group">
            <label for="location_time">เวลาเปิด - ปิด:</label>
            <input type="text" id="location_time" name="location_time" required>
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
     $sql = "SELECT l.location_id, l.location_name, l.location_time, l.location_photo, l.latitude, l.longitude, l.status, l.type_id, GROUP_CONCAT(s.type_name SEPARATOR ', ') as type_names 
     FROM location l 
     LEFT JOIN sport_type s ON FIND_IN_SET(s.type_id, l.type_id)
     GROUP BY l.location_id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
 echo "<table><tr><th>ชื่อ</th><th>เวลาเปิด - ปิด</th><th>รูปภาพ</th><th>ละติจูด</th><th>ลองจิจูด</th><th>ประเภทสนามกีฬา</th><th>การดำเนินการ</th></tr>";
 while($row = $result->fetch_assoc()) {
     $latitude = htmlspecialchars($row["latitude"]);
     $longitude = htmlspecialchars($row["longitude"]);
     $mapsLink = "https://www.google.com/maps/place/$latitude,$longitude";
     echo "<tr><td>".htmlspecialchars($row["location_name"])."</td><td>".htmlspecialchars($row["location_time"])."</td><td><img src='".htmlspecialchars($row["location_photo"])."' alt='รูปภาพ' width='100'></td><td><a href='$mapsLink' target='_blank'>".htmlspecialchars($latitude)."</a></td><td><a href='$mapsLink' target='_blank'>".htmlspecialchars($longitude)."</a></td><td>".htmlspecialchars($row["type_names"])."</td><td>";

     echo "<button class='btn btn-edit' onclick='editLocation(\"".htmlspecialchars($row["location_id"])."\", \"".htmlspecialchars($row["location_name"])."\", \"".htmlspecialchars($row["location_time"])."\", \"".htmlspecialchars($row["location_photo"])."\", \"".htmlspecialchars($row["latitude"])."\", \"".htmlspecialchars($row["longitude"])."\", \"".htmlspecialchars($row["type_id"])."\")'>แก้ไข</button>";
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
        function setDefaultTime() {
            const locationTimeInput = document.getElementById('location_time');
            const daySelection = document.getElementById('day_selection').value;

            let timeString = '';

            if (daySelection >= 1 && daySelection <= 5) {
                // Monday to Friday
                timeString = '05:00 - 21:00';
            } else if (daySelection == 0 || daySelection == 6) {
                // Saturday and Sunday
                timeString = '06:00 - 20:30';
            }

            locationTimeInput.value = timeString;
        }

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
