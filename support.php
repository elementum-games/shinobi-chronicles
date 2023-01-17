<?php
session_start();

require_once("classes.php");

$system = new System();
$guest_support = true;
$layout = 'layout/' . System::DEFAULT_LAYOUT . '.php';
$self_link = $system->link . 'support.php';
$staff_level = 0;
$user_id = 0;

if(isset($_SESSION['user_id'])) {
    $guest_support = false;
    $player = new User($_SESSION['user_id']);
    $player->loadData();
    $layout = $system->fetchLayoutByName($player->layout);
    $staff_level = $player->staff_level;
    $user_id = $player->user_id;

    $supportSystem = new SupportManager($system, $player);
}
else {
    $supportSystem = new SupportManager($system);
}

$request_types = $supportSystem->getSupportTypes($staff_level);
$supportCreated = false;

require($layout);

echo $heading;
echo $top_menu;
echo $header;
echo str_replace("[HEADER_TITLE]", "Support", $body_start);

if(!$guest_support) {
    //Form submitted // 11/6/21 SM{V2} supported
    if(isset($_POST['add_support']) || isset($_POST['add_support_prem']) || isset($_POST['confirm_prem_support'])) {
        try {
            $addSupport = true;
            $request_type = $system->clean($_POST['support_type']);
            $subject = $system->clean($_POST['subject']);
            $subjectLength = strlen($subject);
            $message = $system->clean($_POST['message']);
            $messageLength = strlen($message);
            $cost = ($supportSystem->requestPremiumCosts[$request_type] ?? 0);
            $premium = ($cost > 0 && isset($_POST['confirm_prem_support'])) ? 1 : 0;

            // Validate support
            if (!in_array($request_type, $request_types)) {
                throw new Exception("Invalid support type!");
            }
            if ($subjectLength < SupportManager::$validationConstraints['subject']['min']) {
                throw new Exception("Subject must be at least " .
                    SupportManager::$validationConstraints['subject']['min'] . " characters!");
            }
            if ($subjectLength > SupportManager::$validationConstraints['subject']['max']) {
                throw new Exception("Subject must not exceed " .
                    SupportManager::$validationConstraints['subject']['max'] . " characters!");
            }
            if ($messageLength < SupportManager::$validationConstraints['message']['min']) {
                throw new Exception("Content must be at least " .
                    SupportManager::$validationConstraints['message']['min'] . " characters!");
            }
            if ($messageLength > SupportManager::$validationConstraints['message']['max']) {
                throw new Exception("Content must not exceed " .
                    SupportManager::$validationConstraints['message']['max'] . " characters!");
            }

            // Premium cost
            if (isset($_POST['confirm_prem_support'])) {
                if($player->getPremiumCredits() < $cost) {
                    throw new Exception("You need {$cost}AK for this request.");
                }

                $player->subtractPremiumCredits($cost, "Submitted premium {$request_type} support");
                $player->updateData();
            }

            if(isset($_POST['add_support_prem']) && $cost != 0) {
                require('templates/premiumSupportConfirmation.php');
                $addSupport = false; //Confirmation required
            }

            // Add support
            if($addSupport) {
                if ($supportSystem->createSupport($player->user_name, $request_type, $subject, $message, $premium)) {
                    $system->message("Support Submitted!");
                }
                else {
                    $system->message("Error submitting support.");
                }
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }
    if(isset($_POST['add_guest_support'])){
        try {
            $support_key = $system->clean($_POST['support_key']);

            $support_id = $supportSystem->getSupportIdByKey($support_key);

            if(!$support_id) {
                throw new Exception("Support not found!");
            }

            if($supportSystem->assignGuestSupportToUser($support_id)) {
                $system->message("Support assigned to account!");
            } else {
                $system->message("Error adding support to account or support already assigned!");
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    if($system->message && !$system->message_displayed) {
        $system->printMessage();
    }
    if(!isset($_GET['support_id'])) {
        // New Ticket form
        require('templates/supportTicketForm.php');

        // Load user tickets
        $supports = $supportSystem->fetchUserSupports();
        if (!empty($supports)) {
            require('templates/userTickets.php');
        }
    } else {
        $support_id = (int) $_GET['support_id'];
        $support = $supportSystem->fetchSupportByID($support_id);

        if(!$support) {
            $system->message("Support not found!");
            $system->printMessage();
        } else {
            if(isset($_POST['add_response'])) {
                try {
                    $message = $system->clean($_POST['message']);
                    $messageLength = strlen($message);

                    // Validate
                    if ($messageLength < SupportManager::$validationConstraints['message']['min']) {
                        throw new Exception("Content must be at least " .
                            SupportManager::$validationConstraints['message']['min'] . " characters!");
                    }
                    if ($messageLength > SupportManager::$validationConstraints['message']['max']) {
                        throw new Exception("Content must not exceed " .
                            SupportManager::$validationConstraints['message']['max'] . " characters!");
                    }

                    if ($supportSystem->addSupportResponse($support_id, $player->user_name, $message)) {
                            $system->message("Response added!");
                    }
                    else {
                        throw new Exception("Error adding response!");
                    }
                } catch (Exception $e) {
                    $system->message($e->getMessage());
                }
            }
            if(isset($_POST['close_ticket'])) {
                try {
                    $message = $system->clean($_POST['message']);
                    $messageLength = strlen($message);

                    // Validate user owns support
                    if($support['user_id'] != $player->user_id) {
                        throw new Exception("You can only close your supports!");
                    }

                    // Add resopnse
                    if($message != '') {
                        // Validate
                        if ($messageLength < SupportManager::$validationConstraints['message']['min']) {
                            throw new Exception("Content must be at least " .
                                SupportManager::$validationConstraints['message']['min'] . " characters!");
                        }
                        if ($messageLength > SupportManager::$validationConstraints['message']['max']) {
                            throw new Exception("Content must not exceed " .
                                SupportManager::$validationConstraints['message']['max'] . " characters!");
                        }

                        $supportSystem->addSupportResponse($support_id, $player->user_name, $message);
                        if(!$system->db_last_insert_id) {
                            throw new Exception("Error adding response.");
                        }
                    }

                    if($supportSystem->closeSupport($support_id)) {
                        $support['open'] = 0;
                        $system->message("Support closed.");
                    }
                }catch (Exception $e) {
                    $system->message($e->getMessage());
                }
            }

            $responses = $supportSystem->fetchSupportResponses($support_id);
            $self_link .= "?support_id=" . $support_id;
            if($system->message && !$system->message_displayed) {
                $system->printMessage();
            }
            require('templates/displayTicket.php');
        }
    }

    // Load side menu
    $pages = require_once('routes.php');

    if ($player->clan) {
        $pages[20]['menu'] = System::MENU_VILLAGE;
    }
    if ($player->rank >= 3) {
        $pages[24]['menu'] = System::MENU_USER;
    }

    echo $side_menu_start;
    foreach ($pages as $id => $page) {
        if (!isset($page['menu']) || $page['menu'] != System::MENU_USER) {
            continue;
        }

        echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
    }

    echo $action_menu_header;
    if ($player->in_village) {
        foreach ($pages as $id => $page) {
            if (!isset($page['menu']) || $page['menu'] != 'activity') {
                continue;
            }
            // Page ok if an in-village page or player rank is below chuunin
            if ($page['village_ok'] != System::NOT_IN_VILLAGE || $player->rank < 3) {
                echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
            }
        }
    } else {
        foreach ($pages as $id => $page) {
            if (!isset($page['menu']) || $page['menu'] != 'activity') {
                continue;
            }
            if ($page['village_ok'] != System::ONLY_IN_VILLAGE) {
                echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
            }
        }
    }

// In village or not
    if ($player->in_village) {
        echo $village_menu_start;
        foreach ($pages as $id => $page) {
            if (!isset($page['menu']) || $page['menu'] != System::MENU_VILLAGE) {
                continue;
            }

            echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
        }
    }

    if ($player->isModerator() || $player->hasAdminPanel() || $player->isSupportStaff()) {
        echo $staff_menu_header;
        if ($player->isSupportStaff()) {
            echo "<li><a id='sideMenuOption-SupportPanel' href='{$system->link}?id=30'>Support Panel</a></li>";
        }
        if ($player->isModerator()) {
            echo "<li><a id='sideMenuOption-ModPanel' href='{$system->link}?id=16'>Mod Panel</a></li>";
        }
        if ($player->hasAdminPanel()) {
            echo "<li><a id='sideMenuOption-AdminPanel' href='{$system->link}?id=17'>Admin Panel</a></li>";
        }
    }

// Logout timer
    $logout_limit = System::LOGOUT_LIMIT;
    $time_remaining = ($logout_limit * 60) - (time() - $player->last_login);
    $logout_time = System::timeRemaining($time_remaining, 'short', false, true) . " remaining";

    $logout_display = $player->isUserAdmin() ? "Disabled" : $logout_time;
    echo str_replace("<!--LOGOUT_TIMER-->", $logout_display, $side_menu_end);

    if ($logout_display != "Disabled") {
        echo "<script type='text/javascript'>countdownTimer($time_remaining, 'logoutTimer');</script>";
    }
} else {
    // Get support data
    if(isset($_GET['support_key'])) {
        $support_key = $system->clean($_GET['support_key']);
        $email = $system->clean($_GET['email']);
        $supportData = $supportSystem->fetchSupportByKey($support_key, $email);

        if(!$supportData) {
            $system->message("Support not found!");
        }else {
            $responses = $supportSystem->fetchSupportResponses($supportData['support_id']);
        }
    }

    // Add guest support
    if(isset($_POST['add_support'])) {
        try {
            $subject = $system->clean($_POST['subject']);
            $subjectLength = strlen($subject);
            $email = $system->clean($_POST['email']);
            $support_type = $system->clean($_POST['support_type']);
            $name = $system->clean($_POST['name']);
            $message = $system->clean($_POST['message']);
            $messageLength = strlen($message);
            $support_key = sha1(mt_rand(0, 255384));

            // Name validation
            if($name == '') {
                throw new Exception("You must enter your name or display name.");
            }
            if(strlen($name) < 3) {
                throw new Exception("Your name must be at least 3 characters long.");
            }
            if(strlen($name) > 75) {
                throw new Exception("Your name may not exceed 75 characters. Please shorten or use a nick name.");
            }
            // Email validation
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("You must provide a valid email!");
            }
            if(in_array(strtolower(explode('@', $email)[1]), System::UNSERVICEABLE_EMAIL_DOMAINS)) {
                throw new Exception("We are currently unable to support " .
                    implode(' / ', System::UNSERVICEABLE_EMAIL_DOMAINS) . " email types.");
            }
            // Subject validation
            if($subjectLength < SupportManager::$validationConstraints['subject']['min']) {
                throw new Exception("Subject must be at least " . SupportManager::$validationConstraints['subject']['min']
                 . " characters long.");
            }
            if($subjectLength > SupportManager::$validationConstraints['subject']['max']) {
                throw new Exception("Subject may not exceed " . SupportManager::$validationConstraints['subject']['max']
                . " characters.");
            }
            // Message validation
            if($messageLength < SupportManager::$validationConstraints['message']['min']) {
                throw new Exception("Details must be at least " . SupportManager::$validationConstraints['message']['min']
                    . " characters long.");
            }
            if($messageLength > SupportManager::$validationConstraints['message']['max']) {
                throw new Exception("Details may not exceed " . SupportManager::$validationConstraints['message']['max']
                    . " characters.");
            }

            // Create support
            if($supportSystem->createSupport($name, $support_type, $subject, $message, 0, $email, $support_key)) {
                $supportCreated = true;
                // Send email to user
                $subject = "Shinobi-Chronicles support request";
                $message = "Thank you for submitting your support. Click the link below to access your support: \r\n" .
                    "{$system->link}support.php?support_key={$support_key} \r\n" .
                    "If the link does not work, your support key is: {$support_key}";
                $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
                $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
                if(!mail($email, $subject, $message, $headers)) {
                    $system->message("Email failed to send! Make sure you save your support key somewhere!");
                }
            } else {
                $system->message("Error creating support.");
            }
        }catch(Exception $e) {
            $system->message($e->getMessage());
        }
    }
    // Add guest response
    if(isset($_POST['add_response'])) {
        try {
            $message = $system->clean($_POST['message']);

            // Message validation
            if(strlen($message) < SupportManager::$validationConstraints['message']['min']) {
                throw new Exception("Response must be at least " . SupportManager::$validationConstraints['message']['min'] . " characters.");
            }
            if(strlen($message) > SupportManager::$validationConstraints['message']['max']) {
                throw new Exception("Response may not exceed " . SupportManager::$validationConstraints['message']['max'] . " characters.");
            }

            if(!isset($supportData) || !$supportData) {
                throw new Exception("Support not found!");
            }

            if($supportSystem->addSupportResponse($supportData['support_id'], $supportData['user_name'], $message)) {
                $system->message("Response added!");
                $responses = $supportSystem->fetchSupportResponses($supportData['support_id']);
            } else {
                throw new Exception("Error adding response!");
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    // Print system message
    if($system->message != '' && !$system->message_displayed) {
        $system->printMessage();
    }
    require('templates/guestSupport.php');

    echo $login_menu;
}

echo $footer;