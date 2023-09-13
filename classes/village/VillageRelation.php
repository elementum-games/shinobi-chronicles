<?php

class VillageRelation {

    const RELATION_NEUTRAL = 1;
    const RELATION_ALLIANCE = 2;
    const RELATION_WAR = 3;

    const RELATION_LABEL = [
        self::RELATION_NEUTRAL => 'neutral',
        self::RELATION_ALLIANCE => 'alliance',
        self::RELATION_WAR => 'war',
    ];

    public int $relation_id;
    public int $village1_id;
    public int $village2_id;
    public int $relation_type;
    public string $relation_name;
    public ?int $relation_start;
    public ?int $relation_end;
    public function __construct(array $relation_data) {
        foreach ($relation_data as $key => $value) {
            $this->$key = $value;
        }
    }
}