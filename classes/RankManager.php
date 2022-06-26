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

    // public static

    /**
     * @param User $player
     * @return void
     * @throws Exception
     */
    public function increasePlayerRank(User $player) {
        $new_rank = $player->rank + 1;
        if($new_rank > System::SC_MAX_RANK) {
            throw new Exception("Invalid max rank!");
        }
        if(!$this->ranks_loaded) {
            $this->loadRanks();
        }

        if(!isset($this->ranks[$new_rank])) {
            throw new Exception("Error loading new rank!");
        }

        $player->rank++;
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
                $player->regen_rate += 20;
                break;
            case 3:
                $player->regen_rate += 70;
                break;
            case 4:
                $player->regen_rate += 200;
                break;
        }
    }
}