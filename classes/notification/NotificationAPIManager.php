<?php

require __DIR__ . '/NotificationDto.php';

class NotificationAPIManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;

    }
}