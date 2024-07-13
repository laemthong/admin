<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next member_id
function getNextMemberId($conn) {
    $sql = "SELECT member_id FROM member_in_activity ORDER BY member_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['member_id'], 1) + 1;
        return 'm' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 'm001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST["member_id"] ?? '';
    $activity_id = $_POST["activity_id"] ?? '';
    $user_id = $_POST["user_id"] ?? '';

    if (!empty($member_id)) {
        // Update existing record
        $sql = "UPDATE member_in_activity SET activity_id='$activity_id', user_id='$user_id' WHERE member_id='$member_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Check for duplicate activity_id and user_id
        $sql = "SELECT * FROM member_in_activity WHERE activity_id='$activity_id' AND user_id='$user_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $error = "ข้อมูลซ้ำกรุณาเลือกข้อมูลใหม่";
        } else {
            // Generate new member_id
            $member_id = getNextMemberId($conn);

            // Insert new record
            $sql = "INSERT INTO member_in_activity (member_id, activity_id, user_id) 
                    VALUES ('$member_id', '$activity_id', '$user_id')";
            if ($conn->query($sql) === TRUE) {
                $message = "New member in activity created successfully";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $member_id = $_GET['delete'];
    $sql = "DELETE FROM member_in_activity WHERE member_id='$member_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Member in activity deleted successfully";
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
    <title>Member in Activity Management</title>
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
    <h2>Menu</h2>
    <a href="index.php">User Management</a>
    <a href="sport.php">Sport Management</a>
    <a href="sport_type_in_location.php">Sport Type in Location Management</a>
    <a href="sport_type.php">Sport Type Management</a>
    <a href="location.php">Location Management</a>
    <a href="activity.php">Activity Management</a>
    <a href="member_in_activity.php">Member in Activity Management</a>
    <a href="hashtag.php">Hashtag Management</a>
    <a href="profile.php">Profile Management</a>
</div>

<div class="container">
    <h2>Member in Activity Management</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="member_in_activity.php">
        <input type="hidden" id="member_id" name="member_id">
        <div class="form-group">
            <label for="activity_id">Activity:</label>
            <select id="activity_id" name="activity_id" required>
                <option value="">Select Activity</option>
                <?php
                $sql = "SELECT activity_id, activity_name FROM activity";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['activity_id'] . "'>" . $row['activity_id'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="user_id">User:</label>
            <select id="user_id" name="user_id" required>
                <option value="">Select User</option>
                <?php
                $sql = "SELECT user_id, user_name FROM user_information";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['user_id'] . "'>" . $row['user_id'] . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn-submit">Save</button>
    </form>

    <h2>Member in Activity List</h2>

    <?php
    $sql = "SELECT m.member_id, a.activity_name, u.user_name 
            FROM member_in_activity m
            JOIN activity a ON m.activity_id = a.activity_id
            JOIN user_information u ON m.user_id = u.user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>Member ID</th><th>Activity</th><th>User</th><th>Actions</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["member_id"]."</td><td>".$row["activity_name"]."</td><td>".$row["user_name"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editMemberInActivity(\"".$row["member_id"]."\", \"".$row["activity_name"]."\", \"".$row["user_name"]."\")'>Edit</button>
                <a class='btn btn-delete' href='member_in_activity.php?delete=".$row["member_id"]."'>Delete</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editMemberInActivity(member_id, activity_id, user_id) {
        document.getElementById('member_id').value = member_id;
        document.getElementById('activity_id').value = activity_id;
        document.getElementById('user_id').value = user_id;
    }
    </script>

</div>

</body>
</html>
