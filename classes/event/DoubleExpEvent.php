<?php

require_once __DIR__ . '/Event.php';

class DoubleExpEvent extends Event
{
    const exp_modifier = 2;
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->name = "Double EXP Event";
        $this->end_time = $end_time;
    }
}
