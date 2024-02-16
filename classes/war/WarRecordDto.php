<?php

class WarRecordDto
{
    public VillageRelation $village_relation;
    public WarLogDto $attacker_war_log;
    public WarLogDto $defender_war_log;

    public function __construct($village_relation, $attacker_war_log, $defender_war_log)
    {
        $this->village_relation = $village_relation;
        $this->attacker_war_log = $attacker_war_log;
        $this->defender_war_log = $defender_war_log;
    }
} 