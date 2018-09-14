<?php
include 'fbbot.php';

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "SELECT * FROM ".$database.".index";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $messenger_id[$o] = $row["messenger_id"];
		$names[$o] = $row["name"];
		$order_numbers[$o] = $row["order_number"];
		$keys[$o] = $row["key"];
		$o++;
    }
} else {
 die("No Data found");
}

$order_numbers=order_push($order_numbers);

for ($i=0;$i<count($order_numbers);$i++)
{
$sql = "UPDATE `".$database."`.`index` SET `order_number` = '".$order_numbers[$i]."' WHERE (`key` = '".$keys[$i]."')";

$rr = mysqli_query($conn, $sql) or die($sql);
}    

if ( date('D',time()-500) == 'Sun')
{
	
	
	
$sql = "SELECT * FROM ".$database.".washroom_basement";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $wash_b_names[$o] = $row["name"];
		$wash_b_orders[$o] = $row["order"];
		$keys_b[$o] = $row["id"];
		$o++;
    }
} else {
 die("No Data found");
}

$sql = "SELECT * FROM ".$database.".washroom_upstairs";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $wash_u_names[$o] = $row["name"];
		$wash_u_orders[$o] = $row["order"];
		$keys_u[$o] = $row["id"];
		$o++;
    }
} else {
 die("No Data found");
}

$wash_u_orders=order_push($wash_u_orders);
$wash_b_orders=order_push($wash_b_orders);

for ($i=0;$i<count($wash_u_orders);$i++)
{
$sql = "UPDATE `".$database."`.`washroom_upstairs` SET `order` = '".$wash_u_orders[$i]."' WHERE (`id` = '".$keys_u[$i]."')";

$rr = mysqli_query($conn, $sql) or die($sql);
}   
for ($i=0;$i<count($wash_b_orders);$i++)
{
$sql = "UPDATE `".$database."`.`washroom_basement` SET `order` = '".$wash_b_orders[$i]."' WHERE (`id` = '".$keys_b[$i]."')";

$rr = mysqli_query($conn, $sql) or die($sql);
}  


}

mysqli_close ($conn);
?>