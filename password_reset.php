<?php
session_start();

require_once("classes/_autoload.php");
$system = System::initialize();
if(isset($_SESSION['user_id'])) {
    header("Location: {$system->router->base_url}");
    exit;
}

$system->db->connect();

// Start display
$layout = $system->setLayoutByName("shadow_ribbon");
$layout->renderBeforeContentHTML($system, null, 'Rules');

// If user confirms password reset, check input and reset
if($_POST) {
	$con = $system->db->connect();

	$user_name = $system->db->clean($_POST['username']);
	$email = $system->db->clean($_POST['email']);
	$query = "SELECT `user_id` FROM users WHERE user_name='$user_name' AND email='$email' LIMIT 1";
	$result = $system->db->query($query);
	if($system->db->last_num_rows == 0) {
		$system->message("Invalid username or email address! Please submit a
		    <a href='{$system->router->base_url}support.php'>support request</a>");
		$system->printMessage();
	}
	else {
		$result = $system->db->fetch($result);
		$userid = $result['user_id'];

		$hash = sha1(mt_rand(1, 1000000));
		$new_password = substr($hash, 0, 16);
		$hashed_password = $system->hash_password($new_password);
		$system->db->query("UPDATE users SET password='{$hashed_password}' WHERE user_id=$userid");

		$subject = "Shinobi Chronicles - Password Reset";
		$headers = "From: Shinobi Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
$message = "A password reset was requested for your account $user_name. Your temporary password is:
$new_password
You can login at {$system->router->base_url} with
your temporary password. We strongly suggest you change it to something easier to remember;
It can be changed in the settings page, found on your profile.

If this is your account but you did not request a password reset, please submit a support request: <a href='{$system->router->base_url}support.php'>here</a>.

This message was sent because someone signed up at {$system->router->base_url} with this email
address and requested a password reset. If this is not your account, please disregard this email or submit a
 <a href='{$system->router->base_url}support.php'>support.php</a> to have your address removed from our records.";
		mail($email, $subject, $message, $headers);
		$system->message("Password sent!");
		$system->printMessage();
	}
}

// Print form for password reset
echo "<table class='table' style='width: 95%'>
    <tr><th>Password Reset</th></tr>
    <tr><td style='text-align:center;'>
        Enter your username and the email address you signed up with to have a new password sent to you.
        NOTE: This will reset your current password.<br />
        <form action='' method='post'>
            <div style='float:left;width:140px;text-align:left;'>
                <div style='margin-top:3px;margin-bottom:3px;'>Username: </div>
                <div style='margin-top:3px;margin-bottom:3px;'>Email address: </div>
            </div>
            <div style='text-align:left;margin-left:140px;'>
                <input type='text' name='username' /><br />
                <input type='text' name='email' /><br />
            </div>
            <input type='submit' value='Reset' />
        </form>
    </td></tr>
</table>";

$layout->renderAfterContentHTML($system, $player ?? null);

