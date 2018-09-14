<?php
$verify_token = "kitchen";
$token = "EAAbf6i0r8GoBAAoL1EVtXv1DibWI5lRfMU7r2YkGe3w5a3FnE73f0zxkhFY3mJiE6ACuwyD9IweseZCteAZB7J10PTJXRndTtzyhsV9wLUnwDtIkc2wGfjIoxof5n379YNEgP7le8yXPbtb5sqZAcWEqcJXZBIPRhWZClnTZAMZCuZAAuNQhtwGF";
date_default_timezone_set("America/Edmonton");
if (file_exists(__DIR__.'/config.php')) {
    $config = include __DIR__.'/config.php';
    $verify_token = $config['verify_token'];
    $token = $config['token'];
}

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


/*
Major data from index table

$messenger_id  : array   - messenger id's for sending messengers to users
$names         : array   - names of all people on duty
$order_numbers : array   - order numbers of people. 

*/

$sql = "SELECT * FROM ".$database.".washroom_basement";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $wash_b_names[$o] = $row["name"];
		$wash_b_orders[$o] = $row["order"];
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
		$o++;
    }
} else {
 die("No Data found");
}



$sq = "SELECT * FROM ".$database.".colors";
$resul = $conn->query($sq);
if ($resul->num_rows > 0) {
    // output data of each row
	$l=0;
    while($row = $resul->fetch_assoc()) {
        $key_c[$l] = $row["key"];
		$name_c[$l] = $row["name"];
		$bg[$l] = $row["bg"];
		$bd[$l] = $row["bd"];
		$text[$l] = $row["text"];
		$l++;
    }
} else {
 die($sql);
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');
use pimax\FbBotApp;
use pimax\Menu\MenuItem;
use pimax\Menu\LocalizedMenu;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\StructuredMessage;
use pimax\Messages\MessageElement;
use pimax\Messages\MessageReceiptElement;
use pimax\Messages\Address;
use pimax\Messages\Summary;
use pimax\Messages\Adjustment;
use pimax\Messages\AccountLink;
use pimax\Messages\ImageMessage;
use pimax\Messages\QuickReply;
use pimax\Messages\QuickReplyButton;
use pimax\Messages\SenderAction;

$bot = new FbBotApp($token);

if (!empty($_REQUEST['local'])) {
    $message = new ImageMessage(1585388421775947, dirname(__FILE__).'/fb4d_logo-2x.png');
    $message_data = $message->getData();
    $message_data['message']['attachment']['payload']['url'] = 'fb4d_logo-2x.png';
    echo '<pre>', print_r($message->getData()), '</pre>';
    $res = $bot->send($message);
    echo '<pre>', print_r($res), '</pre>';
}



function wash_gen($days)
{
	global $wash_u_names;
	global $wash_b_names;
	global $wash_b_orders;
	global $wash_u_orders;
	
	$names_gen_b = $wash_b_names;
	$orders_gen_b = $wash_b_orders;
	$names_gen_u = $wash_u_names;
	$orders_gen_u = $wash_u_orders;
	
	$t = time();
	

	$k = $days*86400;
	if ($days*86400 + $t < strtotime('next Sunday'))
	{
	$sch_w['events'][0]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
	$sch_w['events'][0]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][0]['end'] = substr(date("c",($days*86400 + $t)),0,10);
	$sch_w['events'][1]['title'] = 'Upstairs washroom: '.$names_gen_u[mini($orders_gen_u)];
	$sch_w['events'][1]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][1]['end'] = substr(date("c",($days*86400 + $t)),0,10);
	}
	else
	{
	$sch_w['events'][0]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
	$sch_w['events'][0]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][0]['end'] = substr(date("c",strtotime('next Monday')),0,10);
	$sch_w['events'][1]['title'] = 'Upstairs washroom: '.$names_gen_u[mini($orders_gen_u)];
	$sch_w['events'][1]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][1]['end'] = substr(date("c",strtotime('next Monday')),0,10);
	$k = $k - (strtotime('next Sunday') - $t);
	$t = strtotime('next Sunday')+86400;
	$h = 2;
	$orders_gen_u = order_push($orders_gen_u);
	$orders_gen_b = order_push($orders_gen_b);
	
	while ($k > 86400*7)
	{
	$sch_w['events'][$h]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
	$sch_w['events'][$h]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][$h]['end'] = substr(date("c",strtotime('next Monday',$t)),0,10);
	$h++;
	$sch_w['events'][$h]['title'] = 'Upstairs washroom: '.$names_gen_u[mini($orders_gen_u)];
	$sch_w['events'][$h]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][$h]['end'] = substr(date("c",strtotime('next Monday',$t)),0,10);
	$h++;
	$t = strtotime('next Sunday',$t)+86400;
	$k = $k - 86400*7;
	$orders_gen_u = order_push($orders_gen_u);
	$orders_gen_b = order_push($orders_gen_b);
	}
	
	if ($k > 86400)
	{
	$sch_w['events'][$h]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
	$sch_w['events'][$h]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][$h]['end'] = substr(date("c",strtotime('next Monday',$t)),0,10);
	$h++;
	$sch_w['events'][$h]['title'] = 'Upstairs washroom washroom: '.$names_gen_u[mini($orders_gen_u)];
	$sch_w['events'][$h]['start'] = substr(date("c",$t),0,10);
	$sch_w['events'][$h]['end'] = substr(date("c",strtotime('next Monday',$t)),0,10);
	}
	
	
	
	}
	
	
	return $sch_w;
}

