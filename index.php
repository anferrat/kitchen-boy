<?php
$verify_token = "kitchen";
$token = "EAAbf6i0r8GoBAAoL1EVtXv1DibWI5lRfMU7r2YkGe3w5a3FnE73f0zxkhFY3mJiE6ACuwyD9IweseZCteAZB7J10PTJXRndTtzyhsV9wLUnwDtIkc2wGfjIoxof5n379YNEgP7le8yXPbtb5sqZAcWEqcJXZBIPRhWZClnTZAMZCuZAAuNQhtwGF";

if (file_exists(__DIR__.'/config.php')) {
    $config = include __DIR__.'/config.php';
    $verify_token = $config['verify_token'];
    $token = $config['token'];
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');

include 'fbbot.php';
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "SELECT * FROM ".$database.".pending";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
	$o=0;
    while($row = $result->fetch_assoc()) {
        $ms_id_pen[$o] = $row["ms_id"];
		$loc_pen[$o] = $row["location"];
		$o++;
    }
}

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
// Make Bot Instance
$bot = new FbBotApp($token);
if (!empty($_REQUEST['local'])) {
    $message = new ImageMessage(1585388421775947, dirname(__FILE__).'/fb4d_logo-2x.png');
    $message_data = $message->getData();
    $message_data['message']['attachment']['payload']['url'] = 'fb4d_logo-2x.png';
    echo '<pre>', print_r($message->getData()), '</pre>';
    $res = $bot->send($message);
    echo '<pre>', print_r($res), '</pre>';
}


// Receive something


//$bot->send(new Message('2170490766313202', 'Yo'));

