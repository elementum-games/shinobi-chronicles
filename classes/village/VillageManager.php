<?php

require_once __DIR__ . "/VillageSeatDto.php";
require_once __DIR__ . "/VillageProposalDto.php";
require_once __DIR__ . "/VillageStrategicInfoDto.php";
require_once __DIR__ . "/../notification/NotificationManager.php";

class VillageManager {
    const KAGE_NAMES = [
        1 => 'Tsuchikage',
        2 => 'Raikage',
        3 => 'Hokage',
        4 => 'Kazekage',
        5 => 'Mizukage',
    ];
    const VILLAGE_NAMES = [
        1 => 'Stone',
        2 => 'Cloud',
        3 => 'Leaf',
        4 => 'Sand',
        5 => 'Mist',
    ];

    const RESOURCE_LOG_PRODUCTION = 1;
    const RESOURCE_LOG_COLLECTION = 2;
    const RESOURCE_LOG_EXPENDITURE = 3;

    const MIN_KAGE_CLAIM_TIER = 5;
    const MIN_KAGE_CHALLENGE_TIER = 6;
    const MIN_ELDER_CLAIM_TIER = 4;
    const MIN_ELDER_CHALLENGE_TIER = 5;

    const POLICY_NONE = 0;
    const POLICY_GROWTH = 1;
    const POLICY_ESPIONAGE = 2;
    const POLICY_DEFENSE = 3;
    const POLICY_WAR = 4;
    const POLICY_PROSPERITY = 5;

    const POLICY_NAMES = [
        self::POLICY_NONE => "Inactive Policy",
        self::POLICY_GROWTH => "From the Ashes",
        self::POLICY_ESPIONAGE => "Eye of the Storm",
        self::POLICY_DEFENSE => "Fortress of Solitude",
        self::POLICY_WAR => "Forged in Flames",
        self::POLICY_PROSPERITY => "The Gilded Hand",
    ];

    const POLICY_BONUS_INFILTRATE_SPEED = "INFILTRATE_SPEED";
    const POLICY_BONUS_INFILTRATE_LOOT = "INFILTRATE_LOOT";
    const POLICY_BONUS_REINFORCE_SPEED = "REINFORCE_SPEED";
    const POLICY_BONUS_REINFORCE_DEFENSE = "REINFORCE_DEFENSE";
    const POLICY_BONUS_RAID_SPEED = "RAID_SPEED";
    const POLICY_BONUS_RAID_DEFENSE = "RAID_DEFENSE";
    const POLICY_BONUS_CARAVAN_SPEED = "CARAVAN_SPEED";
    const POLICY_BONUS_PATROL_RESPAWN = "PATROL_RESPAWN";
    const POLICY_BONUS_PATROL_TIER = "PATROL_TIER";
    const POLICY_BONUS_TRAINING_SPEED = "TRAINING_SPEED";
    const POLICY_BONUS_FREE_TRANSFER = "FREE_TRANSFER";
    const POLICY_BONUS_HOME_PRODUCTION_BOOST = "HOME_PRODUCTION_BOOST";
    const POLICY_BONUS_SCOUTING = "SCOUTING";
    const POLICY_BONUS_STEALTH = "STEALTH";
    const POLICY_BONUS_LOOT_CAPACITY = "LOOT_CAPACITY";
    const POLICY_BONUS_PVP_VILLAGE_POINT = "PVP_VILLAGE_POINT";
    const POLICY_RESTRICTION_WAR_ENABLED = "WAR_ENABLED";
    const POLICY_RESTRICTION_ALLIANCE_ENABLED = "ALLIANCE_ENABLED";
    const POLICY_COST_MATERIALS = "MATERIALS_COST";
    const POLICY_COST_FOOD = "FOOD_COST";
    const POLICY_COST_WEALTH = "WEALTH_COST";

