<?php
$pi2 = '{"object":"page","entry":[{"id":"1791306587664581","time":1534988947184,"messaging":[{"sender":{"id":"2170490766313202"},"recipient":{"id":"1791306587664581"},"timestamp":1534988539102,"message":{"mid":"x5H7P1urGQClq3UqwO2-56k-EwydcvFMcwgoN0TZpwhIHWusvaZhm7NEcQlZtQhvQ8FDJcgUnufx3TIPNDrDKQ","seq":87870,"text":"Hu"}}]}]}';
$pi = '{"object":"page","entry":[{"id":"1791306587664581","time":1534988539420,"messaging":[{"sender":{"id":"1791306587664581"},"recipient":{"id":"2170490766313202"},"timestamp":1534988073579,"message":{"is_echo":true,"app_id":1935046733459562,"mid":"P5yMBfOtxnp36A4xgcYZtqk-EwydcvFMcwgoN0TZpwi3MDfye3xdCYCWOzZ5gtIVxjEbTuYXOJRcW03Aom3Lrw","seq":87143,"text":"Hi there!"}}]}]}';
$data = json_decode($pi,true);

//$names = array('Andrei','Sergei','Anir','Olga');
//$messenger_id = array('111111111','22222222222222','33333333333','4444444444444');
//$order_numbers = array('1','3','4','2');

$url = getenv('JAWSDB_URL');
$dbparts = parse_url($url);

$hostname = $dbparts['host'];
$username = $dbparts['user'];
$password = $dbparts['pass'];
$database = ltrim($dbparts['path'],'/');


// Create connection
$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT messenger_id, name, order_number FROM ".$database.".index";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $messenger_id[$o] = $row["messenger_id"];
		$names[$o] = $row["name"];
		$order_numbers[$o] = $row["order_number"];
		$o++;
    }
} else {
 die("No Data found");
}



function mini($arr)
{
	$k = 0;
	for ($i=0;$i<count($arr);$i++)
	{
		if ($arr[$i]<$arr[$k])
		{
			$k=$i;
		}
	}
	
	return $k;
}

function order_push ($ord)
{
	$ord_res = array();
	for($i=0;$i<count($ord);$i++)
	{
		if ($ord[$i] + 1 <= count($ord))
		{
			$ord_res[$i] = $ord[$i] + 1;
		}
		else
		{
			$ord_res[$i] = $ord[$i] + 1 - count($ord);
		}
	}
	return $ord_res;
}

function sch_gen ($days)
{
	$sch = array (
events => array(
array(
title =>'',
start =>'',
end =>''))
);
	$n = count($names);
	$t = time();
	global $names;
	global $messenger_id;
	global $order_numbers;
	
	$names_gen = $names;
	$order_gen = $order_numbers;
	
for ($i=0;$i<$days;$i++)
{
	$sch['events'][$i]['title'] = $names_gen[mini($order_gen)];
	$sch['events'][$i]['start'] = substr(date("c",$t),0,10);
	$sch['events'][$i]['end'] = substr(date("c",$t),0,10);
    $t = $t + 86400;
	$order_gen = order_push($order_gen);
}
$sch['header']['left']='title';
$sch['header']['center']='';
$sch['header']['right']='';
	return $sch;
}

function add_client($ms_id,$new_name)
{
	global $names;
	global $messenger_id;
	global $order_numbers;
	global $hostname;
	global $username;
	global $password;
	global $database;
	global $conn;
$stat;
for ($i=0;$i<count($names);$i++)
{
	if ($names[$i] == $new_name)
	{
		$rep_names = true;
	}
}
if (!$rep_names)
{
	$new_order = count($names)+1;
	
$sql2 = "INSERT INTO ".$database.".index (messenger_id, name, order_number) VALUES ('".$ms_id."', '".$new_name."', ".$new_order.")";
if (mysqli_query($conn, $sql2)) {
    $stat=1;
} else {
    $stat=0;
}
}
	return $stat;
}
function id_from_msid ($ms_id)
{
	global $messenger_id;
	$res = null;
	for($i=0;$i<count($messenger_id);$i++)
	{
		if ($messenger_id[$i] == $ms_id)
		{
			$res = $i;
			break;
		}
	}
	return $res;
}

function rem_client($ms_id)
{
	global $names;
	global $messenger_id;
	global $order_numbers;
	global $hostname;
	global $username;
	global $password;
	global $database;
	global $conn;
	$stat =1;
	if (id_from_msid($ms_id) != null)
	{
		
	$rem_order = $order_numbers[id_from_msid($ms_id)];
	$del_item_num = id_from_msid($ms_id);
	array_splice($messenger_id,$del_item_num,1);
	array_splice($names,$del_item_num,1);
	array_splice($order_numbers,$del_item_num,1);
	$messenger_id = array_values($messenger_id);
	$names = array_values($names);
	$order_numbers = array_values($order_numbers);
	
	for ($i=0;$i<count($order_numbers);$i++)
	{
	if ($order_numbers[$i] > $rem_order)
	{
		$order_numbers[$i]--;
	}
	}
	
	$sql3 = "DELETE FROM ".$database.".index";
	
	if (mysqli_query($conn, $sql3)) {
    
} else {
    $stat=0;
}

for($i=0;$i<count($messenger_id);$i++)
{
	$sql4 = "INSERT INTO ".$database.".index (messenger_id, name, order_number) VALUES ('".$messenger_id[$i]."', '".$names[$i]."', ".$order_numbers[$i].")";

if (mysqli_query($conn, $sql4)) {
    
} else {
    $stat=0;
}
	
}

	}
	else 
	{
		$stat = 0;
	}
	return $stat;
}






//rem_client('4567654345676546');
//add_client('54309812354','Julian');
/*add_client('34434553767','Bill');
add_client('7645467384767','Stan');
add_client('165644467384767','Onely');*/
mysqli_close ($conn);
?>