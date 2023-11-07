<?php

require_once __DIR__ . '/Event.php';

class WeekendBoost extends Event
{
    const exp_modifier = 1.5;
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->name = "Weekend Boost";
        $this->end_time = $end_time;
    }
}