    const POLICY_EFFECTS = [
        POLICY_NONE => [
            self::POLICY_BONUS_INFILTRATE_SPEED => 0,
            self::POLICY_BONUS_INFILTRATE_LOOT => 0,
            self::POLICY_BONUS_REINFORCE_SPEED => 0,
            self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
            self::POLICY_BONUS_RAID_SPEED => 0,
            self::POLICY_BONUS_RAID_DEFENSE => 0,
            self::POLICY_BONUS_CARAVAN_SPEED => 0,
            self::POLICY_BONUS_PATROL_RESPAWN => 0,
            self::POLICY_BONUS_PATROL_TIER => 0,
            self::POLICY_BONUS_TRAINING_SPEED => 0,
            self::POLICY_BONUS_FREE_TRANSFER => false,
            self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
            self::POLICY_BONUS_SCOUTING => 0,
            self::POLICY_BONUS_STEALTH => 0,
            self::POLICY_BONUS_LOOT_CAPACITY => 0,
            self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
            self::POLICY_RESTRICTION_WAR_ENABLED => true,
            self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
            self::POLICY_COST_MATERIALS => 0,
            self::POLICY_COST_FOOD => 0,
            self::POLICY_COST_WEALTH => 0,
        ],
        POLICY_GROWTH => [
            self::POLICY_BONUS_INFILTRATE_SPEED => 0,
            self::POLICY_BONUS_INFILTRATE_LOOT => 0,
            self::POLICY_BONUS_REINFORCE_SPEED => 0,
            self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
            self::POLICY_BONUS_RAID_SPEED => 0,
            self::POLICY_BONUS_RAID_DEFENSE => 0,
            self::POLICY_BONUS_CARAVAN_SPEED => 25,
            self::POLICY_BONUS_PATROL_RESPAWN => 0,
            self::POLICY_BONUS_PATROL_TIER => 0,
            self::POLICY_BONUS_TRAINING_SPEED => 10,
            self::POLICY_BONUS_FREE_TRANSFER => true,
            self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 100,
            self::POLICY_BONUS_SCOUTING => 0,
            self::POLICY_BONUS_STEALTH => 0,
            self::POLICY_BONUS_LOOT_CAPACITY => 0,
            self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
            self::POLICY_RESTRICTION_WAR_ENABLED => false,
            self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
            self::POLICY_COST_MATERIALS => 0,
            self::POLICY_COST_FOOD => 25,
            self::POLICY_COST_WEALTH => 0,
        ],
        POLICY_ESPIONAGE => [
            self::POLICY_BONUS_INFILTRATE_SPEED => 25,
            self::POLICY_BONUS_INFILTRATE_LOOT => 1,
            self::POLICY_BONUS_REINFORCE_SPEED => 0,
            self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
            self::POLICY_BONUS_RAID_SPEED => 0,
            self::POLICY_BONUS_RAID_DEFENSE => 0,
            self::POLICY_BONUS_CARAVAN_SPEED => 0,
            self::POLICY_BONUS_PATROL_RESPAWN => 0,
            self::POLICY_BONUS_PATROL_TIER => 0,
            self::POLICY_BONUS_TRAINING_SPEED => 0,
            self::POLICY_BONUS_FREE_TRANSFER => false,
            self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
            self::POLICY_BONUS_SCOUTING => 0,
            self::POLICY_BONUS_STEALTH => 1,
            self::POLICY_BONUS_LOOT_CAPACITY => 5,
            self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
            self::POLICY_RESTRICTION_WAR_ENABLED => true,
            self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
            self::POLICY_COST_MATERIALS => 0,
            self::POLICY_COST_FOOD => 0,
            self::POLICY_COST_WEALTH => 25,
        ],
        POLICY_DEFENSE => [
            self::POLICY_BONUS_INFILTRATE_SPEED => 0,
            self::POLICY_BONUS_INFILTRATE_LOOT => 0,
            self::POLICY_BONUS_REINFORCE_SPEED => 25,
            self::POLICY_BONUS_REINFORCE_DEFENSE => 1,
            self::POLICY_BONUS_RAID_SPEED => 0,
            self::POLICY_BONUS_RAID_DEFENSE => 0,
            self::POLICY_BONUS_CARAVAN_SPEED => 0,
            self::POLICY_BONUS_PATROL_RESPAWN => 0,
            self::POLICY_BONUS_PATROL_TIER => 1,
            self::POLICY_BONUS_TRAINING_SPEED => 0,
            self::POLICY_BONUS_FREE_TRANSFER => false,
            self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
            self::POLICY_BONUS_SCOUTING => 1,
            self::POLICY_BONUS_STEALTH => 0,
            self::POLICY_BONUS_LOOT_CAPACITY => 0,
            self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
            self::POLICY_RESTRICTION_WAR_ENABLED => true,
            self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
            self::POLICY_COST_MATERIALS => 25,
            self::POLICY_COST_FOOD => 0,
            self::POLICY_COST_WEALTH => 0,
        ],
        POLICY_WAR => [
            self::POLICY_BONUS_INFILTRATE_SPEED => 0,
            self::POLICY_BONUS_INFILTRATE_LOOT => 0,
            self::POLICY_BONUS_REINFORCE_SPEED => 0,
            self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
            self::POLICY_BONUS_RAID_SPEED => 25,
            self::POLICY_BONUS_RAID_DEFENSE => 1,
            self::POLICY_BONUS_CARAVAN_SPEED => 0,
            self::POLICY_BONUS_PATROL_RESPAWN => 25,
            self::POLICY_BONUS_PATROL_TIER => 0,
            self::POLICY_BONUS_TRAINING_SPEED => 0,
            self::POLICY_BONUS_FREE_TRANSFER => false,
            self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
            self::POLICY_BONUS_SCOUTING => 0,
            self::POLICY_BONUS_STEALTH => 0,
            self::POLICY_BONUS_LOOT_CAPACITY => 0,
            self::POLICY_BONUS_PVP_VILLAGE_POINT => 1,
            self::POLICY_RESTRICTION_WAR_ENABLED => true,
            self::POLICY_RESTRICTION_ALLIANCE_ENABLED => false,
            self::POLICY_COST_MATERIALS => 25,
            self::POLICY_COST_FOOD => 0,
            self::POLICY_COST_WEALTH => 0,
        ],
    ];

    // Set these to correct values for release
    const PROPOSAL_VOTE_HOURS = 0; // 72
    const PROPOSAL_ENACT_HOURS = 1; // 24
    const PROPOSAL_COOLDOWN_HOURS = 0; // 24
    const KAGE_PROVISIONAL_DAYS = 7; // 7
    const POLICY_CHANGE_COOLDOWN_DAYS = 0; // 14

    const VOTE_NO = 0;
    const VOTE_YES = 1;
    const VOTE_BOOST_COST = 500;

    public static function getLocation(System $system, string $village_id): ?TravelCoords {
        $result = $system->db->query(
            "SELECT `maps_locations`.`x`, `maps_locations`.`y`, `maps_locations`.`map_id` FROM `villages`
                INNER JOIN `maps_locations` ON `villages`.`map_location_id`=`maps_locations`.`location_id`
                WHERE `villages`.`village_id`='{$village_id}' LIMIT 1"
        );
        if($system->db->last_num_rows != 0) {
            $result = $system->db->fetch($result);
            return new TravelCoords(
                x: (int)$result['x'],
                y: (int)$result['y'],
                map_id: (int)$result['map_id']
            );
        }

        return null;
    }

