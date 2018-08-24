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


mysqli_close ($conn);
?>