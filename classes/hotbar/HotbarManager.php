<?php

require __DIR__ . '/HotbarAIDto.php';
require __DIR__ . '/HotbarMissionDto.php';

class HotbarManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return HotbarMissionDto[]
     */
    public function getMissions() : array {
        $max_mission_rank = Mission::maxMissionRank($this->player->rank_num);
        $result = $this->system->query("SELECT `mission_id`, `name` FROM `missions` WHERE `mission_type`=1 OR `mission_type`=5 AND `rank` <= $max_mission_rank");

        $return_arr = [];
        while($row = $this->system->db_fetch($result)) {
            $return_arr[] = new HotbarMissionDto(
                mission_id: $row['mission_id'],
                name: $row['name'],
            );
        }
        return $return_arr;
    }

    /**
     * @return HotbarAIDto[]
     */
    public function getAI() : array {
        $ai_rank = min($this->player->rank_num, System::SC_MAX_RANK);
        $result = $this->system->query("SELECT `ai_id`, `name` FROM `ai_opponents`
			WHERE `rank` = {$ai_rank} ORDER BY `level` ASC");

        $return_arr = [];
        while($row = $this->system->db_fetch($result)) {
            $return_arr[] = new HotbarAIDto(
                ai_id: $row['ai_id'],
                name: $row['name'],
            );
        }
        return $return_arr;
    }
}