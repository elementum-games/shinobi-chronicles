<?php

class Rank {
    public int $id;
    public string $name;
    public int $base_level;
    public int $max_level;
    public int $base_stats;
    public int $stats_per_level;
    public int $health_gain;
    public int $pool_gain;
    public int $stat_cap;
    
    public static function fromDb($db_rank_data): Rank {
        $rank = new Rank();

        $rank->id = $db_rank_data['rank_id'];
        $rank->name = $db_rank_data['name'];
        $rank->base_level = $db_rank_data['base_level'];
        $rank->max_level = $db_rank_data['max_level'];
        $rank->base_stats = $db_rank_data['base_stats'];
        $rank->stats_per_level = $db_rank_data['stats_per_level'];
        $rank->health_gain = $db_rank_data['health_gain'];
        $rank->pool_gain = $db_rank_data['pool_gain'];
        $rank->stat_cap = $db_rank_data['stat_cap'];

        return $rank;
    }
}