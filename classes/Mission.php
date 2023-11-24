<?php

require_once __DIR__ . '/../classes/notification/NotificationManager.php';

/* Class:		Mission
*/
class Mission {
    const RANK_D = 1;
    const RANK_C = 2;
    const RANK_B = 3;
    const RANK_A = 4;
    const RANK_S = 5;

    const TYPE_VILLAGE = 1;
    const TYPE_CLAN = 2;
    const TYPE_TEAM = 3;
    const TYPE_SPECIAL = 4;
    const TYPE_SURVIVAL = 5; // legacy
    const TYPE_EVENT = 6;
    const TYPE_FACTION = 7;

    const FACTION_AYAKASHI = 1; // for future possible use

    public static array $type_names = [
        self::TYPE_VILLAGE => 'Village',
        self::TYPE_CLAN => 'Clan',
        self::TYPE_TEAM => 'Team',
        self::TYPE_SPECIAL => 'Special',
        self::TYPE_SURVIVAL => 'Survival',
        self::TYPE_EVENT => 'Event',
        self::TYPE_FACTION => 'Faction'
    ];

    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETE = 2;

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
    public $rewards = [];
    public ?TravelCoords $custom_start_location;

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
        $result = $this->system->db->query("SELECT * FROM `missions` WHERE `mission_id`='$mission_id' LIMIT 1");
        if($this->system->db->last_num_rows == 0) {
            return false;
        }

        $mission_data = $this->system->db->fetch($result);

        $this->player = $player;
        $this->team = $team;

        $this->mission_id = $mission_data['mission_id'];
        $this->name = $mission_data['name'];
        $this->rank = $mission_data['rank'];
        $this->mission_type = (int) $mission_data['mission_type'];
        $this->money = $mission_data['money'];
        $this->rewards = json_decode($mission_data['rewards'], true);

        if (isset($mission_data['custom_start_location']) && !empty($mission_data['custom_start_location'])) {
            $this->custom_start_location = TravelCoords::fromDbString($mission_data['custom_start_location']);
        }

        // Unset team if normal mission
        if($this->mission_type != Mission::TYPE_TEAM) {
            $this->team = null;
        }

        $stages = json_decode($mission_data['stages'], true);
        foreach($stages as $id => $stage) {
            $this->stages[($id + 1)] = $stage;
            $this->stages[($id + 1)]['stage_id'] = ($id + 1);
        }

        if($this->player->mission_id) {
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

    public function nextStage($stage_id): int {
        $villages = TravelManager::fetchVillageLocationsByCoordsStr($this->system);

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
            return Mission::STATUS_COMPLETE;
        }
        // Set to completion stage if all stages have been completed
        if ($stage_id > count($this->stages)) {
            // if custom_start_location set
            if (isset($this->custom_start_location) && !empty($this->custom_start_location)) {
                $this->current_stage = array(
                    'stage_id' => $stage_id + 1,
                    'action_type' => 'travel',
                    'action_data' => $this->custom_start_location->toString(),
                    'description' => 'Return to ' . $this->custom_start_location->toString() . ' to complete the mission.'
                );
            }
            // otherwise use village
            else {
                $this->current_stage = array(
                    'stage_id' => $stage_id + 1,
                    'action_type' => 'travel',
                    'action_data' => $this->player->village_location->toString(),
                    'description' => 'Report back to the village to complete the mission.'
                );
            }
            $this->player->mission_stage = $this->current_stage;

            //notifications
            $mission_result = $this->system->db->query("SELECT * FROM `missions` where `mission_id` = {$this->player->mission_id} LIMIT 1");
            $mission_result = $this->system->db->fetch($mission_result);
            $notification_rank = '';
            $notification_type = '';
            switch ($mission_result['mission_type']) {
                case 2:
                    $notification_rank = 'C';
                    $notification_type = 'mission_clan';
                    break;
                case 6:
                    $notification_rank = 'E';
                    $notification_type = 'mission';
                    break;
                case 7:
                    $notification_rank = 'F';
                    $notification_type = 'mission';
                    break;
                default:
                    $notification_rank = Mission::$rank_names[$mission_result['rank']];
                    $notification_type = 'mission';
                    break;

            }
            $new_notification = new MissionNotificationDto(
                type: $notification_type,
                message: $this->current_stage['description'],
                user_id: $this->player->user_id,
                created: time(),
                mission_rank: $notification_rank,
                alert: false,
            );
            NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);

            return Mission::STATUS_IN_PROGRESS;
        }

