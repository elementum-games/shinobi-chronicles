<?php
session_start();

require_once("classes/_autoload.php");
$system = new System();
if(isset($_SESSION['user_id'])) {
    header("Location: {$system->link}");
    exit;
}

$system->dbConnect();

// Start display
require("layout/" . System::DEFAULT_LAYOUT . ".php");
echo $heading;
echo $top_menu;
echo $header;
echo str_replace("[HEADER_TITLE]", "Reset your password", $body_start);

// If user confirms password reset, check input and reset
if($_POST) {
	$con = $system->dbConnect();
	
	$user_name = $system->clean($_POST['username']);
	$email = $system->clean($_POST['email']);
	$query = "SELECT `user_id` FROM users WHERE user_name='$user_name' AND email='$email' LIMIT 1";
	$result = $system->query($query);
	if($system->db_last_num_rows == 0) {
		$system->message("Invalid username or email address! Please submit a 
		    <a href='{$system->link}support.php'>support request</a>");
		$system->printMessage();
	}
	else {
		$result = $system->db_fetch($result);
		$userid = $result['user_id'];
		
		$hash = sha1(mt_rand(1, 1000000));
		$new_password = substr($hash, 0, 16);
		$hashed_password = $system->hash_password($new_password);
		$system->query("UPDATE users SET password='{$hashed_password}' WHERE user_id=$userid");
		
		$subject = "Shinobi Chronicles - Password Reset";
		$headers = "From: Shinobi Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
$message = "A password reset was requested for your account $user_name. Your temporary password is:
$new_password
You can login at {$system->link} with 
your temporary password. We strongly suggest you change it to something easier to remember;
It can be changed in the settings page, found on your profile.

If this is your account but you did not request a password reset, please submit a support request: <a href='{$system->link}support.php'>here</a>.

This message was sent because someone signed up at {$system->link} with this email 
address and requested a password reset. If this is not your account, please disregard this email or submit a 
 <a href='{$system->link}support.php'>support.php</a> to have your address removed from our records.";
		mail($email, $subject, $message, $headers);
		$system->message("Password sent!");
		$system->printMessage();
	}	
}

// Print form for password reset
echo "<table class='table' cellspacing='0' width='95%'>";
echo "<tr><th>Password Reset</th></tr>";
echo "<tr><td style='text-align:center;'>";
echo "Enter your username and the email address you signed up with to have a new password sent to you.
NOTE: This will reset your current password.<br />";
echo "<form action='' method='post'>";
echo "<div style='float:left;width:140px;text-align:left;'>";
echo "<div style='margin-top:3px;margin-bottom:3px;'>Username: </div>";
echo "<div style='margin-top:3px;margin-bottom:3px;'>Email address: </div>";
echo "</div><div style='text-align:left;margin-left:140px;'>";
echo "<input type='text' name='username' /><br />";
echo "<input type='text' name='email' /><br />";
echo "</div>";
echo "<input type='submit' value='Reset' />";
echo "</form>";
echo "</td></tr></table>";


echo $login_menu;
echo $footer;



?>