<?php

$verify_token = getenv('VT');
$token = getenv('TT');
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




$sql = "SELECT messenger_id, name, order_number, location FROM ".$database.".index";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $messenger_id[$o] = $row["messenger_id"];
		$names[$o] = $row["name"];
		$order_numbers[$o] = $row["order_number"];
		$locations[$o] = $row["location"];
		$o++;
    }
}


/*
Major data from index table

$messenger_id  : array   - messenger PSID for sending messengers to users
$names         : array   - names of all people on duty
$order_numbers : array   - order numbers of people
$locations     : array   - location of people

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

	$weeks = weeks_count($t);
	$now = week_now($t);
	$o=0;
	$sch_w['events'][$o]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
		$sch_w['events'][$o]['start'] = date("c",$t);
		$sch_w['events'][$o]['end'] = date("c",$weeks[$now]['end']);
		
		$o++;
		$sch_w['events'][$o]['title'] = 'Upstairs washroom: '.$names_gen_u[mini($orders_gen_u)];
	    $sch_w['events'][$o]['start'] = date("c",$t);
		$sch_w['events'][$o]['end'] = date("c",$weeks[$now]['end']);
	
		$o++;
		$orders_gen_u = order_push($orders_gen_u);
		$orders_gen_b = order_push($orders_gen_b);
		
	for ($i=$now+1;$i<count($weeks);$i++)
	{
		$sch_w['events'][$o]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
		$sch_w['events'][$o]['start'] = date("c",$weeks[$i]['start']);
		$sch_w['events'][$o]['end'] = date("c",$weeks[$i]['end']);
		
		$o++;
		$sch_w['events'][$o]['title'] = 'Upstairs washroom: '.$names_gen_u[mini($orders_gen_u)];
	    $sch_w['events'][$o]['start'] = date("c",$weeks[$i]['start']);
		$sch_w['events'][$o]['end'] = date("c",$weeks[$i]['end']);
	
		$o++;
		$orders_gen_u = order_push($orders_gen_u);
		$orders_gen_b = order_push($orders_gen_b);
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

	for ($j=0;$j<count($names);$j++)
	{
		$s=0;
	for ($i=0;$i<count($inp_ar['events']);$i++)
	{
		if ($inp_ar['events'][$i]['title'] == $names[$j])
		{
			
			$calendar[int($j)]['events'][$s]['title'] = $inp_ar['events'][$i]['title'];
			$calendar[int($j)]['events'][$s]['start'] = $inp_ar['events'][$i]['start'];
			$calendar[int($j)]['events'][$s]['end'] = $inp_ar['events'][$i]['end'];
			$s++;
		}
	}
	$col_ar = getcolbyname($names[$j]);
	$calendar[(int)($j)]['backgroundColor'] = $col_ar['bg'];
	$calendar[(int)($j)]['borderColor'] = $col_ar['bd'];
	$calendar[(int)($j)]['textColor'] = $col_ar['text'];
	
	
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

function add_client($ms_id,$new_name,$loc)
{
	global $names;
	global $messenger_id;
	global $order_numbers;
	global $hostname;
	global $username;
	global $password;
	global $database;
	global $conn;
	global $wash_b_names;
	global $wash_u_names;
	
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
	
$sql2 = "INSERT INTO ".$database.".index (messenger_id, name, order_number, location) VALUES ('".$ms_id."', '".$new_name."', ".$new_order.", '".$loc."')";
if (mysqli_query($conn, $sql2)) {
    $stat=1;
} else {
    $stat=0;
}


if ($loc === 'b')
{
	$new_order_wb = count($wash_b_names)+1;
	$sql2 = "INSERT INTO ".$database.".washroom_basement (name, `order`) VALUES ('".$new_name."', ".$new_order_wb.")";
if (mysqli_query($conn, $sql2)) {
    $stat=1;
} else {
    $stat=0;
}
}
else if ($loc === 'u')
{
	$new_order_wu = count($wash_u_names)+1;
	$sql2 = "INSERT INTO ".$database.".washroom_upstairs (name, `order`) VALUES ('".$new_name."', ".$new_order_wu.")";
mysqli_query($conn, $sql2);
 

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
$rr = mysqli_query($conn, $sql);

$names[count($names)] = $new_name;
$messenger_id[count($messenger_id)] = $ms_id;
$order_numbers[count($order_numbers)] = $new_order;
}
	return $stat;
}

function wash_id_from_name ($name,$wash_array)
{
	$res = null;
	for($i=0;$i<count($wash_array);$i++)
	{
		if ($wash_array[$i] == $name)
		{
			$res = $i;
			break;
		}
	}
	return $res;
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
	global $locations;
	global $conn;
	global $wash_b_names;
	global $wash_u_names;
	global $wash_b_orders;
	global $wash_u_orders;
	
	$stat = 1;
	if (id_from_msid($ms_id) != null)
	{
		
	$rem_order = $order_numbers[id_from_msid($ms_id)];
	$del_item_num = id_from_msid($ms_id);
	$rem_name = $names[$del_item_num];
	$rem_loc = $locations[$del_item_num];
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
	
	$sql3 = "DELETE FROM ".$database.".`index` WHERE messenger_id = '".$ms_id."'";
	
	mysqli_query($conn, $sql3);
	
	for($i=0;$i<count($messenger_id);$i++)
	{
		$sql4 = "UPDATE ".$database.".`index` SET order_number = '".$order_numbers[$i]."' WHERE messenger_id = '".$messenger_id[$i]."'";

		mysqli_query($conn, $sql4);
	}

	
	if ($rem_loc === 'b')
	{
	$rem_wid = wash_id_from_name($rem_name,$wash_b_names);
	$rem_wash_order = $wash_b_orders[$rem_wid];
	array_splice($wash_b_names,$rem_wid,1);
	array_splice($wash_b_orders,$rem_wid,1);
	$wash_b_orders = array_values($wash_b_orders);
	$wash_b_names = array_values($wash_b_names);
	$sql = "DELETE FROM ".$database.".`washroom_basement` WHERE name = '".$rem_name."'";
	mysqli_query($conn, $sql);
	for ($i=0;$i<count($wash_b_orders);$i++)
	{
		if ($wash_b_orders[$i] > $rem_wash_order)
			{
				$wash_b_orders[$i]--;
			}
	}
	
	
	for($i=0;$i<count($wash_b_names);$i++)
	{
		$sql4 = "UPDATE ".$database.".washroom_basement SET `order` = '".$wash_b_orders[$i]."' WHERE name = '".$wash_b_names[$i]."'";
		mysqli_query($conn, $sql4);	
	}
	}
	else
	{
	$rem_wid = wash_id_from_name($rem_name,$wash_u_names);
	$rem_wash_order = $wash_u_orders[$rem_wid];
	array_splice($wash_u_names,$rem_wid,1);
	array_splice($wash_u_orders,$rem_wid,1);
	$wash_u_orders = array_values($wash_u_orders);
	$wash_u_names = array_values($wash_u_names);
	$sql = "DELETE FROM ".$database.".`washroom_upstairs` WHERE name = '".$rem_name."'";
	mysqli_query($conn, $sql);
	for ($i=0;$i<count($wash_u_orders);$i++)
	{
		if ($wash_u_orders[$i] > $rem_wash_order)
			{
				$wash_u_orders[$i]--;
			}
	}
	
	
	for($i=0;$i<count($wash_u_names);$i++)
	{
		$sql4 = "UPDATE ".$database.".washroom_upstairs SET `order` = '".$wash_u_orders[$i]."' WHERE name = '".$wash_u_names[$i]."'";
		mysqli_query($conn, $sql4);	
	}


	
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
$rr = mysqli_query($conn, $sql);
	
	
}

	else 
	{
		$stat = 0;
	}
	return $stat;
}

function add_history ($name,$table)
{
	global $conn;
	
$sql = "SELECT * FROM ".$database.".".$table;
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=1;
    while($row = $result->fetch_assoc()) {
        $h_name[$o] = $row["name"];
		$o++;
    }
} 	
for ($i=count($h_name);$i>1;$i--)
{
	$h_name[$i] = $h_name[$i-1];
}
$h_name[1] = $name;


for ($i=1;$i<=count($h_name);$i++)
{
	$sql = "UPDATE ".$database.".".$table." SET `name` = '".$h_name[$i]."' WHERE id = ".$i;
	mysqli_query($conn, $sql);	
}

}

function sch_gen_screenshot ($time)
{
	global $names;
	global $messenger_id;
	global $order_numbers;
	global $hostname;
	global $username;
	global $password;
	global $database;
	
	$conn = mysqli_connect($hostname, $username, $password, $database);
	
	$n = count($names);
	$p = $time;
	
    $t = date('t',$p);
    $day = date('j',$p);
	
	$names_gen = $names;
	$order_gen = $order_numbers;
	
for ($i=$day;$i<=$t;$i++)
{
	$dd = date('d',$p); 
	$sch['events'][$i-1]['title'] = $names_gen[mini($order_gen)];
	$sch['events'][$i-1]['start'] = date('Y',$time).'-'.date('m',$time).'-'.$dd;
	$sch['events'][$i-1]['end'] = date('Y',$time).'-'.date('m',$time).'-'.$dd;
    $p = $p + 86400;
	$order_gen = order_push($order_gen);
}

if ($day != 1)
{

$sql = "SELECT * FROM ".$database.".history ORDER BY id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
		if($row["name"] !='')
		{
        $h_name[$o] = $row["name"];
		$o++;
		}
    }
} 	
$o=0;
$p = time()-86400;
for ($i=$day-1;$i>=1;$i--)
{
	$dd = date('d',$p);
	$sch['events'][$i-1]['title'] = $h_name[$o];
	$o++;
	$sch['events'][$i-1]['start'] = date('Y',$time).'-'.date('m',$time).'-'.$dd;
	$sch['events'][$i-1]['end'] = date('Y',$time).'-'.date('m',$time).'-'.$dd;
	$p=$p-86400;
}

}

$t = $p;
for ($i=0;$i<date('t',$p);$i++)
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


mysqli_close ($conn);
	return $sch;
}

function weeks_count($time)
{
$this_month = date('n',$time);
$weeks[0]['start'] = mktime(0,0,0,date('n',$time),1,date('Y',$time));
if (date('D',mktime(0,0,0,date('n',$time),1,date('Y',$time))) != 'Sun')
{
$first_sunday = strtotime('next Sunday', mktime(0,0,0,date('n',$time),1,date('Y',$time)));
$weeks[0]['end'] = $first_sunday+86399;
}
else{
	$first_sunday = mktime(0,0,0,date('n',$time),1,date('Y',$time));
	$weeks[0]['end'] = mktime(0,0,0,date('n',$time),1,date('Y',$time))+86399;
}
$week_count = 1;
$t=$first_sunday;

 while (date('n',strtotime('next Sunday',$t)) == $this_month)
	{
	$weeks[$week_count]['start'] = $t+86400;
	$weeks[$week_count]['end'] = strtotime('next Sunday',$t)+86399;
	$week_count++;
	$t = $t + 86400*7;
	}
if (date('D',mktime(0,0,0,date('n',$time),date('t',$time),date('Y',$time))) != 'Sun')
{
$weeks[$week_count]['start'] = $t+86400;
$weeks[$week_count]['end'] = mktime(0,0,0,date('n',$time),date('t',$time),date('Y',$time))+86399;
$week_count++;
}	
return $weeks;
} 

function week_now ($time)
{
	$weeks = weeks_count($time);
	$now = '';
	
	for ($i=0;$i<count($weeks);$i++)
	{
		if (($time >= $weeks[$i]['start']) && ($time<=$weeks[$i]['end']))
		{
			$now = $i;
			break;
		}
	}
	return $now;
}

function wash_gen_screenshot($time)
{
	global $wash_u_names;
	global $wash_b_names;
	global $wash_b_orders;
	global $wash_u_orders;
	global $hostname;
	global $username;
	global $password;
	global $database;
	
	$names_gen_b = $wash_b_names;
	$orders_gen_b = $wash_b_orders;
	$names_gen_u = $wash_u_names;
	$orders_gen_u = $wash_u_orders;
	
	$t = $time;

	$weeks = weeks_count($t);
	$now = week_now($t);
	$o=0;
	for ($i=$now;$i<count($weeks);$i++)
	{
		$sch_w['events'][$o]['title'] = 'Basement washroom: '.$names_gen_b[mini($orders_gen_b)];
		$sch_w['events'][$o]['start'] = date("c",$weeks[$i]['start']);
		$sch_w['events'][$o]['end'] = date("c",$weeks[$i]['end']);
		
		$o++;
		$sch_w['events'][$o]['title'] = 'Upstairs washroom: '.$names_gen_u[mini($orders_gen_u)];
	    $sch_w['events'][$o]['start'] = date("c",$weeks[$i]['start']);
		$sch_w['events'][$o]['end'] = date("c",$weeks[$i]['end']);
	
		$o++;
		$orders_gen_u = order_push($orders_gen_u);
		$orders_gen_b = order_push($orders_gen_b);
	}
$l=$o;
$conn = mysqli_connect($hostname, $username, $password, $database);
	

$sql = "SELECT * FROM ".$database.".basement_history ORDER BY id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
		if($row["name"] !='')
		{
        $b_name[$o] = $row["name"];
		$o++;
		}
    }
} 	

$sql = "SELECT * FROM ".$database.".upstairs_history ORDER BY id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
		if($row["name"] !='')
		{
        $u_name[$o] = $row["name"];
		$o++;
		}
    }
} 
$bb = 0;
$uu = 0;
for ($i=$now-1;$i>=0;$i--)
{
	$sch_w['events'][$l]['title'] = 'Basement washroom: '.$b_name[$bb];
	$bb++;
	$sch_w['events'][$l]['start'] = date("c",$weeks[$i]['start']);
	$sch_w['events'][$l]['end'] = date("c",$weeks[$i]['end']);
	$l++;
	$sch_w['events'][$l]['title'] = 'Upstairs washroom: '.$u_name[$uu];
	$uu++;
	$sch_w['events'][$l]['start'] = date("c",$weeks[$i]['start']);
	$sch_w['events'][$l]['end'] = date("c",$weeks[$i]['end']);
	$l++;
	
}

	
	
mysqli_close ($conn);
	return $sch_w;
}

function screenshotlayer($url, $args) {

  // set access key
 
  $access_key =  getenv('scr_sh');

  // encode target URL
  $params['url'] = urlencode($url);

  $params += $args;

  // create the query string based on the options
  foreach($params as $key => $value) { $parts[] = "$key=$value"; }

  // compile query string
  $query = implode("&", $parts);

  return "http://api.screenshotlayer.com/api/capture?access_key=$access_key&$query";

}

function pic_update ()
{   
$params['width'] = '1440';      
$params['viewport']  = '1440x750';  
$params['format'] = 'jpg'; 
$params['force'] = '1';     
  
$call = screenshotlayer("https://warm-caverns-57501.herokuapp.com/calendar-screenshot.php", $params);  
$sourcecode=GetImageFromUrl($call);

$savefile = fopen('calendar.jpg', 'w');
fwrite($savefile, $sourcecode);
fclose($savefile);
 }
 
function GetImageFromUrl($link) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch,CURLOPT_URL,$link);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;
}

if (!file_exists('calendar.jpg'))
{
//pic_update ();
}





mysqli_close ($conn);
?>