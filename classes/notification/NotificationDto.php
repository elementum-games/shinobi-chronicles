<?php

class NotificationDto {
    public function __construct(
        public string $action_url,
        public string $type,
        public string $label,
        public bool $critical = false,
    ) {
    }
}