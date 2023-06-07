<?php

class MissionNotificationDto extends NotificationDto {
    public string $mission_rank;

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