    /**
     * @return VillageRelation[]
     */
    public static function getRelations(System $system, int $village_id): array {
        $relations = [];
        $relations_result = $system->db->query(
            "SELECT
                `village_relations`.*,
                `v1`.`name` AS `village1_name`,
                `v2`.`name` AS `village2_name`
            FROM
                `village_relations`
            INNER JOIN
                `villages` AS `v1` ON `village_relations`.`village1_id` = `v1`.`village_id`
            INNER JOIN
                `villages` AS `v2` ON `village_relations`.`village2_id` = `v2`.`village_id`
            WHERE
                `village_relations`.`relation_end` IS NULL
                AND (`village_relations`.`village1_id` = {$village_id} OR `village_relations`.`village2_id` = {$village_id});
            ");
        $relations_result = $system->db->fetch_all($relations_result);
        foreach ($relations_result as $relation) {
            $relation_target = 0;
            if ($relation['village1_id'] != $village_id) {
                $relation_target = $relation['village1_id'];
            }
            else if ($relation['village2_id'] != $village_id) {
                $relation_target = $relation['village2_id'];
            }
            $relations[$relation_target] = new VillageRelation($relation);
        }
        return $relations;
    }

    /**
     * @return array
     */
    public static function getVillagePopulation(System $system, string $village): array {
        $villager_counts = [];
        $villager_result = $system->db->query(
            "SELECT
                COUNT(IF(`rank`=1,1,NULL)) as `academy`,
                COUNT(IF(`rank`=2,1,NULL)) as `genin`,
                COUNT(IF(`rank`=3,1,NULL)) as `chuunin`,
                COUNT(IF(`rank`=4,1,NULL)) as `jonin`
                FROM `users` WHERE `village` = '{$village}'"
        );
		$villager_result = $system->db->fetch($villager_result);
        foreach ($villager_result as $key => $value) {
            $villager_counts[] = [
                "rank" => $key,
                "count" => (int)$value,
            ];
        }
        return $villager_counts;
    }

    /**
     * @return array
     */
    public static function getVillagePopulationTotal(System $system, string $village): int
    {
        $villager_result = $system->db->query("SELECT COUNT(*) AS count FROM `users` WHERE `village` = '{$village}'");
        $villager_result = $system->db->fetch($villager_result);
        return $villager_result['count'];
    }

    /**
     * @return array
     */
    public static function getVillagePoints(System $system, string $village_id): int
    {
        $points_result = $system->db->query("SELECT `points` FROM `villages` WHERE `village_id` = {$village_id}");
        $points_result = $system->db->fetch($points_result);
        return $points_result['points'];
    }

    /**
     * @return array
     */
    public static function getVillagePolicy(System $system, string $village_id): int
    {
        $policy_result = $system->db->query("SELECT `policy` FROM `villages` WHERE `village_id` = {$village_id}");
        $policy_result = $system->db->fetch($policy_result);
        return $policy_result['policy'];
    }

    /**
     * @return bool
     */
    public static function claimSeat(System $system, User $player, string $seat_type): string {
        switch ($seat_type) {
            case 'kage':
                // Temp disable
                if (!$system->isDevEnvironment()) {
                    return "Not yet available!";
                }
                // check rank
                if ($player->rank_num < 4) {
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_KAGE_CLAIM_TIER) . " - " . UserReputation::$VillageRep[self::MIN_KAGE_CLAIM_TIER]['min_rep'] . " Reputation";
                }
                // check tier
                if ($player->reputation->rank < self::MIN_KAGE_CLAIM_TIER) {
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_KAGE_CLAIM_TIER) . " - " . UserReputation::$VillageRep[self::MIN_KAGE_CLAIM_TIER]['min_rep'] . " Reputation";
                }
                // check seat available
                $result = $system->db->query("SELECT `leader` FROM `villages` WHERE `village_id` = {$player->village->village_id}");
                $result = $system->db->fetch($result);
                //$result = $system->db->query("SELECT * FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `seat_end` IS NULL LIMIT 1");
                if ($result['leader'] > 0) {
                    return "Seat is occupied!";
                }
                // check if has existing seat
                $seat = $player->village_seat;
                if (!empty($seat->seat_id)) {
                    if ($seat->seat_type == 'elder') {
                        self::resign($system, $player);
                    }
                    else {
                        return "You already have a seat in this village!";
                    }
                }
                // claim
                $time = time();
                $reclaim = false;
                $is_provisional = 1;
                $result = $system->db->query("SELECT `seat_title`, `is_provisional` from `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `user_id` = {$player->user_id} AND `is_provisional` = 0 LIMIT 1");
                $result = $system->db->fetch($result);
                if (!empty($result)) {
                    $seat_title = $result['seat_title'];
                    $is_provisional = $result['is_provisional'];
                    $reclaim = true;
                } else {
                    $seat_title = "Provisional " . self::KAGE_NAMES[$player->village->village_id];
                }
                $system->db->query("INSERT INTO `village_seats`
                    (`user_id`, `village_id`, `seat_type`, `seat_title`, `seat_start`, `is_provisional`)
                    VALUES ({$player->user_id}, {$player->village->village_id}, '{$seat_type}', '{$seat_title}', {$time}, {$is_provisional})
                ");
                $system->db->query("UPDATE `villages` SET `leader` = {$player->user_id} WHERE `village_id` = {$player->village->village_id}");
                if ($reclaim) {
                    return "You have reclaimed the title of {$seat_title}!";
                } else {
                    return "You have claimed the title of {$seat_title}!";
                }
                break;
            case 'elder':
                // check if has existing seat
                $seat = $player->village_seat;
                if (!empty($seat->seat_id)) {
                    return "You already have a seat in this village!";
                }
                // check rank
                if ($player->rank_num < 4) {
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_ELDER_CLAIM_TIER) . " - " . UserReputation::$VillageRep[self::MIN_ELDER_CLAIM_TIER]['min_rep'] . " Reputation";
                }
                // check tier
                if ($player->reputation->rank < self::MIN_ELDER_CLAIM_TIER) {
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_ELDER_CLAIM_TIER) . " - " . UserReputation::$VillageRep[self::MIN_ELDER_CLAIM_TIER]['min_rep'] . " Reputation";
                }
                // check seat available
                $result = $system->db->query("SELECT COUNT(*) as 'elder_count' FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `seat_end` IS NULL LIMIT 1");
                $result = $system->db->fetch($result);
                if ($result['elder_count'] == 3) {
                    return "No seats available to claim!";
                }
                // claim
                $time = time();
                $seat_title = "Elder";
                $system->db->query("INSERT INTO `village_seats`
                    (`user_id`, `village_id`, `seat_type`, `seat_title`, `seat_start`)
                    VALUES ({$player->user_id}, {$player->village->village_id}, '{$seat_type}', '{$seat_title}', {$time})
                ");
                return "You have become an Elder of {$player->village->name}!";
                break;
        }
    }

    /**
     * @return bool
     */
    public static function resign(System $system, User $player): string
    {
        $time = time();
        $player_seat = $player->village_seat;
        // clear active votes
        $system->db->query("DELETE `vote_logs` FROM `vote_logs` INNER JOIN `proposals` on `vote_logs`.`proposal_id` = `proposals`.`proposal_id` WHERE `vote_logs`.`user_id` = {$player->user_id} AND `proposals`.`end_time` IS NULL");
        $result = $system->db->query("UPDATE `village_seats` SET `seat_end` = {$time} WHERE `seat_end` IS NULL AND `user_id` = {$player->user_id}");
        if ($player_seat->seat_type == "kage") {
            $result = $system->db->query("UPDATE `villages` SET `leader` = 0 WHERE `village_id` = {$player->village->village_id}");
        }
        if ($system->db->last_affected_rows > 0) {
            return "You have resigned from your position!";
        } else {
            return "No village seat found!";
        }
    }

    /**
     * @return array
     */
    public static function getVillageSeats(System $system, int $village_id): array {
        $seats = [];
        $seat_result = $system->db->query("SELECT `village_seats`.*, `users`.`user_name`, `users`.`avatar_link` FROM `village_seats`
            INNER JOIN `users` on `village_seats`.`user_id` = `users`.`user_id`
            WHERE `seat_end` IS NULL AND `village_id` = {$village_id} ORDER BY `seat_start` ASC");
		$seat_result = $system->db->fetch_all($seat_result);
        $elder_count = 0;
        $has_kage = false;
        foreach ($seat_result as $seat) {
            switch ($seat['seat_type']) {
                case 'kage':
                    $has_kage = true;
                    $seats[] = new VillageSeatDto(
                        seat_key: 'kage',
                        seat_id: $seat['seat_id'],
                        user_id: $seat['user_id'],
                        village_id: $seat['village_id'],
                        seat_type: $seat['seat_type'],
                        seat_title: $seat['seat_title'],
                        seat_start: $seat['seat_start'],
                        user_name: $seat['user_name'],
                        avatar_link: $seat['avatar_link'],
                        is_provisional: $seat['is_provisional']
                    );
                    break;
                case 'elder':
                    $elder_count++;
                    $seats[] = new VillageSeatDto(
                        seat_key: 'elder_' . $elder_count,
                        seat_id: $seat['seat_id'],
                        user_id: $seat['user_id'],
                        village_id: $seat['village_id'],
                        seat_type: $seat['seat_type'],
                        seat_title: $seat['seat_title'],
                        seat_start: $seat['seat_start'],
                        user_name: $seat['user_name'],
                        avatar_link: $seat['avatar_link'],
                        is_provisional: $seat['is_provisional']
                    );
                    break;
                default:
                    break;
            }
        }
        if (!$has_kage) {
            // add empty kage seat to list
            $seats[] = new VillageSeatDto(
                seat_key: 'kage',
                seat_id: null,
                user_id: null,
                village_id: null,
                seat_type: 'kage',
                seat_title: self::KAGE_NAMES[$village_id],
                seat_start:  null,
                user_name: null,
                avatar_link: null,
                is_provisional: null
            );
        }
        for ($i = 3; $i > $elder_count; $i--) {
            // add empty elder seat to list
            $seats[] = new VillageSeatDto(
                seat_key: 'elder_' . $i,
                seat_id: null,
                user_id: null,
                village_id: null,
                seat_type: 'elder',
                seat_title: 'Elder',
                seat_start: null,
                user_name: null,
                avatar_link: null,
                is_provisional: null

            );
        }
        return $seats;
    }

    /**
     * @return array
     */
    public static function getResources(System $system, int $village_id): array
    {
        $resources_result = $system->db->query("SELECT `resources` FROM `villages` WHERE `village_id` = {$village_id}");
        $resources_result = $system->db->fetch($resources_result);
        return json_decode($resources_result['resources'], true);
    }
    /**
     * @return array
     */
    public static function getResourceHistory(System $system, int $village_id, int $days): array
    {
        $resource_history = [];
        $time = time() - ($days * 86400);
        foreach (array_keys(WarManager::RESOURCE_NAMES) as $resource_id) {
            // get produced
            $result = $system->db->query("SELECT SUM(`quantity`) as 'produced' FROM `resource_logs` WHERE `resource_id` = {$resource_id} AND `village_id` = {$village_id} AND `type` = " . self::RESOURCE_LOG_PRODUCTION . " AND `time` > {$time}");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['produced'] = (int)$result['produced'];
            // get collected
            $result = $system->db->query("SELECT SUM(`quantity`) as 'collected' FROM `resource_logs` WHERE `resource_id` = {$resource_id} AND `village_id` = {$village_id} AND `type` = " . self::RESOURCE_LOG_COLLECTION . " AND `time` > {$time}");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['collected'] = (int)$result['collected'];
            // get claimed
            $result = $system->db->query("SELECT COUNT(*) as 'claimed' FROM `loot` WHERE `resource_id` = {$resource_id} AND `claimed_village_id` = {$village_id} AND `claimed_time` > {$time}");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['claimed'] = (int)$result['claimed'];
            // get lost (lol)
            $result = $system->db->query("SELECT COUNT(*) as 'lost' FROM `loot` WHERE `resource_id` = {$resource_id} AND `target_village_id` = {$village_id} AND (`claimed_village_id` != {$village_id} OR `claimed_village_id` IS NULL) AND (`claimed_time` > {$time} OR `claimed_time` IS NULL)");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['lost'] = (int) $result['lost'];
            // get upkeep - WIP
            $resource_history[$resource_id]['spent'] = 0;
        }
        return $resource_history;
    }

    /**
     * @return Village
     */
    public static function getVillageByID(System $system, int $village_id): Village
    {
        $result = $system->db->query(
            "SELECT * FROM `villages`
                WHERE `villages`.`village_id`='{$village_id}'"
        );
        $result = $system->db->fetch($result);
        return new Village($system, village_row: $result);
    }

    /**
     * @return array
     */
    public static function getClans(System $system, string $village): array
    {
        $result = $system->db->query("SELECT * FROM `clans` WHERE `village` = '{$village}'");
        $result = $system->db->fetch_all($result);
        return $result;
    }

    /**
     * @return VillageSeatDto
     */
    public static function getPlayerSeat(System $system, int $player_id): VillageSeatDto
    {
        $result = $system->db->query("SELECT * FROM `village_seats` WHERE `user_id` = {$player_id} AND `seat_end` IS NULL LIMIT 1");
        $result = $system->db->fetch($result);
        if (empty($result)) {
            $seat = new VillageSeatDto(
                seat_key: null,
                seat_id: null,
                user_id: null,
                village_id: null,
                seat_type: null,
                seat_title: null,
                seat_start: null,
                user_name: null,
                avatar_link: null,
                is_provisional: null

            );
            return $seat;
        }
        $seat = new VillageSeatDto(
            seat_key: null,
            seat_id: $result['seat_id'],
            user_id: $player_id,
            village_id: $result['village_id'],
            seat_type: $result['seat_type'],
            seat_title: $result['seat_title'],
            seat_start: $result['seat_start'],
            user_name: null,
            avatar_link: null,
            is_provisional: $result['is_provisional']
        );
        return $seat;
    }

    private static function getOrdinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        } else {
            return $number . $ends[$number % 10];
        }
    }

    /**
     * @return array
     */
    public static function getProposalHistory(System $system, int $village_id, bool $active_only = true): array {
        $proposal_history = [];
        if ($active_only) {
            $proposal_result = $system->db->query("SELECT * FROM `proposals` WHERE `village_id` = {$village_id} AND `end_time` IS NULL");
        } else {
            $proposal_result = $system->db->query("SELECT * FROM `proposals` WHERE `village_id` = {$village_id}");
        }
        $proposal_result = $system->db->fetch_all($proposal_result);
        foreach ($proposal_result as &$proposal) {
            // special - if end_time unset and past vote + enact period, cancel
            if ($proposal['end_time'] == null) {
                if ($proposal['start_time'] + (self::PROPOSAL_VOTE_HOURS * 3600) + (self::PROPOSAL_ENACT_HOURS * 3600) < time()) {
                    $time = time();
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'expired' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    // create notification
                    self::createProposalNotification($system, $village_id, NotificationManager::NOTIFICATION_PROPOSAL_EXPIRED, $proposal['name']);
                    unset($proposal);
                    continue;
                }
            }
            $vote_result = $system->db->query("SELECT * FROM `vote_logs` WHERE `proposal_id` = {$proposal['proposal_id']}");
            $vote_result = $system->db->fetch_all($vote_result);
            $proposal['votes'] = $vote_result;
            // get vote time
            $seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + $proposal['start_time'] - time());
            if ($seconds_remaining > 0) {
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $proposal['vote_time_remaining'] = "Time left to vote: " . ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute " : $minutes . " minutes");
                $proposal['enact_time_remaining'] = null;
            } else {
                $proposal['vote_time_remaining'] = null;
                $seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + (self::PROPOSAL_ENACT_HOURS * 3600) + $proposal['start_time'] - time());
                if ($seconds_remaining > 0) {
                    $hours = floor($seconds_remaining / 3600);
                    $minutes = floor(($seconds_remaining % 3600) / 60);
                    $proposal['enact_time_remaining'] = "Time left to enact: " . ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute " : $minutes . " minutes");
                } else {
                    $proposal['enact_time_remaining'] = null;
                }
            }
            //get enact time


            $proposal_history[] = new VillageProposalDto(
                proposal_id: $proposal['proposal_id'],
                village_id: $proposal['village_id'],
                user_id: $proposal['user_id'],
                start_time: $proposal['start_time'],
                end_time: $proposal['end_time'],
                name: $proposal['name'],
                result: $proposal['result'],
                type: $proposal['type'],
                target_village_id: $proposal['target_village_id'],
                policy_id: $proposal['policy_id'],
                vote_time_remaining: $proposal['vote_time_remaining'],
                enact_time_remaining: $proposal['enact_time_remaining'],
                votes: $proposal['votes'],
            );
            unset($proposal);
        }
        return $proposal_history;
    }

    /**
     * @return string
     */
    public static function createPolicyProposal(System $system, User $player, int $policy_id): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        // check cooldown on policy
        $query = $system->db->query("SELECT `start_time` FROM `policy_logs` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            if ($last_change['start_time'] + self::POLICY_CHANGE_COOLDOWN_DAYS * 86400 > time()) {
                $seconds_remaining = (self::POLICY_CHANGE_COOLDOWN_DAYS * 86400) + $last_change['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return "Cannot change policy until for " . $time_remaining . ".";
            }
        }
        // check different policy than current
        if ($policy_id == $player->village->policy) {
            return "Selected policy is the same.";
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'policy' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check player cooldown on submit proposal
        $query = $system->db->query("SELECT `start_time` FROM `proposals` WHERE `user_id` = {$player->user_id} ORDER BY `start_time` DESC LIMIT 1");
        $last_proposal = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            if ($last_proposal['start_time'] + self::PROPOSAL_COOLDOWN_HOURS * 3600 > time()) {
                $seconds_remaining = (self::PROPOSAL_COOLDOWN_HOURS * 3600) + $last_proposal['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return "Cannot submit another proposal for " . $time_remaining . ".";
            }
        }
        // insert into DB
        $time = time();
        $name = "Change Policy: " . self::POLICY_NAMES[$policy_id];
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `policy_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, 'change_policy', '{$name}', {$policy_id})");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

    /**
     * @return string
     */
    public static function cancelProposal(System $system, User $player, int $proposal_id): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        $proposal = $system->db->query("SELECT * FROM `proposals` WHERE `proposal_id` =  {$proposal_id} LIMIT 1");
        $proposal = $system->db->fetch($proposal);
        $time = time();
        $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal_id} AND `village_id` = {$player->village->village_id} AND `end_time` IS NULL");
        if ($system->db->last_affected_rows > 0) {
            // create notification
            self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CANCELED, $proposal['name']);
            return "Proposal canceled.";
        } else {
            return "Invalid proposal.";
        }
    }

    public static function updateProvisionalKageStatus(System $system, User $player) {
        if (!empty($player->village_seat->seat_id)) {
            if ($player->village_seat->seat_type == "kage" && $player->village_seat->is_provisional) {
                if ($player->village_seat->seat_start + (VillageManager::KAGE_PROVISIONAL_DAYS * 86400) < time()) {
                    // get new title, base on number of previous unique, non-provisional kage in same village
                    $result = $system->db->query("SELECT COUNT(DISTINCT `user_id`) as 'kage_count' FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `user_id` != {$player->user_id} AND `is_provisional` = 0");
                    $result = $system->db->fetch($result);
                    $seat_title = self::getOrdinal($result['kage_count'] + 1) . " " . self::KAGE_NAMES[$player->village->village_id];
                    // update seat
                    $system->db->query("UPDATE `village_seats` SET `is_provisional` = 0, `seat_title` = '{$seat_title}' WHERE `seat_id` = {$player->village_seat->seat_id}");
                }
            }
        }
    }

    /**
     * @return string
     */
    public static function submitProposalVote(System $system, User $player, int $vote, int $proposal_id): string {
        // check if voting period valid
        $proposal = $system->db->query("SELECT * FROM `proposals` WHERE `proposal_id` = {$proposal_id} LIMIT 1");
        $proposal = $system->db->fetch($proposal);
        $seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + $proposal['start_time'] - time());
        if ($seconds_remaining == 0) {
            return "Voting period expired.";
        }
        // create vote
        $time = time();
        $system->db->query("INSERT INTO `vote_logs` (`user_id`, `proposal_id`, `vote`, `rep_adjustment`, `vote_time`) VALUES ({$player->user_id}, {$proposal_id}, {$vote}, 0, {$time})");
        if ($system->db->last_affected_rows > 0) {
            return "Vote success.";
        }
        return "Something went wrong.";
    }

    /**
     * @return string
     */
    public static function cancelProposalVote(System $system, User $player, int $proposal_id): string {
        // check if voting period valid
        $proposal = $system->db->query("SELECT * FROM `proposals` WHERE `proposal_id` = {$proposal_id} LIMIT 1");
        $proposal = $system->db->fetch($proposal);
        $seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + $proposal['start_time'] - time());
        if ($seconds_remaining == 0) {
            return "Voting period expired.";
        }
        // cancel vote
        $system->db->query("DELETE FROM `vote_logs` WHERE `user_id` = {$player->user_id} AND `proposal_id` = {$proposal_id}");
        if ($system->db->last_affected_rows > 0) {
            return "Vote canceled.";
        }
        return "Something went wrong.";
    }

    /**
     * @return string
     */
    public static function boostProposalVote(System $system, User $player, int $proposal_id): string {
        // check if voting period valid
        $proposal = $system->db->query("SELECT * FROM `proposals` WHERE `proposal_id` = {$proposal_id} LIMIT 1");
        $proposal = $system->db->fetch($proposal);
        $seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + $proposal['start_time'] - time());
        if ($seconds_remaining == 0) {
            return "Voting period expired.";
        }
        // check if vote already boosted
        $vote = $system->db->query("SELECT * FROM `vote_logs` WHERE `proposal_id` = {$proposal_id} AND `user_id` = {$player->user_id} LIMIT 1");
        $vote = $system->db->fetch($vote);
        if ($vote['rep_adjustment'] > 0) {
            return "You have already boosted this vote.";
        }
        // boost vote
        if ($vote['vote'] == self::VOTE_YES) {
            $adjustment = self::VOTE_BOOST_COST;
        } else {
            $adjustment = -(self::VOTE_BOOST_COST);
        }
        $system->db->query("UPDATE `vote_logs` SET `rep_adjustment` = {$adjustment} WHERE `vote_id` = {$vote['vote_id']}");
        return "Vote boosted successfully.";
    }

    /**
     * @return string
     */
    public static function enactProposal(System $system, User $player, int $proposal_id): string
    {
        // check if kage
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        $proposal = $system->db->query("SELECT * FROM `proposals` WHERE `proposal_id` = {$proposal_id} AND `village_id` = {$player->village->village_id} AND `end_time` IS NULL LIMIT 1");
        $proposal = $system->db->fetch($proposal);
        if ($system->db->last_num_rows == 0) {
            return "Proposal not found.";
        }
        $votes = $system->db->query("SELECT * FROM `vote_logs` WHERE `proposal_id` = {$proposal_id}");
        $votes = $system->db->fetch_all($votes);
        // if vote time remaining
        $vote_seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + $proposal['start_time'] - time());
        $enact_seconds_remaining = max(0, (self::PROPOSAL_VOTE_HOURS * 3600) + (self::PROPOSAL_ENACT_HOURS * 3600) + $proposal['start_time'] - time());
        if ($vote_seconds_remaining > 0) {
            $elder_count = $system->db->query("SELECT COUNT(*) as `count` FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_end` IS NULL AND `seat_type` = 'elder'");
            $elder_count = $system->db->fetch($elder_count);
            if (count($votes) < $elder_count['count']) {
                return "All votes must be submitted to enact proposal during voting period.";
            } else {
                // if provisional, all votes must be yes
                if ($player->village_seat->is_provisional) {
                    $can_enact = true;
                    foreach ($votes as $vote) {
                        if ($vote['vote'] != 1) {
                            $can_enact = false;
                        }
                    }
                    if (!$can_enact) {
                        return "All Elders must vote yes to pass proposal during provisional period.";
                    }
                }
            }
        }
        // if enact time remaining
        else if ($enact_seconds_remaining > 0) {
            // if provisional, all votes must be yes
            if ($player->village_seat->is_provisional) {
                $can_enact = true;
                foreach ($votes as $vote) {
                    if ($vote['vote'] != 1) {
                        $can_enact = false;
                    }
                }
                if (!$can_enact) {
                    return "All Elders must vote yes to pass proposal during provisional period.";
                }
            }
        } else {
            return "Proposal expired.";
        }

        // switch for type
        $message = '';
        $time = time();
        switch ($proposal['type']) {
            case 'change_policy':
                // update village policy
                $system->db->query("UPDATE `villages` SET `policy` = {$proposal['policy_id']} WHERE `village_id` = {$proposal['village_id']}");
                // update policy log
                $system->db->query("UPDATE `policy_logs` SET `end_time` = {$time} WHERE `village_id` = {$proposal['village_id']} AND `end_time` IS NULL");
                $system->db->query("INSERT INTO `policy_logs` (`village_id`, `policy`, `start_time`) VALUES ({$proposal['village_id']}, {$proposal['policy_id']}, {$time})");
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Set village policy: " . self::POLICY_NAMES[$proposal['policy_id']] . ".";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case 'declare_war':
                // check neutral
                if (!$player->village->isNeutral($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to declare war.";
                }
                // update relation
                self::setNewRelations($system, $proposal['village_id'], $proposal['target_village_id'], VillageRelation::RELATION_WAR, $proposal['type']);
                // clear active proposals
                self::clearDiplomaticProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Declared war on " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case 'offer_peace':
                // check at war
                if (!$player->village->isEnemy($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be at war to offer peace.";
                }
                // create new proposal for target village
                $name = "Accept Peace: " . VillageManager::VILLAGE_NAMES[$player->village->village_id];
                $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$proposal['target_village_id']}, {$player->user_id}, {$time}, 'accept_peace', '{$name}', {$player->village->village_id})");
                // create notification
                self::createProposalNotification($system, $proposal['target_village_id'], NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Peace offer sent to " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case 'offer_alliance':
                // check neutral
                if (!$player->village->isNeutral($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to offer alliance.";
                }
                // neither village can have an existing ally
                $alliance_type = VillageRelation::RELATION_ALLIANCE;
                $system->db->query("SELECT COUNT(*) FROM `village_relations`
                    WHERE `relation_type` = {$alliance_type}
                    AND `relation_end` IS NULL
                    AND ((`village1_id` = {$proposal['village_id']} OR `village1_id` = {$proposal['target_village_id']})
                    OR (`village2_id` = {$proposal['village_id']} OR `village2_id` = {$proposal['target_village_id']})) LIMIT 1");
                if ($system->db->last_num_rows > 0) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Neither village can be in an existing Alliance.";
                }
                // create new proposal for target village
                $name = "Accept Alliance: " . VillageManager::VILLAGE_NAMES[$player->village->village_id];
                $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$proposal['target_village_id']}, {$player->user_id}, {$time}, 'accept_alliance', '{$name}', {$player->village->village_id})");
                // create notification
                self::createProposalNotification($system, $proposal['target_village_id'], NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Alliance offer sent to " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case 'break_alliance':
                // check allied
                if (!$player->village->isAlly($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be allied to break alliance.";
                }
                // update relation
                self::setNewRelations($system, $proposal['village_id'], $proposal['target_village_id'], VillageRelation::RELATION_NEUTRAL, $proposal['type']);
                // clear active proposals
                self::clearDiplomaticProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Broke alliance with " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case 'accept_peace':
                // check at war
                if (!$player->village->isEnemy($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be at war to enter peace.";
                }
                // update relation
                self::setNewRelations($system, $proposal['target_village_id'], $proposal['village_id'], VillageRelation::RELATION_NEUTRAL, $proposal['type']);
                // clear active proposals
                self::clearDiplomaticProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Accepted peace offer from " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case 'accept_alliance':
                // check neutral
                if (!$player->village->isNeutral($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to form alliance.";
                }
                // update relation
                self::setNewRelations($system, $proposal['target_village_id'], $proposal['village_id'], VillageRelation::RELATION_ALLIANCE, $proposal['type']);
                // clear active proposals
                self::clearDiplomaticProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Accepted alliance offer from " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            default:
                return "Invalid proposal type.";
                break;
        }
        // process reputation change
        $rep_adjustment = 0;
        foreach ($votes as $vote) {
            if ($vote['rep_adjustment'] != 0) {
                $rep_adjustment += $vote['rep_adjustment'];
                $user = User::loadFromId($system, $vote['user_id']);
                $user->loadData();
                $user->reputation->subtractRep($vote['rep_adjustment'], false);
                $user->updateData();
            }
        }
        if ($rep_adjustment > 0) {
            $player->reputation->addRep($rep_adjustment, bypass_weekly_cap: true);
            $message .= "\n You have gained {$rep_adjustment} Reputation!";
            $player->updateData();
        } else if ($rep_adjustment < 0) {
            $player->reputation->subtractRep($rep_adjustment, false);
            $message .= "\n You have lost {$rep_adjustment} Reputation!";
            $player->updateData();
        }

        return $message;
    }

    /**
     * @return VillageStrategicInfoDto[]
     */
    public static function getVillageStrategicInfo($system): array {
        $strategic_info = [];
        for ($i = 1; $i <= 5; $i++) {
            // get village
            $village = $system->db->query("SELECT * FROM `villages` WHERE `village_id` = {$i}");
            $village = $system->db->fetch($village);
            $village = new Village($system, village_row: $village);
            // get population
            $population_data = self::getVillagePopulation($system, $village->name);
            // get seat holders
            $seats = self::getVillageSeats($system, $i);
            // get regions
            $regions = $system->db->query("SELECT `name` FROM `regions` WHERE `village` = {$i}");
            $regions = $system->db->fetch_all($regions);
            // get supply points
            $supply_points = [];
            $resource_counts = $system->db->query("SELECT `region_locations`.`resource_id`, COUNT(`region_locations`.`resource_id`) AS `supply_points` FROM `region_locations` INNER JOIN `regions` ON `region_locations`.`region_id` = `regions`.`region_id` WHERE `regions`.`village` = {$i} GROUP BY `region_locations`.`resource_id`");
            $resource_data = [];
            while ($row = $system->db->fetch($resource_counts)) {
                $resource_data[$row['resource_id']] = $row['supply_points'];
            }
            foreach (WarManager::RESOURCE_NAMES as $key => $name) {
                $supply_points[$key] = [
                    "name" => $name,
                    "count" => isset($resource_data[$key]) ? $resource_data[$key] : 0,
                ];
            }
            $allies = [];
            $enemies = [];
            foreach ($village->relations as $relation) {
                if ($relation->relation_type == VillageRelation::RELATION_ALLIANCE) {
                    if ($relation->village1_id != $village->village_id) {
                        $allies[] = $relation->village1_name;
                    } else {
                        $allies[] = $relation->village2_name;
                    }
                }
                else if ($relation->relation_type == VillageRelation::RELATION_WAR) {
                    if ($relation->village1_id != $village->village_id) {
                        $enemies[] = $relation->village1_name;
                    } else {
                        $enemies[] = $relation->village2_name;
                    }
                }
            }
            $strategic_info[] = new VillageStrategicInfoDto(
                village: $village,
                seats: $seats,
                population: $population_data,
                regions: $regions,
                supply_points: $supply_points,
                allies: $allies,
                enemies: $enemies
            );
        }
        return $strategic_info;
    }

    /**
     * @return string
     */
    public static function createWarProposal(System $system, User $player, int $target_village_id): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'declare_war' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check no alliance proposal outgoing
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'offer_alliance' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a proposal to form alliance.";
        }
        // only allow if neutral
        if (!$player->village->isNeutral($target_village_id)) {
            return "Village must be neutral to declare war.";
        }
        // check player cooldown on submit proposal
        $query = $system->db->query("SELECT `start_time` FROM `proposals` WHERE `user_id` = {$player->user_id} ORDER BY `start_time` DESC LIMIT 1");
        $last_proposal = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            if ($last_proposal['start_time'] + self::PROPOSAL_COOLDOWN_HOURS * 3600 > time()) {
                $seconds_remaining = (self::PROPOSAL_COOLDOWN_HOURS * 3600) + $last_proposal['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return "Cannot submit another proposal for " . $time_remaining . ".";
            }
        }
        // insert into DB
        $time = time();
        $name = "Declare War: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, 'declare_war', '{$name}', {$target_village_id})");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

    /**
     * @return string
     */
    public static function createPeaceProposal(System $system, User $player, int $target_village_id): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'offer_peace' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // also check at war
        if (!$player->village->isEnemy($target_village_id)) {
            return "Village must be at war to offer peace.";
        }
        // also check war duration requirement
        // also check not pending on receiving village
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$target_village_id} AND `target_village_id` = {$player->village->village_id} AND `type` = 'accept_peace' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check player cooldown on submit proposal
        $query = $system->db->query("SELECT `start_time` FROM `proposals` WHERE `user_id` = {$player->user_id} ORDER BY `start_time` DESC LIMIT 1");
        $last_proposal = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            if ($last_proposal['start_time'] + self::PROPOSAL_COOLDOWN_HOURS * 3600 > time()) {
                $seconds_remaining = (self::PROPOSAL_COOLDOWN_HOURS * 3600) + $last_proposal['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return "Cannot submit another proposal for " . $time_remaining . ".";
            }
        }
        // insert into DB
        $time = time();
        $name = "Offer Peace: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, 'offer_peace', '{$name}', {$target_village_id})");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

    /**
     * @return string
     */
    public static function createAllianceProposal(System $system, User $player, int $target_village_id): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'offer_alliance' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check no war proposal outgoing
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'declare_war' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a proposal to declare war.";
        }
        // check not at war or allied
        if (!$player->village->isNeutral($target_village_id)) {
            return "Village must be neutral to form alliance.";
        }
        // also check not pending on receiving village
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$target_village_id} AND `target_village_id` = {$player->village->village_id} AND `type` = 'accept_alliance' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check player cooldown on submit proposal
        $query = $system->db->query("SELECT `start_time` FROM `proposals` WHERE `user_id` = {$player->user_id} ORDER BY `start_time` DESC LIMIT 1");
        $last_proposal = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            if ($last_proposal['start_time'] + self::PROPOSAL_COOLDOWN_HOURS * 3600 > time()) {
                $seconds_remaining = (self::PROPOSAL_COOLDOWN_HOURS * 3600) + $last_proposal['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return "Cannot submit another proposal for " . $time_remaining . ".";
            }
        }
        // check neither village has existing alliance
        $time = time();
        $alliance_type = VillageRelation::RELATION_ALLIANCE;
        $system->db->query("SELECT COUNT(*) FROM `village_relations`
            WHERE `relation_type` = {$alliance_type}
            AND `relation_end` IS NULL
            AND ((`village1_id` = {$player->village->village_id} OR `village1_id` = {$target_village_id})
            OR (`village2_id` = {$player->village->village_id} OR `village2_id` = {$target_village_id})) LIMIT 1");
        if ($system->db->last_num_rows > 0) {
            return "Neither village can be in an existing Alliance.";
        }
        // insert into DB
        $name = "Form Alliance: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, 'offer_alliance', '{$name}', {$target_village_id})");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

    /**
     * @return string
     */
    public static function createBreakAllianceProposal(System $system, User $player, int $target_village_id): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = 'break_alliance' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // also check in alliance
        if (!$player->village->isAlly($target_village_id)) {
            return "Village must be allied to break alliance.";
        }
        // also check alliance duration requirement
        // check player cooldown on submit proposal
        $query = $system->db->query("SELECT `start_time` FROM `proposals` WHERE `user_id` = {$player->user_id} ORDER BY `start_time` DESC LIMIT 1");
        $last_proposal = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            if ($last_proposal['start_time'] + self::PROPOSAL_COOLDOWN_HOURS * 3600 > time()) {
                $seconds_remaining = (self::PROPOSAL_COOLDOWN_HOURS * 3600) + $last_proposal['start_time'] - time();
                $hours = floor($seconds_remaining / 3600);
                $minutes = floor(($seconds_remaining % 3600) / 60);
                $time_remaining = ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute" : $minutes . " minutes");
                return "Cannot submit another proposal for " . $time_remaining . ".";
            }
        }
        // insert into DB
        $time = time();
        $name = "Break Alliance: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, 'break_alliance', '{$name}', {$target_village_id})");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

    private static function clearDiplomaticProposals(System $system, int $village1_id, int $village2_id, int $proposal_id) {
        $time = time();
        $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` =  'canceled' WHERE
            ((`village_id` = {$village1_id} OR `village_id` = {$village2_id})
            AND (`target_village_id` = {$village1_id} OR `target_village_id` = {$village2_id}))
            AND `type` IN ('offer_alliance', 'offer_peace', 'accept_alliance', 'accept_peace', 'declare_war', 'break_alliance')
            AND `proposal_id` != {$proposal_id}");
    }

    private static function setNewRelations(System $system, int $initiator_village_id, int $recipient_village_id, int $relation_type, string $proposal_type, ?string $relation_name = null) {
        // determine relation name
        if ($relation_name == null) {
            switch ($relation_type) {
                case VillageRelation::RELATION_NEUTRAL:
                    $relation_name = "Neutral";
                    break;
                case VillageRelation::RELATION_ALLIANCE:
                    $relation_name = VillageManager::VILLAGE_NAMES[$initiator_village_id] . "-" . VillageManager::VILLAGE_NAMES[$recipient_village_id] . " Alliance";
                    break;
                case VillageRelation::RELATION_WAR:
                    $relation_name = VillageManager::VILLAGE_NAMES[$initiator_village_id] . "-" . VillageManager::VILLAGE_NAMES[$recipient_village_id] . " War";
                    break;
                default:
                    break;
            }
        }
        $time = time();
        // end old relation
        $system->db->query("UPDATE `village_relations` SET `relation_end` = {$time}
        WHERE `relation_end` IS NULL
        AND ((`village1_id` = {$initiator_village_id} OR `village1_id` = {$recipient_village_id})
        AND (`village2_id` = {$initiator_village_id} OR `village2_id` = {$recipient_village_id}))");
        // insert new relation
        $system->db->query("INSERT INTO `village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`) VALUES ({$initiator_village_id}, {$recipient_village_id}, '{$relation_type}', '{$relation_name}', {$time})");
        // get list of users to notify
        $initator_village_name = self::VILLAGE_NAMES[$initiator_village_id];
        $recipient_village_name = self::VILLAGE_NAMES[$recipient_village_id];
        $active_threshold = time() - (NotificationManager::ACTIVE_PLAYER_DAYS_LAST_ACTIVE * 86400);
        $user_ids = $system->db->query("SELECT `user_id` FROM `users` WHERE (`village` = '{$initator_village_name}' OR `village` = '{$recipient_village_name}') AND `last_login` > {$active_threshold}");
        $user_ids = $system->db->fetch_all($user_ids);
        // create notifcations
        $message;
        $notification_type;
        switch ($proposal_type) {
            case 'break_alliance':
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_END_ALLIANCE;
                $message = VillageManager::VILLAGE_NAMES[$initiator_village_id] . " has ended and Alliance with " . VillageManager::VILLAGE_NAMES[$recipient_village_id] . "!";
                break;
            case 'accept_peace':
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_END_WAR;
                $message = VillageManager::VILLAGE_NAMES[$recipient_village_id] . " has negotiated peace with " . VillageManager::VILLAGE_NAMES[$recipient_village_id] . "!";
                break;
            case 'accept_alliance':
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_ALLIANCE;
                $message = VillageManager::VILLAGE_NAMES[$recipient_village_id] . " has formed an Alliance with " . VillageManager::VILLAGE_NAMES[$initiator_village_id] . "!";
                break;
            case 'declare_war':
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_WAR;
                $message = VillageManager::VILLAGE_NAMES[$initiator_village_id] . " has declared War on " . VillageManager::VILLAGE_NAMES[$recipient_village_id] . "!";
                break;
            default:
                break;
        }
        foreach ($user_ids as $user) {
            $new_notification = new NotificationDto(
                type: $notification_type,
                message: $message,
                user_id: $user['user_id'],
                created: time(),
                expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_DIPLOMACY * 86400),
                alert: false,
            );
            NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_MULTIPLE);
        }
    }

    private static function createProposalNotification(System $system, $village_id, $notification_type, $proposal_name) {
        // get elders
        $user_ids = $system->db->query("SELECT `user_id` FROM `village_seats` WHERE `village_id` = {$village_id} AND `seat_end` IS NULL");
        $user_ids = $system->db->fetch_all($user_ids);
        // loop create notifs
        $message;
        switch ($notification_type) {
            case NotificationManager::NOTIFICATION_PROPOSAL_CREATED:
                $message = "[New Proposal] - " . $proposal_name;
                break;
            case NotificationManager::NOTIFICATION_PROPOSAL_PASSED:
                $message = "[Passed] - " . $proposal_name;
                break;
            case NotificationManager::NOTIFICATION_PROPOSAL_CANCELED:
                $message = "[Canceled] - " . $proposal_name;
                break;
            case NotificationManager::NOTIFICATION_PROPOSAL_EXPIRED:
                $message = "[Expired] - " . $proposal_name;
                break;
            default:
                break;
        }
        foreach ($user_ids as $user) {
            $new_notification = new NotificationDto(
                type: $notification_type,
                message: $message,
                user_id: $user['user_id'],
                created: time(),
                expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_PROPOSAL * 86400),
                alert: false,
            );
            NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_MULTIPLE);
        }
    }
}