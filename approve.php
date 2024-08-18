<?php
include 'config.php';

$message = '';
$error = '';

if (isset($_POST['approve']) || isset($_POST['reject'])) { //ส่งข้อมูลจากฟอร์มด้วยปุ่ม approve หรือ reject ผ่าน POST หรือไม่
    $location_id = $_POST['location_id'];
    $status = isset($_POST['approve']) ? 'approved' : 'rejected';

    $sql = "UPDATE location SET status='$status' WHERE location_id='$location_id'";

    if ($conn->query($sql) === TRUE) {
        $message = "อัปเดตสถานะการอนุมัติเรียบร้อยแล้ว";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}
        //ใช้ GROUP_CONCAT เพื่อรวมชื่อของประเภทกีฬา (type_name) เป็นสตริงที่คั่นด้วยเครื่องหมาย ,
$sql = "SELECT l.*, GROUP_CONCAT(s.type_name SEPARATOR ', ') as type_names 
        FROM location l 
        LEFT JOIN sport_type s ON FIND_IN_SET(s.type_id, l.type_id) > 0
        WHERE l.status='pending'
        GROUP BY l.location_id";  
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติสถานที่</title>
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
        .btn-container {
            display: inline-block;
            white-space: nowrap;
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
        .btn-approve {
            background: #2ecc71;
        }
        .btn-reject {
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
    <h2>อนุมัติสถานที่</h2>

    <?php
    if ($message) { echo "<div class='message'>$message</div>"; }
    if ($error) { echo "<div class='error'>$error</div>"; }
    ?>

    <table>
        <tr>
            <th>ชื่อสถานที่</th>
            <th>เวลาเปิด - ปิด</th>
            <th>ละติจูด</th>
            <th>ลองจิจูด</th>
            <th>รูปภาพ</th>
            <th>ประเภทสนามกีฬา</th>
            <th>การดำเนินการ</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['location_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['location_time']) . "</td>";
                echo "<td>" . htmlspecialchars($row['latitude']) . "</td>";
                echo "<td>" . htmlspecialchars($row['longitude']) . "</td>";
                echo "<td><img src='" . htmlspecialchars($row['location_photo']) . "' width='100'></td>";
                echo "<td>" . htmlspecialchars($row['type_names']) . "</td>";
                echo "<td class='btn-container'>
                        <form method='post' action=''>
                            <input type='hidden' name='location_id' value='" . htmlspecialchars($row['location_id']) . "'>
                            <button type='submit' name='approve' class='btn btn-approve'>อนุมัติ</button>
                            <button type='submit' name='reject' class='btn btn-reject'>ไม่อนุมัติ</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>ไม่มีสถานที่ที่รอการอนุมัติ</td></tr>";
        }
        ?>
    </table>

</div>

</body>
</html>

<?php
$conn->close();
?>