        // Get last location
        if (isset($this->custom_start_location) && !empty($this->custom_start_location)) {
            // use custom_start_location as default
            $last_location = $this->custom_start_location->toString();
        } else {
            // else use village as default
            $last_location = $this->player->village_location->toString();
        }
        if (isset($this->current_stage['action_type'])) {
            if ($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
                $last_location = $this->current_stage['action_data'];
            } else if ($this->current_stage['action_type'] == 'combat') {
                $last_location = $this->player->location->toString();
            }

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
                // if location is specified and not multi-stage
                if (isset($this->current_stage['action_data']) && $this->current_stage['action_data'] != '0' && $new_stage == true) {
                    $location = TravelCoords::fromDbString($this->current_stage['action_data']);
                }
                // if target type is set
                else if (isset($this->current_stage['target_type']) && $this->current_stage['target_type'] != "default") {
                    // get village in home region
                    if ($this->current_stage['target_type'] == "home_village") {
                        $village_query = $this->system->db->query("SELECT `x`, `y` FROM `region_locations`
                            INNER JOIN `regions` ON `regions`.`region_id` = `region_locations`.`region_id`
                            WHERE `regions`.`region_id` = '{$this->player->village->region_id}' AND `type` = 'village'
                        ");
                    }
                    // get any ally village
                    else if ($this->current_stage['target_type'] == "ally_village") {
                        $village_query = $this->system->db->query("SELECT `x`, `y` FROM `region_locations`
                            INNER JOIN `regions` ON `regions`.`region_id` = `region_locations`.`region_id`
                            WHERE `regions`.`village` = '{$this->player->village->village_id}' AND `type` = 'village'
                        ");
                    }
                    // get any enemy village
                    else if ($this->current_stage['target_type'] == "enemy_village") {
                        $village_query = $this->system->db->query("SELECT `x`, `y` FROM `region_locations`
                            INNER JOIN `regions` ON `regions`.`region_id` = `region_locations`.`region_id`
                            WHERE `regions`.`village` != '{$this->player->village->village_id}' AND `type` = 'village'
                        ");
                    }
                    $village_result = $this->system->db->fetch_all($village_query);
                    $random_village = array_rand($village_result);
                    $location = $this->rollLocation(new TravelCoords($village_result[$random_village]['x'], $village_result[$random_village]['y'], 1));
                }
                // if first stage and custom_start_location set, use custom_start_location as root
                else if ($stage_id == 1 && isset($this->custom_start_location) && !empty($this->custom_start_location)) {
                    $location = $this->rollLocation($this->custom_start_location);
                }
                // if basic mission and first stage, use village as root
                else if ($stage_id == 1 && $this->mission_type != $this::TYPE_EVENT) {
                    $location = $this->rollLocation($this->player->village_location);
                }
                // if last_location is set, use last_location
                else if (!empty($this->current_stage['last_location'])) {
                    $location = $this->rollLocation(TravelCoords::fromDbString($this->current_stage['last_location']));
                }
                // otherwise, use player current location
                else {
                    $location = $this->rollLocation($this->player->location);
                }
                if(!isset($villages[$location->toString()]) || $location->equals($this->player->village_location)) {
                    break;
                }
            }

            // if not multi-stage, update last location
            if ($new_stage) {
                $this->current_stage['last_location'] = $last_location;
            }

            $this->current_stage['action_data'] = $location->toString();
        }

        $search_array = array('[action_data]', '[location_radius]');
        $replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);

        $this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);

        $this->player->mission_stage = $this->current_stage;

