<?php

require_once __DIR__ . '/Event.php';

class HolidayBonusEvent extends Event
{
    public function __construct(DateTimeImmutable $end_time, int string $name = "Holiday Bonus EXP")
    {
        $this->name = $name;
        $this->end_time = $end_time;
        $this->exp_gain_multiplier = 2;
    }
}
