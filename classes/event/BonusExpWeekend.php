<?php

require_once __DIR__ . '/Event.php';

class BonusExpWeekend extends Event
{
    public function __construct(DateTimeImmutable $end_time)
    {
        $this->exp_modifier = 1.5;
        $this->name = "Weekend Bonus EXP";
        $this->end_time = $end_time;
    }
}
