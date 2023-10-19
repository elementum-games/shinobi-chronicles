<?php

require_once __DIR__ . '/Event.php';

class DoubleReputationEvent extends Event
{
    const rep_modifier = 2;
    const pve_modifier = 2;
    const pvp_modifier = 1;
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->name = "Double Reputation";
        $this->end_time = $end_time;
    }
}