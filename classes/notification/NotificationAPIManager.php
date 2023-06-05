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
                    break;
                case "training_complete":
                    if ($this->player->train_time > 0) {
                        $delete = true;
                    }
                    break;
                default:
                    break;
            }
            if ($delete) {
                $notification_delete_result = $this->system->query("DELETE FROM `notifications` WHERE `notification_id` = {$row['notification_id']}");
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
            );
        }
        return $notifications;
    }
}