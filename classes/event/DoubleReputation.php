<?php

require_once __DIR__ . '/Event.php';

class DoubleReputation extends Event
{
    const rep_modifier = 2;
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->name = "Double Reputation";
        $this->end_time = $end_time;
    }
}