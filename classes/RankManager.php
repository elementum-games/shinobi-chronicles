<?php

require_once __DIR__ . "/Rank.php";

class RankManager {
    public System $system;

    /** @var Rank[] */
    public array $ranks = [];
    private bool $ranks_loaded = false;

    public function __construct(System $system) {
        $this->system = $system;
    }

    public function loadRanks() {
        $ranks_result = $this->system->query("SELECT * FROM ranks ORDER BY rank_id");
        $ranks = $this->system->db_fetch_all($ranks_result, 'rank_id');
        foreach($ranks as $rank) {
            $this->ranks[$rank['rank_id']] = Rank::fromDb($rank);
        }

        $this->ranks_loaded = true;
    }

    public function healthForRankAndLevel(int $rank_id, int $level): int {
        if(!$this->ranks_loaded) {
            $this->loadRanks();
        }

        $health = 100 - $this->ranks[1]->health_gain;
        foreach($this->ranks as $id => $rank) {
            if($id > $rank_id) {
                continue;
            }

            $max_level = $id === $rank_id ? $level : $rank->max_level;

            for($i = $rank->base_level; $i <= $max_level; $i++) {
                $health += $rank->health_gain;
            }
        }

        return $health;
    }

    public function chakraForRankAndLevel(int $rank_id, int $level): int {
        if(!$this->ranks_loaded) {
            $this->loadRanks();
        }

        $chakra = 100 - $this->ranks[1]->chakra_gain;
        foreach($this->ranks as $id => $rank) {
            if($id > $rank_id) {
                continue;
            }

            $max_level = $id === $rank_id ? $level : $rank->max_level;

            for($i = $rank->base_level; $i <= $max_level; $i++) {
                $chakra += $rank->pool_gain;
            }
        }

        return $chakra;
    }

    public function statsForRankAndLevel(int $rank_id, int $level): int {
        if(!$this->ranks_loaded) {
            $this->loadRanks();
        }

        $rank = $this->ranks[$rank_id];
        $stats = $rank->base_stats;

        for($i = $rank->base_level + 1; $i <= $level; $i++) {
            $stats += $rank->stats_per_level;
        }

        return $stats;
    }

    public static function fetchNames(System $system): array {
        $result = $system->query("SELECT `rank_id`, `name` FROM `ranks`");

        $rank_names = [];
        while($rank = $system->db_fetch($result)) {
            $rank_names[$rank['rank_id']] = $rank['name'];
        }

        return $rank_names;
    }
}