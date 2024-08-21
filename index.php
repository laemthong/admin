<?php
session_start();
include 'config.php'; // เชื่อมต่อกับฐานข้อมูล

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // เข้ารหัสรหัสผ่านถ้าจำเป็น
    // $password = md5($password); 

    // คำสั่ง SQL เพื่อตรวจสอบผู้ใช้และรหัสผ่านในฐานข้อมูล admin
    $sql = "SELECT * FROM admin WHERE admin_name='$username' AND admin_password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['admin_login'] = $username;
        header("Location: user.php"); // เปลี่ยนเส้นทางไปยังหน้าแดชบอร์ด
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nanum+Gothic|Quicksand" rel="stylesheet">
    <style>
        .container {
            width: 100%;
            height: 100%;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            background: url('./images/background.jpg') no-repeat !important;
            background-size: cover !important;
        }

        .content-form {
            width: 400px;
            height: 500px;
            padding: 90px 10px 10px 10px;
            background-color: #fbfbfb;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        .barra-icons {
            margin-bottom: 20px;
        }

        .barra-icons img {
    max-width: 200px; /* ปรับขนาดตามต้องการ */
    height: auto; /* ให้รูปภาพคงอัตราส่วนเดิมไว้ */
    margin: 0 auto; /* จัดกึ่งกลางใน div */
    display: block; /* ทำให้รูปภาพเป็น block element เพื่อให้ margin มีผล */
}

        .marca {
            font-family: 'Quicksand', sans-serif;
            font-size: 25px;
            margin-top: 8px;
        }

        .input-text {
            width: 90%;
            height: 40px;
            margin: 15px 10px 0px 10px;
            padding: 5px 2px 5px 5px;
            font-family: 'Nanum Gothic', sans-serif;
            font-size: 20px;
            border: 1px solid #c9c9c9;
            border-radius: 5px;
        }

        .button {
            width: 92%;
            height: 50px;
            margin: 15px 10px 0px 10px;
            padding: 5px 2px 5px 5px;
            font-family: 'Nanum Gothic', sans-serif;
            font-size: 20px;
            border: 1px solid #a1a1a1;
            border-radius: 5px;
            background-color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }

        .button:first-child {
            background-color: #20b2aa;
            color: #fff;
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="content-form">
            <div class="barra-icons">
                <img src="./images/logo.png" alt="Logo">
                
            </div>
           
            <!-- <div class="marca">ผู้ดูแลระบบ</div> -->
            
            <form action="index.php" method="POST">
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <input type="text" class="input-text" name="username" placeholder="User" />
                <input type="password" class="input-text" name="password" placeholder="Password" />
                
                <button class="button" type="submit">SIGN IN</button>
                
            </form>
        </div>
    </div>
</body>
</html>