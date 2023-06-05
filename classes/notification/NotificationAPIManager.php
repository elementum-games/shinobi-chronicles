<?php

require_once __DIR__ . '/NotificationDto.php';
require_once __DIR__ . "/../../classes.php";

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
        $notifications = [];
        $notification_table_result = $this->system->query("SELECT * FROM `notifications` WHERE `user_id` = {$this->player->user_id}");
        while ($row = $this->system->db_fetch($notification_table_result)) {
            $delete = false;
            switch ($row['type']) {
                case "training":
                    if ($this->player->train_time <= 0) {
                        $delete = true;
                    }
                    else {
                        $row['action_url'] = $this->system->router->getUrl("training");
                    }
                    break;
                case "training_complete":
                    if ($this->player->train_time > 0) {
                        $delete = true;
                    }
                    else {
                        $row['action_url'] = $this->system->router->getUrl("training");
                    }
                    break;
                case "specialmission":
                    if ($this->player->special_mission == 0) {
                        $delete = true;
                    } else {
                        $row['action_url'] = $this->system->router->getUrl("specialmissions");
                    }
                    break;
                case "specialmission_complete":
                    if ($this->player->special_mission != 0) {
                        $delete = true;
                    } else {
                        $row['action_url'] = $this->system->router->getUrl("specialmissions");
                    }
                    break;
                case "mission":
                    if ($this->player->mission_id == 0) {
                        $delete = true;
                    } else {
                        $row['action_url'] = $this->system->router->getUrl("mission");
                    }
                    break;
                default:
                    break;
            }
            if ($delete) {
                $this->system->query("START TRANSACTION;");
                $this->system->query("DELETE FROM `notifications` WHERE `notification_id` = {$row['notification_id']}");
                $this->system->query("COMMIT;");
                continue;
            }
            $notifications[] = new NotificationDto(
                type: $row['type'],
                message: $row['message'],
                notification_id: $row['notification_id'],
                user_id: $row['user_id'],
                created: $row['created'],
                duration: $row['duration'],
                alert: $row['alert'],
                action_url: $row['action_url'],
            );
        }
        return $notifications;
    }

    public function closeNotification(int $notification_id): bool {
        $this->system->query("START TRANSACTION;");
        $this->system->query("DELETE FROM `notifications` WHERE `notification_id` = {$notification_id}");
        $this->system->query("COMMIT;");
        return $this->system->db_last_num_rows > 0 ? true : false;
    }
}