<?php

require_once __DIR__ . "/VillageSeatDto.php";

class VillageManager {
    const KAGE_NAMES = [
        1 => 'Tsuchikage',
        2 => 'Raikage',
        3 => 'Hokage',
        4 => 'Kazekage',
        5 => 'Mizukage',
    ];

    const RESOURCE_LOG_PRODUCTION = 1;
    const RESOURCE_LOG_COLLECTION = 2;
    const RESOURCE_LOG_EXPENDITURE = 3;

    const MIN_KAGE_CLAIM_TIER = 5;
    const MIN_KAGE_CHALLENGE_TIER = 6;
    const MIN_ELDER_CLAIM_TIER = 4;
    const MIN_ELDER_CHALLENGE_TIER = 5;

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
            "SELECT `village_relations`.* FROM `village_relations`
                WHERE `relation_end` IS NULL
                AND (`village1_id` = {$village_id} OR `village2_id` = {$village_id});
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
     * @return bool
     */
    public static function claimSeat(System $system, User $player, string $seat_type): string {
        switch ($seat_type) {
            case 'kage':
                // Temp disable
                return "Not yet available.";
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
                $seat = self::getPlayerSeat($system, $player->user_id);
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
                $result = $system->db->query("SELECT `seat_title` from `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `user_id` = {$player->user_id} LIMIT 1");
                $result = $system->db->fetch($result);
                if (!empty($result)) {
                    $seat_title = $result['seat_title'];
                    $reclaim = true;
                } else {
                    $result = $system->db->query("SELECT COUNT(DISTINCT `user_id`) as 'kage_count' FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `user_id` != {$player->user_id}");
                    $result = $system->db->fetch($result);
                    $seat_title = self::getOrdinal($result['kage_count'] + 1) . " " . self::KAGE_NAMES[$player->village->village_id];
                }
                $system->db->query("INSERT INTO `village_seats`
                    (`user_id`, `village_id`, `seat_type`, `seat_title`, `seat_start`)
                    VALUES ({$player->user_id}, {$player->village->village_id}, '{$seat_type}', '{$seat_title}', {$time})
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
                $seat = self::getPlayerSeat($system, $player->user_id);
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
        $result = $system->db->query("UPDATE `village_seats` SET `seat_end` = {$time} WHERE `seat_end` IS NULL AND `user_id` = {$player->user_id}");
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
        $time = time() - $days * 86400;
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
            // can determine resources lost based on difference of produced and collected
            $resource_history[$resource_id]['lost'] = $resource_history[$resource_id]['produced'] - $resource_history[$resource_id]['collected'];
            // factor in resources which haven't been collected from regions
            $result = $system->db->query("SELECT SUM(`resource_count`) as 'quantity' FROM `region_locations` INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id` WHERE `resource_id` = {$resource_id} AND `village` = {$village_id}");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['lost'] -= (int) $result['quantity'];
            // get upkeep - WIP
            $resource_history[$resource_id]['spent'] = 0;
        }
        // extra step to calculate lost resources: don't include resources which haven't been collected from caravans as lost
        $caravan_result = $system->db->query("SELECT * FROM `caravans` where `village_id` = {$village_id}");
        $caravan_result = $system->db->fetch_all($caravan_result);
        foreach ($caravan_result as $caravan) {
            $caravan_resources = json_decode($caravan['resources'], true);
            // reduce resource from lost count
            foreach ($caravan_resources as $resource_id => $quantity) {
                $resource_history[$resource_id]['lost'] -= $quantity;
            }
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
}