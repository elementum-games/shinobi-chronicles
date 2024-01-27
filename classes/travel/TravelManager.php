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
    const INACTIVE_SECONDS = 120;

    private System $system;
    private User $user;

    public WarManager $warManager;

    public string $travel_message = '';

    public array $map_data;

    const DISPLAY_RADIUS = 12;
    const GRID_POSITIVE_X = 15;
    const GRID_NEGATIVE_X = 14;
    const GRID_POSITIVE_Y = 9;
    const GRID_NEGATIVE_Y = 8;

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

        // check if the user is in an active war action
        if ($this->user->war_action_id) {
            $war_action = $this->warManager->getWarActionById($this->user->war_action_id);
            $message = "You are currently " . WarAction::WAR_ACTION_TYPE_DESCRIPTOR[$war_action->type] . " and cannot travel!";
            throw new InvalidMovementException($message);
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
        try {
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
        } catch (InvalidMovementException $e) {
            $this->setTravelMessage($e->getMessage());
            return false;
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
     * @throws DatabaseDeadlockException
     */
    public function enterPortal($portal_id): bool {
        $ignore_travel_restrictions = $this->user->isHeadAdmin();

        // portal data
        $portal_data = Travel::getPortalData($this->system, $portal_id);

        try {
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
        } catch (InvalidMovementException $e) {
            $this->setTravelMessage($e->getMessage());
            return false;
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
     * @throws DatabaseDeadlockException
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    public function fetchNearbyPlayers(): array {
        $sql = "SELECT `users`.`user_id`,
                    `users`.`user_name`,
                    `users`.`village`,
                    `users`.`rank`,
                    `users`.`stealth`,
                    `users`.`war_action_id`,
                    `users`.`level`,
                    `users`.`attack_id`,
                    `users`.`battle_id`,
                    `ranks`.`name` as `rank_name`,
                    `users`.`location`,
                    `users`.`pvp_immunity_ms`,
                    `villages`.`village_id`,
                    `users`.`special_mission`,
                    COUNT(`loot`.`id`) as `loot_count`
                FROM `users`
                INNER JOIN `ranks` ON `users`.`rank`=`ranks`.`rank_id`
                INNER JOIN `villages` ON `users`.`village` = `villages`.`name`
                LEFT JOIN `loot` ON (`loot`.`user_id`=`users`.`user_id` AND `loot`.`claimed_village_id` IS NULL AND `loot`.`battle_id` IS NULL)
                WHERE `users`.`last_active` > UNIX_TIMESTAMP() - " . TravelManager::INACTIVE_SECONDS . "
                GROUP BY `users`.`user_id`, `users`.`exp`, `users`.`user_name`, `villages`.`village_id`
                ORDER BY `users`.`exp` DESC, `users`.`user_name` DESC;";
        $result = $this->system->db->query($sql);
        $users = $this->system->db->fetch_all($result, 'user_id');
        $return_arr = [];

        $user_ids_by_coords = [];

        foreach ($users as $user) {
            // Build map of coords => user IDs for efficient checks against users on same square
            if(!isset($user_ids_by_coords[$user['location']])) {
                $user_ids_by_coords[$user['location']] = [];
            }
            $user_ids_by_coords[$user['location']][] = $user['user_id'];

            // give bonus stealth if in special mission
            if ($user['special_mission'] > 0) {
                $user['stealth'] += User::SPECIAL_MISSION_STEALTH_BONUS;
            }
            // check if the user is nearby (including stealth)
            $scout_range = max(0, $this->user->scout_range - $user['stealth']);
            $user_location = TravelCoords::fromDbString($user['location']);
            if ($user_location->map_id !== $this->user->location->map_id ||
                $user_location->distanceDifference($this->user->location) > $scout_range) {
                continue;
            }

            // if there were alliance we can do additional checks here - the future is now, old man
            if ($this->warManager->villagesAreAllies($this->user->village->village_id, $user['village_id'])) {
                $user_alignment = 'Ally';
            }
            else {
                $alignment = $this->user->village->relations[$user['village_id']]->relation_type;
                switch ($alignment) {
                    case VillageRelation::RELATION_NEUTRAL:
                        $user_alignment = 'Neutral';
                        break;
                    case VillageRelation::RELATION_ALLIANCE:
                        $user_alignment = 'Ally';
                        break;
                    case VillageRelation::RELATION_WAR:
                        $user_alignment = 'Enemy';
                        break;
                }
            }

            // loot count
            $loot_count = 0;
            $loot_result = $this->system->db->query("SELECT COUNT(*) as `count` FROM `loot` WHERE `user_id` = {$user['user_id']} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL LIMIT 1");
            if ($this->system->db->last_num_rows > 0) {
                $loot_result = $this->system->db->fetch($loot_result);
                $loot_count = $loot_result['count'];
            }

            // only display attack links if the same rank OR carrying loot OR in war action
            $can_attack = false;
            if ($this->user->location->equals(TravelCoords::fromDbString($user['location']))
                && $user['user_id'] != $this->user->user_id
                && $user_alignment !== 'Ally') {
                if ((int)$user['rank'] === $this->user->rank_num) {
                    $can_attack = true;
                } else if ($user['war_action_id'] > 0) {
                    $can_attack = true;
                } else if ($loot_count > 0) {
                    $can_attack = true;
                }
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
            if ($user['pvp_immunity_ms'] > System::currentTimeMs()) {
                $invulnerable = true;
            }

            // add to return
            $return_arr[] = new NearbyPlayerDto(
                user_id: $user['user_id'],
                user_name: $user['user_name'],
                location: $user_location,
                rank_name: $user['rank_name'],
                rank_num: $user['rank'],
                village_icon: TravelManager::VILLAGE_ICONS[$user['village']],
                alignment: $user_alignment,
                attack: $can_attack,
                attack_id: $user['attack_id'],
                level: $user['level'],
                battle_id: $user['battle_id'],
                direction: $user_direction,
                village_id: $user['village_id'],
                invulnerable: $invulnerable,
                distance: $distance,
                loot_count: $user['loot_count'],
                is_protected: false,
            );
        }

        // Check for protection
        foreach($return_arr as $nearby_player) {
            $players_on_same_tile = array_map(function($user_id) use($users) {
                $user = $users[$user_id];
                // TODO: Use same array as the first pass
                return new NearbyPlayerDto(
                    user_id: $user['user_id'],
                    user_name: $user['user_name'],
                    location: TravelCoords::fromDbString($user['location']),
                    rank_name: $user['rank_name'],
                    rank_num: $user['rank'],
                    village_icon: TravelManager::VILLAGE_ICONS[$user['village']],
                    alignment: '',
                    attack: false,
                    attack_id: $user['attack_id'],
                    level: $user['level'],
                    battle_id: $user['battle_id'],
                    direction: '',
                    village_id: $user['village_id'],
                    invulnerable: $user['pvp_immunity_ms'] > System::currentTimeMs(),
                    distance: 0,
                    loot_count: $user['loot_count'],
                    is_protected: false,
                );
            }, $user_ids_by_coords[$nearby_player->location->toString()]);

            if ($this->isProtectedByAlly($nearby_player, players_on_same_tile: $players_on_same_tile) && $this->user->rank_num >= 4) {
                $nearby_player->attack = false;
                $nearby_player->is_protected = true;
            }
        }

        // Add more users for display
        if ($this->system->isDevEnvironment()) {
            $placeholder_coords = new TravelCoords(19, 14, 1);

            for ($i = 0; $i < 2; $i++) {
                $return_arr[] = new NearbyPlayerDto(
                    user_id: $i . mt_rand(10000, 20000),
                    user_name: 'Konohamaru',
                    location: $placeholder_coords,
                    rank_name: 'Akademi-sei',
                    rank_num: 3,
                    village_icon: TravelManager::VILLAGE_ICONS['Mist'],
                    alignment: 'Enemy',
                    attack: $this->user->location->equals($placeholder_coords),
                    attack_id: 'abc' . $i . mt_rand(10000, 20000),
                    level: 30,
                    battle_id: 0,
                    direction: $this->user->location->directionToTarget($placeholder_coords),
                    village_id: 3,
                    invulnerable: false,
                    distance: $this->user->location->distanceDifference($placeholder_coords),
                    loot_count: 6,
                    is_protected: false,
                );
            }
            for ($i = 0; $i < 2; $i++) {
                $return_arr[] = new NearbyPlayerDto(
                    user_id: $i . mt_rand(10000, 20000),
                    user_name: 'Konohamaru',
                    location: $placeholder_coords,
                    rank_name: 'Akademi-sei',
                    rank_num: 3,
                    village_icon: TravelManager::VILLAGE_ICONS['Stone'],
                    alignment: 'Ally',
                    attack: $this->user->location->equals($placeholder_coords),
                    attack_id: 'abc' . $i . mt_rand(10000, 20000),
                    level: 30,
                    battle_id: 0,
                    direction: $this->user->location->directionToTarget($placeholder_coords),
                    village_id: 3,
                    invulnerable: false,
                    distance: $this->user->location->distanceDifference($placeholder_coords),
                    loot_count: 11,
                    is_protected: false,
                );
            }
            for ($i = 0; $i < 2; $i++) {
                $return_arr[] = new NearbyPlayerDto(
                    user_id: $i . mt_rand(10000, 20000),
                    user_name: 'Konohamaru',
                    location: $placeholder_coords,
                    rank_name: 'Akademi-sei',
                    rank_num: 3,
                    village_icon: TravelManager::VILLAGE_ICONS['Sand'],
                    alignment: 'Neutral',
                    attack: $this->user->location->equals($placeholder_coords),
                    attack_id: 'abc' . $i . mt_rand(10000, 20000),
                    level: 30,
                    battle_id: 0,
                    direction: $this->user->location->directionToTarget($placeholder_coords),
                    village_id: 3,
                    invulnerable: false,
                    distance: $this->user->location->distanceDifference($placeholder_coords),
                    loot_count: 11,
                    is_protected: true,
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
     * @throws DatabaseDeadlockException
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
            //if ($loc['name'] == "Ayakashi's Abyss" && $this->system->event == null) {
            if ($loc['name'] == "Ayakashi's Abyss") {
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
                $new_location->location_type = "key_location";
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
     * @throws DatabaseDeadlockException
     */
    #[Trace]
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
     * @throws DatabaseDeadlockException
     * @throws DatabaseDeadlockException
     */
    #[Trace]
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

        try {
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

            // bypass rank restruction if target taking war action or carrying loot
            if ($user->rank_num !== $this->user->rank_num) {
                if ($user->war_action_id == 0) {
                    $loot_count = 0;
                    $loot_result = $this->system->db->query("SELECT COUNT(*) as `count` FROM `loot` WHERE `user_id` = {$user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL LIMIT 1");
                    if ($this->system->db->last_num_rows > 0) {
                        $loot_result = $this->system->db->fetch($loot_result);
                        $loot_count = $loot_result['count'];
                    }
                    if ($loot_count == 0) {
                        throw new RuntimeException("You can only attack people of the same rank!");
                    }
                }
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
            /*
            if ($this->user->pvp_immunity_ms > System::currentTimeMs()) {
                throw new RuntimeException("You were defeated within the last " . User::PVP_IMMUNITY_SECONDS . "s, please wait " .
                    ceil(($this->user->pvp_immunity_ms - System::currentTimeMs()) / 1000) . " more seconds.");
            }*/
            if ($this->user->last_death_ms > System::currentTimeMs() - (User::PVP_IMMUNITY_SECONDS * 1000)) {
                throw new RuntimeException("You died within the last " . User::PVP_IMMUNITY_SECONDS . "s, please wait " .
                    ceil((($this->user->last_death_ms + (User::PVP_IMMUNITY_SECONDS * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
            }
            if ($user->pvp_immunity_ms > System::currentTimeMs()) {
                throw new RuntimeException("Target has died recently and immune to being attacked.");
                }
                /*
                if ($user->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
                    throw new RuntimeException("Target has died within the last minute, please wait " .
                        ceil((($user->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
                }*/
            if ($this->user->war_action_id > 0) {
                $war_action = $this->warManager->getWarActionById($this->user->war_action_id);
                $message = "You cannot attack while " . WarAction::WAR_ACTION_TYPE_DESCRIPTOR[$war_action->type] . "!";
                throw new RuntimeException($message);
            }
            if ($this->dbFetchIsProtectedByAlly($user) && $this->user->rank_num >= 4) {
                throw new RuntimeException("Target is protected by a higher rank ally! Attack them first.");
            }
        } catch (RuntimeException $e) {
            $this->setTravelMessage($e->getMessage());
            return false;
        }

        $battle_background = TravelManager::getLocationBattleBackgroundLink($this->system, $this->user->location);
        if (empty($battle_background)) {
            $battle_background = $this->user->region->battle_background_link;
        }
        if ($this->system->USE_NEW_BATTLES) {
            BattleV2::start($this->system, $this->user, $user, Battle::TYPE_FIGHT, battle_background_link: $battle_background);
        } else {
            Battle::start($this->system, $this->user, $user, Battle::TYPE_FIGHT, battle_background_link: $battle_background);
        }
        return true;
    }

    /**
     * @param NearbyPlayerDto $target_player
     * @param NearbyPlayerDto[]    $players_on_same_tile
     * @return bool
     */
    #[Trace]
    public function isProtectedByAlly(NearbyPlayerDto $target_player, array $players_on_same_tile): bool {
        if ($target_player->rank_num < System::SC_MAX_RANK) {
            foreach($players_on_same_tile as $nearby_player) {
                if($nearby_player->rank_num <= $target_player->rank_num) continue;
                if($nearby_player->battle_id != 0) continue;
                if($nearby_player->invulnerable) continue;

                if(!$this->warManager->villagesAreAllies($target_player->village_id, $nearby_player->village_id)) {
                    continue;
                }

                return true;
            }
        }
        return false;
    }

    /**
     * Do not use this method for batch war actions, use isProtectedByAlly instead
     *
     * @param User $user
     * @return bool
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    public function dbFetchIsProtectedByAlly(User $user): bool {
         if ($user->rank_num < System::SC_MAX_RANK) {
            $time = System::currentTimeMs();
            $player_result = $this->system->db->query("SELECT `users`.`user_id`, `villages`.`village_id` FROM `users`
            INNER JOIN `villages` ON `users`.`village` = `villages`.`name`
            WHERE `users`.`location` = '{$user->location->toString()}'
            AND `users`.`rank` > {$user->rank_num}
            AND `users`.`battle_id` = 0
            AND `users`.`last_active` > UNIX_TIMESTAMP() - " . TravelManager::INACTIVE_SECONDS . "
            AND `users`.`pvp_immunity_ms` < {$time}");

            $player_result = $this->system->db->fetch_all($player_result);
            foreach ($player_result as $player) {
                if ($user->village->isAlly($player['village_id'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array village names, keyed by travel coords str
     * @throws DatabaseDeadlockException
     */
    public static function fetchVillageLocationsByCoordsStr(System $system): array {
        $village_locations = [];

        $result = $system->db->query("SELECT `villages`.`name`, `x`, `y`, `map_id` FROM `villages`
            INNER JOIN `maps_locations` on `villages`.`map_location_id` = `maps_locations`.`location_id`
        ");
        while($row = $system->db->fetch($result)) {
            $village_coords = new TravelCoords($row['x'], $row['y'], $row['map_id']);
            $village_locations[$village_coords->toString()] = $row['name'];
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
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    public function getRegions(User $player): array {
        // generate vertices for player view area
        $player_area = [
            [$player->location->x - self::GRID_NEGATIVE_X, $player->location->y - self::GRID_NEGATIVE_Y], // top left bound
            [$player->location->x + self::GRID_POSITIVE_X, $player->location->y - self::GRID_NEGATIVE_Y], // top right bound
            [$player->location->x + self::GRID_POSITIVE_X, $player->location->y + self::GRID_POSITIVE_Y], // bottom right bound
            [$player->location->x - self::GRID_NEGATIVE_X, $player->location->y + self::GRID_POSITIVE_Y], // bottom left bound
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
                $regions[] = Region::fromDb($region, $player->location->x - self::GRID_NEGATIVE_X, $player->location->y - self::GRID_NEGATIVE_Y, $player->location->x + self::GRID_POSITIVE_X, $player->location->y + self::GRID_POSITIVE_Y);
            }
        }

        return $regions;
    }

    /**
     * @param $regions
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
     * @throws DatabaseDeadlockException
     */
    public function getColosseumCoords(): TravelCoords {
        $result = $this->system->db->query("SELECT * FROM `maps_locations` WHERE `name` = 'Underground Colosseum'");
        $location_result = $this->system->db->fetch($result);
        return new TravelCoords($location_result['x'], $location_result['y'], 1);
    }

    /**
     * @return MapNPC[]
     * @throws DatabaseDeadlockException
     * @throws DatabaseDeadlockException
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    public function fetchNearbyNPCs(): array {
        $return_arr = [];

        // exit if war disabled
        if (!$this->system->war_enabled) {
            return $return_arr;
        }

        $time = time();
        $result = $this->system->db->query("SELECT * FROM `patrols` where `start_time` < {$time}");
        $result = $this->system->db->fetch_all($result);
        $region_locations = $this->system->db->query("SELECT * FROM `region_locations`");
        $region_locations = $this->system->db->fetch_all($region_locations);
        foreach ($result as $row) {
            $patrol = new MapNPC($row, "patrol");
            $patrol->setLocation($this->system, $region_locations);
            $patrol->setAlignment($this->user);
            $distance = $this->user->location->distanceDifference(new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id));
            if ($distance == 0) {
                $this->warManager->tryBeginPatrolBattle($patrol);
            }
            if ($distance <= $this->user->scout_range) {
                $return_arr[] = $patrol;
            }
        }
        $result = $this->system->db->query("SELECT * FROM `caravans` where `start_time` < {$time}");
        $result = $this->system->db->fetch_all($result);
        foreach ($result as $row) {
            // if travel time is set then only display if active
            if (!empty($row['travel_time'])) {
                if ($row['travel_time'] + ($row['start_time'] * 1000) + MapNPC::DESTINATION_BUFFER_MS > (time() * 1000)) {
                    $caravan = new MapNPC($row, "caravan");
                    $caravan->setLocation($this->system, $region_locations);
                    $caravan->setAlignment($this->user);
                    if ($this->user->location->distanceDifference(new TravelCoords($caravan->current_x, $caravan->current_y, $caravan->map_id)) <= $this->user->scout_range) {
                        $return_arr[] = $caravan;
                    }
                }
            } else {
                $caravan = new MapNPC($row, "caravan");
                $caravan->setLocation($this->system, $region_locations);
                $caravan->setAlignment($this->user);
                if ($this->user->location->distanceDifference(new TravelCoords($caravan->current_x, $caravan->current_y, $caravan->map_id)) <= $this->user->scout_range) {
                    $return_arr[] = $caravan;
                }
            }
        }

        return $return_arr;
    }

    /**
     * @return MapObjective[]
     * @throws DatabaseDeadlockException
     * @throws DatabaseDeadlockException
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
                    id: MapObjective::MISSION_OBJECTIVE_ID,
                    name: $this->system->db->fetch($mission_result)['name'],
                    map_id: $mission_location->map_id,
                    x: $mission_location->x,
                    y: $mission_location->y,
                    image: "/images/map/icons/reticle.png",
                    objective_type: "target",
                );
            }
            if ($this->user->mission_stage['action_type'] == 'search') {
                $mission_result = $this->system->db->query("SELECT `name` FROM `missions` WHERE `mission_id` = '{$this->user->mission_id}' LIMIT 1");
                $mission_location = !empty($this->user->mission_stage['last_location']) ? TravelCoords::fromDbString($this->user->mission_stage['last_location']) : TravelCoords::fromDbString($this->user->mission_stage['action_data']);
                $objectives[] = new MapObjective(
                    id: MapObjective::MISSION_OBJECTIVE_ID,
                    name: $this->system->db->fetch($mission_result)['name'],
                    map_id: $mission_location->map_id,
                    x: $mission_location->x,
                    y: $mission_location->y,
                    image: "/images/v2/icons/magnifying-glass.png",
                );
            }
        }

        // TEMP Add Events - We have to hard code the mission IDs is System for now
        $event_objective_id = MapObjective::EVENT_ID_START;
        if ($this->system->event != null) {
            if ($this->system->event instanceof LanternEvent) {
                foreach ($this->system->event->mission_coords['gold'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: $event_objective_id,
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
                    $event_objective_id++;
                }
                foreach ($this->system->event->mission_coords['special'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: $event_objective_id,
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
                    $event_objective_id++;
                }
                foreach ($this->system->event->mission_coords['easy'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: $event_objective_id,
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
                    $event_objective_id++;
                }
                foreach ($this->system->event->mission_coords['medium'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: $event_objective_id,
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
                    $event_objective_id++;
                }
                foreach ($this->system->event->mission_coords['hard'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: $event_objective_id,
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
                    $event_objective_id++;
                }
                foreach ($this->system->event->mission_coords['nightmare'] as $event_mission) {
                    $objectives[] = new MapObjective(
                        id: $event_objective_id,
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
                    $event_objective_id++;
                }
            }
        }

        return $objectives;
    }

    /**
     * @return RegionObjective[]
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    public function fetchRegionObjectives(): array
    {
        $objectives = [];

        // Get Region Objectives
        $region_result = $this->system->db->query("SELECT * FROM `region_locations`");
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
                            objective_health: $this->system->war_enabled ? $obj['health'] : WarManager::BASE_CASTLE_HEALTH,
                            objective_max_health: WarManager::BASE_CASTLE_HEALTH,
                            defense: $obj['defense'],
                            objective_type: $obj['type'],
                            image: $image,
                            village_id: $obj['occupying_village_id'],
                            resource_id: $obj['resource_id'],
                            resource_count: $obj['resource_count'],
                            stability: $obj['stability'],
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
                            village_id: $obj['occupying_village_id'],
                            resource_id: $obj['resource_id'],
                            resource_count: $obj['resource_count'],
                            stability: $obj['stability'],
                        );
                    case "village":
                        if ($distance <= $this->user->scout_range) {
                            $image = "/images/map/icons/village.png";
                            $objectives[] = new RegionObjective(
                                id: $obj['region_location_id'],
                                name: $obj['name'],
                                map_id: 1,
                                x: $obj['x'],
                                y: $obj['y'],
                                objective_health: $this->system->war_enabled ? $obj['health'] : WarManager::BASE_TOWN_HEALTH,
                                objective_max_health: WarManager::BASE_TOWN_HEALTH,
                                defense: $obj['defense'],
                                objective_type: $obj['type'],
                                image: $image,
                                village_id: $obj['occupying_village_id'],
                                resource_id: $obj['resource_id'],
                                resource_count: $obj['resource_count'],
                                stability: $obj['stability'],
                            );
                        }
                        break;
                }
            }
        }

        return $objectives;
    }

    /**
     * @return string
     * @throws DatabaseDeadlockException
     */
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
                    case Battle::TYPE_AI_WAR:
                        $link = $this->system->router->getUrl('war');
                        break;
                }
            }
        }
        return $link;
    }

    /**
     * @return bool
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    function beginWarAction($war_action_type): bool {
        $message = '';
        /*
        if ($this->user->pvp_immunity_ms > System::currentTimeMs()) {
            $message = "You were defeated within the last " . User::PVP_IMMUNITY_SECONDS . "s, please wait " .
                ceil(($this->user->pvp_immunity_ms - System::currentTimeMs()) / 1000) . " more seconds.";
            $this->setTravelMessage($message);
            return false;
        }*/
        if ($this->user->last_death_ms > System::currentTimeMs() - (User::PVP_IMMUNITY_SECONDS * 1000)) {
            $message = "You died within the last " . User::PVP_IMMUNITY_SECONDS . "s, please wait " .
                ceil((($this->user->last_death_ms + (User::PVP_IMMUNITY_SECONDS * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.";
            $this->setTravelMessage($message);
            return false;
        }
        if ($war_action_type == WarAction::WAR_ACTION_LOOT) {
            $time = time();
            $caravans = $this->system->db->query("SELECT * FROM `caravans` where `start_time` < {$time} && `village_id` != {$this->user->village->village_id}");
            $caravans = $this->system->db->fetch_all($caravans);
            $region_locations = $this->system->db->query("SELECT * FROM `region_locations`");
            $region_locations = $this->system->db->fetch_all($region_locations);
            foreach ($caravans as $caravan_data) {
                $caravan = new MapNPC($caravan_data, "caravan");
                $caravan->setLocation($this->system, $region_locations);
                $caravan->setAlignment($this->user);
                if ($this->user->location->distanceDifference(new TravelCoords($caravan->current_x, $caravan->current_y, $caravan->map_id)) == 0 && $caravan->alignment != "Ally") {
                    $this->warManager->beginWarAction($war_action_type, $caravan->id, $caravan);
                    $message = System::unSlug(WarAction::WAR_ACTION_TYPE_DESCRIPTOR[$war_action_type]) . "!";
                    $this->user->updateData();
                    $this->setTravelMessage($message);
                    return true;
                }
            }
            return false;
        } else {
            $target = $this->system->db->query("SELECT `region_location_id` FROM `region_locations`
                WHERE `x` = {$this->user->location->x}
                AND `y` = {$this->user->location->y}
                AND `map_id` = {$this->user->location->map_id}
                LIMIT 1
            ");
            if ($this->system->db->last_num_rows == 0) {
                throw new RuntimeException("No valid target found!");
            }
            $target = $this->system->db->fetch($target);
            $this->warManager->beginWarAction($war_action_type, $target['region_location_id']);
            $message = System::unSlug(WarAction::WAR_ACTION_TYPE_DESCRIPTOR[$war_action_type]) . "!";
            $this->user->updateData();
            $this->setTravelMessage($message);
            return true;
        }
    }

    /**
     * @return bool
     */
    #[Trace]
    function cancelWarAction(): bool {
        $message = '';
        $this->warManager->cancelWarAction();
        $this->user->updateData();
        $this->setTravelMessage($message);
        return true;
    }

    #[Trace]
    function checkWarAction() {
        $message = '';
        if ($this->system->war_enabled && $this->user->war_action_id > 0) {
            try {
                $message = $this->warManager->processWarAction($this->user->war_action_id);
            } catch (RuntimeException $e) {
                if ($this->system->isDevEnvironment()) {
                    $message .= ": " . $e->getMessage();
                }
                $this->user->war_action_id = 0;
            }
        }
        $this->user->updateData();
        $this->setTravelMessage($message);
    }

    /**
     * @return bool
     */
    #[Trace]
    function claimLoot(): bool
    {
        $message = '';
        $message = $this->warManager->processLoot();
        $this->setTravelMessage($message);
        return true;
    }

    #[Trace]
    private function setTravelMessage($message) {
        if (!empty($message)) {
            if (!empty($this->travel_message)) {
                $this->travel_message .= "\n" . $message;
            }
            else {
                $this->travel_message = $message;
            }
        }
    }

    /**
     * @return int
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    function getPlayerLootCount(): int {
        $loot_count = 0;
        $loot_result = $this->system->db->query("SELECT COUNT(*) as `count` FROM `loot` WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL LIMIT 1");
        if ($this->system->db->last_num_rows > 0) {
            $loot_result = $this->system->db->fetch($loot_result);
            $loot_count = $loot_result['count'];
        }
        return $loot_count;
    }

    /**
     * @return string
     * @param System $system
     * @param TravelCoords $location
     */
    public static function getLocationBattleBackgroundLink(System $system, TravelCoords $location): string {
        $result = $system->db->query(
            "SELECT `battle_background_link` FROM `maps_locations`
            WHERE `x`='{$location->x}' AND `y`='{$location->y}' AND `map_id`='{$location->map_id}' LIMIT 1"
        );
        $result = $system->db->fetch($result);
        if (isset($result['battle_background_link'])) {
            return $result['battle_background_link'];
        } else {
            return '';
        }
    }
}