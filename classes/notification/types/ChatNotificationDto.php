<?php

class CHatNotificationDto extends NotificationDto {
    public int $post_id;

    public function __construct(
        string $action_url = "",
        string $type = "",
        string $message = "",
        int $notification_id = 0,
        int $user_id = 0,
        int $created = 0,
        int $duration = 0,
        bool $alert = false,
        int $post_id = 0,
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
        $this->post_id = $post_id;
    }


    public static function fromDb($row, $action_url): ChatNotificationDto {
        $attributes = json_decode($row['attributes'], true);

        return new ChatNotificationDto(
            action_url: $action_url,
            type: $row['type'],
            message: $row['message'],
            notification_id: $row['notification_id'],
            user_id: $row['user_id'],
            created: $row['created'],
            duration: $row['duration'],
            alert: $row['alert'],
            post_id: $attributes['post_id'],
        );
    }

    public function getAttributes(): array {
        return [
            'post_id' => $this->post_id,
        ];
    }
}