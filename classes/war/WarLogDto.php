<?php

class WarLogDto {
    public int $log_id;
    public string $log_type;
    public ?int $user_id = null;
    public ?string $user_name = null;
    public int $village_id;
    public ?string $village_name = null;
    public ?int $relation_id;
    public int $rank = 0;
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
    public int $stability_gained = 0;
    public int $stability_reduced = 0;
    public int $war_score = 0;
    public int $objective_score = 0;
    public int $resource_score = 0;
    public int $battle_score = 0;

    public function __construct(array $row, string $log_type) {
        $this->log_id = $row['log_id'];
        $this->village_id = $row['village_id'];
        $this->log_type = $log_type;
        $this->user_id = $row['user_id'] ?? null;
        $this->user_name = $row['user_name'] ?? null;
        $this->village_name = $row['village_name'] ?? null;
        $this->relation_id = $row['relation_id'] ?? null;
        $this->rank = $row['rank'] ?? 0;
        $this->infiltrate_count = $row['infiltrate_count'] ?? 0;
        $this->reinforce_count = $row['reinforce_count'] ?? 0;
        $this->raid_count = $row['raid_count'] ?? 0;
        $this->loot_count = $row['loot_count'] ?? 0;
        $this->damage_dealt = $row['damage_dealt'] ?? 0;
        $this->damage_healed = $row['damage_healed'] ?? 0;
        $this->defense_gained = $row['defense_gained'] ?? 0;
        $this->defense_reduced = $row['defense_reduced'] ?? 0;
        $this->resources_stolen = $row['resources_stolen'] ?? 0;
        $this->resources_claimed = $row['resources_claimed'] ?? 0;
        $this->patrols_defeated = $row['patrols_defeated'] ?? 0;
        $this->regions_captured = $row['regions_captured'] ?? 0;
        $this->villages_captured = $row['villages_captured'] ?? 0;
        $this->pvp_wins = $row['pvp_wins'] ?? 0;
        $this->points_gained = $row['points_gained'] ?? 0;
        $this->stability_gained = $row['stability_gained'] ?? 0;
        $this->stability_reduced = $row['stability_reduced'] ?? 0;
    }

    public function addValues(WarLogDto $new_log) {
        $this->infiltrate_count += $new_log->infiltrate_count;
        $this->reinforce_count += $new_log->reinforce_count;
        $this->raid_count += $new_log->raid_count;
        $this->loot_count += $new_log->loot_count;
        $this->damage_dealt += $new_log->damage_dealt;
        $this->damage_healed += $new_log->damage_healed;
        $this->defense_gained += $new_log->defense_gained;
        $this->defense_reduced += $new_log->defense_reduced;
        $this->resources_stolen += $new_log->resources_stolen;
        $this->resources_claimed += $new_log->resources_claimed;
        $this->patrols_defeated += $new_log->patrols_defeated;
        $this->regions_captured += $new_log->regions_captured;
        $this->villages_captured += $new_log->villages_captured;
        $this->pvp_wins += $new_log->pvp_wins;
        $this->points_gained += $new_log->points_gained;
        $this->stability_gained += $new_log->stability_gained;
        $this->stability_reduced += $new_log->stability_reduced;
        $this->war_score += $new_log->war_score;
        $this->objective_score += $new_log->objective_score;
        $this->resource_score += $new_log->resource_score;
        $this->battle_score += $new_log->battle_score;
    }
}