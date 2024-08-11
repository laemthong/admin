<?php
header('Content-Type: application/json');

include 'config.php'; // ไฟล์ config.php นี้ควรมีการเชื่อมต่อฐานข้อมูล

$user_id = $_POST['user_id'];
$user_name = $_POST['user_name'];
$user_text = $_POST['user_text'];

// อัปเดตข้อมูล user_text ใหม่
$sql = "UPDATE user_information SET user_name='$user_name', user_text='$user_text' WHERE user_id='$user_id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(array("message" => "Profile updated successfully"));
} else {
    echo json_encode(array("message" => "Error updating profile: " . $conn->error));
}

$conn->close();
?>
