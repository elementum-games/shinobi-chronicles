<?php
/*
File: 		profile.php
Coder:		Levi Meahan
Created:	02/26/2013
Revised:	08/24/2013 by Levi Meahan
Purpose:	Functions for displaying user profile
Algorithm:	See master_plan.html
*/

function userProfile() {
    global $system;

    global $player;
    global $self_link;

    // Submenu
    renderProfileSubmenu();

    // Level up/rank up checks
    $exp_needed = $player->expForNextLevel();

    // Level up
    if($player->level < $player->max_level && $player->exp >= $exp_needed) {
        if($player->battle_id) {
            echo "<p style='text-align:center;font-style:italic;'>
				You must be out of battle to level up.</p>";
        }
        else {
            require("levelUp.php");
            levelUp();
            $exp_needed = $player->expForNextLevel();
        }
    }
    // Rank up
    else if($player->level >= $player->max_level && $player->exp >= $exp_needed && $player->rank < System::SC_MAX_RANK) {
        if($player->battle_id > 0 or !$player->in_village) {
            echo "<p style='text-align:center;font-style:italic;'>
				You must be out of battle and in your village to rank up.</p>";
        }
        else {
            if($player->exam_stage) {
                $prompt = "Resume exam for the next rank";
            }
            else {
                $prompt = "Take exam for the next rank";
            }

            echo "<p style='text-align:center;font-size:1.1em;'>
				<a class='button' style='padding:5px 10px 4px;margin-bottom:0;text-decoration:none;' href='{$system->links['rankup']}'>{$prompt}</a>
			</p>";
        }
    }

    $page = 'profile';
    if(isset($_GET['page'])) {
        switch($_GET['page']) {
            case 'send_money':
                if($player->rank > 1) {
                    $page = 'send_money';
                }
                break;
            case 'send_ak':
                if($player->rank > 1) {
                    $page = 'send_ak';
                }
                break;
        }
    }

    // Process input
    if(isset($_POST['send_currency'])) {
        $recipient = $system->clean($_POST['recipient']);
        $amount = (int)$system->clean($_POST['amount']);

        try {
            if(strtolower($recipient) == strtolower($player->user_name)) {
                throw new Exception("You cannot send money/AK to yourself!");
            }
            $result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_name`='$recipient' LIMIT 1");
            if(!$system->db_last_num_rows) {
                throw new Exception("Invalid user!");
            }
            else {
                $recipient = $system->db_fetch($result);
            }
            if(isset($_POST['yen'])) {
                $type = 'money';
            }
            else if(isset($_POST['kunai'])) {
                $type = 'premium_credits';
            }
            else {
                throw new Exception("Invalid Currency Type!");
            }
            if($amount <= 0 && !$player->isHeadAdmin()) {
                throw new Exception("Invalid amount!");
            }
            if($amount > $player->$type) {
                throw new Exception("You do not have that much money/AK!");
            }
            $player->$type -= $amount;
            $system->query("UPDATE `users` SET `{$type}`=`{$type}` + $amount WHERE `user_id`='{$recipient['user_id']}' LIMIT 1");
            if($type == 'money') {
                $system->log(
                    'money_transfer',
                    'Money Sent',
                    "{$amount} yen - #{$player->user_id} ($player->user_name) to #{$recipient['user_id']}"
                );
                $system->send_pm('Currency Transfer System', $recipient['user_id'], 'Money Received', $player->user_name . " has sent you &yen;$amount.");
            }
            else {
                $system->log(
                    'premium_credit_transfer',
                    'Premium Credits Sent',
                    "{$amount} AK - #{$player->user_id} ($player->user_name) to #{$recipient['user_id']}"
                );
                $system->send_pm('Currency Transfer System', $recipient['user_id'], 'AK Received', $player->user_name . " has sent you $amount Ancient Kunai.");
            }

            $system->message("Currency sent!");
        } catch(Exception $e) {
            $system->message($e->getMessage());
        }
        $system->printMessage();
    }
    if($page == 'send_money' || $page == 'send_ak') {
        $type = ($page == 'send_money') ? "Money" : "AK";
        $currency = ($type == 'Money') ? "money" : "premium_credits";
        $hidden = ($type == 'Money') ? "yen" : "kunai";

        $recipient = !empty($_GET['recipient']) ? $_GET['recipient'] : '';

        echo "<table class='table'><tr><th>Send {$type}</th></tr>
		<tr><td style='text-align:center;'>
		<form action='{$self_link}&page={$page}' method='post'>
		<b>Your {$type}:</b> {$player->$currency}<br />
		<br />
		Send {$type} to:<br />
		<input type='hidden' name='{$hidden}' value='1'/>
		<input type='text' name='recipient' value='{$recipient}' /><br />
		Amount:<br />
		<input type='text' name='amount' /><br />
		<input type='submit' name='send_currency' value='Send {$type}' />
		</form>
		</td></tr></table>";
    }
    else if($page == 'profile') {
       require 'templates/profile.php';
    }
}

function renderProfileSubmenu() {
    global $system;
    global $player;
    global $self_link;

    $submenu_links = [
        [
            'link' => $system->links['profile'],
            'title' => 'Character',
        ],
        [
            'link' => $system->links['settings'],
            'title' => 'Settings',
        ],
    ];
    if($player->rank > 1) {
        $submenu_links[] = [
            'link' => $system->links['profile'] . "&page=send_money",
            'title' => 'Send Money',
        ];
        $submenu_links[] = [
            'link' => $system->links['profile'] . "&page=send_ak",
            'title' => 'Send AK',
        ];
    }
    if($player->bloodline_id) {
        $submenu_links[] = [
            'link' => $system->links['bloodline'],
            'title' => 'Bloodline',
        ];
    }

    echo "<div class='submenu'>
    <ul class='submenu'>";
    $submenu_link_width = round(100 / count($submenu_links), 1);
    foreach($submenu_links as $link) {
        echo "<li style='width:{$submenu_link_width}%;'><a href='{$link['link']}'>{$link['title']}</a></li>";
    }
    echo "</ul>
    </div>
    <div class='submenuMargin'></div>
    ";
}
