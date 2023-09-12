<?php

use DDTrace\Trace;

require __DIR__ . '/NearbyPlayerDto.php';
require __DIR__ . '/MapObjective.php';
require __DIR__ . '/RegionObjective.php';
require __DIR__ . '/MapLocationAction.php';
require __DIR__ . '/InvalidMovementException.php';

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

    public WarManager $warManager;

    public array $map_data;

    const DISPLAY_RADIUS = 12;

    /**
     * @var TravelCoords[]
     */
    private array $village_locations;

    public function __construct(System $system, User $user) {
        $this->system = $system;
        $this->user = $user;
        $this->warManager = new WarManager($system, $user);

        $result = $this->system->db->query("SELECT * FROM `maps` WHERE `map_id`={$this->user->location->map_id}");
        $this->map_data = $this->system->db->fetch($result);
    }

    public static function locationIsInVillage(System $system, TravelCoords $location): bool {
        $result = $system->db->query(
            "SELECT COUNT(*) as `count` FROM `villages`
            INNER JOIN `maps_locations` ON `maps_locations`.`location_id` = `villages`.`map_location_id`
            WHERE `x`='{$location->x}' AND `y`='{$location->y}' AND `map_id`='{$location->map_id}' LIMIT 1"
        );
        $count = (int)$system->db->fetch($result)['count'];

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
            case 'strategic_view':
                $this->user->filters['strategic_view'] = $filter_value;
                $this->user->updateData();
                return true;
            case 'display_grid':
                $this->user->filters['display_grid'] = $filter_value;
                $this->user->updateData();
                return true;
            default;
                return false;
        }
    }

    /**
     * @throws RuntimeException
     */
    public function checkRestrictions(): bool {
        // check if the user has moved too recently
        $move_time_left = Travel::checkMovementDelay($this->user->last_movement_ms);
        if ($move_time_left > 0) {
            throw new InvalidMovementException("Moving...");
        }

        // check if the user has exited an AI too recently
        $ai_time_left = Travel::checkAIDelay($this->user->last_ai_ms);
        if ($ai_time_left > 0) {
            throw new InvalidMovementException('You have recently left an AI battle and cannot move for ' . floor($ai_time_left / 1000) . ' seconds!');
        }

        // check if the user has exited battle too recently
        $pvp_time_left = Travel::checkPVPDelay($this->user->last_pvp_ms);
        if ($pvp_time_left > 0) {
            throw new InvalidMovementException('You have recently left a battle and cannot move for ' . floor($pvp_time_left / 1000) . ' seconds!');
        }

        // check if the user has died to recently
        $death_time_left = Travel::checkDeathDelay($this->user->last_death_ms);
        if ($death_time_left > 0) {
            throw new InvalidMovementException('You are still recovering from a defeat and cannot move for ' . floor($death_time_left / 1000) . ' seconds!');
        }

        // check if the user is in battle
        if ($this->user->battle_id) {
            throw new InvalidMovementException('You are in battle!');
        }

        // check if the user is in a special mission
        if ($this->user->special_mission) {
            throw new InvalidMovementException('You are currently in a Special Mission and cannot travel!');
        }

        // check if the user is in an active operation
        if ($this->user->operation) {
            throw new InvalidMovementException('You are currently in an active Operation and cannot travel!');
        }

        // check if the user is in a combat mission fail it
        if ($this->user->mission_id
            && $this->user->mission_stage['action_type'] == 'combat') {
            $mission = new Mission($this->user->mission_id, $this->user);
            if ($mission->mission_type == 5) {
                $mission->nextStage($this->user->mission_stage['stage_id'] = 4);
                $this->user->mission_stage['mission_money'] /= 2;
                throw new InvalidMovementException('Mission failed! Return to the village');
            }
        }
        return true;
    }

    /**
     * @throws RuntimeException
     */
    #[Trace]
    public function movePlayer($direction): bool {
        $new_coords = Travel::getNewMovementValues($direction, $this->user->location);
        $ignore_coord_restrictions = $this->user->isHeadAdmin();

        if (!$this->checkRestrictions()) {
            throw new InvalidMovementException('Unable to move!');
        }

        // check if the coords exceed the map dimensions
        if (($new_coords->x > $this->map_data['end_x']
            || $new_coords->y > $this->map_data['end_y']
            || $new_coords->x < 1
            || $new_coords->y < 1)
            && !$ignore_coord_restrictions) {
            throw new InvalidMovementException('You cannot move past this point!');
        }

        // check if the user is trying to move to a village that is not theirs
        if (TravelManager::locationIsInVillage($this->system, $new_coords)
            && !$new_coords->equals($this->user->village_location)
            && !$ignore_coord_restrictions) {
            throw new InvalidMovementException('You cannot enter another village!');
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
     * @throws RuntimeException
     */
    public function enterPortal($portal_id): bool {
        $ignore_travel_restrictions = $this->user->isHeadAdmin();

        // portal data
        $portal_data = Travel::getPortalData($this->system, $portal_id);
        if (empty($portal_data)) {
            throw new InvalidMovementException('You cannot enter here!');
        }
        if (!$this->checkRestrictions()) {
            throw new InvalidMovementException('Unable to move!');
        }

        // check if the player is at the correct entrance
        if (!$this->user->location->equals(new TravelCoords($portal_data['entrance_x'], $portal_data['entrance_y'], $portal_data['from_id']))
            && !$ignore_travel_restrictions) {
            throw new InvalidMovementException('You cannot enter here!');
        }

        // check if the player is in a faction that allows this portal
        $portal_whitelist = array_map('trim', explode(',', $portal_data['whitelist']));
        if (!in_array($this->user->village->name, $portal_whitelist) && !$ignore_travel_restrictions) {
            throw new InvalidMovementException('You are unable to enter here!');
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
    #[Trace]
    public function fetchNearbyPlayers(): array {
        $sql = "SELECT `users`.`user_id`, `users`.`user_name`, `users`.`village`, `users`.`rank`, `users`.`stealth`,
                `users`.`level`, `users`.`attack_id`, `users`.`battle_id`, `ranks`.`name` as `rank_name`, `users`.`location`, `users`.`last_death_ms`, `villages`.`village_id`
                FROM `users`
                INNER JOIN `ranks`
                ON `users`.`rank`=`ranks`.`rank_id`
                INNER JOIN `villages`
                ON `users`.`village` = `villages`.`name`
                WHERE `users`.`last_active` > UNIX_TIMESTAMP() - 120
                ORDER BY `users`.`exp` DESC, `users`.`user_name` DESC";
        $result = $this->system->db->query($sql);
        $users = $this->system->db->fetch_all($result);
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
                $user_direction = $this->user->location->directionToTarget($user_location);
            }

            // calculate distance
            $distance = $this->user->location->distanceDifference($user_location);

            $invulnerable = false;
            // determine if vulnerable to attack
            if ($user['last_death_ms'] > System::currentTimeMs() - (300 * 1000)) {
                $invulnerable = true;
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
                invulnerable: $invulnerable,
                distance: $distance,
                village_id: $user['village_id'],
            );
        }

        // Add more users for display
        if ($this->system->isDevEnvironment()) {
            $placeholder_coords = new TravelCoords(15, 15, 1);

            for ($i = 0; $i < 2; $i++) {
                $return_arr[] = new NearbyPlayerDto(
                    user_id: $i . mt_rand(10000, 20000),
                    user_name: 'Konohamaru',
                    target_x: $placeholder_coords->x,
                    target_y: $placeholder_coords->y,
                    target_map_id: $placeholder_coords->map_id,
                    rank_name: 'Akademi-sei',
                    rank_num: 3,
                    village_icon: TravelManager::VILLAGE_ICONS['Mist'],
                    alignment: 'Enemy',
                    attack: $this->user->location->equals($placeholder_coords),
                    attack_id: 'abc' . $i . mt_rand(10000, 20000),
                    level: 30,
                    battle_id: 0,
                    direction: $this->user->location->directionToTarget($placeholder_coords),
                    distance: $this->user->location->distanceDifference($placeholder_coords),
                    invulnerable: false,
                    village_id: 3,
                );
            }
        }

        usort($return_arr, function ($a, $b) {
            if ($a->alignment == 'Enemy' && $b->alignment == 'Ally') {
                return -1; // $a comes before $b
            } elseif ($a->alignment == 'Ally' && $b->alignment == 'Enemy') {
                return 1; // $b comes before $a
            } else {
                // Sort by distance first
                if ($a->distance < $b->distance) {
                    return -1; // $a comes before $b
                } elseif ($a->distance > $b->distance) {
                    return 1; // $b comes before $a
                } else {
                    // Sort by level if distances are equal
                    if ($a->level > $b->level) {
                        return -1; // $a comes before $b
                    } elseif ($a->level < $b->level) {
                        return 1; // $b comes before $a
                    } else {
                        return 0; // Objects are equal
                    }
                }
            }
        });

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
    #[Trace]
    public function fetchCurrentMapLocations(): array {
        $result = $this->system->db->query(
            "
                SELECT *
                FROM `maps_locations`
                WHERE `map_id`={$this->user->location->map_id}
            "
        );

        $locations = [];
        foreach ($this->system->db->fetch_all($result) as $loc) {
            if ($loc['name'] == "Ayakashi's Abyss" && $this->system->event == null) {
                $loc['action_url'] = $this->system->router->getUrl("forbiddenShop");
                $loc['action_message'] = "Enter the Abyss";

                $abyss_shop = new MapLocation($loc);
                $distance = $this->user->location->distanceDifference(new TravelCoords(
                    x: $abyss_shop->x,
                    y: $abyss_shop->y,
                    map_id: $abyss_shop->map_id
                ));

                if($distance <= 3) {
                    $locations[] = $abyss_shop;
                }
            }
            else if ($loc['name'] == "Unknown") {
                $unknown = new MapLocation($loc);
                $distance = $this->user->location->distanceDifference(
                    new TravelCoords(
                        x: $unknown->x,
                        y: $unknown->y,
                        map_id: $unknown->map_id
                    )
                );

                if ($distance <= 1) {
                    $locations[] = $unknown;
                }
            }
            else {
                $new_location = new MapLocation($loc);
                $new_location->objective_type = "key_location";
                $distance = $this->user->location->distanceDifference(
                    new TravelCoords(
                        x: $new_location->x,
                        y: $new_location->y,
                        map_id: $new_location->map_id
                    )
                );
                if ($distance <= self::DISPLAY_RADIUS) {
                    $locations[] = $new_location;
                }
            }
        }

        return $locations;
    }

    /**
     * @return array|null
     */
    public function fetchCurrentLocationPortal(): ?array {
        $portal_data = null;

        $result = $this->system->db->query(
            "
                SELECT *
                FROM `maps_portals`
                WHERE `entrance_x`={$this->user->location->x}
                  AND `entrance_y`={$this->user->location->y}
                  AND `from_id`={$this->user->location->map_id}
                  AND `active`=1
                  "
        );

        if ($this->system->db->last_num_rows) {
            $portal_data = $this->system->db->fetch($result);
        }

        return $portal_data;
    }

    /**
     * @throws RuntimeException
     */
    public function attackPlayer(string $target_attack_id): bool {
        // get user id off the attack link
        $result = $this->system->db->query("SELECT `user_id` FROM `users` WHERE `attack_id`='{$target_attack_id}' LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("Invalid user!");
        }

        $target_user= $this->system->db->fetch($result);
        $target_user_id = $target_user['user_id'];

        $user = User::loadFromId($this->system, $target_user_id);
        $user->loadData(User::UPDATE_NOTHING, true);

        // check if the location forbids pvp
        if ($this->user->current_location->location_id && $this->user->current_location->pvp_allowed == 0) {
            throw new RuntimeException("You cannot fight at this location!");
        }

        if ($user->village->name == $this->user->village->name) {
            throw new RuntimeException("You cannot attack people from your own village!");
        }

        if ($user->rank_num < 3) {
            throw new RuntimeException("You cannot attack people below Chuunin rank!");
        }
        if ($this->user->rank_num < 3) {
            throw new RuntimeException("You cannot attack people Chuunin rank and higher!");
        }

        if ($user->rank_num !== $this->user->rank_num) {
            throw new RuntimeException("You can only attack people of the same rank!");
        }

        if (!$user->location->equals($this->user->location)) {
            throw new RuntimeException("Target is not at your location!");
        }
        if ($user->battle_id) {
            throw new RuntimeException("Target is in battle!");
        }
        if ($user->last_active < time() - 120) {
            throw new RuntimeException("Target is inactive/offline!");
        }
        if ($this->user->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
            throw new RuntimeException("You died within the last minute, please wait " .
                ceil((($this->user->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
        }
        if ($user->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
            throw new RuntimeException("Target has died within the last minute, please wait " .
                ceil((($user->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
        }

        if ($this->system->USE_NEW_BATTLES) {
            BattleV2::start($this->system, $this->user, $user, Battle::TYPE_FIGHT);
        } else {
            Battle::start($this->system, $this->user, $user, Battle::TYPE_FIGHT);
        }
        return true;
    }

    /**
     * @return array village names, keyed by travel coords str
     */
    public static function fetchVillageLocationsByCoordsStr(System $system): array {
        $village_locations = [];

        $result = $system->db->query("SELECT `villages`.`name`, `x`, `y`, `map_id` FROM `villages`
            INNER JOIN `maps_locations` on `villages`.`map_location_id` = `maps_locations`.`location_id`
        ");
        while($row = $system->db->fetch($result)) {
            $village_coords = new TravelCoords($row['x'], $row['y'], $row['map_id']);
            $village_locations[$village_coords->fetchString()] = $row['name'];
        }

        return $village_locations;
    }

    /**
     * @return MapLocationAction
     */
    public static function getMapLocationAction(array $locations = [], User $player): MapLocationAction
    {
        foreach ($locations as $location) {
            if ($location->x == $player->location->x && $location->y == $player->location->y) {
                return new MapLocationAction($location->action_url, $location->action_message);
            }
        }
        return new MapLocationAction();
    }

    /**
     * @return Region[]
     */
    #[Trace]
    public function getRegions(User $player): array {
        // generate vertices for player view area
        $player_area = [
            [$player->location->x - self::DISPLAY_RADIUS, $player->location->y - self::DISPLAY_RADIUS], // top left bound
            [$player->location->x + self::DISPLAY_RADIUS, $player->location->y - self::DISPLAY_RADIUS], // top right bound
            [$player->location->x + self::DISPLAY_RADIUS, $player->location->y + self::DISPLAY_RADIUS], // bottom right bound
            [$player->location->x - self::DISPLAY_RADIUS, $player->location->y + self::DISPLAY_RADIUS], // bottom left bound
        ];

        $result = $this->system->db->query("SELECT * FROM `regions`");

        $regions = [];
        foreach ($this->system->db->fetch_all($result) as $region) {
            $in_view_area = false;
            $region_vertices = json_decode($region['vertices']);
            // if a region vertex is in the view area add to list
            foreach ($region_vertices as $vertex) {
                $coord = new RegionCoords($vertex[0], $vertex[1], 1);
                if (Region::coordInRegion($coord, $player_area)) {
                    $in_view_area = true;
                }
            }
            if ($in_view_area) {
                $regions[] = Region::fromDb($region, $player->location->x - self::DISPLAY_RADIUS, $player->location->y - self::DISPLAY_RADIUS, $player->location->x + self::DISPLAY_RADIUS, $player->location->y + self::DISPLAY_RADIUS);
            }
        }

        return $regions;
    }

    /**
     * @return array
     */
    public function getCoordsByRegion($regions): array
    {
        $locations = [];
        foreach ($regions as $region) {
            if (isset($region->coordinates) && is_array($region->coordinates)) {
                foreach ($region->coordinates as $x => $innerArray) {
                    foreach ($innerArray as $y => $value) {
                        $locations[$x][$y] = $value;
                    }
                }
            }
        }
        return $locations;
    }

    /**
     * @return TravelCoords
     */
    public function getColosseumCoords(): TravelCoords {
        $result = $this->system->db->query("SELECT * FROM `maps_locations` WHERE `name` = 'Underground Colosseum'");
        $location_result = $this->system->db->fetch($result);
        return new TravelCoords($location_result['x'], $location_result['y'], 1);
    }

    /**
     * @return NearbyPatrol[]
     */
    #[Trace]
    public function fetchNearbyPatrols(): array {
        $return_arr = [];

        // exit if war disabled
        if (!$this->system->war_enabled) {
            return $return_arr;
        }

        $result = $this->system->db->query("SELECT * FROM `patrols`");
        $result = $this->system->db->fetch_all($result);
        foreach ($result as $row) {
            $patrol = new NearbyPatrol($row, "patrol");
            $patrol->setLocation($this->system);
            if ($this->user->location->distanceDifference(new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id)) <= $this->user->scout_range) {
                $return_arr[] = $patrol;
            }
        }
        $result = $this->system->db->query("SELECT * FROM `caravans`");
        $result = $this->system->db->fetch_all($result);
        foreach ($result as $row) {
            // if travel time is set then only display if active
            if (!empty($row['travel_time'])) {
                if ($row['travel_time'] + ($row['start_time'] * 1000) + NearbyPatrol::DESTINATION_BUFFER_MS > (time() * 1000)) {
                    $patrol = new NearbyPatrol($row, "caravan");
                    $patrol->setLocation($this->system);
                    if ($this->user->location->distanceDifference(new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id)) <= $this->user->scout_range) {
                        $return_arr[] = $patrol;
                    }
                }
            } else {
                $patrol = new NearbyPatrol($row, "caravan");
                $patrol->setLocation($this->system);
                if ($this->user->location->distanceDifference(new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id)) <= $this->user->scout_range) {
                    $return_arr[] = $patrol;
                }
            }
        }

        return $return_arr;
    }

    /**
     * @return MapObjective[]
     */
    #[Trace]
    public function fetchMapObjectives(): array
    {
        $objectives = [];

        // Get mission objectives
        if ($this->user->mission_id > 0) {
            if ($this->user->mission_stage['action_type'] == 'travel') {
                $mission_result = $this->system->db->query("SELECT `name` FROM `missions` WHERE `mission_id` = '{$this->user->mission_id}' LIMIT 1");
                $mission_location = TravelCoords::fromDbString($this->user->mission_stage['action_data']);
                $objectives[] = new MapObjective(
                    id: null,
                    name: $this->system->db->fetch($mission_result)['name'],
                    map_id: $mission_location->map_id,
                    x: $mission_location->x,
                    y: $mission_location->y,
                    image: "/images/v2/icons/reticle.png",
                    objective_type: "target",
                );
            }
            if ($this->user->mission_stage['action_type'] == 'search') {
                $mission_result = $this->system->db->query("SELECT `name` FROM `missions` WHERE `mission_id` = '{$this->user->mission_id}' LIMIT 1");
                $mission_location = TravelCoords::fromDbString($this->user->mission_stage['last_location']);
                $objectives[] = new MapObjective(
                    id: null,
                    name: $this->system->db->fetch($mission_result)['name'],
                    map_id: $mission_location->map_id,
                    x: $mission_location->x,
                    y: $mission_location->y,
                    image: "/images/v2/icons/magnifying-glass.png",
                );
            }
        }

        // TEMP Add Events - We have to hard code the mission IDs is System for now
        if ($this->system->event != null) {
            if ($this->system->event instanceof LanternEvent) {
                foreach ($this->system->event->mission_coords['gold'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: null,
                        name: "Treasure",
                        map_id: 1,
                        x: $event_mission['x'],
                        y: $event_mission['y'],
                        image: "/images/events/lanternyellow.png",
                        action_url: $this->system->router->getUrl("mission", [
                            'start_mission' => $this->system->event->mission_ids['gold_mission_id'],
                            'mission_type' => 'event'
                        ]),
                        action_message: "Chase the Kotengu",
                    );
                }
                foreach ($this->system->event->mission_coords['special'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: null,
                        name: "Special",
                        map_id: 1,
                        x: $event_mission['x'],
                        y: $event_mission['y'],
                        image: "/images/events/cultsign.png",
                        action_url: $this->system->router->getUrl("mission", [
                            'start_mission' => $this->system->event->mission_ids['special_mission_id'],
                            'mission_type' => 'event'
                        ]),
                        action_message: "Enter the Fray",
                    );
                }
                foreach ($this->system->event->mission_coords['easy'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: null,
                        name: "Easy",
                        map_id: 1,
                        x: $event_mission['x'],
                        y: $event_mission['y'],
                        image: "/images/events/lanternred.png",
                        action_url: $this->system->router->getUrl("mission", [
                            'start_mission' => $this->system->event->mission_ids['easy_mission_id'],
                            'mission_type' => 'event'
                        ]),
                        action_message: "Begin Search",
                    );
                }
                foreach ($this->system->event->mission_coords['medium'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: null,
                        name: "Medium",
                        map_id: 1,
                        x: $event_mission['x'],
                        y: $event_mission['y'],
                        image: "/images/events/lanternblue.png",
                        action_url: $this->system->router->getUrl("mission", [
                            'start_mission' => $this->system->event->mission_ids['medium_mission_id'],
                            'mission_type' => 'event'
                        ]),
                        action_message: "Follow Signs of battle",
                    );
                }
                foreach ($this->system->event->mission_coords['hard'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: null,
                        name: "Hard",
                        map_id: 1,
                        x: $event_mission['x'],
                        y: $event_mission['y'],
                        image: "/images/events/lanternviolet.png",
                        action_url: $this->system->router->getUrl("mission", [
                            'start_mission' => $this->system->event->mission_ids['hard_mission_id'],
                            'mission_type' => 'event'
                        ]),
                        action_message: "Investigate Suspicious Markings",
                    );
                }
                foreach ($this->system->event->mission_coords['nightmare'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: null,
                        name: "Nightmare",
                        map_id: 1,
                        x: $event_mission['x'],
                        y: $event_mission['y'],
                        image: "/images/events/yokai_cropped.png",
                        action_url: $this->system->router->getUrl("mission", [
                            'start_mission' => $this->system->event->mission_ids['nightmare_mission_id'],
                            'mission_type' => 'event'
                        ]),
                        action_message: "Stop the Ritual",
                    );
                }
            }
        }

        return $objectives;
    }

    /**
     * @return RegionObjective[]
     */
    #[Trace]
    public function fetchRegionObjectives(): array
    {
        $objectives = [];

        // Get Region Objectives
        $region_result = $this->system->db->query("SELECT `region_locations`.`id` as `region_location_id`, `region_locations`.`region_id`, `region_locations`.`name`, `health`, `max_health`, `type`, `x`, `y`, `resource_name`, `defense`, `village`, `villages`.`name` as `village_name`, `villages`.`village_id` FROM `region_locations`
            INNER JOIN `regions` ON `regions`.`region_id` = `region_locations`.`region_id`
            INNER JOIN `villages` ON `regions`.`village` = `villages`.`village_id`
            LEFT JOIN `resources` on `region_locations`.`resource_id` = `resources`.`resource_id`");
        $region_objectives = $this->system->db->fetch_all($region_result);
        foreach ($region_objectives as $obj) {
            $distance = $this->user->location->distanceDifference(
                new TravelCoords(
                    x: $obj['x'],
                    y: $obj['y'],
                    map_id: 1,
                )
            );
            if ($distance <= self::DISPLAY_RADIUS) {
                switch ($obj['type']) {
                    case "castle":
                        $image = "/images/map/icons/castle.png";
                        $objectives[] = new RegionObjective(
                            id: $obj['region_location_id'],
                            name: $obj['name'],
                            map_id: 1,
                            x: $obj['x'],
                            y: $obj['y'],
                            objective_health: $obj['health'],
                            objective_max_health: $obj['max_health'],
                            defense: $obj['defense'],
                            objective_type: $obj['type'],
                            image: $image,
                            village_id: $obj['village_id'],
                        );
                        break;
                    case "tower":
                        break;
                        $image = "/images/map/icons/tower.png";
                        $objectives[] = new RegionObjective(
                            id: $obj['region_location_id'],
                            name: $obj['name'],
                            map_id: 1,
                            x: $obj['x'],
                            y: $obj['y'],
                            objective_health: $obj['health'],
                            objective_max_health: $obj['max_health'],
                            defense: $obj['defense'],
                            objective_type: $obj['type'],
                            image: $image,
                            village_id: $obj['village_id'],
                        );
                    case "village":
                        if ($distance <= $this->user->scout_range || $obj['village_name'] == $this->user->village->name) {
                            $image = "/images/map/icons/village.png";
                            if (isset($obj['resource_name'])) {
                                switch ($obj['resource_name']) {
                                    case null:
                                        break;
                                    case "food":
                                        $image = "/images/map/icons/food.png";
                                        break;
                                    case "materials":
                                        $image = "/images/map/icons/materials.png";
                                        break;
                                    case "wealth":
                                        $image = "/images/map/icons/wealth.png";
                                        break;
                                }
                            }
                            $objectives[] = new RegionObjective(
                                id: $obj['region_location_id'],
                                name: $obj['name'],
                                map_id: 1,
                                x: $obj['x'],
                                y: $obj['y'],
                                objective_health: $obj['health'],
                                objective_max_health: $obj['max_health'],
                                defense: $obj['defense'],
                                objective_type: $obj['type'],
                                image: $image,
                                village_id: $obj['village_id'],
                            );
                        }
                        break;
                }
            }
        }

        return $objectives;
    }

    function getPlayerBattleUrl(): ?string {
        $link = null;
        if ($this->user->battle_id > 0) {
            $result = $this->system->db->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='{$this->user->battle_id}' LIMIT 1"
            );
            if (!$this->system->db->last_num_rows == 0) {
                $result = $this->system->db->fetch($result);
                switch ($result['battle_type']) {
                    case Battle::TYPE_AI_ARENA:
                        $link = $this->system->router->getUrl('arena');
                        break;
                    case Battle::TYPE_AI_MISSION:
                        $link = $this->system->router->getUrl('mission');
                        break;
                    case Battle::TYPE_AI_RANKUP:
                        $link = $this->system->router->getUrl('rankup');
                        break;
                    case Battle::TYPE_SPAR:
                        $link = $this->system->router->getUrl('spar');
                        break;
                    case Battle::TYPE_FIGHT:
                        $link = $this->system->router->getUrl('battle');
                        break;
                }
            }
        }
        return $link;
    }

    function beginOperation($operation_type): bool {
        $target = $this->system->db->query("SELECT `id` FROM `region_locations`
            WHERE `x` = {$this->user->location->x}
            AND `y` = {$this->user->location->y}
            AND `map_id` = {$this->user->location->map_id}
            LIMIT 1
        ");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("No operation target found!");
        }
        $target = $this->system->db->fetch($target);
        $this->warManager->beginOperation($operation_type, $target['id']);
        $this->user->updateData();
        return true;
    }

    function cancelOperation(): bool {
        $this->warManager->cancelOperation();
        $this->user->updateData();
        return true;
    }
}