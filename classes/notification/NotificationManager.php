<?php

require_once __DIR__ . '/NotificationDto.php';

class NotificationManager {
    const UPDATE_REPLACE = 0;
    const UPDATE_UNIQUE = 1;
    const UPDATE_MULTIPLE = 2;

    public static function createNotification(NotificationDto $notification, System $system, int $UPDATE, int $limit = 5): bool {
        $db_modified = false;
        $attributes = json_encode($notification->getAttributes(), JSON_FORCE_OBJECT);

        if ($UPDATE == self::UPDATE_UNIQUE) {
            $system->db->query(
                "INSERT INTO `notifications`
                (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`)
                SELECT '{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', " . (int)$notification->alert . ", '{$notification->created}', '{$notification->duration}', '{$attributes}' FROM DUAL
                WHERE NOT EXISTS (SELECT 1 FROM `notifications` WHERE `type` = '{$notification->type}' AND `user_id` = '{$notification->user_id}')"
            );
            if ($system->db->last_num_rows > 0) {
                $db_modified = true;
            }
            return $db_modified;
        }

        if ($UPDATE == self::UPDATE_REPLACE) {
            NotificationManager::closeNotificationByType($notification->type, $notification->user_id, $system);
            $system->db->query(
                "INSERT INTO `notifications`
                (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`)
                VALUES ('{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', " . (int) $notification->alert . ", '{$notification->created}', '{$notification->duration}', '{$attributes}')"
            );
        }

        if ($UPDATE == self::UPDATE_MULTIPLE) {
            $system->db->query(
                "INSERT INTO `notifications`
                (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`)
                VALUES ('{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', " . (int) $notification->alert . ", '{$notification->created}', '{$notification->duration}', '{$attributes}')"
            );
            NotificationManager::closeOldestNotificationByType($notification->type, $notification->user_id, $system, $limit);
        }

        if ($system->db->last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeNotificationByType(string $type, int $user_id, System $system): bool {
        $db_modified = false;
        $system->db->query("DELETE FROM `notifications` WHERE `user_id` = {$user_id} && `type` = '{$type}'");
        if ($system->db->last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }

    public static function closeOldestNotificationByType(string $type, int $user_id, System $system, $limit): bool
    {
        $db_modified = false;
        $notification_query = $system->db->query("SELECT COUNT(*) AS notification_count FROM `notifications` WHERE `user_id` = {$user_id} && `type` = '{$type}'");
        $notification_count = $system->db->fetch($notification_query);
        if ($notification_count['notification_count'] > $limit) {
            $system->db->query("
                DELETE FROM `notifications`
                WHERE `user_id` = {$user_id}
                AND `type` = '{$type}'
                AND `notification_id` = (
                    SELECT `notification_id` FROM (
                        SELECT MIN(`notification_id`) AS `notification_id`
                        FROM `notifications`
                        WHERE `user_id` = {$user_id}
                        AND `type` = '{$type}'
                    ) AS tmp
                )
            ");
        }
        if ($system->db->last_num_rows > 0) {
            $db_modified = true;
        }
        return $db_modified;
    }
}