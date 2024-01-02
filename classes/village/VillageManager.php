<?php

require_once __DIR__ . "/VillageSeatDto.php";
require_once __DIR__ . "/VillageProposalDto.php";
require_once __DIR__ . "/VillageStrategicInfoDto.php";
require_once __DIR__ . "/ChallengeRequestDto.php";
require_once __DIR__ . "/../notification/NotificationManager.php";
require_once __DIR__ . '/../notification/BlockedNotificationManager.php';

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
    const RESOURCE_LOG_EXPENSE = 3;
    const RESOURCE_LOG_TRADE_GAIN = 4;
    const RESOURCE_LOG_TRADE_LOSS = 5;

    const MIN_KAGE_CLAIM_TIER = 5;
    const MIN_KAGE_CHALLENGE_TIER = 5;
    const MIN_ELDER_CLAIM_TIER = 4;
    const MIN_ELDER_CHALLENGE_TIER = 4;

    // Set these to correct values for release
    const PROPOSAL_VOTE_HOURS = 24; // 24
    const PROPOSAL_ENACT_HOURS = 24; // 24
    const PROPOSAL_COOLDOWN_HOURS = 1; // 1
    const KAGE_PROVISIONAL_DAYS = 7; // 7
    const POLICY_CHANGE_COOLDOWN_DAYS = 3; // 3
    const SEAT_RECLAIM_COOLDOWN_HOURS = 24; // 24

    const VOTE_NO = 0;
    const VOTE_YES = 1;
    const VOTE_BOOST_COST = 500;

    const PROPOSAL_TYPE_CHANGE_POLICY = "change_policy";
    const PROPOSAL_TYPE_DECLARE_WAR = "declare_war";
    const PROPOSAL_TYPE_OFFER_ALLIANCE = "offer_alliance";
    const PROPOSAL_TYPE_OFFER_PEACE = "offer_peace";
    const PROPOSAL_TYPE_FORCE_PEACE = "force_peace";
    const PROPOSAL_TYPE_ACCEPT_ALLIANCE = "accept_alliance";
    const PROPOSAL_TYPE_ACCEPT_PEACE = "accept_peace";
    const PROPOSAL_TYPE_BREAK_ALLIANCE = "break_alliance";
    const PROPOSAL_TYPE_OFFER_TRADE = "offer_trade";
    const PROPOSAL_TYPE_ACCEPT_TRADE = "accept_trade";

    const CHALLENGE_EXPIRE_DAYS = 1;
    const CHALLENGE_MIN_DELAY_HOURS = 12;
    const CHALLENGE_COOLDOWN_DAYS = 3;
    const CHALLENGE_LOCK_TIME_MINUTES = 5;
    const CHALLENGE_SCHEDULE_INCREMENT_MINUTES = 15;
    const CHALLENGE_MINIMUM_TIMES_SELECTED = 12;
    const CHALLENGE_SCHEDULE_TIME_HOURS = 24;

    const FORCE_PEACE_MINIMUM_DURATION_DAYS = 7;
    const WAR_COOLDOWN_DAYS = 1;

    const MAX_TRADE_RESOURCE_TYPE = 25000;

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
     * @param System   $system
     * @param int|null $village_id
     * @return VillageRelation[]
     */
    private static function getRelations(System $system, ?int $village_id = null): array {
        $query = "SELECT
                `village_relations`.*,
                `v1`.`name` AS `village1_name`,
                `v2`.`name` AS `village2_name`
            FROM `village_relations`
            INNER JOIN `villages` AS `v1` ON `village_relations`.`village1_id` = `v1`.`village_id`
            INNER JOIN `villages` AS `v2` ON `village_relations`.`village2_id` = `v2`.`village_id`
            WHERE `village_relations`.`relation_end` IS NULL";
        if($village_id != null) {
            $query .= " AND (`village_relations`.`village1_id` = {$village_id} OR `village_relations`.`village2_id` = {$village_id})";
        }

        $relations_result = $system->db->query($query);
        $relations_arr = $system->db->fetch_all($relations_result);

        return array_map(function ($relation_raw) {
            return new VillageRelation($relation_raw);
        }, $relations_arr);
    }

    /**
     * @return VillageRelation[]
     */
    public static function getRelationsForVillage(System $system, int $village_id): array {
        $relations = self::getRelations($system, $village_id);

        $relations_by_id = [];
        foreach($relations as $relation) {
            $other_village_id = 0;
            if ($relation->village1_id == $village_id) {
                $other_village_id = $relation->village2_id;
            }
            else if ($relation->village2_id == $village_id) {
                $other_village_id = $relation->village1_id;
            }
            $relations_by_id[$other_village_id] = $relation;
        }
        return $relations_by_id;
    }

    /**
     * Returns all relations keyed by both village ids, e.g. relations[1][3] and relations[3][1] both work
     *
     * @param System $system
     * @return VillageRelation[][]
     */
    public static function getAllRelationsByVillageIds(System $system): array {
        $relations = self::getRelations($system);
        $relations_by_village_ids = [];

        foreach($relations as $relation) {
            if(!isset($relations_by_village_ids[$relation->village1_id])) {
                $relations_by_village_ids[$relation->village1_id] = [];
            }
            if(!isset($relations_by_village_ids[$relation->village2_id])) {
                $relations_by_village_ids[$relation->village2_id] = [];
            }

            $relations_by_village_ids[$relation->village1_id][$relation->village2_id] = $relation;
            $relations_by_village_ids[$relation->village2_id][$relation->village1_id] = $relation;
        }

        return $relations_by_village_ids;
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
    public static function getVillagePolicyID(System $system, string $village_id): int
    {
        $policy_result = $system->db->query("SELECT `policy_id` FROM `villages` WHERE `village_id` = {$village_id}");
        $policy_result = $system->db->fetch($policy_result);
        return $policy_result['policy_id'];
    }

    /**
     * @return bool
     */
    public static function claimSeat(System $system, User $player, string $seat_type): string {
        switch ($seat_type) {
            case 'kage':
                // check requirements
                if (!self::checkSeatRequirements($system, $player, $seat_type)) {
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_KAGE_CLAIM_TIER) . " - " . UserReputation::$VillageRep[self::MIN_KAGE_CLAIM_TIER]['min_rep'] . " Reputation";
                }
                // check if recently left this seat
                $result = $system->db->query("SELECT * FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `user_id` = {$player->user_id} AND `seat_end` IS NOT NULL ORDER BY `seat_end` DESC LIMIT 1");
                $result = $system->db->fetch($result);
                if ($system->db->last_num_rows > 0) {
                    $cooldown_remaining = ($result['seat_end'] + self::SEAT_RECLAIM_COOLDOWN_HOURS * 3600) - time();
                    if ($cooldown_remaining > 0) {
                        $message = "You must wait another " . $system->time_remaining($cooldown_remaining) . " before reclaiming this seat.";
                        return $message;
                    }
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
                // clear active challenges
                self::cancelUserChallenges($system, $player->user_id);
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
                $player->village_seat = self::getPlayerSeat($system, $player);
                // create notifiction
                $active_threshold = time() - (NotificationManager::ACTIVE_PLAYER_DAYS_LAST_ACTIVE * 86400);
                $user_ids = $system->db->query("SELECT `user_id`, `blocked_notifications` FROM `users` WHERE `village` = '{$player->village->name}' AND `last_login` > {$active_threshold}");
                $user_ids = $system->db->fetch_all($user_ids);
                $notification_message = $reclaim ? $player->user_name . " has reclaimed the title of " . $seat_title . "!" : $player->user_name . " has claimed the title of " . $seat_title . "!";
                foreach ($user_ids as $user) {
                    $blockedNotifManager = BlockedNotificationManager::fromDb(system: $system, blocked_notifications_string: $user['blocked_notifications']);
                    if($blockedNotifManager->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_KAGE_CHANGE)) {
                        continue;
                    }

                    $new_notification = new NotificationDto(
                        type: NotificationManager::NOTIFICATION_KAGE_CHANGE,
                        message: $notification_message,
                        user_id: $user['user_id'],
                        created: time(),
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                }
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
                // check requirements
                if (!self::checkSeatRequirements($system, $player, $seat_type)) {
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_ELDER_CLAIM_TIER) . " - " . UserReputation::$VillageRep[self::MIN_ELDER_CLAIM_TIER]['min_rep'] . " Reputation";
                }
                // check if recently left this seat
                $result = $system->db->query("SELECT * FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `user_id` = {$player->user_id} AND `seat_end` IS NOT NULL ORDER BY `seat_end` DESC LIMIT 1");
                $result = $system->db->fetch($result);
                if ($system->db->last_num_rows > 0) {
                    $cooldown_remaining = ($result['seat_end'] + self::SEAT_RECLAIM_COOLDOWN_HOURS * 3600) - time();
                    if ($cooldown_remaining > 0) {
                        $message = "You must wait another " . $system->time_remaining($cooldown_remaining) . " before reclaiming this seat.";
                        return $message;
                    }
                }
                // check seat available
                $result = $system->db->query("SELECT COUNT(*) as 'elder_count' FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = '{$seat_type}' AND `seat_end` IS NULL LIMIT 1");
                $result = $system->db->fetch($result);
                if ($result['elder_count'] == 3) {
                    return "No seats available to claim!";
                }
                // clear active challenges
                self::cancelUserChallenges($system, $player->user_id);
                // claim
                $time = time();
                $seat_title = "Elder";
                $system->db->query("INSERT INTO `village_seats`
                    (`user_id`, `village_id`, `seat_type`, `seat_title`, `seat_start`)
                    VALUES ({$player->user_id}, {$player->village->village_id}, '{$seat_type}', '{$seat_title}', {$time})
                ");
                $player->village_seat = self::getPlayerSeat($system, $player);
                return "You have become an Elder of {$player->village->name}!";
                break;
            default:
                return '';
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
        // clear active challenges
        self::cancelUserChallenges($system, $player->user_id);
        // clear active votes
        $system->db->query("DELETE `vote_logs` FROM `vote_logs` INNER JOIN `proposals` on `vote_logs`.`proposal_id` = `proposals`.`proposal_id` WHERE `vote_logs`.`user_id` = {$player->user_id} AND `proposals`.`end_time` IS NULL");
        $result = $system->db->query("UPDATE `village_seats` SET `seat_end` = {$time} WHERE `seat_end` IS NULL AND `user_id` = {$player->user_id}");
        if ($player_seat->seat_type == "kage") {
            $result = $system->db->query("UPDATE `villages` SET `leader` = 0 WHERE `village_id` = {$player->village->village_id}");
        }
        if ($system->db->last_affected_rows > 0) {
            $player->village_seat = self::getPlayerSeat($system, $player);
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
                    $provisional_days_remaining = "";
                    if ($seat['is_provisional']) {
                        $provisional_days_remaining = System::timeRemaining(time() - $seat['seat_start'], format: 'days');
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
                            is_provisional: $seat['is_provisional'],
                            provisional_days_label: $provisional_days_remaining,
                        );
                    } else {
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
                            is_provisional: $seat['is_provisional'],
                        );
                    }
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
            $result = $system->db->query("SELECT COUNT(*) as 'claimed_loot_count' FROM `loot` WHERE `resource_id` = {$resource_id} AND `claimed_village_id` = {$village_id} AND `claimed_time` > {$time}");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['claimed'] = (int)$result['claimed_loot_count'];
            // get lost (lol)
            $result = $system->db->query("SELECT COUNT(*) as 'lost_loot_count' FROM `loot` WHERE `resource_id` = {$resource_id} AND `target_village_id` = {$village_id} AND (`claimed_village_id` != {$village_id} OR `claimed_village_id` IS NULL) AND (`claimed_time` > {$time} OR `claimed_time` IS NULL)");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['lost'] = (int) $result['lost_loot_count'];
            // get upkeep - WIP
            $result = $system->db->query("SELECT SUM(`quantity`) as 'spent' FROM `resource_logs` WHERE `resource_id` = {$resource_id} AND `village_id` = {$village_id} AND `type` = " . self::RESOURCE_LOG_EXPENSE . " AND `time` > {$time}");
            $result = $system->db->fetch($result);
            $resource_history[$resource_id]['spent'] = (int) $result['spent'];
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
     * @return ChallengeRequestDto[]
     */
    public static function getChallengeData(System $system, User $player): array
    {
        $challenge_data = [];
        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `seat_holder_id` = {$player->user_id} AND `end_time` IS NULL");
        $challenge_result = $system->db->fetch_all($challenge_result);
        foreach ($challenge_result as $challenge) {
            // process any challenges that are unresolved (outside of scheduling OR lock-in period)
            if (empty($challenge['start_time'])) {
                $max_schedule_time = $challenge['created_time'] + self::CHALLENGE_SCHEDULE_TIME_HOURS * 3600;
                if (time() > $max_schedule_time) {
                    $winner = $challenge['challenger_id'];
                    self::processChallengeEnd($system, $challenge['request_id'], $winner, $player);
                    continue;
                }
            }
            else if (!empty($challenge['start_time'])) {
                $min_lock_time = $challenge['start_time'];
                $max_lock_time = $min_lock_time + self::CHALLENGE_LOCK_TIME_MINUTES * 60;
                // if outside of lock period and no battle in progress
                if (time() > $max_lock_time && empty($challenge['battle_id'])) {
                    $challenger_locked = (bool)$challenge['challenger_locked'];
                    $seat_holder_locked = (bool)$challenge['seat_holder_locked'];
                    if (!$challenger_locked && !$seat_holder_locked) {
                        $winner = null;
                    } else if (!$challenger_locked && $seat_holder_locked) {
                        $winner = $challenge['seat_holder_id'];
                    } else if ($challenger_locked && !$seat_holder_locked) {
                        $winner = $challenge['challenger_id'];
                    } else {
                        // failsafe in case crazy stuff happens
                        continue;
                    }
                    self::processChallengeEnd($system, $challenge['request_id'], $winner, $player);
                    continue;
                }
            }
            $player_result = $system->db->query("SELECT `user_name`, `avatar_link` FROM `users` WHERE `user_id` = {$challenge['challenger_id']} LIMIT 1");
            $player_result = $system->db->fetch($player_result);
            $challenge_data[] = new ChallengeRequestDto(
                request_id: (int) $challenge['request_id'],
                challenger_id: (int) $challenge['challenger_id'],
                seat_holder_id: (int) $challenge['seat_holder_id'],
                seat_id: (int) $challenge['seat_id'],
                created_time: (int) $challenge['created_time'],
                accepted_time: isset($challenge['accepted_time']) ? (int) $challenge['accepted_time'] : null,
                start_time: isset($challenge['start_time']) ? (int) $challenge['start_time'] : null,
                end_time: isset($challenge['end_time']) ? (int) $challenge['end_time'] : null,
                seat_holder_locked: (bool) $challenge['seat_holder_locked'],
                challenger_locked: (bool) $challenge['challenger_locked'],
                selected_times: (array) $challenge['selected_times'],
                battle_id: isset($challenge['battle_id']) ? (int) $challenge['battle_id'] : null,
                winner: isset($challenge['winner']) ? $challenge['winner'] : null,
                challenger_name: isset($player_result['user_name']) ? $player_result['user_name'] : '',
                challenger_avatar: isset($player_result['avatar_link']) ? $player_result['avatar_link'] : '',
                seat_holder_name: isset($player_result['user_name']) ? $player_result['user_name'] : '',
                seat_holder_avatar: isset($player_result['avatar_link']) ? $player_result['avatar_link'] : ''
            );
        }

        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `challenger_id` = {$player->user_id} AND `end_time` IS NULL");
        $challenge_result = $system->db->fetch_all($challenge_result);
        foreach ($challenge_result as $challenge) {
            // process any challenges that are unresolved (outside of scheduling OR lock-in period)
            if (empty($challenge['start_time'])) {
                $max_schedule_time = $challenge['created_time'] + self::CHALLENGE_SCHEDULE_TIME_HOURS * 3600;
                if (time() > $max_schedule_time) {
                    $winner = $challenge['challenger_id'];
                    self::processChallengeEnd($system, $challenge['request_id'], $winner, $player);
                    continue;
                }
            }
            else if (!empty($challenge['start_time'])) {
                $min_lock_time = $challenge['start_time'];
                $max_lock_time = $min_lock_time + self::CHALLENGE_LOCK_TIME_MINUTES * 60;
                // if outside of lock period and no battle in progress
                if (time() > $max_lock_time && empty($challenge['battle_id'])) {
                    $challenger_locked = (bool)$challenge['challenger_locked'];
                    $seat_holder_locked = (bool)$challenge['seat_holder_locked'];
                    if (!$challenger_locked && !$seat_holder_locked) {
                        $winner = null;
                    } else if (!$challenger_locked && $seat_holder_locked) {
                        $winner = $challenge['seat_holder_id'];
                    } else if ($challenger_locked && !$seat_holder_locked) {
                        $winner = $challenge['challenger_id'];
                    } else {
                        // failsafe in case crazy stuff happens
                        continue;
                    }
                    self::processChallengeEnd($system, $challenge['request_id'], $winner, $player);
                    continue;
                }
            }
            $player_result = $system->db->query("SELECT `user_name`, `avatar_link` FROM `users` WHERE `user_id` = {$challenge['seat_holder_id']} LIMIT 1");
            $player_result = $system->db->fetch($player_result);
            $challenge_data[] = new ChallengeRequestDto(
                request_id: (int) $challenge['request_id'],
                challenger_id: (int) $challenge['challenger_id'],
                seat_holder_id: (int) $challenge['seat_holder_id'],
                seat_id: (int) $challenge['seat_id'],
                created_time: (int) $challenge['created_time'],
                accepted_time: isset($challenge['accepted_time']) ? (int) $challenge['accepted_time'] : null,
                start_time: isset($challenge['start_time']) ? (int) $challenge['start_time'] : null,
                end_time: isset($challenge['end_time']) ? (int) $challenge['end_time'] : null,
                seat_holder_locked: (bool) $challenge['seat_holder_locked'],
                challenger_locked: (bool) $challenge['challenger_locked'],
                selected_times: (array) $challenge['selected_times'],
                battle_id: isset($challenge['battle_id']) ? (int) $challenge['battle_id'] : null,
                winner: isset($challenge['winner']) ? $challenge['winner'] : null,
                challenger_name: isset($player_result['user_name']) ? $player_result['user_name'] : '',
                challenger_avatar: isset($player_result['avatar_link']) ? $player_result['avatar_link'] : '',
                seat_holder_name: isset($player_result['user_name']) ? $player_result['user_name'] : '',
                seat_holder_avatar: isset($player_result['avatar_link']) ? $player_result['avatar_link'] : ''
            );
        }
        return $challenge_data;
    }

    /**
     * @return string
     */
    public static function submitChallenge(System $system, User $player, int $seat_id, array $selected_times): string
    {
        // check sufficient times selected
        if (count($selected_times) < self::CHALLENGE_MINIMUM_TIMES_SELECTED) {
            return self::CHALLENGE_MINIMUM_TIMES_SELECTED . " time slots must be selected.";
        }
        // check active challenges
        $active_challenge = $system->db->query("SELECT * FROM `challenge_requests` WHERE `challenger_id` = {$player->user_id} AND `end_time` IS NULL LIMIT 1");
        if ($system->db->last_num_rows > 0) {
            return "You can only have one challenge request in progress at a time.";
        }

        // check challenge cooldown, ignoring cancelled challenges (complete, but winner is not null)
        $last_challenge = $system->db->query(
            "SELECT * FROM `challenge_requests`
                WHERE `challenger_id` = {$player->user_id}
                AND `winner` IS NOT NULL
                ORDER BY `start_time` DESC LIMIT 1");
        $last_challenge = $system->db->fetch($last_challenge);
        if ($system->db->last_num_rows > 0) {
            $cooldown_remaining = ($last_challenge['created_time'] + (self::CHALLENGE_COOLDOWN_DAYS * 86400)) - time();
            if ($cooldown_remaining > 0) {
                $hours = floor($cooldown_remaining / 3600);
                $minutes = floor(($cooldown_remaining % 3600) / 60);
                $message = "You must wait another " . ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute " : $minutes . " minutes" . " before submiting another challenge.");
                return $message;
            }
        }

        // get seat holder
        $seat_result = $system->db->query("SELECT * FROM `village_seats` WHERE `seat_id` = {$seat_id} AND `village_id` = {$player->village->village_id} AND `seat_end` IS NULL LIMIT 1");
        $seat_result = $system->db->fetch($seat_result);
        if ($system->db->last_num_rows == 0) {
            return "Invalid challenge target.";
        }

        // if elder, check if other seat available
        if($seat_result['seat_type'] == 'elder') {
            $elder_count = $system->db->query("SELECT count(*) as `elder_count` FROM `village_seats` WHERE `seat_type` = 'elder' AND `village_id` = {$player->village->village_id} AND `seat_end` IS NULL");
            $elder_count = $system->db->fetch($elder_count);
            if (isset($elder_count['elder_count']) && $elder_count['elder_count'] < 3) {
                return "Cannot challenge Elder with open seat available for claim.";
            }
        }

        // check if already has seat
        if (isset($player->village_seat->seat_id) && $seat_result['seat_type'] != 'kage') {
            return "Invalid challenge target.";
        }
        // check meet challenge requirements
        if (!self::checkSeatRequirements($system, $player, $seat_result['seat_type'], true)) {
            switch ($seat_result['seat_type']) {
                case 'kage':
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_KAGE_CHALLENGE_TIER) . " - " . UserReputation::$VillageRep[self::MIN_KAGE_CHALLENGE_TIER]['min_rep'] . " Reputation";
                    break;
                case 'elder':
                    return "You do not meet the requirements!\nJonin Rank, " . UserReputation::nameByRepRank(self::MIN_ELDER_CHALLENGE_TIER) . " - " . UserReputation::$VillageRep[self::MIN_ELDER_CHALLENGE_TIER]['min_rep'] . " Reputation";
                    break;
                default:
                    return "Seat requirements not met.";
                    break;
            }
        }
        // create challenge
        $time = time();
        $selected_times = json_encode($selected_times);
        $system->db->query("INSERT INTO `challenge_requests` (`challenger_id`, `seat_holder_id`, `seat_id`, `created_time`, `selected_times`)
            VALUES ({$player->user_id}, {$seat_result['user_id']}, {$seat_id}, {$time}, '{$selected_times}')");
        // create notification
        $new_notification = new NotificationDto(
            type: NotificationManager::NOTIFICATION_CHALLENGE_PENDING,
            message: "New challenge pending!",
            user_id: $seat_result['user_id'],
            created: time(),
            alert: false,
        );
        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_MULTIPLE);
        return "Challenge submitted!";
    }

    /**
     * Forfeits a challenge. Note this is different from cancelling a challenge (e.g. due to seat claim) as it sets
     * the challenge as if the seat holder fought and won. This preserves the challenge cooldown if the challenger
     * tries to make another challenge.
     *
     * @param System $system
     * @param User   $player
     * @return string
     */
    public static function forfeitChallenge(System $system, User $player): string {
        // verify challenge target is valid
        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `challenger_id` = {$player->user_id} AND `end_time` IS NULL LIMIT 1");
        $challenge_result = $system->db->fetch($challenge_result);
        if ($system->db->last_num_rows == 0) {
            return "Invalid challenge.";
        }
        $time = time();
        $system->db->query("UPDATE `challenge_requests` SET `winner` = 'seat_holder', `end_time` = {$time} WHERE `request_id` = {$challenge_result['request_id']}");
        return "Challenge forfeited.";
    }

    /**
     * @return string
     */
    public static function acceptChallenge(System $system, User $player, int $challenge_id, string $challenge_time): string
    {
        $slots_per_hour = 60 / self::CHALLENGE_SCHEDULE_INCREMENT_MINUTES;
        // check challenge valid
        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `request_id` = {$challenge_id} AND `end_time` IS NULL AND `seat_holder_id` = {$player->user_id}");
        $challenge_result = $system->db->fetch($challenge_result);
        if ($system->db->last_num_rows == 0) {
            return "Invalid challenge.";
        }
        // check challenge time in options
        $selected_times = json_decode($challenge_result['selected_times']);
        if (!in_array($challenge_time, $selected_times)) {
            return "Invalid time slot.";
        }
        // convert challenge time to UTC
        $date = new DateTime($challenge_time, new DateTimeZone('UTC'));
        $min_challenge_time = $date->getTimestamp();
        // check min number of hours from current time
        $difference = $min_challenge_time - time();
        if ($difference < self::CHALLENGE_MIN_DELAY_HOURS * 60 * 60) {
            // Add 24 hours to the challenge time
            $min_challenge_time += 24 * 60 * 60;
        }
        // calculate last possible time for current slot
        $max_challenge_time = $min_challenge_time + (($slots_per_hour - 1) * 60 * self::CHALLENGE_SCHEDULE_INCREMENT_MINUTES); // e.g. 8:00->8:15->8:30->8:45
        // get number of scheduled challenges in same hour
        $challenges_result = $system->db->query("SELECT COUNT(*) as `challenge_count` FROM `challenge_requests` WHERE `seat_holder_id` = {$player->user_id} AND `start_time` >= {$min_challenge_time} AND `start_time` <= {$max_challenge_time}");
        $challenges_result = $system->db->fetch($challenges_result);
        if ($challenges_result['challenge_count'] >= $slots_per_hour) {
            return "Can not schedule any more challenges for that time slot.";
        } else {
            $challenge_time = $min_challenge_time + (self::CHALLENGE_SCHEDULE_INCREMENT_MINUTES * 60) * $challenges_result['challenge_count'];
        }
        // update challenge
        $time = time();
        $hours = floor(($challenge_time - time()) / 3600);
        $minutes = floor((($challenge_time - time()) % 3600) / 60);
        $message = "Lock-in period begins in " . ($hours == 1 ? $hours . " hour " : $hours . " hours ") . ($minutes == 1 ? $minutes . " minute " : $minutes . " minutes" . ".");
        $system->db->query("UPDATE `challenge_requests` SET `accepted_time` = {$time}, `start_time` = {$challenge_time} WHERE `request_id` = {$challenge_id}");
        // create notification
        $new_notification = new NotificationDto(
            type: NotificationManager::NOTIFICATION_CHALLENGE_ACCEPTED,
            message: "Challenge accepted: " . $message,
            user_id: $challenge_result['challenger_id'],
            created: time(),
            alert: false,
        );
        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
        return $message;
    }

    /**
     * @return string
     */
    public static function lockChallenge(System $system, User $player, int $challenge_id): string
    {
        // get challenge data
        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `request_id` = {$challenge_id} AND `end_time` IS NULL AND `start_time` IS NOT NULL AND (`seat_holder_id` = {$player->user_id} OR `challenger_id` = {$player->user_id}) LIMIT 1");
        $challenge_result = $system->db->fetch($challenge_result);
        if ($system->db->last_num_rows == 0) {
            return "Invalid challenge request.";
        }
        // check lock period
        $min_lock_time = $challenge_result['start_time'];
        $max_lock_time = $min_lock_time + self::CHALLENGE_LOCK_TIME_MINUTES * 60;
        if (time() > $max_lock_time || time() < $min_lock_time) {
            return "Outside of lock-in period.";
        }
        // check location
        if (!$player->location->equals($player->village_location)) {
            return "You must be in your village to lock-in for a challenge.";
        }
        // update player data
        $player->locked_challenge = $challenge_id;
        $player->updateData();
        // update challenge
        if ($player->user_id == $challenge_result['challenger_id']) {
            $system->db->query("UPDATE `challenge_requests` SET `challenger_locked` = 1 WHERE `request_id`={$challenge_id}");
            return "Locked in! Your battle will begin shortly.";
        } else {
            $system->db->query("UPDATE `challenge_requests` SET `seat_holder_locked` = 1 WHERE `request_id`={$challenge_id}");
            return "Locked in! Your battle will begin shortly.";
        }
    }

    /**
     * Cancels active challenges. Note that winner is not set, in order to represent that this challenge was not completed
     * and not trigger the new challenge cooldown.
     *
     * @param System $system
     * @param int    $user_id
     * @return void
     */
    public static function cancelUserChallenges(System $system, int $user_id): void {
        $time = time();
        $system->db->query("
            UPDATE `challenge_requests` SET `end_time` = {$time}
            WHERE `end_time` IS NULL AND (`seat_holder_id` = {$user_id} OR `challenger_id` = {$user_id})
        ");
    }

    public static function checkChallengeLock(System $system, User $player) {
        // get challenge data
        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `request_id` = {$player->locked_challenge} LIMIT 1");
        $challenge_result = $system->db->fetch($challenge_result);
        if ($system->db->last_num_rows == 0) {
            $player->locked_challenge = 0;
            $player->updateData();
            return;
        }
        // if challenge finished, clear lock
        if (isset($challenge_result['end_time'])) {
            $player->locked_challenge = 0;
            $player->updateData();
            return;
        }
        // if both players locked begin battle
        if ((int) $challenge_result['challenger_locked'] && (int) $challenge_result['seat_holder_locked']) {
            if ($player->user_id == $challenge_result['challenger_id']) {
                $challenger = $player;
                $seat_holder = User::loadFromId($system, $challenge_result['seat_holder_id']);
                $seat_holder->loadData(User::UPDATE_NOTHING);
            } else {
                $seat_holder = $player;
                $challenger = User::loadFromId($system, $challenge_result['challenger_id']);
                $challenger->loadData(User::UPDATE_NOTHING);
            }
            if ($system->USE_NEW_BATTLES) {
                $battle_id = BattleV2::start($system, $challenger, $seat_holder, Battle::TYPE_CHALLENGE);
            } else {
                $battle_id = Battle::start($system, $challenger, $seat_holder, Battle::TYPE_CHALLENGE);
            }
            $system->db->query("UPDATE `challenge_requests` SET `battle_id` = {$battle_id} WHERE `request_id` = {$player->locked_challenge}");
            return;
        }
        // else if outside of lock period, clear lock and set winner
        $min_lock_time = $challenge_result['start_time'];
        $max_lock_time = $min_lock_time + self::CHALLENGE_LOCK_TIME_MINUTES * 60;
        if (time() > $max_lock_time || time() < $min_lock_time) {
            $player->locked_challenge = 0;
            $player->updateData();
            self::processChallengeEnd($system, $challenge_result['request_id'], $player->user_id, $player);
            return;
        }
    }

    /**
     * @return string
     */
    public static function processChallengeEnd(System $system, int $challenge_id, ?int $winner_id, $player) {
        // get challenge data
        $challenge_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `request_id` = {$challenge_id} LIMIT 1");
        $challenge_result = $system->db->fetch($challenge_result);
        if ($system->db->last_num_rows == 0) {
            $player->locked_challenge = 0;
            $player->updateData();
            return;
        }
        // if already processed
        if (isset($challenge_result['end_time'])) {
            $player->locked_challenge = 0;
            $player->updateData();
            return;
        }
        // get users
        if ($player->user_id == $challenge_result['challenger_id']) {
            $challenger = $player;
            $seat_holder = User::loadFromId($system, $challenge_result['seat_holder_id']);
            $seat_holder->loadData(User::UPDATE_NOTHING);
        } else {
            $seat_holder = $player;
            $challenger = User::loadFromId($system, $challenge_result['challenger_id']);
            $challenger->loadData(User::UPDATE_NOTHING);
        }
        $seat_result = $system->db->query("SELECT * FROM `village_seats` WHERE `seat_id` = {$challenge_result['seat_id']} AND `village_id` = {$player->village->village_id} AND `seat_end` IS NULL LIMIT 1");
        $seat_result = $system->db->fetch($seat_result);
        switch ($winner_id) {
            case $challenge_result['challenger_id']:
                // verify challenger meets requirements
                self::checkSeatRequirements($system, $challenger, $seat_result['seat_type']);
                // remove seat holder from seat
                self::resign($system, $seat_holder);
                // claim seat for challenger
                self::claimSeat($system, $challenger, $seat_result['seat_type']);
                break;
            case null:
            case $challenge_result['seat_holder_id']:
                // no change
                break;
            default:
                break;
        }
        // update challenge
        $time = time();
        $system->db->query("UPDATE `challenge_requests` SET `winner` = " . (!empty($winner_id) ? $winner_id : "NULL") . ", `end_time` = {$time} WHERE `request_id` = {$challenge_id}");
    }

    /**
     * @return VillageSeatDto
     */
    public static function getPlayerSeat(System $system, User $player): VillageSeatDto
    {
        $result = $system->db->query("SELECT * FROM `village_seats` WHERE `user_id` = {$player->user_id} AND `seat_end` IS NULL LIMIT 1");
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
        } else {
            $seat = new VillageSeatDto(
                seat_key: null,
                seat_id: $result['seat_id'],
                user_id: $player->user_id,
                village_id: $result['village_id'],
                seat_type: $result['seat_type'],
                seat_title: $result['seat_title'],
                seat_start: $result['seat_start'],
                user_name: null,
                avatar_link: null,
                is_provisional: $result['is_provisional']
            );
            // check requirement met
            switch ($result['seat_type']) {
                case "kage":
                    if ($player->reputation->rank < self::MIN_KAGE_CLAIM_TIER) {
                        $player->village_seat = $seat;
                        self::resign($system, $player);
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
                    }
                    break;
                case "elder":
                    if ($player->reputation->rank < self::MIN_ELDER_CLAIM_TIER) {
                        $player->village_seat = $seat;
                        self::resign($system, $player);
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
                    }
                    break;
                default:
                    break;
            }
        }
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
            if (empty($proposal['trade_data'])) {
                $trade_data = [];
            } else {
                $trade_data = json_decode($proposal['trade_data'], true);
                $result = $system->db->query("SELECT `name`, `region_id` FROM `regions`");
                $all_regions = $system->db->fetch_all($result);

                $regions_lookup = [];
                foreach ($all_regions as $region) {
                    $regions_lookup[$region['region_id']] = $region['name'];
                }

                $trade_data['offered_regions'] = array_map(function ($region_id) use ($regions_lookup) {
                    return [
                        'region_id' => $region_id,
                        'name' => $regions_lookup[$region_id]
                    ];
                }, $trade_data['offered_regions']);

                $trade_data['requested_regions'] = array_map(function ($region_id) use ($regions_lookup) {
                    return [
                        'region_id' => $region_id,
                        'name' => $regions_lookup[$region_id]
                    ];
                }, $trade_data['requested_regions']);
            }

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
                trade_data: $trade_data,
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
        if ($policy_id == $player->village->policy_id) {
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
        $name = "Change Policy: " . VillagePolicy::POLICY_NAMES[$policy_id];
        $type = self::PROPOSAL_TYPE_CHANGE_POLICY;
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `policy_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, '{$type}', '{$name}', {$policy_id})");

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
                    $result = $system->db->query("SELECT COUNT(DISTINCT `user_id`) as 'kage_count' FROM `village_seats` WHERE `village_id` = {$player->village->village_id} AND `seat_type` = 'kage' AND `user_id` != {$player->user_id} AND `is_provisional` = 0");
                    $result = $system->db->fetch($result);
                    $seat_title = self::getOrdinal($result['kage_count'] + 1) . " " . self::KAGE_NAMES[$player->village->village_id];
                    // update seat
                    $time = time();
                    $system->db->query("UPDATE `village_seats` SET `seat_end` = {$time} WHERE `seat_id` = {$player->village_seat->seat_id}");
                    $system->db->query("INSERT INTO `village_seats`
                    (`user_id`, `village_id`, `seat_type`, `seat_title`, `seat_start`)
                    VALUES ({$player->user_id}, {$player->village->village_id}, 'kage', '{$seat_title}', {$time})");

                    $new_seat_id = $system->db->last_insert_id;

                    // Update existing challenges to new seat
                    $system->db->query("UPDATE `challenge_requests` SET `seat_id`={$new_seat_id} WHERE `seat_id`={$player->village_seat->seat_id}");
                }
            }
        }
        $player->village_seat = self::getPlayerSeat($system, $player);
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
            case self::PROPOSAL_TYPE_CHANGE_POLICY:
                // policy restriction - check not in alliance
                if (VillagePolicy::$POLICY_EFFECTS[$proposal['policy_id']][VillagePolicy::POLICY_RESTRICTION_ALLIANCE_ENABLED] == false) {
                    $relation_type = VillageRelation::RELATION_ALLIANCE;
                    $system->db->query("SELECT * FROM `village_relations` WHERE `relation_end` IS NULL AND `relation_type` = {$relation_type} AND (`village1_id` = {$proposal['village_id']} OR `village2_id` = {$proposal['village_id']})");
                    if ($system->db->last_num_rows > 0) {
                        return "Cannot change policy to " . VillagePolicy::POLICY_NAMES[$proposal['policy_id']] . " while in an active alliance.";
                    }
                }
                // policy restriction - check not in offensive war, village1_id is always the initiating village
                if (VillagePolicy::$POLICY_EFFECTS[$proposal['policy_id']][VillagePolicy::POLICY_RESTRICTION_WAR_ENABLED] == false) {
                    $relation_type = VillageRelation::RELATION_WAR;
                    $system->db->query("SELECT * FROM `village_relations` WHERE `relation_end` IS NULL AND `relation_type` = {$relation_type} AND `village1_id` = {$proposal['village_id']}");
                    if ($system->db->last_num_rows > 0) {
                        return "Cannot change policy to " . VillagePolicy::POLICY_NAMES[$proposal['policy_id']] . " while in an offensive war.";
                    }
                }
                // update village policy
                $system->db->query("UPDATE `villages` SET `policy_id` = {$proposal['policy_id']} WHERE `village_id` = {$proposal['village_id']}");
                // create notifications
                $active_threshold = time() - (NotificationManager::ACTIVE_PLAYER_DAYS_LAST_ACTIVE * 86400);
                $user_ids = $system->db->query("SELECT `user_id` FROM `users` WHERE `village` = '{$player->village->name}' AND `last_login` > {$active_threshold}");
                $user_ids = $system->db->fetch_all($user_ids);
                $notification_message = "New village policy: " . VillagePolicy::POLICY_NAMES[$proposal['policy_id']];
                foreach ($user_ids as $user) {
                    $new_notification = new NotificationDto(
                        type: NotificationManager::NOTIFICATION_POLICY_CHANGE,
                        message: $notification_message,
                        user_id: $user['user_id'],
                        created: time(),
                        expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_POLICY * 86400),
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                }
                // update policy log
                $system->db->query("UPDATE `policy_logs` SET `end_time` = {$time} WHERE `village_id` = {$proposal['village_id']} AND `end_time` IS NULL");
                $system->db->query("INSERT INTO `policy_logs` (`village_id`, `policy_id`, `start_time`) VALUES ({$proposal['village_id']}, {$proposal['policy_id']}, {$time})");
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Set village policy: " . VillagePolicy::POLICY_NAMES[$proposal['policy_id']] . ".";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case self::PROPOSAL_TYPE_DECLARE_WAR:
                // check neutral
                if (!$player->village->isNeutral($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to declare war.";
                }
                // check diplomatic restriction
                $policy_result = $system->db->query("SELECT `policy_id` FROM `villages` WHERE `village_id` = {$proposal['village_id']}");
                $policy_result = $system->db->fetch($policy_result);
                if (VillagePolicy::$POLICY_EFFECTS[$policy_result['policy_id']][VillagePolicy::POLICY_RESTRICTION_WAR_ENABLED] == false) {
                    return "Cannot declare war due to policy restriction.";
                }
                // check war cooldown
                $relation_type = VillageRelation::RELATION_WAR;
                $last_war = $system->db->query("SELECT * FROM `village_relations` WHERE `relation_end` IS NOT NULL AND `relation_type` = {$relation_type}
                AND (`village1_id` = {$proposal['village_id']} OR `village2_id` = {$proposal['village_id']})
                AND (`village1_id` = {$proposal['target_village_id']} OR `village2_id` = {$proposal['target_village_id']})
                ORDER BY `relation_end` DESC LIMIT 1");
                $last_war = $system->db->fetch($last_war);
                if ($system->db->last_num_rows > 0) {
                    $war_cooldown = ($last_war['relation_end'] + self::WAR_COOLDOWN_DAYS * 86400) - time();
                    if ($war_cooldown > 0) {
                        $message = "You must wait another " . $system->time_remaining($war_cooldown) . " before declaring war on this village!";
                        return $message;
                    }
                }
                // update relation
                self::setNewRelations($system, $proposal['village_id'], $proposal['target_village_id'], VillageRelation::RELATION_WAR, $proposal['type']);
                // clear active proposals
                self::clearDiplomaticProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                self::clearTradeProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Declared war on " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                break;
            case self::PROPOSAL_TYPE_OFFER_PEACE:
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
            case self::PROPOSAL_TYPE_OFFER_ALLIANCE:
                // check neutral
                if (!$player->village->isNeutral($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to offer alliance.";
                }
                // neither village can have an existing ally
                $alliance_type = VillageRelation::RELATION_ALLIANCE;
                $system->db->query("SELECT * FROM `village_relations`
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
            case self::PROPOSAL_TYPE_BREAK_ALLIANCE:
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
            case self::PROPOSAL_TYPE_ACCEPT_PEACE:
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
                // if village occupied by enemy, return to owner's control
                $system->db->query("UPDATE `region_locations`
                    INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
                    SET `region_locations`.`occupying_village_id` = NULL
                    WHERE `regions`.`village` = {$proposal['target_village_id']} AND `region_locations`.`occupying_village_id` = {$proposal['village_id']}");
                $system->db->query("UPDATE `region_locations`
                    INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
                    SET `region_locations`.`occupying_village_id` = NULL
                    WHERE `regions`.`village` = {$proposal['village_id']} AND `region_locations`.`occupying_village_id` = {$proposal['target_village_id']}");
                break;
            case self::PROPOSAL_TYPE_ACCEPT_ALLIANCE:
                // check neutral
                if (!$player->village->isNeutral($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to form alliance.";
                }
                // check diplomatic restriction
                $policy_result = $system->db->query("SELECT `policy_id` FROM `villages` WHERE `village_id` = {$proposal['village_id']}");
                $policy_result = $system->db->fetch($policy_result);
                if (VillagePolicy::$POLICY_EFFECTS[$policy_result['policy_id']][VillagePolicy::POLICY_RESTRICTION_ALLIANCE_ENABLED] == false) {
                    return "Cannot form alliance due to policy restriction.";
                }
                $policy_result = $system->db->query("SELECT `policy_id` FROM `villages` WHERE `village_id` = {$proposal['target_village_id']}");
                $policy_result = $system->db->fetch($policy_result);
                if (VillagePolicy::$POLICY_EFFECTS[$policy_result['policy_id']][VillagePolicy::POLICY_RESTRICTION_ALLIANCE_ENABLED] == false) {
                    return "Cannot form alliance due to policy restriction.";
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
            case self::PROPOSAL_TYPE_FORCE_PEACE:
                // check at war
                if (!$player->village->isEnemy($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Proposal canceled. Villages must be neutral to form alliance.";
                }
                // update relation
                self::setNewRelations($system, $proposal['village_id'], $proposal['target_village_id'], VillageRelation::RELATION_NEUTRAL, $proposal['type']);
                // clear active proposals
                self::clearDiplomaticProposals($system, $proposal['village_id'], $proposal['target_village_id'], $proposal['proposal_id']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                $message = "Forced peace with " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                // if village occupied by enemy, return to owner's control
                $system->db->query("UPDATE `region_locations`
                    INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
                    SET `region_locations`.`occupying_village_id` = NULL
                    WHERE `regions`.`village` = {$proposal['target_village_id']} AND `region_locations`.`occupying_village_id` = {$proposal['village_id']}");
                $system->db->query("UPDATE `region_locations`
                    INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
                    SET `region_locations`.`occupying_village_id` = NULL
                    WHERE `regions`.`village` = {$proposal['village_id']} AND `region_locations`.`occupying_village_id` = {$proposal['target_village_id']}");
                break;
            case self::PROPOSAL_TYPE_OFFER_TRADE:
                // check is ally
                if (!$player->village->isAlly($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Villages must be allied in order to trade!";
                }
                // check has resources / regions
                $trade_data = json_decode($proposal['trade_data'], true);
                $is_valid_trade = self::checkTradeValid($system, $player->village->village_id, $proposal['target_village_id'], $trade_data['offered_resources'], $trade_data['offered_regions'], $trade_data['requested_resources'], $trade_data['requested_regions']);
                if (!$is_valid_trade) {
                    return "One or either village does not have the necessary items to complete the trade.";
                }
                // create new proposal for target village
                $name = "Accept Trade: " . VillageManager::VILLAGE_NAMES[$player->village->village_id];
                $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`, `trade_data`) VALUES ({$proposal['target_village_id']}, {$player->user_id}, {$time}, 'accept_trade', '{$name}', {$player->village->village_id}, '{$proposal['trade_data']}')");
                // create notification
                self::createProposalNotification($system, $proposal['target_village_id'], NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                return "Sent trade offer to " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
                break;
            case self::PROPOSAL_TYPE_ACCEPT_TRADE:
                // check at war
                if ($player->village->isEnemy($proposal['target_village_id'])) {
                    $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'canceled' WHERE `proposal_id` = {$proposal['proposal_id']}");
                    return "Villages must be at peace in order to trade!";
                }
                // check has resources / regions
                $trade_data = json_decode($proposal['trade_data'], true);
                $is_valid_trade = self::checkTradeValid($system, $proposal['target_village_id'], $player->village->village_id, $trade_data['offered_resources'], $trade_data['offered_regions'], $trade_data['requested_resources'], $trade_data['requested_regions']);
                if (!$is_valid_trade) {
                    return "One or either village does not have the necessary items to complete the trade.";
                }
                // handle region and resource change
                self::handleTradeCompletion($system, $proposal['target_village_id'], $player->village->village_id, $trade_data['offered_resources'], $trade_data['offered_regions'], $trade_data['requested_resources'], $trade_data['requested_regions']);
                // update proposal
                $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` = 'passed' WHERE `proposal_id` = {$proposal['proposal_id']}");
                // create notification
                self::createProposalNotification($system, $proposal['village_id'], NotificationManager::NOTIFICATION_PROPOSAL_PASSED, $proposal['name']);
                return "Accepted trade offer from " . VillageManager::VILLAGE_NAMES[$proposal['target_village_id']] . "!";
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
                $user->reputation->subtractRep($vote['rep_adjustment'], UserReputation::ACTIVITY_TYPE_UNCAPPED);
                $user->updateData();
            }
        }

        if ($rep_adjustment > 0) {
            $player->reputation->addRep($rep_adjustment, UserReputation::ACTIVITY_TYPE_UNCAPPED);
            $message .= "\n You have gained {$rep_adjustment} Reputation!";
            $player->updateData();
        } else if ($rep_adjustment < 0) {
            $player->reputation->subtractRep($rep_adjustment, UserReputation::ACTIVITY_TYPE_UNCAPPED);
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
            $regions = $system->db->query("SELECT `name`, `region_id` FROM `regions` WHERE `village` = {$i}");
            $regions = $system->db->fetch_all($regions);
            // get supply points
            $supply_points = [];
            $resource_counts = $system->db->query("SELECT `region_locations`.`resource_id`, COUNT(`region_locations`.`resource_id`) AS `supply_points`
                FROM `region_locations`
                INNER JOIN `regions` ON `region_locations`.`region_id` = `regions`.`region_id`
                WHERE (`regions`.`village` = {$i} AND `region_locations`.`occupying_village_id` IS NULL)
                OR `region_locations`.`occupying_village_id` = {$i}
                GROUP BY `region_locations`.`resource_id`");
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
        $type = self::PROPOSAL_TYPE_DECLARE_WAR;
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = '{$type}' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check no alliance proposal outgoing
        $type = self::PROPOSAL_TYPE_OFFER_ALLIANCE;
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = '{$type}' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a proposal to form alliance.";
        }
        // only allow if neutral
        if (!$player->village->isNeutral($target_village_id)) {
            return "Village must be neutral to declare war.";
        }
        // check war cooldown
        $relation_type = VillageRelation::RELATION_WAR;
        $last_war = $system->db->query("SELECT * FROM `village_relations` WHERE `relation_end` IS NOT NULL AND `relation_type` = {$relation_type}
        AND (`village1_id` = {$player->village->village_id} OR `village2_id` = {$player->village->village_id})
        AND (`village1_id` = {$target_village_id} OR `village2_id` = {$target_village_id})
        ORDER BY `relation_end` DESC LIMIT 1");
        $last_war = $system->db->fetch($last_war);
        if ($system->db->last_num_rows > 0) {
            $war_cooldown = ($last_war['relation_end'] + self::WAR_COOLDOWN_DAYS * 86400) - time();
            if ($war_cooldown > 0) {
                $message = "You must wait another " . $system->time_remaining($war_cooldown) . " before declaring war on this village!";
                return $message;
            }
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
        $type = self::PROPOSAL_TYPE_DECLARE_WAR;
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, '{$type}', '{$name}', {$target_village_id})");

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
        // check duration
        if ($player->village->relations[$target_village_id]->relation_start + self::FORCE_PEACE_MINIMUM_DURATION_DAYS * 86400 < time()) {
            $type = self::PROPOSAL_TYPE_FORCE_PEACE;
            $name = "Force Peace: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        } else {
            $type = self::PROPOSAL_TYPE_OFFER_PEACE;
            $name = "Offer Peace: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = '{$type}' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // also check at war
        if (!$player->village->isEnemy($target_village_id)) {
            return "Village must be at war to offer peace.";
        }
        // also check not pending on receiving village
        $check_type = self::PROPOSAL_TYPE_ACCEPT_PEACE;
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$target_village_id} AND `target_village_id` = {$player->village->village_id} AND `type` = '{$check_type}' LIMIT 1");
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
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, '{$type}', '{$name}', {$target_village_id})");

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
        $type = self::PROPOSAL_TYPE_OFFER_ALLIANCE;
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = '{$type}' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending proposal of the same type.";
        }
        // check no war proposal outgoing
        $type = self::PROPOSAL_TYPE_DECLARE_WAR;
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND `type` = '{$type}' LIMIT 1");
        $last_change = $system->db->fetch($query);
        if ($system->db->last_num_rows > 0) {
            return "There is already a proposal to declare war.";
        }
        // check not at war or allied
        if (!$player->village->isNeutral($target_village_id)) {
            return "Village must be neutral to form alliance.";
        }
        // also check not pending on receiving village
        $type = self::PROPOSAL_TYPE_ACCEPT_ALLIANCE;
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$target_village_id} AND `target_village_id` = {$player->village->village_id} AND `type` = '{$type}' LIMIT 1");
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
        $result = $system->db->query("SELECT * FROM `village_relations`
            WHERE `relation_type` = {$alliance_type}
            AND `relation_end` IS NULL
            AND ((`village1_id` = {$player->village->village_id} OR `village1_id` = {$target_village_id})
            OR (`village2_id` = {$player->village->village_id} OR `village2_id` = {$target_village_id})) LIMIT 1");
        if ($system->db->last_num_rows > 0) {
            return "Neither village can be in an existing Alliance.";
        }
        // insert into DB
        $name = "Form Alliance: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        $type = self::PROPOSAL_TYPE_OFFER_ALLIANCE;
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, '{$type}', '{$name}', {$target_village_id})");

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
        $type = self::PROPOSAL_TYPE_BREAK_ALLIANCE;
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, '{$type}', '{$name}', {$target_village_id})");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

     /**
     * @return string
     */
    public static function createTradeProposal(System $system, User $player, int $target_village_id, array $offered_resources, array $offered_regions, array $requested_resources, array $requested_regions): string {
        // check player permissions
        $seat = $player->village_seat;
        if ($seat->seat_type != "kage") {
            return "You do not meet the seat requirements.";
        }
        // check not at war
        if ($player->village->isEnemy($target_village_id)) {
            return "Villages must be at peace in order to trade!";
        }
        // check no pending proposal of same type
        $query = $system->db->query("SELECT * FROM `proposals` WHERE `end_time` IS NULL AND `village_id` = {$player->village->village_id} AND (`type` = 'offer_trade' OR `type` = 'accept_trade') LIMIT 1");
        if ($system->db->last_num_rows > 0) {
            return "There is already a pending trade request.";
        }
        // check has resources / regions
        $is_valid_trade = self::checkTradeValid($system, $player->village->village_id, $target_village_id, $offered_resources, $offered_regions, $requested_resources, $requested_regions);
        if (!$is_valid_trade) {
            return "One or either village does not have the necessary items to complete the trade.";
        }
        // insert into DB
        $trade_data = [
            "offered_resources" => $offered_resources,
            "offered_regions" => $offered_regions,
            "requested_resources" => $requested_resources,
            "requested_regions" => $requested_regions
        ];
        $trade_data = json_encode($trade_data);
        $time = time();
        $name = "Offer Trade: " . VillageManager::VILLAGE_NAMES[$target_village_id];
        $type = self::PROPOSAL_TYPE_OFFER_TRADE;
        $system->db->query("INSERT INTO `proposals` (`village_id`, `user_id`, `start_time`, `type`, `name`, `target_village_id`, `trade_data`) VALUES ({$player->village->village_id}, {$player->user_id}, {$time}, '{$type}', '{$name}', {$target_village_id}, '{$trade_data}')");

        // create notification
        self::createProposalNotification($system, $player->village->village_id, NotificationManager::NOTIFICATION_PROPOSAL_CREATED, $name);

        return "Proposal created!";
    }

    private static function checkTradeValid(System $system, int $village1_id, int $village2_id, array $offered_resources, array $offered_regions, array $requested_resources, array $requested_regions): bool {
        // get regions
        $village1_regions = $system->db->query("SELECT `region_id` FROM `regions` WHERE `village` = {$village1_id}");
        $village1_regions = $system->db->fetch_all($village1_regions);
        $village2_regions = $system->db->query("SELECT `region_id` FROM `regions` WHERE `village` = {$village2_id}");
        $village2_regions = $system->db->fetch_all($village2_regions);
        // get resources
        $village1_resources = self::getResources($system, $village1_id);
        $village2_resources = self::getResources($system, $village2_id);
        // check valid
        foreach ($offered_resources as $resource) {
            if ($resource['count'] > self::MAX_TRADE_RESOURCE_TYPE) {
                return false;
            }
            $resource_id = $resource['resource_id'];
            $required_count = $resource['count'];
            if (!isset($village1_resources[$resource_id]) || $village1_resources[$resource_id] < $required_count) {
                return false;
            }
        }
        foreach ($requested_resources as $resource) {
            if ($resource['count'] > self::MAX_TRADE_RESOURCE_TYPE) {
                return false;
            }
            $resource_id = $resource['resource_id'];
            $required_count = $resource['count'];
            if (!isset($village2_resources[$resource_id]) || $village2_resources[$resource_id] < $required_count) {
                return false;
            }
        }
        $village_region_ids = array_column($village1_regions, 'region_id');
        foreach ($offered_regions as $offered_region_id) {
            if (!in_array($offered_region_id, $village_region_ids)) {
                return false;
            }
        }
        $village_region_ids = array_column($village2_regions, 'region_id');
        foreach ($requested_regions as $requested_region_id) {
            if (!in_array($requested_region_id, $village_region_ids)) {
                return false;
            }
        }
        return true;
    }

    private static function handleTradeCompletion(System $system, int $village1_id, int $village2_id, array $offered_resources, array $offered_regions, array $requested_resources, array $requested_regions)
    {
        // update regions
        foreach ($offered_regions as $region) {
            $system->db->query("UPDATE `regions` SET `village` = {$village2_id} WHERE `region_id` = {$region}");
        }
        foreach ($requested_regions as $region) {
            $system->db->query("UPDATE `regions` SET `village` = {$village1_id} WHERE `region_id` = {$region}");
        }
        // update resources
        $villages_result = $system->db->query("SELECT * FROM `villages` WHERE `village_id` = {$village1_id} OR `village_id`={$village2_id} LIMIT 2");
        $villages = $system->db->fetch_all($villages_result, 'village_id');
        $village1 = new Village($system, village_row: $villages[$village1_id]);
        $village2 = new Village($system, village_row: $villages[$village2_id]);
        foreach ($offered_resources as $resource) {
            $village1->subtractResource($resource['resource_id'], $resource['count']);
            $system->db->query("INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village1->village_id}, {$resource['resource_id']}, " . VillageManager::RESOURCE_LOG_TRADE_LOSS . ", {$resource['count']}, " . time() . ")");
            $village2->addResource($resource['resource_id'], $resource['count']);
            $system->db->query("INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village2->village_id}, {$resource['resource_id']}, " . VillageManager::RESOURCE_LOG_TRADE_GAIN . ", {$resource['count']}, " . time() . ")");
        }
        foreach ($requested_resources as $resource) {
            $village2->subtractResource($resource['resource_id'], $resource['count']);
            $system->db->query("INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village2->village_id}, {$resource['resource_id']}, " . VillageManager::RESOURCE_LOG_TRADE_LOSS . ", {$resource['count']}, " . time() . ")");
            $village1->addResource($resource['resource_id'], $resource['count']);
            $system->db->query("INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village1->village_id}, {$resource['resource_id']}, " . VillageManager::RESOURCE_LOG_TRADE_GAIN . ", {$resource['count']}, " . time() . ")");
        }
        $village1->updateResources(true);
        $village2->updateResources(true);
    }

    private static function clearDiplomaticProposals(System $system, int $village1_id, int $village2_id, int $proposal_id) {
        $time = time();
        $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` =  'canceled' WHERE
            ((`village_id` = {$village1_id} OR `village_id` = {$village2_id})
            AND (`target_village_id` = {$village1_id} OR `target_village_id` = {$village2_id}))
            AND `type` IN ('offer_alliance', 'offer_peace', 'accept_alliance', 'accept_peace', 'declare_war', 'break_alliance', 'force_peace')
            AND `proposal_id` != {$proposal_id}");
    }
    private static function clearTradeProposals(System $system, int $village1_id, int $village2_id, int $proposal_id)
    {
        $time = time();
        $system->db->query("UPDATE `proposals` SET `end_time` = {$time}, `result` =  'canceled' WHERE
            ((`village_id` = {$village1_id} OR `village_id` = {$village2_id})
            AND (`target_village_id` = {$village1_id} OR `target_village_id` = {$village2_id}))
            AND `type` IN ('offer_trade', 'accept_trade')
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
        $village_users_result = $system->db->query("SELECT `user_id`, `blocked_notifications` FROM `users` WHERE (`village` = '{$initator_village_name}' OR `village` = '{$recipient_village_name}') AND `last_login` > {$active_threshold}");
        $village_users = $system->db->fetch_all($village_users_result);
        // create notifcations
        $message;
        $notification_type;
        switch ($proposal_type) {
            case self::PROPOSAL_TYPE_BREAK_ALLIANCE:
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_END_ALLIANCE;
                $message = VillageManager::VILLAGE_NAMES[$initiator_village_id] . " has ended an Alliance with " . VillageManager::VILLAGE_NAMES[$recipient_village_id] . "!";
                break;
            case self::PROPOSAL_TYPE_ACCEPT_PEACE:
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_END_WAR;
                $message = VillageManager::VILLAGE_NAMES[$recipient_village_id] . " has negotiated peace with " . VillageManager::VILLAGE_NAMES[$initiator_village_id] . "!";
                break;
            case self::PROPOSAL_TYPE_ACCEPT_ALLIANCE:
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_ALLIANCE;
                $message = VillageManager::VILLAGE_NAMES[$recipient_village_id] . " has formed an Alliance with " . VillageManager::VILLAGE_NAMES[$initiator_village_id] . "!";
                break;
            case self::PROPOSAL_TYPE_DECLARE_WAR:
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_WAR;
                $message = VillageManager::VILLAGE_NAMES[$initiator_village_id] . " has declared War on " . VillageManager::VILLAGE_NAMES[$recipient_village_id] . "!";
                break;
            case self::PROPOSAL_TYPE_FORCE_PEACE:
                $notification_type = NotificationManager::NOTIFICATION_DIPLOMACY_END_WAR;
                $message = VillageManager::VILLAGE_NAMES[$initiator_village_id] . " has forced peace with " . VillageManager::VILLAGE_NAMES[$recipient_village_id] . "!";
                break;
            default:
                break;
        }
        foreach ($village_users as $user) {
            $blockedNotifManager = BlockedNotificationManager::fromDb(
                system: $system,
                blocked_notifications_string: $user['blocked_notifications']
            );
            if($blockedNotifManager->notificationBlocked(notification_type: NotificationManager::NOTIFICATION_DIPLOMACY)) {
                continue;
            }

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

    private static function checkSeatRequirements(System $system, User $player, string $seat_type, bool $is_challenge = false): bool {
        switch ($seat_type) {
            case 'kage':
                if ($player->rank_num < 4) {
                    return false;
                }
                if ($is_challenge) {
                    if ($player->reputation->rank < self::MIN_KAGE_CHALLENGE_TIER) {
                        return false;
                    }
                } else {
                    if ($player->reputation->rank < self::MIN_KAGE_CLAIM_TIER) {
                        return false;
                    }
                }
                return true;
                break;
            case 'elder':
                if ($player->rank_num < 4) {
                    return false;
                }
                if ($is_challenge) {
                    if ($player->reputation->rank < self::MIN_ELDER_CHALLENGE_TIER) {
                        return false;
                    }
                } else {
                    if ($player->reputation->rank < self::MIN_ELDER_CLAIM_TIER) {
                        return false;
                    }
                }
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * @return array
     */
    public static function getKageRecord(System $system, int $village_id): array
    {
        $timePerUser = [];
        $kageRecords = [];
        $result = $system->db->query("SELECT `village_seats`.*, `users`.`user_name`
            FROM `village_seats`
            INNER JOIN `users` ON `users`.`user_id` = `village_seats`.`user_id`
            WHERE `village_id` = {$village_id}
            AND `seat_type` = 'kage'
            AND `is_provisional` = 0
            ORDER BY `seat_start` ASC
        ");
        $result = $system->db->fetch_all($result);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $user_id = $row['user_id'];
                $start = $row['seat_start'];
                $end = !empty($row['seat_end']) ? $row['seat_end'] : time();

                $time_held = $end - $start;

                // Add to running total
                if (isset($timePerUser[$user_id])) {
                    $timePerUser[$user_id] += $time_held;
                }
                // Start running total
                else {
                    $timePerUser[$user_id] = $time_held;
                }

                // If not set, add to records
                if (!isset($kageRecords[$user_id])) {
                    $kageRecords[$user_id] = $row;
                }
                // Otherwise get newest end time
                else {
                    $kageRecords[$user_id]['seat_end'] = $end;
                }
            }

            // Add total time to records
            foreach ($timePerUser as $key => $value) {
                $kageRecords[$key]['time_held'] = $value;
            }
        }

        // Format
        foreach ($kageRecords as &$record) {
            $time_held = System::timeRemaining($record['time_held'], format: 'days', include_seconds: false);
            $seat_start = date("M jS Y", $record['seat_start']);
            $record['time_held'] = $time_held;
            $record['seat_start'] = $seat_start;
        }

        return $kageRecords;
    }
}