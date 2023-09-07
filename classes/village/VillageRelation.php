<?php

class VillageRelation {
    public int $relation_id;
    public int $village1_id;
    public int $village2_id;
    public string $relation_type;
    public string $relation_name;
    public ?int $relation_start;
    public ?int $relation_end;
    public function __construct(array $relation_data) {
        foreach ($relation_data as $key => $value) {
            $this->$key = $value;
        }
    }
}