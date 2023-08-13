<?php
/*
File: 		profile.php
Coder:		Levi Meahan
Created:	02/26/2013
Revised:	08/24/2013 by Levi Meahan
Purpose:	Functions for displaying user profile
Algorithm:	See master_plan.html
*/

require_once __DIR__ . '/../classes/notification/NotificationManager.php';

/**
 * @throws RuntimeException
 */
function userProfile() {
    global $system;
    global $player;

    $use_new_layout = $system->layout->usesV2Interface();

    // Submenu
    if(!$use_new_layout) {
        renderProfileSubmenu();
    }

    // Level up/rank up checks
    $exp_needed = $player->expForNextLevel();

    // Level up
    if($player->level < $player->rank->max_level
        && $player->exp >= $exp_needed
        && ($player->level_up || $player->exp > $player->expForNextLevel(5))
    ) {
        if($player->battle_id) {
            require 'templates/level_rank_up/level_up_in_battle.php';
        }
        else {
            require("levelUp.php");
            levelUp();
            $exp_needed = $player->expForNextLevel();
        }
    }
    // Rank up
    else if($player->level >= $player->rank->max_level && $player->exp >= $exp_needed && $player->rank_num < System::SC_MAX_RANK && $player->rank_up) {
        // Create notification
        $new_notification = new NotificationDto(
            type: "rank",
            message: "Rank up available",
            user_id: $player->user_id,
            created: time(),
            alert: false,
        );
        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);

        if($player->battle_id > 0 or !$player->in_village) {
            require "templates/level_rank_up/rank_up_in_battle.php";
        }
        else {
            $rankManager = new RankManager($system);
            $rankManager->loadRanks();
            $exam_name = $rankManager->ranks[$player->rank_num + 1]->name . " Exam";
            if($player->exam_stage) {
                $prompt = "Resume $exam_name";
            }
            else {
                $prompt = "Take $exam_name";
            }
            require "templates/level_rank_up/rank_up_prompt.php";
        }
    }

    $page = $_GET['page'] ?? 'profile';

    if($use_new_layout) {
        require 'templates/profile_v2.php';
    }
    else {
        require 'templates/profile.php';
    }
}



/**
 * @throws RuntimeException
 */
function renderProfileSubmenu(): void {
    global $system;
    global $player;
    global $self_link;

    $submenu_links = [
        [
            'link' => $system->router->links['profile'],
            'title' => 'Character',
        ],
    ];
    if($player->rank_num > 1) {
        $submenu_links[] = [
            'link' => $system->router->links['send_money'],
            'title' => 'Send ' . Currency::MONEY_NAME . '/' . Currency::PREMIUM_SYMBOL,
        ];
    }
    if($player->bloodline_id) {
        $submenu_links[] = [
            'link' => $system->router->links['bloodline'],
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
