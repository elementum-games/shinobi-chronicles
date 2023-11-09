<?php

class WarLogDto {
    public int $log_id;
    public string $log_type;
    public ?int $user_id = null;
    public ?string $user_name = null;
    public int $village_id;
    public ?string $village_name = null;
    public ?int $relation_id;
    public int $infiltrate_count = 0;
    public int $reinforce_count = 0;
    public int $raid_count = 0;
    public int $loot_count = 0;
    public int $damage_dealt = 0;
    public int $damage_healed = 0;
    public int $defense_gained = 0;
    public int $defense_reduced = 0;
    public int $resources_stolen = 0;
    public int $resources_claimed = 0;
    public int $patrols_defeated = 0;
    public int $regions_captured = 0;
    public int $villages_captured = 0;
    public int $pvp_wins = 0;
    public int $points_gained = 0;
    public int $war_score = 0;
    public int $objective_score = 0;
    public int $resource_score = 0;
    public int $battle_score = 0;

    public function __construct(array $row, string $log_type) {
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        $this->log_type = $log_type;
    }
}