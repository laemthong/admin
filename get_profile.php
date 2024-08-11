<?php
header('Content-Type: application/json');

include 'config.php'; // ไฟล์ config.php นี้ควรมีการเชื่อมต่อฐานข้อมูล

$user_id = $_GET['user_id'];

$sql = "SELECT user_name, user_id, user_text FROM user_information WHERE user_id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(array("message" => "No user found"));
}

$conn->close();
?>
