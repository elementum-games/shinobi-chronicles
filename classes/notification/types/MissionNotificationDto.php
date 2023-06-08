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


    public static function fromDb($row, $action_url) {
        $attributes = json_decode($row['attributes'], true);
        $notification = new MissionNotificationDto();
        $notification->type = $row['type'];
        $notification->message = $row['message'];
        $notification->notification_id = $row['notification_id'];
        $notification->user_id = $row['user_id'];
        $notification->created = $row['created'];
        $notification->duration = $row['duration'];
        $notification->alert = $row['alert'];
        $notification->action_url = $action_url;
        $notification->mission_rank = $attributes['mission_rank'];
        return $notification;
    }

    public function getAttributes() {
        return [
            'mission_rank' => $this->mission_rank,
        ];
    }
}