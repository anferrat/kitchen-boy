<?php
$verify_token = "kitchen";
$token = "EAAbf6i0r8GoBAAoL1EVtXv1DibWI5lRfMU7r2YkGe3w5a3FnE73f0zxkhFY3mJiE6ACuwyD9IweseZCteAZB7J10PTJXRndTtzyhsV9wLUnwDtIkc2wGfjIoxof5n379YNEgP7le8yXPbtb5sqZAcWEqcJXZBIPRhWZClnTZAMZCuZAAuNQhtwGF";

include 'fbbot.php';

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

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
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

//$bot->send(new Message('2170490766313202', 'kak jizn'));

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
	
	$bot->send(new Message($messenger_id[$recipient_not_id], 'Hello, '.$names[$recipient_not_id].'! Today is your lucky day to clean the kitchen. Make sure you dont forget it'));
	
}

note_gen();
mysqli_close ($conn);
?>