<?php

require_once __DIR__ . '/Notification.php';

class Notifications {
    // TODO: Refactor into a separate function that returns JSON for notifications and drop the $ajax parameter
    public static function displayNotifications(System $system, User $player, bool $ajax = false) {
        $notifications = Notifications::getNotifications($system, $player);

        if(!$ajax) {
            echo "<div id='notifications'>";
        }

        if(!empty($notifications)) {
            if($player->layout == 'shadow_ribbon' || $player->layout === 'blue_scroll') {
                if(count($notifications) > 1) {
                    echo "<img class='slideButtonLeft' onclick='slideNotificationLeft()' src='./images/left_arrow.png' />";
                }

                echo "<div id='notificationSlider'>";

                foreach($notifications as $id => $notification) {
                    $extra_class_names = $notification->critical ? 'red' : '';
                    echo "<p class='notification' data-notification-id='$id'>
                        <a class='link {$extra_class_names}' href='{$notification->action_url}'>{$notification->title}</a>
                    </p>";
                }

                echo "</div>";
                if(count($notifications) > 1) {
                    echo "<img class='slideButtonRight' onclick='slideNotificationRight()' src='./images/right_arrow.png' />";
                }

            }
            else if($player->layout == 'geisha') {
                foreach($notifications as $id => $notification) {
                    $extra_class_names = $notification->critical ? 'red' : '';
                    echo "<p class='notification' style='margin-top:5px;margin-bottom:10px;'>
                        <a class='link {$extra_class_names}' href='{$notification->action_url}'>{$notification->title}</a>
                    </p>";
                }
            }
            else {
                echo "<div style='margin:0;border:1px solid #AAAAAA;border-radius:inherit;'>
                    <div class='header'>
                    Notifications
                    </div>";

                foreach($notifications as $id => $notification) {
                    $extra_class_names = $notification->critical ? 'red' : '';
                    echo "<p class='notification'>
                        <a class='link {$extra_class_names}' href='{$notification->action_url}'>{$notification->title}</a>
                    </p>";
                }
                echo "</div>";
            }
        }

        if(!$ajax) {
            echo "</div>";
        }
    }

    /**
     * @param System $system
     * @param User   $player
     * @return Notification[]
     */
    public static function getNotifications(System $system, User $player): array {
        /** @var Notification[] $notifications */
        $notifications = [];

        if($player->battle_id > 0) {
            $result = $system->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1"
            );
            if($system->db_last_num_rows == 0) {
                $player->battle_id = 0;
            }
            else {
                $result = $system->db_fetch($result);
                $link = null;
                switch($result['battle_type']) {
                    case Battle::TYPE_AI_ARENA:
                        $link = $system->links['arena'];
                        break;
                    case Battle::TYPE_AI_MISSION:
                        $link = $system->links['mission'];
                        break;
                    case Battle::TYPE_AI_RANKUP:
                        $link = $system->links['rankup'];
                        break;
                    case Battle::TYPE_SPAR:
                        $link = $system->links['spar'];
                        break;
                    case Battle::TYPE_FIGHT:
                        $link = $system->links['battle'];
                        break;
                    /* case Battle::TYPE_CHALLENGE:
                         $link = $system->links['spar'];
                         break;*/
                }
                if($link) {
                    $notifications[] = new Notification($link, "In battle!", critical: true);
                }
            }
        }

        $result = $system->query(
            "SELECT `message_id` FROM `private_messages` 
		WHERE `recipient`='{$player->user_id}' AND `message_read`=0 LIMIT 1"
        );
        if($system->db_last_num_rows) {
            $notifications[] = new Notification("{$system->link}?id=2", "You have unread PM(s)");
        }

        if($player->isModerator()) {
            $result = $system->query(
                "SELECT `report_id` FROM `reports` WHERE `status` = 0 AND `staff_level` < $player->staff_level LIMIT 1"
            );
            if($system->db_last_num_rows > 0) {
                $notifications[] = new Notification("{$system->links['report']}&page=view_all_reports", "New report(s)!");
            }
        }

        if($player->challenge) {
            $notifications[] = new Notification($system->links['spar'], "Challenged!");
        }

        if($player->team_invite) {
            $notifications[] = new Notification("{$system->link}?id=24", "Invited to team!");
        }

        if($player->spouse < 0) {
            $notifications[] = new Notification($system->links['marriage'], "Proposal received!");
        }
        // "<a class='link' href='{$system->links['marriage']}'>Proposal received!</a>";

        return $notifications;
    }
}