<?php

require_once __DIR__ . '/Event.php';

class DoubleReputationEvent extends Event
{
    const rep_gain_multiplier = 2;
    const pve_cap_multiplier = 2;
    const pvp_cap_multiplier = 2;
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->name = "Double Reputation";
        $this->end_time = $end_time;
    }
}