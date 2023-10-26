<?php

require_once __DIR__ . '/NotificationDto.php';

class NotificationManager {
    const UPDATE_REPLACE = 0;
    const UPDATE_UNIQUE = 1;
    const UPDATE_MULTIPLE = 2;

    const NOTIFICATION_TRAINING = "training";
    const NOTIFICATION_TRAINING_COMPLETE = "training_complete";
    const NOTIFICATION_STAT_TRANSFER = "stat_transfer";
    const NOTIFICATION_SPECIALMISSION = "specialmission";
    const NOTIFICATION_SPECIALMISSION_COMPLETE = "specialmission_complete";
    const NOTIFICATION_SPECIALMISSION_FAILED = "specialmission_failed";
    const NOTIFICATION_MISSION = "mission";
    const NOTIFICATION_MISSION_TEAM = "mission_team";
    const NOTIFICATION_MISSION_CLAN = "mission_clan";
    const NOTIFICATION_RANK = "rank";
    const NOTIFICATION_SYSTEM = "system";
    const NOTIFICATION_WARNING = "warning";
    const NOTIFICATION_REPORT = "report";
    const NOTIFICATION_BATTLE = "battle";
    const NOTIFICATION_CHALLENGE = "challenge";
    const NOTIFICATION_TEAM = "team";
    const NOTIFICATION_MARRIAGE = "marriage";
    const NOTIFICATION_STUDENT = "student";
    const NOTIFICATION_INBOX = "inbox";
    const NOTIFICATION_CHAT = "chat";
    const NOTIFICATION_EVENT = "event";
    const NOTIFICATION_RAID_ALLY = "raid_ally";
    const NOTIFICATION_RAID_ENEMY = "raid_enemy";
    const NOTIFICATION_CARAVAN = "caravan";
    const NOTIFICATION_PROPOSAL_CREATED = "proposal_created";
    const NOTIFICATION_PROPOSAL_PASSED = "proposal_passed";
    const NOTIFICATION_PROPOSAL_CANCELED = "proposal_canceled";
    const NOTIFICATION_PROPOSAL_EXPIRED = "proposal_expired";
    const NOTIFICATION_POLICY_CHANGE = "policy_change";
    const NOTIFICATION_DIPLOMACY_WAR = "diplomacy_declare_war";
    const NOTIFICATION_DIPLOMACY_ALLIANCE = "diplomacy_form_alliance";
    const NOTIFICATION_DIPLOMACY_END_WAR = "diplomacy_end_war";
    const NOTIFICATION_DIPLOMACY_END_ALLIANCE = "diplomacy_end_alliance";
    const NOTIFICATION_NEWS = "news";
    const NOTIFICATION_CHALLENGE_PENDING = "challenge_pending";
    const NOTIFICATION_CHALLENGE_ACCEPTED = "challenge_accepted";
    const NOTIFICATION_KAGE_CHANGE = "kage_change";
    const NOTIFICATION_ACHIEVEMENT = "achievement";

    const ACTIVE_PLAYER_DAYS_LAST_ACTIVE = 14;

    const NOTIFICATION_EXPIRATION_DAYS_POLICY = 3;
    const NOTIFICATION_EXPIRATION_DAYS_PROPOSAL = 3;
    const NOTIFICATION_EXPIRATION_DAYS_DIPLOMACY = 3;
    const NOTIFICATION_EXPIRATION_DAYS_NEWS = 14;
    const NOTIFICATION_EXPIRATION_DAYS_CHAT = 7;
    const NOTIFICATION_EXPIRATION_DAYS_SPECIAL_MISSION = 1;

    public static function createNotification(NotificationDto $notification, System $system, int $UPDATE, int $limit = 5): bool {
        $db_modified = false;
        $attributes = json_encode($notification->getAttributes(), JSON_FORCE_OBJECT);

        if ($UPDATE == self::UPDATE_UNIQUE) {
            $system->db->query(
                "INSERT INTO `notifications`
                (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`, `expires`)
                SELECT '{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', " . (int)$notification->alert . ", '{$notification->created}', '{$notification->duration}', '{$attributes}', " . (!empty($notification->expires) ? $notification->expires : "NULL") . " FROM DUAL
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
                (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`, `expires`)
                VALUES ('{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', " . (int) $notification->alert . ", '{$notification->created}', '{$notification->duration}', '{$attributes}', " . (!empty($notification->expires) ? $notification->expires : "NULL") . ")"
            );
        }

        if ($UPDATE == self::UPDATE_MULTIPLE) {
            $system->db->query(
                "INSERT INTO `notifications`
                (`notification_id`, `user_id`, `type`, `message`, `alert`, `created`, `duration`, `attributes`, `expires`)
                VALUES ('{$notification->notification_id}', '{$notification->user_id}', '{$notification->type}', '{$notification->message}', " . (int) $notification->alert . ", '{$notification->created}', '{$notification->duration}', '{$attributes}', " . (!empty($notification->expires) ? $notification->expires : "NULL") . ")"
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