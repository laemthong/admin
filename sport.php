<?php
include 'config.php';

$message = '';
$error = '';

// Function to generate the next sport_id
function getNextSportId($conn) {
    $sql = "SELECT sport_id FROM sport ORDER BY sport_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['sport_id'], 1) + 1;
        return 's' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 's001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sport_id = $_POST["sport_id"] ?? '';
    $sport_name = $_POST["sport_name"] ?? '';

    if (!empty($sport_id)) {
        // Update existing record
        $sql = "UPDATE sport SET sport_name='$sport_name' WHERE sport_id='$sport_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Generate new sport_id
        $sport_id = getNextSportId($conn);

        // Insert new record
        $sql = "INSERT INTO sport (sport_id, sport_name) VALUES ('$sport_id', '$sport_name')";
        if ($conn->query($sql) === TRUE) {
            $message = "New sport created successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

if (isset($_GET['delete'])) {
    $sport_id = $_GET['delete'];
    $sql = "DELETE FROM sport WHERE sport_id='$sport_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Sport deleted successfully";
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
    <title>Sport Management</title>
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
        .form-group input {
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
    <h2>Sport Management</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="sport.php">
        <input type="hidden" id="sport_id" name="sport_id">
        <div class="form-group">
            <label for="sport_name">Sport Name:</label>
            <input type="text" id="sport_name" name="sport_name" required>
        </div>
        <button type="submit" class="btn-submit">Save</button>
    </form>

    <h2>Sport List</h2>

    <?php
    $sql = "SELECT sport_id, sport_name FROM sport";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table><tr><th>Sport ID</th><th>Sport Name</th><th>Actions</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["sport_id"]."</td><td>".$row["sport_name"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editSport(\"".$row["sport_id"]."\", \"".$row["sport_name"]."\")'>Edit</button>
                <a class='btn btn-delete' href='sport.php?delete=".$row["sport_id"]."'>Delete</a>
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

    <script>
    function editSport(sport_id, sport_name) {
        document.getElementById('sport_id').value = sport_id;
        document.getElementById('sport_name').value = sport_name;
    }
    </script>

</div>

</body>
</html>
