<?php

class TravelManager {

    const VILLAGE_ICONS = [
        'Stone' => 'images/village_icons/stone.png',
        'Mist' => 'images/village_icons/mist.png',
        'Cloud' => 'images/village_icons/cloud.png',
        'Sand' => 'images/village_icons/sand.png',
        'Leaf' => 'images/village_icons/leaf.png'
    ];
    
    private System $system;
    private User $user;
    private array $map_data;

    public function __construct(System $system, User $user) {
        $this->system = $system;
        $this->user = $user;

        $result = $this->system->query("SELECT * FROM `maps` WHERE `map_id`={$this->user->location->map_id}");
        $this->map_data = $this->system->db_fetch($result);
    }

    public function fetchMapDataAPI(): array {
        // all locations
        $result = $this->system->query("
            SELECT * 
            FROM `maps_locations` 
            WHERE `map_id`={$this->user->location->map_id}
            ");
        $locations = [];
        foreach ($this->system->db_fetch_all($result) as $loc) {
            $locations[] = new MapLocation($loc);
        }

        // portal check
        $portal_data = [];
        $result = $this->system->query("
            SELECT * 
            FROM `maps_portals` 
            WHERE `entrance_x`={$this->user->location->x} 
              AND `entrance_y`={$this->user->location->y} 
              AND `from_id`={$this->user->location->map_id} 
              AND `active`=1
              ");
        if ($this->system->db_last_num_rows) {
            $portal_data = $this->system->db_fetch($result);
        }

        // mission location check
        $mission_button = false;
        if ($this->user->mission_id
            && ($this->user->mission_stage['action_type'] == 'travel'
            || $this->user->mission_stage['action_type'] == 'search')
            && $this->user->location->equals(TravelCoords::fromDbString($this->user->mission_stage['action_data']))) {
            $mission_button = true;
        }

        return [
            'player_x'          => $this->user->location->x,
            'player_y'          => $this->user->location->y,
            'player_map_id'          => $this->user->location->map_id,
            'player_id'         => $this->user->user_id,
            'player_filters'    => $this->user->filters,
            'player_icon'       => Travel::PLAYER_ICON,
            'map_name'          => $this->map_data['map_name'],
            'background_image'  => $this->map_data['background'],
            'start_x'           => $this->map_data['start_x'],
            'start_y'           => $this->map_data['start_y'],
            'end_x'             => $this->map_data['end_x'],
            'end_y'             => $this->map_data['end_y'],
            'in_village'        => $this->user->in_village,
            'current_portal'    => $portal_data,
            'current_mission'   => $mission_button,
            'all_locations'     => $locations,
            'tile_width'        => $this->map_data['tile_width'],
            'tile_height'       => $this->map_data['tile_height']
        ];
    }

    public function updateFilter(string $filter, bool $filter_value): bool {
        $new_value = !$filter_value;
        $this->user->filters['travel_filter'][$filter] = $new_value;
        $this->user->updateData();
        return true;
    }

    /**
     * @throws Exception
     */
    public function checkRestrictions(): bool {
        $ignore_travel_restrictions = $this->user->isHeadAdmin();
        // check if the user has moved too recently
        $move_time_left = Travel::checkMovementDelay($this->user->last_movement_ms);
        if ($move_time_left > 0) {
            throw new Exception('Moving...');
        }
        // check if the user has exited an AI too recently
        $ai_time_left = Travel::checkAIDelay($this->user->last_ai_ms);
        if ($ai_time_left > 0 && !$ignore_travel_restrictions) {
            throw new Exception('You have recently left an AI battle and cannot move for ' . floor($ai_time_left / 1000) . ' seconds!');
        }
        // check if the user has exited battle too recently
        $pvp_time_left = Travel::checkPVPDelay($this->user->last_pvp_ms);
        if ($pvp_time_left > 0 && !$ignore_travel_restrictions) {
            throw new Exception('You have recently left a battle and cannot move for ' . floor($pvp_time_left / 1000) . ' seconds!');
        }
        // check if the user has died to recently
        $death_time_left = Travel::checkDeathDelay($this->user->last_death_ms);
        if ($death_time_left > 0 && !$ignore_travel_restrictions) {
            throw new Exception('You are still recovering from a defeat and cannot move for ' . floor($death_time_left / 1000) . ' seconds!');
        }
        // check if the user is in battle
        if ($this->user->battle_id && !$ignore_travel_restrictions) {
            throw new Exception('You are in battle!');
        }
        // check if the user is in a special mission
        if ($this->user->special_mission && !$ignore_travel_restrictions) {
            throw new Exception('You are currently in a Special Mission and cannot travel!');
        }
        // check if the user is in a combat mission fail it
        if ($this->user->mission_id
            && $this->user->mission_stage['action_type'] == 'combat') {
            $mission = new Mission($this->user->mission_id, $this->user);
            if ($mission->mission_type == 5) {
                $mission->nextStage($this->user->mission_stage['stage_id'] = 4);
                $this->user->mission_stage['mission_money'] /= 2;
                throw new Exception('Mission failed! Return to the village');
            }
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function movePlayer($direction): bool {
        $new_coords = Travel::getNewMovementValues($direction, $this->user->location);
        $ignore_travel_restrictions = $this->user->isHeadAdmin();

        if (!$this->checkRestrictions()) {
            throw new Exception('Unable to move!');
        }

        // check if the coords exceed the map dimensions
        if (($new_coords->x > $this->map_data['end_x']
            || $new_coords->y > $this->map_data['end_y']
            || $new_coords->x < 1
            || $new_coords->y < 1)
            && !$ignore_travel_restrictions) {
            throw new Exception('You cannot move past this point!');
        }
        // check if the user is trying to move to a village that is not theirs
        $villages = $this->system->getVillageLocations();
        if (isset($villages[$new_coords->fetchString()])
            && !$new_coords->equals($this->user->village_location)
            && !$ignore_travel_restrictions) {
            throw new Exception('You cannot enter another village!');
        }
        // check if the user is entering their own village or out of it
        if ($new_coords->equals($this->user->village_location)) {
            $this->user->in_village = true;
        } else {
            $this->user->in_village = false;
        }
        // update the player data
        $this->user->location->x = $new_coords->x;
        $this->user->location->y = $new_coords->y;
        $this->user->last_movement_ms = System::currentTimeMs();
        $this->user->updateData();
        return true;
    }

    /**
     * @throws Exception
     */
    public function enterPortal($portal_id): bool {
        $ignore_travel_restrictions = $this->user->isHeadAdmin();
        // portal data
        $portal_data = Travel::getPortalData($this->system, $portal_id);
        if (empty($portal_data)) {
            throw new Exception('You cannot enter here!');
        }
        if (!$this->checkRestrictions()) {
            throw new Exception('Unable to move!');
        }
        // check if the player is at the correct entrance
        if (!$this->user->location->equals(new TravelCoords($portal_data['entrance_x'], $portal_data['entrance_y'], $portal_data['from_id']))
            && !$ignore_travel_restrictions) {
            throw new Exception('You cannot enter here!');
        }
        // check if the player is in a faction that allows this portal
        $portal_whitelist = array_map('trim', explode(',', $portal_data['whitelist']));
        if (!in_array($this->user->village, $portal_whitelist) && !$ignore_travel_restrictions) {
            throw new Exception('You are unable to enter here!');
        }
        // update the player data
        $this->user->location->x = $portal_data['exit_x'];
        $this->user->location->y = $portal_data['exit_y'];
        $this->user->location->map_id = $portal_data['to_id'];
        $this->user->last_movement_ms = System::currentTimeMs();
        $this->user->updateData();
        return true;
    }

    public function fetchScoutData(): array {
        return $this->fetchNearbyPlayers();
    }

    public function fetchNearbyPlayers(): array {
        $sql = "SELECT `users`.`user_id`, `users`.`user_name`, `users`.`village`, `users`.`rank`, `users`.`stealth`,
                `users`.`level`, `users`.`attack_id`, `users`.`battle_id`, `ranks`.`name`, `users`.`location`
                FROM `users`
                INNER JOIN `ranks`
                ON `users`.`rank`=`ranks`.`rank_id`
                WHERE `users`.`last_active` > UNIX_TIMESTAMP() - 120
                ORDER BY `users`.`exp` DESC";
        $result = $this->system->query($sql);
        $users = $this->system->db_fetch_all($result);
        $return_arr = [];
        foreach ($users as $user) {
            // check if the user is nearby (including stealth
            $scout_range = max(0, $this->user->scout_range - $user['stealth']);
            $location = TravelCoords::fromDbString($user['location']);
            if ($location->map_id !== $this->user->location->map_id ||
                $location->distanceDifference($this->user->location) > $scout_range) {
                continue;
            }
            $user['target_x'] = $location->x;
            $user['target_y'] = $location->y;
            $user['target_map_id'] = $location->map_id;
            // village icon
            $user['village_icon'] = TravelManager::VILLAGE_ICONS[$user['village']];

            // if ally or enemy
            // if there were alliance we can do additional checks here
            if ($user['village'] === $this->user->village) {
                $user['alignment'] = 'Ally';
            } else {
                $user['alignment'] = 'Enemy';
            }

            // only display attack links if the same rank
            $user['attack'] = false;
            if ((int)$user['rank'] === $this->user->rank_num
                && $this->user->location->equals(TravelCoords::fromDbString($user['location']))
                && $user['user_id'] != $this->user->user_id
                && $user['village'] !== $this->user->village) {
                $user['attack'] = true;
            }

            // add to return
            $return_arr[] = $user;
        }

        return $return_arr;
    }

}