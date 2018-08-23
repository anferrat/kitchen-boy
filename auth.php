<?php

include 'fbbot.php';

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT ms_id, name, key, type FROM ".$database.".pending";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       echo  $row["key"].'     '.$row["ms_id"].'     '.$row["name"].'     '.$row["type"];
    }
} else {
 die("No Data found");
}
mysqli_close ($conn);
?>