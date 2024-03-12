<?php

class WarRecordDto {
    public VillageRelation $village_relation;
    public WarLogDto $attacker_war_log;
    public WarLogDto $defender_war_log;
    public int $victory_percent_required;
    public int $war_duration = 0;

    public function __construct(
        VillageRelation $village_relation,
        WarLogDto $attacker_war_log,
        WarLogDto $defender_war_log,
        int $victory_percent_required,
        int $war_duration
    ) {
        $this->village_relation = $village_relation;
        $this->attacker_war_log = $attacker_war_log;
        $this->defender_war_log = $defender_war_log;
        $this->victory_percent_required = $victory_percent_required;
        $this->war_duration = $war_duration;
    }
}