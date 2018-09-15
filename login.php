<?php
$pass = getenv('sec');
    if (isset($_POST['password']) && $_POST['password'] == $pass) {
        setcookie("password", $pass, strtotime('+30 days'));
        header('Location: auth.php');
        exit;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password protected</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
        <form method="POST">			
			<div class="form-group">
    <label for="exampleInputPassword1">Enter password:</label>
    <input type="password" class="form-control" id="password" placeholder="Password" name="password">
  </div>
			
			<button type="submit" class="btn btn-primary" name="send">Submit</button>
        </form>
</body>
</html>