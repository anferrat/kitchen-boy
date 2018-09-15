<!doctype html>
<html lang="en">
<head>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body> 
<?php

include 'fbbot.php';

$conn = mysqli_connect($hostname, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo '<form action= "redirect.php" method= "POST">';
echo "<h1>Pending requests</h1>";
echo '<table class="table"><thead class="black white-text"><tr><th scope="col">ID</th><th scope="col">Messenger PSID</th><th scope="col">Name</th><th scope="col">Location</th><th scope="col">Login/Logout</th><th></th></tr></thead><tbody>';


$sql = "SELECT * FROM ".$database.".pending";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
		if ($row['location'] === 'b')
		{
			$loc_text ='Basement';
		}
		else if ($row['location'] === 'u')
		{
			$loc_text ='Upstairs';
		}
		else
		{
			$loc_text ='Unknown';
		}
       echo  '<tr><td>'.$row["key"].'</td><td>'.$row["ms_id"].'</td><td>'.$row["name"].'</td><td>'.$loc_text.'</td><td>'.$row["type"].'</td><td><button type="submit" class="btn btn-primary" name="'.$row["type"].'" value="'.$row["key"].'">Approve</button>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary" name="remove" value="'.$row["key"].'">Remove</button>'.'</td></tr>';
    }
}





echo '</tbody></table>';
mysqli_close ($conn);
?>

 
</body>
</html> 