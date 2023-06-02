<?php

require __DIR__ . '/NearbyPlayerDto.php';

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

    public array $map_data;

    /**
     * @var TravelCoords[]
     */
    private array $village_locations;

    public function __construct(System $system, User $user) {
        $this->system = $system;
        $this->user = $user;

        $result = $this->system->query("SELECT * FROM `maps` WHERE `map_id`={$this->user->location->map_id}");
        $this->map_data = $this->system->db_fetch($result);
    }

    public static function locationIsInVillage(System $system, TravelCoords $location): bool {
        $result = $system->query("SELECT COUNT(*) as `count` FROM `villages` WHERE `location`='{$location->fetchString()}' LIMIT 1");
        $count = (int)$system->db_fetch($result)['count'];

        return $count >= 1;
    }


    public function updateFilter(string $filter, string $filter_value): bool {
        switch($filter) {
            case 'travel_ranks_to_view':
                // $filter_value will be a CSV list
                $filter_value_arr = explode(",", $filter_value);

                $this->user->filters['travel_ranks_to_view'] = [];
                for($i = 1; $i <= System::SC_MAX_RANK; $i++) {
                    $this->user->filters['travel_ranks_to_view'][$i] = in_array($i, $filter_value_arr);
                }

                $this->user->updateData();
                return true;
            default;
                return false;
        }
    }

    /**
     * @throws Exception
     */
    public function checkRestrictions(): bool {
        $ignore_travel_restrictions = $this->user->isHeadAdmin();

        // check if the user has moved too recently
        $move_time_left = Travel::checkMovementDelay($this->user->last_movement_ms);
        if ($move_time_left > 0) {
            throw new Exception("Moving...");
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
        if (TravelManager::locationIsInVillage($this->system, $new_coords)
            && !$new_coords->equals($this->user->village_location)
            && !$ignore_travel_restrictions) {
            throw new Exception('You cannot enter another village!');
        }

        // check if the user is entering their own village or out of it
        if ($new_coords->equals($this->user->village_location)) {
            $this->user->in_village = true;
        }
        else {
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
        if (!in_array($this->user->village->name, $portal_whitelist) && !$ignore_travel_restrictions) {
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

    /**
     * @return NearbyPlayerDto[]
     */
    public function fetchNearbyPlayers(): array {
        $sql = "SELECT `users`.`user_id`, `users`.`user_name`, `users`.`village`, `users`.`rank`, `users`.`stealth`,
                `users`.`level`, `users`.`attack_id`, `users`.`battle_id`, `ranks`.`name` as `rank_name`, `users`.`location`
                FROM `users`
                INNER JOIN `ranks`
                ON `users`.`rank`=`ranks`.`rank_id`
                WHERE `users`.`last_active` > UNIX_TIMESTAMP() - 120
                ORDER BY `users`.`exp` DESC, `users`.`user_name` DESC";
        $result = $this->system->query($sql);
        $users = $this->system->db_fetch_all($result);
        $return_arr = [];
        foreach ($users as $user) {
            // check if the user is nearby (including stealth
            $scout_range = max(0, $this->user->scout_range - $user['stealth']);
            $user_location = TravelCoords::fromDbString($user['location']);
            if ($user_location->map_id !== $this->user->location->map_id ||
                $user_location->distanceDifference($this->user->location) > $scout_range) {
                continue;
            }

            // if ally or enemy
            // if there were alliance we can do additional checks here
            if ($user['village'] === $this->user->village->name) {
                $user_alignment = 'Ally';
            } else {
                $user_alignment = 'Enemy';
            }

            // only display attack links if the same rank
            $can_attack = false;
            if ((int)$user['rank'] === $this->user->rank_num
                && $this->user->location->equals(TravelCoords::fromDbString($user['location']))
                && $user['user_id'] != $this->user->user_id
                && $user['village'] !== $this->user->village->name) {
                $can_attack = true;
            }

            // calculate direction
            $user_direction = "none";
            if ($user['user_id'] != $this->user->user_id) {
                $diff_x = ($user_location->x - $this->user->location->x);
                $diff_y = ($user_location->y - $this->user->location->y);
                if ($diff_x != 0 || $diff_y != 0) {
                    $angle = atan2($diff_y, $diff_x);
                    $angle_degrees = rad2deg($angle);
                    $angle_degrees = fmod(($angle_degrees + 450), 360);
                    $directions = array("north", "northeast", "east", "southeast", "south", "southwest", "west", "northwest");
                    $index = round($angle_degrees / (360 / count($directions)));
                    $user_direction = $directions[$index % count($directions)];
                }
            }

            // add to return
            $return_arr[] = new NearbyPlayerDto(
                user_id: $user['user_id'],
                user_name: $user['user_name'],
                target_x: $user_location->x,
                target_y: $user_location->y,
                target_map_id: $user_location->map_id,
                rank_name: $user['rank_name'],
                rank_num: $user['rank'],
                village_icon: TravelManager::VILLAGE_ICONS[$user['village']],
                alignment: $user_alignment,
                attack: $can_attack,
                attack_id: $user['attack_id'],
                level: $user['level'],
                battle_id: $user['battle_id'],
                direction: $user_direction,
            );
        }

        // Add more users for display
        if ($this->system->environment == System::ENVIRONMENT_DEV) {
            for ($i = 0; $i < 7; $i++) {
                $return_arr[] = new NearbyPlayerDto(
                    user_id: $i . mt_rand(10000, 20000),
                    user_name: 'Konohamaru',
                    target_x: 15, // rank name
                    target_y: 15,
                    target_map_id: 2,
                    rank_name: 'Akademi-sei',
                    rank_num: 3,
                    village_icon: TravelManager::VILLAGE_ICONS['Mist'],
                    alignment: 'Enemy',
                    attack: true,
                    attack_id: 'abc' . $i . mt_rand(10000, 20000),
                    level: 30,
                    battle_id: 0,
                    direction: "none", 
                );
            }
        }

        return $return_arr;
    }

    public function shouldShowMissionLocationPrompt(): bool {
        $mission_stage_uses_travel = $this->user->mission_stage != null && (
            $this->user->mission_stage['action_type'] == 'travel' || $this->user->mission_stage['action_type'] == 'search'
        );

        if ($this->user->mission_id && $mission_stage_uses_travel
            && $this->user->location->equals(TravelCoords::fromDbString($this->user->mission_stage['action_data']))) {
            return true;
        }

        return false;
    }

    /**
     * @return MapLocation[]
     */
    public function fetchCurrentMapLocations(): array {
        $result = $this->system->query("
            SELECT *
            FROM `maps_locations`
            WHERE `map_id`={$this->user->location->map_id}
        ");

        $locations = [];
        foreach ($this->system->db_fetch_all($result) as $loc) {
            $locations[] = new MapLocation($loc);
        }

        return $locations;
    }

    /**
     * @return array|null
     */
    public function fetchCurrentLocationPortal(): ?array {
        $portal_data = null;

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

        return $portal_data;
    }

    /**
     * @return array village names, keyed by travel coords str
     */
    public static function fetchVillageLocationsByCoordsStr(System $system): array {
        $village_locations = [];

        $result = $system->query("SELECT `name`, `location` FROM `villages`");
        while($row = $system->db_fetch($result)) {
            $village_locations[$row['location']] = $row['name'];
        }

        return $village_locations;
    }

}