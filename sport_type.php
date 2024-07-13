<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next type_id
function getNextTypeId($conn) {
    $sql = "SELECT type_id FROM sport_type ORDER BY type_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['type_id'], 1) + 1;
        return 't' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 't001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type_id = $_POST["type_id"] ?? '';
    $type_name = $_POST["type_name"] ?? '';

    if (!empty($type_id)) {
        // Update existing record
        $sql = "UPDATE sport_type SET type_name='$type_name' WHERE type_id='$type_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Check for duplicate type_name
        $sql = "SELECT * FROM sport_type WHERE type_name='$type_name'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $error = "ข้อมูลซ้ำกรุณากรอกใหม่";
        } else {
            // Generate new type_id
            $type_id = getNextTypeId($conn);

            // Insert new record
            $sql = "INSERT INTO sport_type (type_id, type_name) VALUES ('$type_id', '$type_name')";
            if ($conn->query($sql) === TRUE) {
                $message = "New sport type created successfully";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $type_id = $_GET['delete'];
    $sql = "DELETE FROM sport_type WHERE type_id='$type_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Sport type deleted successfully";
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
    <title>Sport Type Management</title>
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
    <h2>Sport Type Management</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="sport_type.php">
        <input type="hidden" id="type_id" name="type_id">
        <div class="form-group">
            <label for="type_name">Sport Type Name:</label>
            <input type="text" id="type_name" name="type_name" required>
        </div>
        <button type="submit" class="btn-submit">Save</button>
    </form>

    <h2>Sport Type List</h2>

    <?php
    $sql = "SELECT type_id, type_name FROM sport_type";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>Type ID</th><th>Name</th><th>Actions</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["type_id"]."</td><td>".$row["type_name"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editType(\"".$row["type_id"]."\", \"".$row["type_name"]."\")'>Edit</button>
                <a class='btn btn-delete' href='sport_type.php?delete=".$row["type_id"]."'>Delete</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editType(type_id, type_name) {
        document.getElementById('type_id').value = type_id;
        document.getElementById('type_name').value = type_name;
    }
    </script>

</div>

</body>
</html>
