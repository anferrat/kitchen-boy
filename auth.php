<?php

include 'fbbot.php';

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM ".$database.".pending";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       echo  $row["key"].'--------'.$row["ms_id"].'-------'.$row["name"].'---------'.$row["type"]."<br />";
    }
} else {
 die("No Data found");
}

function approve($key)
{
	$sql = "SELECT ms_id, name FROM ".$database.".pending WHERE key=".$key;
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       add_client($row['ms_is'],$row['name']);
    }
} else {
 die("No Data found");
}

}
if (isset($_POST['sub_key']) != '') 
{
approve($_POST['sub_key']) 
}



mysqli_close ($conn);
?>

<html><body> 
 
<form action= "auth.php" method= "POST"> 
 
<p> <input type= "text" name= "sub_key"> </p> 
 

<input type= "submit" value= "Send"> 
 
</body></html> 