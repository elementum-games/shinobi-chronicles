<?php

require_once __DIR__ . '/Event.php';

class BonusExpWeekend extends Event
{
    public function __construct(DateTimeImmutable $end_time, string $name = "Weekend Bonus EXP", int $exp_modifier = 1.5)
    {
        $this->exp_gain_multiplier = $exp_modifier;
        $this->name = $name;
        $this->end_time = $end_time;
    }
}
