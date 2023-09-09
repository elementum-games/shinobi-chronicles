<?php

class TravelManager {
    private System $system;
    private User $user;

    const OPERATION_INFILTRATE = 1;
    const OPERATION_REINFORCE = 2;
    const OPERATION_RAID = 3;

    const OPERATION_TYPE = [
        self::OPERATION_INFILTRATE => "infiltrate",
        self::OPERATION_REINFORCE => "reinforce",
        self::OPERATION_RAID => "raid",
    ];

    const OPERATION_ACTIVE = 1;
    const OPERATION_FAILED = 2;
    const OPERATION_COMPLETE = 3;

    const OPERATION_STATUS = [
        self::OPERATION_ACTIVE => 'active',
        self::OPERATION_FAILED => 'failed',
        self::OPERATION_COMPLETE => 'complete',
    ];

    const BASE_OPERATION_SPEED = 5;
    const BASE_OPERATION_INTERVAL = 10000; // 100 / 5 * 10000ms = 3:20

    public function __construct(System $system, User $user)
    {
        $this->system = $system;
        $this->user = $user;
    }


}