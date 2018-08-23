<?php
$verify_token = "kitchen";
$token = "EAAbf6i0r8GoBAAoL1EVtXv1DibWI5lRfMU7r2YkGe3w5a3FnE73f0zxkhFY3mJiE6ACuwyD9IweseZCteAZB7J10PTJXRndTtzyhsV9wLUnwDtIkc2wGfjIoxof5n379YNEgP7le8yXPbtb5sqZAcWEqcJXZBIPRhWZClnTZAMZCuZAAuNQhtwGF";

if (file_exists(__DIR__.'/config.php')) {
    $config = include __DIR__.'/config.php';
    $verify_token = $config['verify_token'];
    $token = $config['token'];
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');

use pimax\FbBotApp;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\StructuredMessage;
use pimax\Messages\MessageElement;
use pimax\Messages\MessageReceiptElement;
use pimax\Messages\Address;
use pimax\Messages\Summary;
use pimax\Messages\Adjustment;


$bott = new FbBotApp($token);

if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe' && $_REQUEST['hub_verify_token'] == $verify_token)
{
     // Webhook setup request
    echo $_REQUEST['hub_challenge'];
} else {

     $data = json_decode(file_get_contents("php://input"), true);
     if (!empty($data['entry'][0]['messaging']))
     {
            foreach ($data['entry'][0]['messaging'] as $message)
           {
$content = file_get_contents("php://input");
$fp = fopen("myText.txt","wb");
fwrite($fp,$content);
fclose($fp);
//$bott->send(new Message($message['sender']['id'], 'Hi there!'));

            }
   }
}




?>