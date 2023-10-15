<?php

class WarLog
{
    public int $log_id;
    public int $entity_id;
    public ?int $user_id;
    public int $type;
    public int $start_time;

    public function __construct(array $log_data)
    {
        foreach ($log_data as $key => $value) {
            $this->$key = $value;
        }
    }
}