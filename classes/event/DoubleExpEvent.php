<?php

require_once __DIR__ . '/Event.php';

class DoubleExpEvent extends Event
{
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->name = "Double EXP Event";
        $this->end_time = $end_time;
        $this->exp_gain_multiplier = 2;
    }
}
