<?php

abstract class Event {
    public string $name;
    public DateTimeImmutable $end_time;
    public float $exp_gain_multiplier = 1;
}