<?php
session_start();

require_once("classes.php");
$system = new System();

if(isset($_SESSION['user_id'])) {
    header("Location: {$system->link}}");
    exit;
}

// Start display
require($system->fetchLayoutByName(System::DEFAULT_LAYOUT));
echo $heading;
echo $top_menu;
echo $header;
echo str_replace("[HEADER_TITLE]", "Create Account", $body_start);

if(!$system->register_open) {
    echo "Sorry, not currently functional. Check back later.";

    echo $login_menu;
    echo $footer;

    exit;
}

$min_user_name_length = User::MIN_NAME_LENGTH;
$max_user_name_length = 18;
$min_password_length = User::MIN_PASSWORD_LENGTH;

if(isset($_GET['act'])) {
    if($_GET['act'] == 'verify') {
        $key = $system->clean($_GET['verify_key']);
        $user_name = $system->clean($_GET['username']);

        $result = $system->query("UPDATE `users` SET `user_verified`=1 WHERE `user_name`='$user_name' AND `verify_key`='$key' LIMIT 1");
        if($system->db_last_affected_rows > 0) {
            $system->message("Account activated! You may log in and start playing. <a href='{$system->link}'>Continue</a>");
            $system->printMessage();
        }
        else {
            $accountData = $system->query("SELECT `user_verified` FROM `users` WHERE `user_name`='$user_name' AND `verify_key`='$key' LIMIT 1");
            if(!$system->db_last_num_rows) {
                $system->message("User not found!. Please contact an administrator. Staff can be found on
                        <a href='{$system->links['discord']}' target='_blank'>Discord.</a>");
            }
            else {
                $accountData = $system->db_fetch($accountData);
                if($accountData['user_verified']) {
                    $system->message("Your account is already activated and you may login!");
                }
                else {
                    $system->message("Account activation error! Please contact an administrator. Staff can be found on
                        <a href='{$system->links['discord']}' target='_blank'>Discord.</a>");
                }
            }
            $system->printMessage();
        }
    }
    else if($_GET['act'] == 'resend_verification') {
        $user_name = $system->clean($_GET['username']);
        $result = $system->query("SELECT `email`, `verify_key`, `user_verified` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
        if($system->db_last_num_rows == 0) {
            $system->message("Invalid user!");
            $system->printMessage();
        }
        else {
            $result = $system->db_fetch($result);

            $subject = "Shinobi-Chronicles account verification";
            $message = "Welcome to Shinobi-Chronicles RPG. Please visit the link below to verify your account: \r\n" .
                "{$system->link}register.php?act=verify&username={$user_name}&verify_key={$result['verify_key']}";
            $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
            $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
            if(mail($result['email'], $subject, $message, $headers)) {
                ;
                $system->message("Email sent! Please check your email (including spam folder)");
            }
            else {
                $system->message("There was a problem sending the email to the address provided: $email
				Please contact a staff member on the forums for manual activation."
                );
            }
            $system->printMessage();
        }
    }
}

$alpha_code = 'keepoutNub';

// Load villages
$result = $system->query("SELECT `name`, `location` FROM `villages`");
$villages = [];
while($row = mysqli_fetch_array($result)) {
    $villages[$row['name']] = $row;
}

$register_ok = false;
if(isset($_POST['register'])) {
    try {
        if(isset($_POST['user_name'])) {
            $user_name = $system->clean(trim($_POST['user_name']));
        }
        if(isset($_POST['password'])) {
            $password = trim($_POST['password']);
        }
        if(isset($_POST['confirm_password'])) {
            $confirm_password = trim($_POST['confirm_password']);
        }
        if(isset($_POST['email'])) {
            $email = $system->clean(trim($_POST['email']));
        }
        if(isset($_POST['gender'])) {
            $gender = trim($_POST['gender']);
        }
        if(isset($_POST['village'])) {
            $village = trim($_POST['village']);
        }

        // Username
        if(strlen($user_name) < $min_user_name_length) {
            throw new Exception("Please enter a username longer than 3 characters!");
        }
        if(strlen($user_name) > $max_user_name_length) {
            throw new Exception("Please enter a username shorter than " . ($max_user_name_length + 1) . " characters!");
        }

        if(!preg_match('/^[a-zA-Z0-9_-]+$/', $user_name)) {
            throw new Exception("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
        }

        // Banned words
        if($system->explicitLanguageCheck($user_name)) {
            throw new Exception("Inappropriate language is not allowed in usernames!");
        }

        // Password
        if(strlen($password) < $min_password_length) {
            throw new Exception("Please enter a password longer than 3 characters!");
        }

        if(preg_match('/[0-9]/', $password) == false) {
            throw new Exception("Password must include at least one number!");
        }
        if(preg_match('/[A-Z]/', $password) == false) {
            throw new Exception("Password must include at least one capital letter!");
        }
        if(preg_match('/[a-z]/', $password) == false) {
            throw new Exception("Password must include at least one lowercase letter!");
        }
        $common_passwords = [
            'Password1',
        ];
        foreach($common_passwords as $pword) {
            if($pword == $password) {
                throw new Exception("This password is too common, please choose a more unique password!");
            }
        }

        if($password != $confirm_password) {
            throw new Exception("The passwords do not match!");
        }

        // Email
        if(strlen($email) < 5) {
            throw new Exception("Please enter a valid email address!");
        }

        $email_pattern = '/^[\w\-\.\+]+@[\w\-\.]+\.[a-zA-Z]{2,4}$/';
        if(!preg_match($email_pattern, $email)) {
            throw new Exception("Please enter a valid email address!");
        }

        // Check for hotmail

        $email_arr = explode('@', $email);
        $email_arr[1] = strtolower($email_arr[1]);

        if(array_search($email_arr[1], System::UNSERVICEABLE_EMAIL_DOMAINS) !== false) {
            throw new Exception(implode(' / ', System::UNSERVICEABLE_EMAIL_DOMAINS) . " emails are currently not supported!");
        }

        // Check for username/email existing
        $result = $system->query("SELECT `user_id`, `user_name`, `email` FROM `users`
			WHERE `email`='$email' OR `user_name`='$user_name' LIMIT 1"
        );
        if(mysqli_num_rows($result) > 0) {
            $result = mysqli_fetch_assoc($result);
            if(strtolower($result['user_name']) == strtolower($user_name)) {
                throw new Exception("Username already in use!");
            }
            else if(strtolower($result['email']) == strtolower($email)) {
                throw new Exception("Email address already in use!");
            }
        }

        // Gender
        if(!in_array($gender, User::$genders, true)) {
            throw new Exception("Invalid gender!");
        }

        // Village
        if(!isset($villages[$village])) {
            throw new Exception("Invalid village!");
        }

        // Encrypt password
        $password = $system->hash_password($password);

        $verification_code = sha1(mt_rand(1, 1337000));

        User::create(
            $system,
            $user_name,
            $password,
            $email,
            $gender,
            $village,
            $villages[$village]['location'],
            $verification_code
        );

        $subject = 'Shinobi-Chronicles account verification';
        $message = "Welcome to Shinobi-Chronicles RPG. Please visit the link below to verify your account: \r\n
		{$system->link}register.php?act=verify&username={$user_name}&verify_key=$verification_code";
        $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
        $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
        if(mail($email, $subject, $message, $headers)) {
            ;
            $system->message("Account created!<br />Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!");
        }
        else {
            $system->message("There was a problem sending the email to the address provided: $email Please contact a staff member on the forums for manual activation.");
        }

        $register_ok = true;
    } catch(Exception $e) {

        $system->message($e->getMessage());
        $system->printMessage();
    }
}

if(!$register_ok) {
    // Set variables for pre-filling form info if user has previously attempted registry
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $village = $_POST['village'];


    $stone = 0;
    $cloud = 0;
    $leaf = 0;
    $sand = 0;
    $mist = 0;



    echo "
	<style type='text/css'>
	label {
		width:125px;
		display:inline-block;
	}
	</style>

	<table class='table'><tr><th>Create an account</th></tr>
	<tr><td>
		<form action='{$system->link}register.php' method='post'>
		<label for='user_name'>Username</label>
			<input type='text' name='user_name' value='$user_name' /><br />
			<br />
		<label for='password'>Password</label>
			<input type='password' name='password' /><br />
			<br />
		<label for='confirm_password'>Confirm Password</label>
			<input type='password' name='confirm_password' /><br />
			<br />
		<label for='email'>Email</label>
			<input type='text' name='email' value='$email' /><br />
			<span style='font-style:italic;font-size:0.9em;'>(Note: Currently we cannot send emails to addresses from: " . implode(', ', System::UNSERVICEABLE_EMAIL_DOMAINS) . ")</span>
			<br />
			<br />
		<label for='gender'>Gender</label><br />";
        foreach(User::$genders as $gender_opt) {
                echo "<input type='radio' name='gender' value='$gender_opt' " . ($gender == $gender_opt ? "checked='checked'" : "") . " /> $gender_opt<br />";
            }
			echo"<br />

    <label for='village'>Village</label><br />
			<select name='village'>";
    foreach($villages as $name => $loop_village) {
        echo "<option value='$name' " . ($village == $name ? "selected='selected'" : "") . ">$name</option>";
    }

    echo "</select><br />
		<br />
		<span style='font-size:0.9em;'>By clicking 'Register' I affirm that I have read and agree to abide by the <a href='./rules.php'>Rules</a> and
		<a href='./terms.php'>Terms of Service</a>. I understand that if I fail to abide by the rules as determined by the moderating staff, I may be temporarily or permanently banned
		and that I will not be compensated for time lost. I also understand that any actions taken by anyone on my account are
		my responsibility.</span><br />
		<br />
		<input type='submit' name='register' value='Register' />
		</form>
	</td></tr></table>";
}
else {
    $system->printMessage();
}

echo $login_menu;
echo $footer;
