<?php

require_once __DIR__ . '/NotificationDto.php';

class NotificationManager {
    public static function createNotification(NotificationDto $notification, System $system, bool $allow_duplicate): bool {
        $db_modified = false;
        if (!$allow_duplicate) {
            NotificationManager::closeNotificationByType($notification->type, $notification->user_id, $system);
        }
        $attributes = json_encode($notification->attributes);
        $system->query("INSERT INTO `notifications`
            (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`)
            VALUES ('{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', '{$notification->alert}', '{$notification->created}', '{$notification->duration}', '{$attributes}')");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeNotificationByType(string $type, int $user_id, System $system): bool {
        $db_modified = false;
        $system->query("DELETE FROM `notifications` WHERE `user_id` = {$user_id} && `type` = '{$type}'");
        if ($system->db_last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }
}