        //notifications
        if ($this->player->mission_id > 0) {
            $mission_result = $this->system->db->query("SELECT * FROM `missions` where `mission_id` = {$this->player->mission_id} LIMIT 1");
            $mission_result = $this->system->db->fetch($mission_result);
            $notification_rank = '';
            $notification_type = '';
            switch ($mission_result['mission_type']) {
                case 2:
                    $notification_rank = 'C';
                    $notification_type = 'mission_clan';
                    break;
                case 6:
                    $notification_rank = 'E';
                    $notification_type = 'mission';
                    break;
                case 7:
                    $notification_rank = 'F';
                    $notification_type = 'mission';
                    break;
                default:
                    $notification_rank = Mission::$rank_names[$mission_result['rank']];
                    $notification_type = 'mission';
                    break;

            }
            if ($this->player->mission_stage['action_type'] == 'travel') {
                $mission_location = TravelCoords::fromDbString($this->player->mission_stage['action_data']);
                $new_notification = new MissionNotificationDto(
                    type: $notification_type,
                    message: $mission_result['name'] . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                    user_id: $this->player->user_id,
                    created: time(),
                    mission_rank: $notification_rank,
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
            } else {
                $new_notification = new MissionNotificationDto(
                    type: $notification_type,
                    message: $mission_result['name'] . " in progress",
                    user_id: $this->player->user_id,
                    created: time(),
                    mission_rank: $notification_rank,
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
            }
        }

        return Mission::STATUS_IN_PROGRESS;
    }

    public function nextTeamStage($stage_id): int {
        $villages = TravelManager::fetchVillageLocationsByCoordsStr($this->system);

        // Return signal for mission complete
        if($stage_id > count($this->stages) + 1) {
            return Mission::STATUS_COMPLETE;
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
                $this->system->db->query(
                    "UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`={$this->team->id} LIMIT 1"
                );
            }
        }
        else {
            $new_stage = false;
        }

        // Set to completion stage if all stages have been completed
        if($stage_id > count($this->stages)) {
            // if custom_start_location set
            if (isset($this->custom_start_location) && !empty($this->custom_start_location)) {
                $this->current_stage = array(
                    'stage_id' => $stage_id + 1,
                    'action_type' => 'travel',
                    'action_data' => $this->custom_start_location->toString(),
                    'description' => 'Return to ' . $this->custom_start_location->toString() . ' to complete the mission.'
                );
            }
            // otherwise use village
            else {
                $this->current_stage = array(
                    'stage_id' => $stage_id + 1,
                    'action_type' => 'travel',
                    'action_data' => $this->player->village_location->toString(),
                    'description' => 'Report back to the village to complete the mission.'
                );
            }
            $this->player->mission_stage = $this->current_stage;

            //notifications
            $new_notification = new MissionNotificationDto(
                type: "mission_team",
                message: $this->current_stage['description'],
                user_id: $this->player->user_id,
                created: time(),
                mission_rank: 'T',
                alert: false,
            );
            NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);

            return Mission::STATUS_IN_PROGRESS;
        }

        // Clear mission if it was cancelled
        if($new_stage && !$this->team->mission_id) {
            $this->player->clearMission();
            return Mission::STATUS_IN_PROGRESS;
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

            $this->system->db->query(
                "UPDATE `teams` SET `mission_stage`='$mission_stage' WHERE `team_id`='{$this->team->id}' LIMIT 1"
            );
        }

        if($this->current_stage['action_type'] == 'travel' || $this->current_stage['action_type'] == 'search') {
            for($i = 0; $i < 3; $i++) {
                $location = $this->rollLocation($this->player->village_location);
                if(!isset($villages[$location->toString()]) || $location->equals($this->player->village_location)) {
                    break;
                }
            }

            $this->current_stage['action_data'] = $location->toString();
        }

        $search_array = array('[action_data]', '[location_radius]');
        $replace_array = array($this->current_stage['action_data'], $this->current_stage['location_radius']);
        $this->current_stage['description'] = str_replace($search_array, $replace_array, $this->current_stage['description']);

        $this->player->mission_stage = $this->current_stage;

