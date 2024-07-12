<?php
include 'config.php';

$message = '';
$error = '';

<<<<<<< HEAD
// Function to generate the next hashtag_id
function getNextHashtagId($conn) {
    $sql = "SELECT hashtag_id FROM hashtag ORDER BY hashtag_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $lastId = $result->fetch_assoc();
    if ($lastId) {
        $num = (int)substr($lastId['hashtag_id'], 1) + 1;
        return 'H' . str_pad($num, 3, '0', STR_PAD_LEFT);
    } else {
        return 'H001';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hashtag_id = $_POST["hashtag_id"] ?? '';
    $hashtag_message = $_POST["hashtag_message"] ?? '';

    if (!empty($hashtag_id)) {
        // Update existing record
        $sql = "UPDATE hashtag SET hashtag_message='$hashtag_message' WHERE hashtag_id='$hashtag_id'";
        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Check for duplicate hashtag_message
        $sql = "SELECT * FROM hashtag WHERE hashtag_message='$hashtag_message'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $error = "ข้อมูลซ้ำกรุณากรอกใหม่";
        } else {
            // Generate new hashtag_id
            $hashtag_id = getNextHashtagId($conn);

            // Insert new record
            $sql = "INSERT INTO hashtag (hashtag_id, hashtag_message) VALUES ('$hashtag_id', '$hashtag_message')";
            if ($conn->query($sql) === TRUE) {
                $message = "New hashtag created successfully";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $hashtag_id = $_GET['delete'];
    $sql = "DELETE FROM hashtag WHERE hashtag_id='$hashtag_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Hashtag deleted successfully";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
=======
// Insert or Update hashtag
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hashtag_message = $_POST['hashtag_message'];

    // Get the latest hashtag_id and increment it
    $sql = "SELECT hashtag_id FROM hashtag ORDER BY hashtag_id DESC LIMIT 1";
    $result = $conn->query($sql);
    $new_id = 'H001';

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_id = $row['hashtag_id'];
        $num = (int)substr($last_id, 1) + 1;
        $new_id = 'H' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    // Insert new hashtag
    $sql = "INSERT INTO hashtag (hashtag_id, hashtag_message) VALUES ('$new_id', '$hashtag_message')";

    if ($conn->query($sql) === TRUE) {
        $message = "Record saved successfully";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Delete hashtag
if (isset($_GET['delete'])) {
    $hashtag_id = $_GET['delete'];

    $sql = "DELETE FROM hashtag WHERE hashtag_id='$hashtag_id'";

    if ($conn->query($sql) === TRUE) {
        $message = "Record deleted successfully";
>>>>>>> 13a8a9b5c8e16d2c1f7a1ae0e655c881bcb89f45
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
    <title>Hashtag Management</title>
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
<<<<<<< HEAD
    <a href="sport_type_in_location.php">Sport Type in Location Management</a>
    <a href="sport_type.php">Sport Type Management</a>
    <a href="location.php">Location Management</a>
    <a href="activity.php">Activity Management</a>
=======
    <a href="location.php">Location Management</a>
    <a href="activity.php">Activity Management</a>
    <a href="sport_type.php">Sport Type Management</a>
    <a href="sport_type_in_location.php">Sport Type in Location Management</a>
>>>>>>> 13a8a9b5c8e16d2c1f7a1ae0e655c881bcb89f45
    <a href="member_in_activity.php">Member in Activity Management</a>
    <a href="hashtag.php">Hashtag Management</a>
</div>

<div class="container">
    <h2>Hashtag Management</h2>

<<<<<<< HEAD
    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="hashtag.php">
        <input type="hidden" id="hashtag_id" name="hashtag_id">
=======
    <?php
    if ($message) { echo "<div class='message'>$message</div>"; }
    if ($error) { echo "<div class='error'>$error</div>"; }
    ?>

    <form method="POST" action="hashtag.php">
>>>>>>> 13a8a9b5c8e16d2c1f7a1ae0e655c881bcb89f45
        <div class="form-group">
            <label for="hashtag_message">Hashtag Message:</label>
            <input type="text" id="hashtag_message" name="hashtag_message" required>
        </div>
        <button type="submit" class="btn-submit">Save</button>
    </form>

    <h2>Hashtag List</h2>

    <?php
    $sql = "SELECT hashtag_id, hashtag_message FROM hashtag";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
<<<<<<< HEAD
        echo "<table><tr><th>Hashtag ID</th><th>Message</th><th>Actions</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["hashtag_id"]."</td><td>".$row["hashtag_message"]."</td>
            <td>
                <button class='btn btn-edit' onclick='editHashtag(\"".$row["hashtag_id"]."\", \"".$row["hashtag_message"]."\")'>Edit</button>
                <a class='btn btn-delete' href='hashtag.php?delete=".$row["hashtag_id"]."'>Delete</a>
=======
        echo "<table><tr><th>Hashtag ID</th><th>Hashtag Message</th><th>Actions</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["hashtag_id"]."</td><td>".$row["hashtag_message"]."</td>
            <td>
                <a class='btn btn-delete' href='?delete=".$row["hashtag_id"]."'>Delete</a>
>>>>>>> 13a8a9b5c8e16d2c1f7a1ae0e655c881bcb89f45
            </td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>

<<<<<<< HEAD
    <script>
    function editHashtag(hashtag_id, hashtag_message) {
        document.getElementById('hashtag_id').value = hashtag_id;
        document.getElementById('hashtag_message').value = hashtag_message;
    }
    </script>

=======
>>>>>>> 13a8a9b5c8e16d2c1f7a1ae0e655c881bcb89f45
</div>

</body>
</html>
