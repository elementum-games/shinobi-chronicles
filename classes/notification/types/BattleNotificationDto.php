<?php

class BattleNotificationDto extends NotificationDto {
    public int $battle_id;

    public function __construct(
        string $action_url = "",
        string $type = "",
        string $message = "",
        int $notification_id = 0,
        int $user_id = 0,
        int $created = 0,
        int $duration = 0,
        ?int $expires = null,
        bool $alert = false,
        int $battle_id = 0,
    ) {
        parent::__construct(
            $action_url,
            $type,
            $message,
            $notification_id,
            $user_id,
            $created,
            $duration,
            $alert,
        );
        $this->battle_id = $battle_id;
    }

    public static function fromDb($row, $action_url): BattleNotificationDto {
        $attributes = json_decode($row['attributes'], true);
        $notification = new BattleNotificationDto();
        $notification->type = $row['type'];
        $notification->message = $row['message'];
        $notification->notification_id = $row['notification_id'];
        $notification->user_id = $row['user_id'];
        $notification->created = $row['created'];
        $notification->duration = $row['duration'];
        $notification->expires = $row['expires'];
        $notification->alert = $row['alert'];
        $notification->action_url = $action_url;
        $notification->battle_id = $attributes['battle_id'];
        return $notification;
    }

    public function getAttributes(): array {
        return [
            'battle_id' => $this->battle_id,
        ];
    }
}