        //notifications
        if ($this->player->mission_id > 0) {
            $mission_result = $this->system->db->query("SELECT * FROM `missions` where `mission_id` = {$this->player->mission_id} LIMIT 1");
            $mission_result = $this->system->db->fetch($mission_result);
            if ($this->player->mission_stage['action_type'] == 'travel') {
                $mission_location = TravelCoords::fromDbString($this->player->mission_stage['action_data']);
                $new_notification = new MissionNotificationDto(
                    type: "mission_team",
                    message: $mission_result['name'] . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                    user_id: $this->player->user_id,
                    created: time(),
                    mission_rank: 'T',
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
            } else {
                $new_notification = new MissionNotificationDto(
                    type: "mission_team",
                    message: $mission_result['name'] . " in progress",
                    user_id: $this->player->user_id,
                    created: time(),
                    mission_rank: 'T',
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
            }
        }

        return Mission::STATUS_IN_PROGRESS;
    }

    public function rollLocation(TravelCoords $starting_location): TravelCoords {
        $max = $this->current_stage['location_radius'] * 2;
        $x = mt_rand(0, $max) - $this->current_stage['location_radius'];
        $y = mt_rand(0, $max) - $this->current_stage['location_radius'];
        $map_id = $starting_location->map_id;

        // minimum 1 tile off target if radius > 0;
        if ($this->current_stage['location_radius'] > 0) {
            if ($x == 0 && $y == 0) {
                $x++;
            }
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
     * @throws RuntimeException
     */
    public static function start($player, $mission_id): Mission {
        if($player->mission_id) {
            throw new RuntimeException("You are already on a mission!");
        }

        $fight_timer = System::ARENA_COOLDOWN;
        $max_last_ai_ms = System::currentTimeMs() - $fight_timer;
        if($player->last_ai_ms > $max_last_ai_ms) {
            throw new RuntimeException("Please wait " . ceil(($player->last_ai_ms - $max_last_ai_ms) / 1000) . " more seconds!");
        }

        $mission = new Mission($mission_id, $player);

        // TEMP Event Logic
        global $system;
        if ($mission->mission_type == Mission::TYPE_EVENT) {
            if($system->event instanceof LanternEvent) {
                if ($mission->mission_id == $system->event->mission_ids['gold_mission_id']) {
                    $valid = false;
                    foreach ($system->event->mission_coords['gold'] as $location) {
                        if ($player->location->x == $location['x'] && $player->location->y == $location['y']) {
                            $valid = true;
                        }
                    }
                    if ($valid == false) {
                        throw new RuntimeException("Invalid event location!");
                    }
                }
                if ($mission->mission_id == $system->event->mission_ids['special_mission_id']) {
                    $valid = false;
                    foreach ($system->event->mission_coords['special'] as $location) {
                        if ($player->location->x == $location['x'] && $player->location->y == $location['y']) {
                            $valid = true;
                        }
                    }
                    if ($valid == false) {
                        throw new RuntimeException("Invalid event location!");
                    }
                }
                if ($mission->mission_id == $system->event->mission_ids['easy_mission_id']) {
                    $valid = false;
                    foreach ($system->event->mission_coords['easy'] as $location) {
                        if ($player->location->x == $location['x'] && $player->location->y == $location['y']) {
                            $valid = true;
                        }
                    }
                    if ($valid == false) {
                        throw new RuntimeException("Invalid event location!");
                    }
                }
                if ($mission->mission_id == $system->event->mission_ids['medium_mission_id']) {
                    $valid = false;
                    foreach ($system->event->mission_coords['medium'] as $location) {
                        if ($player->location->x == $location['x'] && $player->location->y == $location['y']) {
                            $valid = true;
                        }
                    }
                    if ($valid == false) {
                        throw new RuntimeException("Invalid event location!");
                    }
                }
                if ($mission->mission_id == $system->event->mission_ids['hard_mission_id']) {
                    $valid = false;
                    foreach ($system->event->mission_coords['hard'] as $location) {
                        if ($player->location->x == $location['x'] && $player->location->y == $location['y']) {
                            $valid = true;
                        }
                    }
                    if ($valid == false) {
                        throw new RuntimeException("Invalid event location!");
                    }
                }
                if ($mission->mission_id == $system->event->mission_ids['nightmare_mission_id']) {
                    $valid = false;
                    foreach ($system->event->mission_coords['nightmare'] as $location) {
                        if ($player->location->x == $location['x'] && $player->location->y == $location['y']) {
                            $valid = true;
                        }
                    }
                    if ($valid == false) {
                        throw new RuntimeException("Invalid event location!");
                    }
                }
            }
            else {
                throw new RuntimeException("Invalid event type!");
            }
        }

        // Faction Mission Logic
        if ($mission->mission_type == Mission::TYPE_FACTION) {
            if (isset($mission->custom_start_location) && !empty($mission->custom_start_location)) {
                if ($player->location != $mission->custom_start_location) {
                    throw new RuntimeException("Invalid location!");
                }
            } else {
                throw new RuntimeException("Invalid custom_start_location!");
            }
        }

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

    public static function processRewards(Mission $mission, User $player, System $system): string {
        if (count($mission->rewards) > 0) {
            try {
                $reward_text = "";
                // load player inventory
                $player->getInventory();

                // get items from DB
                $reward_item_ids = [];
                foreach ($mission->rewards as $item) {
                    $reward_item_ids[] = $item['item_id'];
                }
                $reward_ids_string = '(' . implode(',', $reward_item_ids) . ')';
                /** @var Item[] $reward_items */
                $reward_items = array();
                $result = $system->db->query("SELECT * FROM `items` where `item_id` IN {$reward_ids_string}");
                while ($row = $system->db->fetch($result)) {
                    $reward_items[$row['item_id']] = Item::fromDb($row);
                }

                // roll RNG, add to inventory
                foreach ($mission->rewards as $item) {
                    if (mt_rand(0, 100) <= $item['chance']) {
                        $player->giveItem(
                            $reward_items[$item['item_id']],
                            $item['quantity']
                        );
                        if ($item['chance'] < 100) {
                            $reward_text .= "Gained " . $reward_items[$item['item_id']]->name . " x" . $item['quantity'] . " " . $item['chance'] . "%<br>";
                        } else {
                            $reward_text .= "Gained " . $reward_items[$item['item_id']]->name . " x" . $item['quantity'] . "<br>";
                        }

                    }
                }

                // save inventory
                $player->updateInventory();

                // add message
                return $reward_text;
            } catch (RuntimeException $e) {
                return $e->getMessage();
            }
        }
        return "";
    }
}

