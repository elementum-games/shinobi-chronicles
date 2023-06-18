<?php

require_once __DIR__ . "/Rank.php";

class RankManager {
    public System $system;

    const CHUUNIN_STAGE_WRITTEN = 1;
    const CHUUNIN_STAGE_SURVIVAL_START = 2;
    const CHUUNIN_STAGE_SURVIVAL_MIDDLE = 3;
    const CHUUNIN_STAGE_SURVIVAL_END = 4;
    const CHUUNIN_STAGE_DUEL = 5;
    const CHUUNIN_STAGE_PASS = 6;

    const JONIN_MISSION_ID = 10;

    /** @var Rank[] */
    public array $ranks = [];
    private bool $ranks_loaded = false;

    public function __construct(System $system) {
        $this->system = $system;
    }

    public function loadRanks() {
        $ranks_result = $this->system->db->query("SELECT * FROM ranks ORDER BY rank_id");
        $ranks = $this->system->db->fetch_all($ranks_result, 'rank_id');
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

        $chakra = 100 - $this->ranks[1]->pool_gain;
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

    public function calculateRankFromTotalStats(int $total_stats) {
        $rank_id = 1;
        foreach($this->ranks as $rank) {
            if($total_stats >= $rank->base_stats) {
                $rank_id = $rank->id;
            }
        }

        return $rank_id;
    }
    public function calculateMaxLevel(int $total_stats, int $rank) {
        $rank = $this->ranks[$rank];
        $level = $rank->base_level;
        $total_stats -= $rank->base_stats;

        while($total_stats >= $rank->stats_per_level) {
            $level++;
            $total_stats -= $rank->stats_per_level;
        }
        if($level > $rank->max_level) {
            $level = $rank->max_level;
        }

        return $level;
    }

    public static function fetchNames(System $system): array {
        $result = $system->db->query("SELECT `rank_id`, `name` FROM `ranks`");

        $rank_names = [];
        while($rank = $system->db->fetch($result)) {
            $rank_names[$rank['rank_id']] = $rank['name'];
        }

        return $rank_names;
    }

    /**
     * @param User $player
     * @return void
     * @throws Exception
     */
    public function increasePlayerRank(User $player) {
        $new_rank = $player->rank_num + 1;
        if($new_rank > System::SC_MAX_RANK) {
            throw new Exception("Invalid max rank!");
        }
        if(!$this->ranks_loaded) {
            $this->loadRanks();
        }

        if(!isset($this->ranks[$new_rank])) {
            throw new Exception("Error loading new rank!");
        }

        $player->rank_num++;
        $player->rank = $this->ranks[$player->rank_num];

        $player->level++;

        $player->max_health += $this->ranks[$new_rank]->health_gain;
        $player->max_chakra += $this->ranks[$new_rank]->pool_gain;
        $player->max_stamina += $this->ranks[$new_rank]->pool_gain;

        $player->health = $player->max_health;
        $player->chakra = $player->max_chakra;
        $player->stamina = $player->max_stamina;

        $player->exp = $player->total_stats * 10;

        switch($new_rank) {
            case 2:
                // Use cost 25
                $player->regen_rate = 50;
                break;
            case 3:
                // Use cost 50
                $player->regen_rate = 100;
                break;
            case 4:
                // Use cost 75
                $player->regen_rate = 170;
                break;
        }
    }
}