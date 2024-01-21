<?php
global $system;
global $initial_home_view;
global $register_pre_fill;

if(isset($_POST['register'])) {
    try {
        if (!$system->register_open) {
            throw new RuntimeException("Sorry, not currently functional. Check back later.");
        }

        $user_name = $system->db->clean($_POST['user_name']);
        $password = $system->db->clean($_POST['password']);
        $confirm_password = $system->db->clean($_POST['confirm_password']);
        $email = $system->db->clean($_POST['email']);
        $gender = $system->db->clean($_POST['gender']);
        $village = $system->db->clean($_POST['village']);

        // Store variables for any errors - force re-enter of password (security reasons)
        $system->homeVars['register_prefill'] = [
            'user_name' => $user_name,
            'email' => $email,
            'gender' => $gender,
            'village' => $village,
        ];

        // Username
        if (strlen($user_name) < User::MIN_NAME_LENGTH) {
            throw new RuntimeException("Please enter a username longer than " . (User::MIN_NAME_LENGTH - 1) . " characters!");
        }
        if (strlen($user_name) > User::MAX_NAME_LENGTH) {
            throw new RuntimeException("Please enter a username shorter than " . (User::MAX_NAME_LENGTH + 1) . " characters!");
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $user_name)) {
            throw new RuntimeException("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
        }

        // Banned words
        if ($system->explicitLanguageCheck($user_name)) {
            throw new RuntimeException("Inappropriate language is not allowed in usernames!");
        }

        // Password
        if (strlen($password) < User::MIN_PASSWORD_LENGTH) {
            throw new RuntimeException("Please enter a password longer than " . (User::MIN_NAME_LENGTH - 1) . " characters!");
        }

        if (preg_match('/[0-9]/', $password) == false) {
            throw new RuntimeException("Password must include at least one number!");
        }
        if (preg_match('/[A-Z]/', $password) == false) {
            throw new RuntimeException("Password must include at least one capital letter!");
        }
        if (preg_match('/[a-z]/', $password) == false) {
            throw new RuntimeException("Password must include at least one lowercase letter!");
        }

        // Todo: Brush up this logic
        $common_passwords = [
            'Password1',
        ];
        foreach ($common_passwords as $pword) {
            if ($pword == $password) {
                throw new RuntimeException("This password is too common, please choose a more unique password!");
            }
        }

        if ($password != $confirm_password) {
            throw new RuntimeException("The passwords do not match!");
        }

        // Email
        if (strlen($email) < 5) {
            $register_pre_fill['email'] = '';
            throw new RuntimeException("Please enter a valid email address!");
        }

        /** @noinspection RegExpRedundantEscape */
        $email_pattern = '/^[\w\-\.\+]+@[\w\-\.]+\.[a-zA-Z]{2,4}$/';
        if (!preg_match($email_pattern, $email)) {
            $register_pre_fill['email'] = '';
            throw new RuntimeException("Please enter a valid email address!");
        }

        // Check for emails that can't be sent
        $email_arr = explode('@', $email);
        $email_arr[1] = strtolower($email_arr[1]);

        if (array_search($email_arr[1], System::UNSERVICEABLE_EMAIL_DOMAINS) !== false) {
            throw new RuntimeException(implode(' / ', System::UNSERVICEABLE_EMAIL_DOMAINS) . " emails are currently not supported!");
        }

        // Check for username/email existing
        $result = $system->db->query(
            "SELECT `user_id`, `user_name`, `email` FROM `users`
                    WHERE `email`='$email' OR `user_name`='$user_name' LIMIT 1"
        );
        if (mysqli_num_rows($result) > 0) {
            $result = mysqli_fetch_assoc($result);
            if (strtolower($result['user_name']) == strtolower($user_name)) {
                throw new RuntimeException("Username already in use!");
            } else if (strtolower($result['email']) == strtolower($email)) {
                throw new RuntimeException("Email address already in use!");
            }
        }

        // Gender
        if (!in_array($gender, User::$genders, true)) {
            throw new RuntimeException("Invalid gender!");
        }

        // Village
        // Load villages
        $result = $system->db->query("SELECT `villages`.`name`, `x`, `y`, `map_id` FROM `villages`
                INNER JOIN `maps_locations` on `villages`.`map_location_id` = `maps_locations`.`location_id`
            ");
        $villages = [];
        while ($row = mysqli_fetch_array($result)) {
            $villages[$row['name']] = $row;
        }
        if (!isset($villages[$village])) {
            throw new RuntimeException("Invalid village!");
        }

        // Encrypt password
        $password = $system->hash_password($password);

        $verification_code = sha1(mt_rand(1, 1337000));

        $village_coords = new TravelCoords($villages[$village]['x'], $villages[$village]['y'], $villages[$village]['map_id']);

        User::create(
            $system,
            $user_name,
            $password,
            $email,
            $gender,
            $village,
            $village_coords->toString(),
            $verification_code
        );

        $subject = 'Shinobi-Chronicles account verification';
        $message = "Welcome to Shinobi-Chronicles RPG. Please visit the link below to verify your account: \r\n
		    {$system->router->base_url}register.php?act=verify&username={$user_name}&verify_key=$verification_code";
        $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
        $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
        if (mail($email, $subject, $message, $headers)) {
            ;
            $system->message("Account created! Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!");
            //$system->homeVars['messages']['login'] = "Account created! Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!";
            $system->homeVars['messages']['login'] = "Account created! Log in to continue.";
        } else {
            $system->message("There was a problem sending the email to the address provided: $email. If you are unable to log in please submit a ticket or contact a staff member on discord for manual activation.");
            $system->homeVars['messages']['login'] = "Account created! Log in to continue.";
        }
    } catch (Exception $e) {
        $system->db->rollbackTransaction();
        $system->message($e->getMessage());
        error_log($e->getMessage());
        $system->homeVars['errors']['register'] = $e->getMessage();
        $system->homeVars['view'] = 'register';
    }
}