if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe' && $_REQUEST['hub_verify_token'] == $verify_token) {
    // Webhook setup request
    echo $_REQUEST['hub_challenge'];
} else {
    // Other event
    $data = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
    if (!empty($data['entry'][0]['messaging'])) {
        foreach ($data['entry'][0]['messaging'] as $message) {
            // Skipping delivery messages
            if (!empty($message['delivery'])) {
                continue;
            }
            // skip the echo of my own messages
            if (($message['message']['is_echo'] == "true")) {
                continue;
            }
            $command = "";
            // When bot receive message from user
            if (!empty($message['message'])) {
                $command = trim($message['message']['text']);
            // When bot receive button click from user
            } else if (!empty($message['postback'])) {
                $text = "Postback received: ".trim($message['postback']['payload']);
                $bot->send(new Message($message['sender']['id'], $text));
                continue;
            }
            // Handle command
			
			//Login condition check
			    $login_res = 100;
				for ($i=0;$i<count($ms_id_pen);$i++)
				{
					if($ms_id_pen[$i] == $message['sender']['id'])
					{
						if ($loc_pen[$i] === '0')
						{
						$login_res = 200; // request is pending
						break;
						}
						else
						{
							$login_res = 400;
						}
					}
				
				}
				
				for ($i=0;$i<count($messenger_id);$i++)
				{
					if($messenger_id[$i] == $message['sender']['id'])
					{
						$login_res = 300; //already registred
						break;
					}
				}	
			
		// command condition check	generate $req with type of command
			if ((strpos(strtolower($command),'login ') === 0) )
			{
				$req = 'login';
			}
			if (strtolower($command) == 'basement')
			{
				$req = 'basement';
			}
			if (strtolower($command) == 'upstairs')
			{
				$req = 'upstairs';
			}
			
			
			if (strtolower($command) == 'calendar')
			{
				$req = 'calendar';
			}
			
			if (strtolower ($command) == 'logout')
			{
				$req = 'logout';
			}
			
			if (strtolower($command) == 'next')
			{
				$req = 'next';
			}
			if (strtolower($command) == 'remind')
			{
				$req = 'remind';
			}
			if (strtolower($command) == 'today')
			{
				$req = 'today';
			}
			if (strpos(strtolower($command),'dish') !== false)
			{
				if (strpos(strtolower($command),'dish ') === 0)
				{
					$req = 'dish';
					$dish_name = substr($command,5);
				}
				else
				{
					$req = 'dish_wrong';
				}
			}
			
			
			
// command action gen
			if (!empty($command))
				{
					if ($login_res == 200 && $req == 'basement')
				{
					$sql = "UPDATE ".$database.".pending SET location = 'b' WHERE (ms_id LIKE '".$message['sender']['id']."')";
					mysqli_query($conn, $sql);
					$bot->send(new Message($message['sender']['id'], 'Your request has been sent'));
				}
				else if ($login_res == 200 && $req == 'upstairs')
				{
					$sql = "UPDATE ".$database.".pending SET location = 'u' WHERE (ms_id LIKE '".$message['sender']['id']."')";
					mysqli_query($conn, $sql);
					$bot->send(new Message($message['sender']['id'], 'Your request has been sent'));
				}

				else if ($login_res == 300 && $req == 'login')
				{
					$bot->send(new Message($message['sender']['id'], 'You are already registred'));
				} 
				else if ($login_res == 100 && $req == 'login')
				{
					$pen_name = substr($command,6);
					$sql = "INSERT INTO ".$database.".pending (ms_id, name, type, login, location) VALUES ('".$message['sender']['id']."', '".$pen_name."', 'login', 0, '0')";
					mysqli_query($conn, $sql);
					$bot->send(new QuickReply($message['sender']['id'], 'Which part of the house do you live?', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Basement', 'PAYLOAD 1'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Upstairs', 'PAYLOAD 2'),
                            
                            ]
                    ));
				}
				else if ($login_res == 100 && $req != 'login')
				{
					$bot->send(new Message($message['sender']['id'], 'Welcome to automatic schedule generator. The system is designed to keep you updated about current cleaning schedule. In order to start, please type login <your name>.'));
				}
				else if ($login_res == 300 && $req != 'login')
				{
					
					if ($req == 'calendar')
					{
					$bot->send(new Message($message['sender']['id'], 'https://warm-caverns-57501.herokuapp.com/calendar.php'));
					
					}
					else if ($req == 'logout')
					{
						for ($i=0;$i<count($messenger_id);$i++)
						{
							if($messenger_id[$i] == $message['sender']['id'])
							{
								$usname=$names[$i];
								break;
							}
						}
					$sql = "INSERT INTO ".$database.".pending (ms_id, name, type, login) VALUES ('".$message['sender']['id']."', '".$usname."', 'logout', 0)";
					mysqli_query($conn, $sql);
					$bot->send(new Message($message['sender']['id'], 'Request to logout has been sent.'));
					}
					else if ($req == 'next')
					{
						$next_date = nex_date($message['sender']['id']);
						$day_w = date("l",strtotime($next_date));
						$next_date = date("F",strtotime($next_date)).' '.date("j",strtotime($next_date));
						$bot->send(new Message($message['sender']['id'], 'Your next kitchen day is scheduled for '.$day_w.', '.$next_date.'. Good luck!'));
					}
					else if ($req == 'remind')
					{
						note_gen();
						$bot->send(new Message($message['sender']['id'], 'Additional reminder has been sent to the person on duty.'));
					}
					else if ($req == 'today')
					{
						$today_cl = sch_gen (1);
						
						$bot->send(new Message($message['sender']['id'],$today_cl['events'][0]['title'].' cleans kitchen today'));
					}
					else if ($req == 'dish_wrong')
					{
						$bot->send(new Message($message['sender']['id'], 'Invalid format. Type: dish <Name of person>'));
					}
					else if ($req == 'dish')
					{
						if (ms_id_from_name($dish_name) != 0)
						{
							$bot->send(new Message(ms_id_from_name($dish_name), 'You have dishes in the sink. Please wash them ASAP.'));
							$bot->send(new Message($message['sender']['id'], 'Dishes reminder has been sent.'));;
						}
						else
						{
						$bot->send(new Message($message['sender']['id'], 'Name has not been found. Make sure you spell it right.'));	
						}
					}
					
				else
					{
						
						$bot->send(new QuickReply($message['sender']['id'], 'Try some of these commands', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Calendar', 'PAYLOAD 1'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Next', 'PAYLOAD 2'),
								new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Remind', 'PAYLOAD 3'),
								new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Today', 'PAYLOAD 4'),
                            
                            ]
                    ));
                    
						
					}
				}
				else if ($login_res == 200 && $req != 'login')
				{
					$bot->send(new QuickReply($message['sender']['id'], 'Which part of the house do you live?', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Basement', 'PAYLOAD 1'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Upstairs', 'PAYLOAD 2'),
                            
                            ]
                    ));
					
				}
				else if ($login_res == 400 && $req == 'login')
				{
					$bot->send(new Message($message['sender']['id'], 'You have pending login request'));
				}
				else
				{
					///
				}
					
				}
			}

			
            /* switch ($command) {
                // When bot receive "login"
                case 'smt':
                    $bot->send(new Message($message['sender']['id'], 'your id'.$message['sender']['id']));
                    break;
                
				
				
				// When bot receive "image"
                case 'image':
                    $bot->send(new ImageMessage($message['sender']['id'], 'http://bit.ly/2p9WZBi'));
                    break;
                // When bot receive "local image"
                //case 'local image':
                    //$bot->send(new ImageMessage($message['sender']['id'], dirname(__FILE__).'/fb_logo.png'));
                    //break;
                // When bot receive "profile"
                case 'profile':
                    $user = $bot->userProfile($message['sender']['id']);
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new MessageElement($user->getFirstName()." ".$user->getLastName(), " ", $user->getPicture())
                            ]
                        ],
                        [ 
                        	new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD') 
                        ]
                    ));
                    break;
                // When bot receive "button"
                case 'button':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_BUTTON,
                        [
                            'text' => 'Choose category',
                            'buttons' => [
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'First button', 'PAYLOAD 1'),
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button', 'PAYLOAD 2'),
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'Third button', 'PAYLOAD 3')
                            ]
                        ],
                        [ 
                        	new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD') 
                        ]
                    ));
                    break;
                
                // When bot receive "quick reply"
                case 'quick reply':
                    $bot->send(new QuickReply($message['sender']['id'], 'Your ad here!', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button 1', 'PAYLOAD 1'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button 2', 'PAYLOAD 2'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button 3', 'PAYLOAD 3'),
                            ]
                    ));
                    break;
                    
                // When bot receive "location"
                case 'location':
                    $bot->send(new QuickReply($message['sender']['id'], 'Please share your location', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_LOCATION),
                            ]
                    ));
                    break;
                    
                // When bot receive "generic"
                case 'generic':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new MessageElement("First item", "Item description", "", [
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                                    new MessageButton(MessageButton::TYPE_WEB, 'Web link', 'http://facebook.com')
                                ]),
                                new MessageElement("Second item", "Item description", "", [
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button')
                                ]),
                                new MessageElement("Third item", "Item description", "", [
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button')
                                ])
                            ]
                        ],
                        [ 
                        	new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD')
                        ]
                    ));
                    break;
                    
                // When bot receive "list"
                case 'list':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_LIST,
                        [
                            'elements' => [
                                new MessageElement(
                                    'Classic T-Shirt Collection', // title
                                    'See all our colors', // subtitle
                                    'http://bit.ly/2pYCuIB', // image_url
                                    [ // buttons
                                        new MessageButton(MessageButton::TYPE_POSTBACK, // type
                                            'View', // title
                                            'POSTBACK' // postback value
                                        )
                                    ]
                                ),
                                new MessageElement(
                                    'Classic White T-Shirt', // title
                                    '100% Cotton, 200% Comfortable', // subtitle
                                    'http://bit.ly/2pb1hqh', // image_url
                                    [ // buttons
                                        new MessageButton(MessageButton::TYPE_WEB, // type
                                            'View', // title
                                            'https://google.com' // url
                                        )
                                    ]
                                )
                            ],
                            'buttons' => [
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'First button', 'PAYLOAD 1')
                            ]
                        ],
                        [
                            new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD')
                        ]
                    ));
                    break;
                // When bot receive "receipt"
                case 'receipt':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_RECEIPT,
                        [
                            'recipient_name' => 'Fox Brown',
                            'order_number' => rand(10000, 99999),
                            'currency' => 'USD',
                            'payment_method' => 'VISA',
                            'order_url' => 'http://facebook.com',
                            'timestamp' => time(),
                            'elements' => [
                                new MessageReceiptElement("First item", "Item description", "", 1, 300, "USD"),
                                new MessageReceiptElement("Second item", "Item description", "", 2, 200, "USD"),
                                new MessageReceiptElement("Third item", "Item description", "", 3, 1800, "USD"),
                            ],
                            'address' => new Address([
                                'country' => 'US',
                                'state' => 'CA',
                                'postal_code' => 94025,
                                'city' => 'Menlo Park',
                                'street_1' => '1 Hacker Way',
                                'street_2' => ''
                            ]),
                            'summary' => new Summary([
                                'subtotal' => 2300,
                                'shipping_cost' => 150,
                                'total_tax' => 50,
                                'total_cost' => 2500,
                            ]),
                            'adjustments' => [
                                new Adjustment([
                                    'name' => 'New Customer Discount',
                                    'amount' => 20
                                ]),
                                new Adjustment([
                                    'name' => '$10 Off Coupon',
                                    'amount' => 10
                                ])
                            ]
                        ]
                    ));
                    break;
                // When bot receive "set menu"
                case 'set menu':
                    $bot->deletePersistentMenu();
                    $bot->setPersistentMenu([
                        new LocalizedMenu('default', false, [
                            new MenuItem(MenuItem::TYPE_NESTED, 'My Account', [
                                new MenuItem(MenuItem::TYPE_NESTED, 'History', [
                                    new MenuItem(MenuItem::TYPE_POSTBACK, 'History Old', 'HISTORY_OLD_PAYLOAD'),
                                    new MenuItem(MenuItem::TYPE_POSTBACK, 'History New', 'HISTORY_NEW_PAYLOAD')
                                ]),
                                new MenuItem(MenuItem::TYPE_POSTBACK, 'Contact Info', 'CONTACT_INFO_PAYLOAD')
                            ])
                        ])
                    ]);
                    break;
                // When bot receive "delete menu"
                case 'delete menu':
                    $bot->deletePersistentMenu();
                    break;
                // When bot receive "login"
                case 'login':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new AccountLink(
                                    'Welcome to Bank',
                                    'To be sure, everything is safe, you have to login to your administration.',
                                    'https://www.example.com/oauth/authorize',
                                    'https://www.facebook.com/images/fb_icon_325x325.png')
                            ]
                        ]
                    ));
                    break;
                // When bot receive "logout"
                case 'logout':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new AccountLink(
                                    'Welcome to Bank',
                                    'To be sure, everything is safe, you have to login to your administration.',
                                    '',
                                    'https://www.facebook.com/images/fb_icon_325x325.png',
                                    TRUE)
                            ]
                        ]
                    ));
                    break;
                // When bot receive "sender action on"
                case 'sender action on':
                    $bot->send(new SenderAction($message['sender']['id'], SenderAction::ACTION_TYPING_ON));
                    break;
                // When bot receive "sender action off"
                case 'sender action off':
                    $bot->send(new SenderAction($message['sender']['id'], SenderAction::ACTION_TYPING_OFF));
                    break;
                // When bot receive "set get started button"
                case 'set get started button':
                    $bot->setGetStartedButton('PAYLOAD - get started button');
                    break;
                // When bot receive "delete get started button"
                case 'delete get started button':
                    $bot->deleteGetStartedButton();
                    break;
                // When bot receive "show greeting text"
                case 'show greeting text':
                    $response = $bot->getGreetingText();
                    $text = "";
                    if(isset($response['data'][0]['greeting']) AND is_array($response['data'][0]['greeting'])){
                        foreach ($response['data'][0]['greeting'] as $greeting)
                        {
                            $text .= $greeting['locale']. ": ".$greeting['text']."\n";
                        }
                    } else {
                        $text = "Greeting text not set!";
                    }
                    $bot->send(new Message($message['sender']['id'], $text));
                    break;
                // When bot receive "delete greeting text"
                case 'delete greeting text':
                    $bot->deleteGreetingText();
                    break;
                // When bot receive "set greeting text"
                case 'set greeting text':
                    $bot->setGreetingText([
                        [
                            "locale" => "default",
                            "text" => "Hello {{user_full_name}}"
                        ],
                        [
                            "locale" => "en_US",
                            "text" => "Hi {{user_first_name}}, welcome to this bot."
                        ],
                        [
                            "locale" => "de_DE",
                            "text" => "Hallo {{user_first_name}}, herzlich willkommen."
                        ]
                    ]);
                    break;
                // When bot receive "set target audience"
                case 'show target audience':
                    $response = $bot->getTargetAudience();
                    break;
                // When bot receive "set target audience"
                case 'set target audience':
                    $bot->setTargetAudience("all");
                    //$bot->setTargetAudience("none");
                    //$bot->setTargetAudience("custom", "whitelist", ["US", "CA"]);
                    //$bot->setTargetAudience("custom", "blacklist", ["US", "CA"]);
                    break;
                // When bot receive "delete target audience"
                case 'delete target audience':
                    $bot->deleteTargetAudience();
                    break;
                // When bot receive "show domain whitelist"
                case 'show domain whitelist':
                    $response = $bot->getDomainWhitelist();
                    $text = "";
                    if(isset($response['data'][0]['whitelisted_domains']) AND is_array($response['data'][0]['whitelisted_domains'])){
                        foreach ($response['data'][0]['whitelisted_domains'] as $domains)
                        {
                            $text .= $domains."\n";
                        }
                    } else {
                        $text = "No domains in whitelist!";
                    }
                    $bot->send(new Message($message['sender']['id'], $text));
                    break;
                // When bot receive "set domain whitelist"
                case 'set domain whitelist':
                    //$bot->setDomainWhitelist("https://petersfancyapparel.com");
                    $bot->setDomainWhitelist([
                        "https://petersfancyapparel-1.com",
                        "https://petersfancyapparel-2.com",
                    ]);
                    break;
                // When bot receive "delete domain whitelist"
                case 'delete domain whitelist':
                    $bot->deleteDomainWhitelist();
                    break;
                // Other message received
                default:
                    if (!empty($command)) // otherwise "empty message" wont be understood either
                        $bot->send(new Message($message['sender']['id'], 'Sorry. I don’t understand you.'));   
            }*/
        }
    }


mysqli_close ($conn);
?>