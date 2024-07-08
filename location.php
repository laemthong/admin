<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next location_id
function getNextLocationId($conn) {
    $sql = "SELECT location_id FROM location ORDER BY location_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['location_id'], 1) + 1;
        return 'l' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 'l001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location_id = $_POST["location_id"] ?? '';
    $location_name = $_POST["location_name"] ?? '';
    $location_time = $_POST["location_time"] ?? '';
    $location_photo = $_POST["location_photo"] ?? '';
    $location_rules = $_POST["location_rules"] ?? '';
    $location_map = $_POST["location_map"] ?? '';

    if (!empty($location_id)) {
        // Update existing record
        $sql = "UPDATE location SET location_name='$location_name', location_time='$location_time', location_photo='$location_photo', location_rules='$location_rules', location_map='$location_map' WHERE location_id='$location_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Generate new location_id
        $location_id = getNextLocationId($conn);

        // Insert new record
        $sql = "INSERT INTO location (location_id, location_name, location_time, location_photo, location_rules, location_map) 
                VALUES ('$location_id', '$location_name', '$location_time', '$location_photo', '$location_rules', '$location_map')";
        if ($conn->query($sql) === TRUE) {
            $message = "New location created successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if (isset($_GET['delete'])) {
    $location_id = $_GET['delete'];
    $sql = "DELETE FROM location WHERE location_id='$location_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Location deleted successfully";
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
    <title>Location Management</title>
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
    <a href="location.php">Location Management</a>
    <a href="activity.php">Activity Management</a>
    <a href="sport_type.php">Sport Type Management</a>
    <a href="sport_type_in_location.php">Sport Type in Location Management</a>
    <a href="member_in_activity.php">Member in Activity Management</a>
    <!-- Add more links as needed -->
</div>

<div class="container">
    <h2>Location Management</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="location.php">
        <input type="hidden" id="location_id" name="location_id">
        <div class="form-group">
            <label for="location_name">Location Name:</label>
            <input type="text" id="location_name" name="location_name" required>
        </div>
        <div class="form-group">
            <label for="location_time">Location Time:</label>
            <input type="datetime-local" id="location_time" name="location_time" required>
        </div>
        <div class="form-group">
            <label for="location_photo">Location Photo:</label>
            <input type="text" id="location_photo" name="location_photo" required>
        </div>
        <div class="form-group">
            <label for="location_rules">Location Rules:</label>
            <input type="text" id="location_rules" name="location_rules" required>
        </div>
        <div class="form-group">
            <label for="location_map">Location Map:</label>
            <input type="text" id="location_map" name="location_map" required>
        </div>
        <button type="submit" class="btn-submit">Save</button>
    </form>

    <h2>Location List</h2>

    <?php
    $sql = "SELECT location_id, location_name, location_time, location_photo, location_rules, location_map FROM location";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>Location ID</th><th>Name</th><th>Time</th><th>Photo</th><th>Rules</th><th>Map</th><th>Actions</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["location_id"]."</td><td>".$row["location_name"]."</td><td>".$row["location_time"]."</td><td>".$row["location_photo"]."</td><td>".$row["location_rules"]."</td><td>".$row["location_map"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editLocation(\"".$row["location_id"]."\", \"".$row["location_name"]."\", \"".$row["location_time"]."\", \"".$row["location_photo"]."\", \"".$row["location_rules"]."\", \"".$row["location_map"]."\")'>Edit</button>
                <a class='btn btn-delete' href='location.php?delete=".$row["location_id"]."'>Delete</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editLocation(location_id, location_name, location_time, location_photo, location_rules, location_map) {
        document.getElementById('location_id').value = location_id;
        document.getElementById('location_name').value = location_name;
        document.getElementById('location_time').value = location_time;
        document.getElementById('location_photo').value = location_photo;
        document.getElementById('location_rules').value = location_rules;
        document.getElementById('location_map').value = location_map;
    }
    </script>

</div>

</body>
</html>
