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
        $notification_ids_to_delete = [];
        $notification_table_result = $this->system->query("SELECT * FROM `notifications` WHERE `user_id` = {$this->player->user_id}");
        while ($row = $this->system->db_fetch($notification_table_result)) {
            switch ($row['type']) {
                case "training":
                    if ($this->player->train_time <= 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $row['action_url'] = $this->system->router->getUrl("training");
                    }
                    break;
                case "training_complete":
                    if ($this->player->train_time > 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    }
                    else {
                        $row['action_url'] = $this->system->router->getUrl("training");
                    }
                    break;
                case "specialmission":
                    if ($this->player->special_mission == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $row['action_url'] = $this->system->router->getUrl("specialmissions");
                    }
                    break;
                case "specialmission_complete":
                    if ($this->player->special_mission != 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $row['action_url'] = $this->system->router->getUrl("specialmissions");
                    }
                    break;
                case "mission":
                    if ($this->player->mission_id == 0) {
                        $notification_ids_to_delete[] = $row['notification_id'];
                        continue 2;
                    } else {
                        $row['action_url'] = $this->system->router->getUrl("mission");
                    }
                    break;
                default:
                    break;
            }

            $notifications[] = new NotificationDto(
                type: $row['type'],
                message: $row['message'],
                notification_id: $row['notification_id'],
                user_id: $row['user_id'],
                created: $row['created'],
                duration: $row['duration'],
                alert: $row['alert'],
                attributes: json_decode($row['attributes'], true),
                action_url: $row['action_url'],
            );
        }
        if (count($notification_ids_to_delete) > 0) {
            $this->system->query("DELETE FROM `notifications` WHERE `notification_id` IN (" . implode(",", $notification_ids_to_delete) . ")");
        }
        return $notifications;
    }

    public function closeNotification(int $notification_id): bool {
        $this->system->query("DELETE FROM `notifications` WHERE `notification_id` = {$notification_id}");
        return $this->system->db_last_num_rows > 0 ? true : false;
    }
}