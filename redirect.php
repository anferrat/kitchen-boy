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
	   add_client($row['ms_id'],$row['name'],$row['location']);
	   }
    }
} 
$sql8 = "DELETE FROM `".$database."`.`pending` WHERE (`key` = '".$key."')";
$rr = mysqli_query($conn, $sql8);
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
}

$sql8 = "DELETE FROM `".$database."`.`pending` WHERE (`key` = '".$key."')";
$rr = mysqli_query($conn, $sql8);
rem_conf($ms_id_del);
}

function del($key)
{
	global $conn;
	global $database;
	
$sql8 = "DELETE FROM `".$database."`.`pending` WHERE (`key` = '".$key."')";
$rr = mysqli_query($conn, $sql8);
}



if (isset($_POST['login'])) 
{
approve($_POST['login']);
}

if (isset($_POST['logout'])) 
{
remove($_POST['logout']);
}

if (isset($_POST['remove'])) 
{
del($_POST['remove']);

}

mysqli_close ($conn);

echo '<script>
  location.href= "/auth.php";
</script>';
?>