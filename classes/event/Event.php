<?php

abstract class Event {
    public string $name;
    public DateTimeImmutable $end_time;
    public int $exp_modifier = 1;
}