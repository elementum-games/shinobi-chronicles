<?php

require_once __DIR__ . "/NotificationDto.php";
require_once __DIR__ . "/../../classes.php";
require_once __DIR__ . "/../ReportManager.php";

class NotificationAPIManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return NotificationDto[]
     */
    public function getUserNotifications(): array
    {
        // Staff check
        if ($this->player->staff_manager->isModerator()) {
            $reportManager = new ReportManager($this->system, $this->player, true);
        }
        // Used for PM checks
        $playerInbox = new InboxManager($this->system, $this->player);

        // Return array
        $notifications = [];
        $notification_ids_to_delete = [];
        $notification_table_result = $this->system->query("SELECT * FROM `notifications` WHERE `user_id` = {$this->player->user_id}");
        while ($row = $this->system->db_fetch($notification_table_result)) {
            // If notification not valid mark for deletion and go to next loop, otherwise add to list
            switch ($row['type']) {
                case "training":
                    if ($this->player->train_time <= 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("training"));
                    }
                    break;
                case "training_complete":
                    if ($this->player->train_time > 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("training"));
                    }
                    break;
                case "specialmission":
                    if ($this->player->special_mission == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("specialmissions"));
                    }
                    break;
                case "specialmission_complete":
                    if ($this->player->special_mission != 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("specialmissions"));
                    }
                    break;
                case "specialmission_failed":
                    if ($this->player->special_mission != 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("specialmissions"));
                    }
                    break;
                case "mission":
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = MissionNotificationDto::fromDb($row, $this->system->router->getUrl("mission"));
                    }
                    break;
                case "mission_team":
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = MissionNotificationDto::fromDb($row, $this->system->router->getUrl("team"));
                    }
                    break;
                case "mission_clan":
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = MissionNotificationDto::fromDb($row, $this->system->router->getUrl("clan"));
                    }
                    break;
                case "rank":
                    if (!($this->player->level >= $this->player->rank->max_level && $this->player->exp >= $this->player->expForNextLevel() && $this->player->rank_num < System::SC_MAX_RANK && $this->player->rank_up)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("profile"));
                    }
                    break;
                case "system":
                    if (false) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl(""));
                    }
                    break;
                case "warning":
                    if (!($this->player->getOfficialWarnings(true))) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl('account_record'));
                    }
                    break;
                case "report":
                    if (!($this->player->staff_manager->isModerator() && $reportManager->getActiveReports(true))) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl('report', ['page' => 'view_all_reports']));
                    }
                    break;
                case "battle":
                    if (json_decode($row['attributes'], true)['battle_id'] != $this->player->battle_id) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("battle"));
                        // to-do switch for URL based on battle type
                    }
                    break;
                case "challenge":
                    if (!($this->player->challenge)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("spar"));
                    }
                    break;
                case "team":
                    if (!($this->player->team_invite)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("team"));
                    }
                    break;
                case "marriage":
                    if (!($this->player->spouse < 0)) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("marriage"));
                    }
                    break;
                case "student":
                    if (!(SenseiManager::hasApplications($this->player->user_id, $this->system))) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl('villageHQ', ['view' => 'sensei']));
                    }
                    break;
                case "inbox":
                    if (!($playerInbox->checkIfUnreadMessages() || $playerInbox->checkIfUnreadAlerts())) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $notifications[] = NotificationDto::fromDb($row, $this->system->router->getUrl("inbox"));
                    }
                    break;
                case "chat":
                    if (false) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $chat_notification = ChatNotificationDto::fromDb($row, $this->system->router->getUrl("chat"));
                        if (isset($chat_notification->post_id)) {
                            $chat_notification->action_url = $this->system->router->getUrl("chat", ['post_id' => $chat_notification->post_id]);
                        }
                        $notifications[] = $chat_notification;
                    }
                default:
                    break;
            }
        }
        if (count($notification_ids_to_delete) > 0) {
            $this->system->query("DELETE FROM `notifications` WHERE `notification_id` IN (" . implode(",", $notification_ids_to_delete) . ")");
        }

        /* Check for general notifications */


        //Battle
        if ($this->player->battle_id > 0) {
            $result = $this->system->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='{$this->player->battle_id}' LIMIT 1"
            );
            if ($this->system->db_last_num_rows == 0) {
                $this->player->battle_id = 0;
            } else {
                $result = $this->system->db_fetch($result);
                $link = null;
                switch ($result['battle_type']) {
                    case Battle::TYPE_AI_ARENA:
                        $link = $this->system->router->getUrl('arena');
                        break;
                    case Battle::TYPE_AI_MISSION:
                        $link = $this->system->router->getUrl('mission');
                        break;
                    case Battle::TYPE_AI_RANKUP:
                        $link = $this->system->router->getUrl('rankup');
                        break;
                    case Battle::TYPE_SPAR:
                        $link = $this->system->router->getUrl('spar');
                        break;
                    case Battle::TYPE_FIGHT:
                        // battle notifications for PVP created on attack
                        break;
                }
                if ($link) {
                    $notifications[] = new NotificationDto(
                        action_url: $link,
                        type: "battle",
                        message: "In battle!",
                        user_id: $this->player->user_id,
                        created: time(),
                        alert: false,
                    );
                }
            }
        }
        //New PM
        if ($playerInbox->checkIfUnreadMessages() || $playerInbox->checkIfUnreadAlerts()) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('inbox'),
                type: "inbox",
                message: "You have unread PM(s)",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Official Warning
        if ($this->player->getOfficialWarnings(true)) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('account_record'),
                type: "warning",
                message: "Official Warning(s)!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //New Report
        if ($this->player->staff_manager->isModerator() && $reportManager->getActiveReports(true)) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('report', ['page' => 'view_all_reports']),
                type: "report",
                message: "New Report(s)!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //New spar
        if ($this->player->challenge) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('spar'),
                type: "challenge",
                message: "Challenged!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Team invite
        if ($this->player->team_invite) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('team'),
                type: "team",
                message: "Invited to team!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Proposal
        if ($this->player->spouse < 0) {
            $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('marriage'),
                type: "marriage",
                message: "Proposal received!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
        }
        //Student Applications
        if (SenseiManager::isSensei($this->player->user_id, $this->system)) {
            if (SenseiManager::hasApplications($this->player->user_id, $this->system)) {
                $notifications[] = new NotificationDto(
                action_url: $this->system->router->getUrl('villageHQ', ['view' => 'sensei']),
                type: "student",
                message: "Application received!",
                user_id: $this->player->user_id,
                created: time(),
                alert: false,
            );
            }
        }

        return $notifications;
    }

    public function closeNotification(int $notification_id): bool {
        $this->system->query("DELETE FROM `notifications` WHERE `notification_id` = {$notification_id}");
        return $this->system->db_last_affected_rows > 0 ? true : false;
    }

    public function clearNotificationAlert(int $notification_id): bool {
        $this->system->query("UPDATE `notifications` set `alert` = 0 WHERE `notification_id` = {$notification_id}");
        return $this->system->db_last_affected_rows > 0 ? true : false;
    }
}