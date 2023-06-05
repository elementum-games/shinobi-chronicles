<?php

require_once __DIR__ . '/NotificationDto.php';

class NotificationManager {
    public static function createNotification(NotificationDto $notification, System $system, bool $allow_duplicate): bool {
        if (!$allow_duplicate) {
            NotificationManager::closeNotificationByType($notification->type, $notification->user_id, $system);
        }
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("INSERT INTO `notifications`
            (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`)
            VALUES ('{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', '{$notification->alert}', '{$notification->created}', '{$notification->duration}')");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeNotificationByType(string $type, int $user_id, System $system): bool {
        $db_modified = false;
        $system->query("START TRANSACTION;");
        $system->query("DELETE FROM `notifications` WHERE `user_id` = {$user_id} && `type` = '{$type}'");
        $system->query("COMMIT;");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }
}