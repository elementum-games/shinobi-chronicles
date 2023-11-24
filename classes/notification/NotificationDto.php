<?php

require_once __DIR__ . '/types/MissionNotificationDto.php';
require_once __DIR__ . '/types/BattleNotificationDto.php';
require_once __DIR__ . '/types/ChatNotificationDto.php';

class NotificationDto {
    public function __construct(
        public string $action_url = "",
        // TODO: Enum/set of constants for types
        public string $type = "",
        public string $message = "",
        public int $notification_id = 0,
        public int $user_id = 0,
        public int $created = 0,
        public int $duration = 0,
        public bool $alert = false,
        public ?int $expires = null,
    ) {
    }

    public static function fromDb($row, $action_url): NotificationDto {
        return new NotificationDto(
            action_url: $action_url,
            type: $row['type'],
            message: $row['message'],
            notification_id: $row['notification_id'],
            user_id: $row['user_id'],
            created: $row['created'],
            duration: $row['duration'],
            expires: $row['expires'],
            alert: $row['alert'],
        );
    }

    public function getAttributes(): array {
        return [];
    }
}