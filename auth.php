<?php

include 'fbbot.php';

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function approve($key)
{
	global $conn;
	global $database;
	global $bot;
	
	$sql = "SELECT * FROM ".$database.".pending";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       if ($row['key'] == $key)
	   {
		   $ms_id_del = $row['ms_id'];
	   add_client($row['ms_id'],$row['name']);
	   }
    }
} else {
 die("No Data found");
}
$sql8 = "DELETE FROM `".$database."`.`pending` WHERE (`key` = '".$key."') and (`ms_id` = '".$ms_id_del."')";
$rr = mysqli_query($conn, $sql8) or die($sql8);
reg_conf($ms_id_del);
}

function remove($key)
{
	global $conn;
	global $database;
	global $bot;
	
	$sql = "SELECT * FROM ".$database.".pending";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       if ($row['key'] == $key)
	   {
		   $ms_id_del = $row['ms_id'];
	   rem_client($row['ms_id']);
	   }
    }
} else {
 die("No Data found");
}
$sql8 = "DELETE FROM `".$database."`.`pending` WHERE (`key` = '".$key."') and (`ms_id` = '".$ms_id_del."')";
$rr = mysqli_query($conn, $sql8) or die($sql8);
rem_conf($ms_id_del);
}










if (isset($_POST['sub_key'])) 
{
approve($_POST['sub_key']);

}
if (isset($_POST['sub_key2'])) 
{
remove($_POST['sub_key2']);

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






mysqli_close ($conn);
?>

<html>
<body> 
 
<form action= "auth.php" method= "POST"> 
 
<p>Login <input type= "text" name= "sub_key"> </p> 
<p>Logout <input type= "text" name= "sub_key2"> </p> 

<input type= "submit" value= "Send"> 
 
</body>
</html> 