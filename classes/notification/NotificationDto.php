<?php

class NotificationDto {
    public function __construct(
        public string $action_url = "",
        public string $type = "",
        public string $message = "",
        public int $notification_id = 0,
        public int $user_id = 0,
        public int $created = 0,
        public int $duration = 0,
        public bool $alert = false,
        public array $attributes = [],
    ) {
    }
}