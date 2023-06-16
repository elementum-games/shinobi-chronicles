<?php

class MissionNotificationDto extends NotificationDto {
    public string $mission_rank;

    public function __construct(
        string $action_url = "",
        string $type = "",
        string $message = "",
        int $notification_id = 0,
        int $user_id = 0,
        int $created = 0,
        int $duration = 0,
        bool $alert = false,
        string $mission_rank = "",
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
        $this->mission_rank = $mission_rank;
    }


    public static function fromDb($row, $action_url): MissionNotificationDto {
        $attributes = json_decode($row['attributes'], true);

        return new MissionNotificationDto(
            action_url: $action_url,
            type: $row['type'],
            message: $row['message'],
            notification_id: $row['notification_id'],
            user_id: $row['user_id'],
            created: $row['created'],
            duration: $row['duration'],
            alert: $row['alert'],
            mission_rank: $attributes['mission_rank'],
        );
    }

    public function getAttributes(): array {
        return [
            'mission_rank' => $this->mission_rank,
        ];
    }
}