function gr_bl_bin($t)
{
	if (date("N",$t) == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function black_bin($t)
{
	$const_date = 1534273200;
	$rr = false;
	while (($t+1209600)>=$const_date)
	{
		if (substr(date("c",$t),0,10) == '2018-08-14')
		{
			$rr = true;
			 
		}
		$t=$t - 1209600;
	}
	return $rr;
	
}

function nex_date($idd)
{
	global $names;
	global $messenger_id;
	
	for($i=0;$i<count($messenger_id);$i++)
	{
		if ($messenger_id[$i] == $idd)
		{
			$set_name = $names[$i];
			break;
		}
	}
	$d = sch_gen(15);
	$n_date = 'never';
	for($i=0;$i<count($d['events']);$i++)
	{
		if ($d['events'][$i]['title'] == $set_name)
		{
	$n_date = $d['events'][$i]['start'];
break;	
		}
	}
	
	return $n_date;
}

function reg_conf($idd2)
{
    global $bot;
	$dd = nex_date($idd2);
	$bot->send(new Message($idd2, 'Your login request has been approved. If you want to see the current schedule, type calendar. Your next duty day is scheduled for '.$dd.'. You will recieve reminder in that day'));

	}
	
function rem_conf($idd2)
{
	global $bot;
	$bot->send(new Message($idd2, 'You have been removed from the kitchen schedule'));
}

function note_gen()
{
	global $names;
	global $messenger_id;
	global $bot;
	
	$d = sch_gen(1);
	for($i=0;$i<count($names);$i++)
	{
		if ($d['events'][0]['title'] == $names[$i])
		{
	$recipient_not_id = $i;		
		}
	}
	
	$bot->send(new Message($messenger_id[$recipient_not_id], 'Hello, '.$names[$recipient_not_id].'! Today is your lucky day to clean the kitchen. Make sure you dont forget it.'));
	
	if (gr_bl_bin(time()))
	{
		$bot->send(new Message($messenger_id[$recipient_not_id], 'Tomorrow is garbage day. Push GREEN and BLUE bins to the road tonight.'));
	}
	
	if (black_bin(time()))
	{
		$bot->send(new Message($messenger_id[$recipient_not_id], 'Tomorrow is garbage day. Push BLACK bin to the road tonight.'));
	}
	
}

function mini($arr)  // returns number of smallest int element in array
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
$t = time();
for ($i=0;$i<$days;$i++)
{
	$uy = count($sch['events']);
	if (gr_bl_bin($t))
	{
	$sch['events'][$uy]['title'] = 'Push Green and Blue bins';
	$sch['events'][$uy]['start'] = substr(date("c",$t),0,10);
	$sch['events'][$uy]['end'] = substr(date("c",$t),0,10);
	}
	if (black_bin($t))
	{
	$sch['events'][$uy]['title'] = 'Push Black bin';
	$sch['events'][$uy]['start'] = substr(date("c",$t),0,10);
	$sch['events'][$uy]['end'] = substr(date("c",$t),0,10);
	}
	$t = $t + 86400;
}

	return $sch;
}

function cal_data($inp_ar, $inp_wash)
{
	global $names;
	$calendar = array(
	array (
	events => array (
	array(
	title=>"",
	start=>"",
	end=>""
	)
	),
	backgroundColor => "",
	borderColor => "",
	textColor=>""
	
	)
	);

	for ($j=0;$j<count($names);$j++)
	{
		$s=0;
	for ($i=0;$i<count($inp_ar['events']);$i++)
	{
		if ($inp_ar['events'][$i]['title'] == $names[$j])
		{
			
			$calendar[$j]['events'][$s]['title'] = $inp_ar['events'][$i]['title'];
			$calendar[$j]['events'][$s]['start'] = $inp_ar['events'][$i]['start'];
			$calendar[$j]['events'][$s]['end'] = $inp_ar['events'][$i]['end'];
			$s++;
		}
	}
	$col_ar = getcolbyname($names[$j]);
	$calendar[$j]['backgroundColor'] = $col_ar['bg'];
	$calendar[$j]['borderColor'] = $col_ar['bd'];
	$calendar[$j]['textColor'] = $col_ar['text'];
	
	
	}
		$s=0;
		$ii = count($calendar);
	for ($i=0;$i<count($inp_ar['events']);$i++)
	{
		if ($inp_ar['events'][$i]['title'] == 'Push Green and Blue bins')
		{
			
			$calendar[$ii]['events'][$s]['title'] = $inp_ar['events'][$i]['title'];
			$calendar[$ii]['events'][$s]['start'] = $inp_ar['events'][$i]['start'];
			$calendar[$ii]['events'][$s]['end'] = $inp_ar['events'][$i]['end'];
			$s++;
		}
		if ($inp_ar['events'][$i]['title'] == 'Push Black bin')
		{
			
			$calendar[$ii]['events'][$s]['title'] = $inp_ar['events'][$i]['title'];
			$calendar[$ii]['events'][$s]['start'] = $inp_ar['events'][$i]['start'];
			$calendar[$ii]['events'][$s]['end'] = $inp_ar['events'][$i]['end'];
			$s++;
		}
	}
	$calendar[$ii]['backgroundColor'] = 'black';
	$calendar[$ii]['borderColor'] = 'white';
	$calendar[$ii]['textColor'] = 'white';
	$ii++;
	
	if ($inp_wash !== 0)
	{
	for ($j=0;$j<count($names);$j++)
	{
		$s=0;
		for ($i=0;$i<count($inp_wash['events']);$i++)
		{
		
		if (strpos($inp_wash['events'][$i]['title'],$names[$j]) !== false)
		{
			
			$calendar[$ii]['events'][$s]['title'] = $inp_wash['events'][$i]['title'];
			$calendar[$ii]['events'][$s]['start'] = $inp_wash['events'][$i]['start'];
			$calendar[$ii]['events'][$s]['end'] = $inp_wash['events'][$i]['end'];
			$s++;
		}
		
		
		}
		if ($s!=0)
		{
		$col_ar = getcolbyname($names[$j]);
		$calendar[$ii]['backgroundColor'] = $col_ar['bg'];
		$calendar[$ii]['borderColor'] = $col_ar['bd'];
		$calendar[$ii]['textColor'] = $col_ar['text'];
		//$calendar[$ii]['nextDayThreshold'] = '00:00:00';
		}
		$ii++;
	}
	}

	
	
	return $calendar;
}

function getcolbyname ($name_q)
{
global $key_c;
global $name_c;
global $bg;
global $bd;
global $text;

for($i=0;$i<count($key_c);$i++)
{
	if ($name_c[$i] === $name_q)
	{
		$col_arr['bg'] = $bg[$i];
		$col_arr['bd'] = $bd[$i];
		$col_arr['text'] = $text[$i];
		break;
	}
}

return $col_arr;

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
$sql11 = "SELECT * FROM ".$database.".colors";
$result11 = $conn->query($sql11);
if ($result11->num_rows > 0) {
    // output data of each row
	$l=0;
    while($row = $result11->fetch_assoc()) {
        $key_c[$l] = $row["key"];
		$name_c[$l] = $row["name"];
		$bg[$l] = $row["bg"];
		$bd[$l] = $row["bd"];
		$text[$l] = $row["text"];
		$l++;
    }
} else {
 die($sql);
}

for ($i=0;$i<count($key_c);$i++)
{
	if ($name_c[$i] === 'na')
	{
		$ins_point = $i;
		break;
	}
}
$sql = "UPDATE `".$database."`.`colors` SET `name` = '".$new_name."' WHERE (`key` = '".$ins_point."')";
$rr = mysqli_query($conn, $sql) or die($sql);

$names[count($names)] = $new_name;
$messenger_id[count($messenger_id)] = $ms_id;
$order_numbers[count($order_numbers)] = $new_order;
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
function ms_id_from_name ($name_id)
{
	global $names;
	global $messenger_id;
	$res_id = 0;
	for ($i=0;$i<count($names);$i++)
	{
		if ($names[$i] == $name_id)
		{
			$res_id = $messenger_id[$i];
			break;
		}
	}
	return $res_id;
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
	$rem_name = $names[$del_item_num];
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
	
	$sql11 = "SELECT * FROM ".$database.".colors";
$result11 = $conn->query($sql11);
if ($result11->num_rows > 0) {
    // output data of each row
	$l=0;
    while($row = $result11->fetch_assoc()) {
        $key_c[$l] = $row["key"];
		$name_c[$l] = $row["name"];
		$bg[$l] = $row["bg"];
		$bd[$l] = $row["bd"];
		$text[$l] = $row["text"];
		$l++;
    }
} else {
 die($sql);
}

for ($i=0;$i<count($key_c);$i++)
{
	if ($name_c[$i] == $rem_name)
	{
		$del_point = $i;
		break;
	}
}
$sql = "UPDATE `".$database."`.`colors` SET `name` = 'na' WHERE (`key` = '".$del_point."')";
$rr = mysqli_query($conn, $sql) or die($sql);
	
	
}

	}
	else 
	{
		$stat = 0;
	}
	return $stat;
}

mysqli_close ($conn);
?>