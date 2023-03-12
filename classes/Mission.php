<?php

/* Class:		Mission
*/
class Mission {
    const RANK_D = 1;
    const RANK_C = 2;
    const RANK_B = 3;
    const RANK_A = 4;
    const RANK_S = 5;

    const TYPE_TEAM = 3;

    public static array $rank_names = [
        Mission::RANK_D => 'D-Rank',
        Mission::RANK_C => 'C-Rank',
        Mission::RANK_B => 'B-Rank',
        Mission::RANK_A => 'A-Rank',
        Mission::RANK_S => 'S-Rank'
    ];

    public int $mission_id;
    public string $name;
    public $rank;
    public $mission_type;
    public $stages;
    public $money;

    public User $player;
    public ?Team $team;

    /**
     * @var false|mixed
     */
    public $current_stage;

    private $system;

    public function __construct($mission_id, User $player, ?Team $team = null) {
        global $system;
        $this->system = $system;
        $result = $this->system->query("SELECT * FROM `missions` WHERE `mission_id`='$mission_id' LIMIT 1");
        if($this->system->db_last_num_rows == 0) {
            return false;
        }

        $mission_data = $this->system->db_fetch($result);

        $this->player = $player;
        $this->team = $team;

        $this->mission_id = $mission_data['mission_id'];
        $this->name = $mission_data['name'];
        $this->rank = $mission_data['rank'];
        $this->mission_type = $mission_data['mission_type'];
        $this->money = $mission_data['money'];

        // Unset team if normal mission
        if($this->mission_type != Mission::TYPE_TEAM) {
            $this->team = null;
        }

        $stages = json_decode($mission_data['stages'], true);
        foreach($stages as $id => $stage) {
            $this->stages[($id + 1)] = $stage;
            $this->stages[($id + 1)]['stage_id'] = ($id + 1);
        }

        if($this->player && $this->player->mission_id) {
            $this->current_stage = $this->player->mission_stage;
        }
        else {
            if($this->team != null) {
                $this->nextTeamStage(1);
            }
            else {
                $this->nextStage(1);
            }
        }
    }

    public function nextStage($stage_id) {
        $villages = $this->system->getVillageLocations();

        // Check for multi-count, stop stage ID
        $new_stage = true;
        if(!empty($this->current_stage['count_needed'])) {
            $this->current_stage['count']++;
            if($this->current_stage['count'] < $this->current_stage['count_needed']) {
                $stage_id--;
                $new_stage = false;
                $this->current_stage['description'] = $this->stages[$stage_id]['description'];
            }
        }

        // Return signal for mission complete
        if($stage_id > count($this->stages) + 1) {
            return 2;
        }
        // Set to completion stage if all stages have been completed
        if($stage_id > count($this->stages)) {
            $this->current_stage = array(
                'stage_id' => $stage_id + 1,
                'action_type' => 'travel',
                'action_data' => $this->player->village_location->fetchString(),
                'description' => 'Report back to the village to complete the mission.'
            );
            if($this->mission_type == 5) {
                $this->current_stage['ai_defeated'] = $this->player->mission_stage['ai_defeated'] ?? 0;
                $this->current_stage['mission_money'] = $this->player->mission_stage['mission_money'] ?? 0;
                $this->current_stage['round_complete'] = $this->player->mission_stage['round_complete'] ?? false;
            }
            $this->player->mission_stage = $this->current_stage;
            return 1;
        }

        // Load new stage data
        if($new_stage) {
            $this->current_stage = $this->stages[$stage_id];
            if($this->current_stage['count'] ?? 0 > 1) {
                $this->current_stage['count_needed'] = $this->current_stage['count'];
                $this->current_stage['count'] = 0;
            }
            else {
                $this->current_stage['count'] = 0;
            }
        }

        if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
            for($i = 0; $i < 3; $i++) {
                $location = $this->rollLocation($this->player->village_location);
                if(!isset($villages[$location->fetchString()]) || $location->equals($this->player->village_location)) {
                    break;
                }
            }

            $this->current_stage['action_data'] = $location;

        }

