<?php

require __DIR__ . '/TopbarNotificationDto.php';

class TopbarManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;

    }
}