<?php

class TopbarNotificationDto {
    public function __construct(
        public string $title,
        public string $url,
        public bool $active,
        public int $id,
    ) {}
}