        $search_array = array('[action_data]', '[location_radius]');
        $replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);

        $this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);

        if($this->mission_type == 5) {
            $this->current_stage['ai_defeated'] = $this->player->mission_stage['ai_defeated'] ?? 0;
            $this->current_stage['mission_money'] = $this->player->mission_stage['mission_money'] ?? 0;
            $this->current_stage['round_complete'] = $this->player->mission_stage['round_complete'] ?? false;
        }
        $this->player->mission_stage = $this->current_stage;
        return 1;
    }

    public function nextTeamStage($stage_id): int {
        $villages = $this->system->getVillageLocations();

        // Return signal for mission complete
        if($stage_id > count($this->stages) + 1) {
            return 2;
        }

        // Check for old stage
        $old_stage = false;
        if(!isset($this->player->mission_stage) || $this->player->mission_stage['stage_id'] < $this->team->mission_stage['stage_id']) {
            $old_stage = true;
        }

        // Check multi counts, block stage id
        $new_stage = true;
        if(!isset($this->team->mission_stage) || $this->team->mission_stage['count_needed'] && !$old_stage) {
            if(isset($this->team->mission_stage['count'])) {
                $this->team->mission_stage['count']++;
            }
            else {
                $this->team->mission_stage['count'] = 0;
            }
            if(isset($this->team->mission_stage['count_needed']) && $this->team->mission_stage['count'] < $this->team->mission_stage['count_needed']) {
                $stage_id--;
                $new_stage = false;
                $mission_stage = json_encode($this->team->mission_stage);
                $this->system->query("UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`={$this->team->id} LIMIT 1");
            }
        }
        else {
            $new_stage = false;
        }

        // Set to completion stage if all stages have been completed
        if($stage_id > count($this->stages)) {
            $this->current_stage = array(
                'stage_id' => $stage_id + 1,
                'action_type' => 'travel',
                'action_data' => $this->player->village_location->fetchString(),
                'description' => 'Report back to the village to complete the mission.'
            );
            $this->player->mission_stage = $this->current_stage;
            return 1;
        }

        // Clear mission if it was cancelled
        if($new_stage && !$this->team->mission_id) {
            $this->player->clearMission();
            return 1;
        }

        // Load new stage data
        $this->current_stage = $this->stages[$stage_id];
        if($new_stage) {
            if($this->current_stage['count'] > 1) {
                $this->current_stage['count_needed'] = $this->current_stage['count'];
                $this->current_stage['count'] = 0;
            }
            else {
                $this->current_stage['count'] = 0;
                $this->current_stage['count_needed'] = 0;
            }

            $this->team->mission_stage['stage_id'] = $stage_id;
            $this->team->mission_stage['count'] = $this->current_stage['count'];
            $this->team->mission_stage['count_needed'] = $this->current_stage['count_needed'];

            $mission_stage = json_encode($this->team->mission_stage);

            $this->system->query("UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`='{$this->team->id}' LIMIT 1");
        }

        if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
            for($i = 0; $i < 3; $i++) {
                $location = $this->rollLocation($this->player->village_location);
                if(!isset($villages[$location->fetchString()]) || $location->equals($this->player->village_location)) {
                    break;
                }
            }

            $this->current_stage['action_data'] = $location;
        }

        $search_array = array('[action_data]', '[location_radius]');
        $replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);
        $this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);

        $this->player->mission_stage = $this->current_stage;
        return 1;
    }

    public function rollLocation(TravelCoords $starting_location): TravelCoords {

        $max = $this->current_stage['location_radius'] * 2;
        $x = mt_rand(0, $max) - $this->current_stage['location_radius'];
        $y = mt_rand(0, $max) - $this->current_stage['location_radius'];
        $map_id = $starting_location->map_id;
        if($x == 0 && $y == 0) {
            $x++;
        }

        $x += $starting_location->x;
        $y += $starting_location->y;

        if($x < 1) {
            $x = 1;
        }
        if($y < 1) {
            $y = 1;
        }

        $map_data = Travel::getMapData($this->system, $this->player->location->map_id);

        if($x > $map_data['end_x']) {
            $x = $map_data['end_x'];
        }
        if($y > $map_data['end_y']) {
            $y = $map_data['end_y'];
        }

        return new TravelCoords($x, $y, $map_id);
    }

    /**
     * @param $player
     * @param $mission_id
     * @return Mission
     * @throws Exception
     */
    public static function start($player, $mission_id): Mission {
        if($player->mission_id) {
            throw new Exception("You are already on a mission!");
        }

        $fight_timer = 20 * 1000;
        if($player->last_ai_ms > System::currentTimeMs() - $fight_timer) {
            throw new Exception("Please wait " . ($player->last_ai_ms - (System::currentTimeMs() - $fight_timer) / 1000) . " more seconds!");
        }

        $mission = new Mission($mission_id, $player);

        $player->mission_id = $mission_id;


        return $mission;
    }

    public static function maxMissionRank(int $player_rank): int {
        $max_mission_rank = Mission::RANK_D;
        if($player_rank == 3) {
            $max_mission_rank = Mission::RANK_B;
        }
        else if($player_rank >= 4) {
            $max_mission_rank = Mission::RANK_A;
        }
        return $max_mission_rank;
    }
}