<?php

require_once __DIR__ . "/WarAction.php";

class WarManager {
    const BASE_TOWN_RESOURCE_PRODUCTION = 15; // 2x at full stability, 26/hour at 75%
    const BASE_CASTLE_RESOURCE_PRODUCTION = 20; // 2x at full stability, 35/hour at 75%
    const VILLAGE_BASE_RESOURCE_PRODUCTION = 75;
    const BASE_CARAVAN_TIME_MS = 300000; // 5 minute travel time
    const CARAVAN_TIMER_HOURS = 6; // 24m average caravan spawn timer, so 19 minute average downtime
    const BASE_TOWN_REGEN_PER_MINUTE = 150; // 2x at full stability, Chuu max raid damage at 75% stability
    const BASE_CASTLE_REGEN_PER_MINUTE = 300; // 2x at full stability, Jon max raid damage at 75% stability
    const TOWN_REGEN_SHARE_PERCENT = 100; // 2 villages + base, 1200/min max at 2x villages 100% stability
    const BASE_REBELLION_DAMAGE_PER_MINUTE = 150; // 2x at -100 stability, Chuu max raid damage at -75% stability
    const BASE_TOWN_HEALTH = 5000;
    const BASE_CASTLE_HEALTH = 15000;
    const BASE_TOWN_DEFENSE = 50;
    const BASE_CASTLE_DEFENSE = 75;
    const BASE_CASTLE_STABILITY = 75;
    const BASE_TOWN_STABILITY = 0;
    const OCCUPIED_TOWN_STABILITY_PENALTY = 100;
    const BASE_STABILITY_SHIFT_PER_HOUR = 1;
    const BASE_DEFENSE_SHIFT_PER_HOUR = 1;
    const STABILITY_DEFENSE_SHIFT_INCREMENT = 20; // for every 20 points gap between current and resting point, shift 1 point
    const MAX_STABILITY_DEFENSE_SHIFT = 5;
    const MAX_STABILITY = 100;
    const MIN_STABILITY = -100;
    const HOME_REGION_STABILITY_BONUS = 75;
    const INITIAL_LOCATION_CAPTURE_HEALTH_PERCENT = 50;
    const INITIAL_LOCATION_CAPTURE_DEFENSE = 25;
    const INITIAL_LOCATION_CAPTURE_STABILITY = 25;

    // region_regen_cron.php must run on matching cadence to this interval
    // if you change this value, change the cron job config to run region_regen_cron.php at whatever the new value is
    const REGEN_INTERVAL_MINUTES = 5;

    const RESOURCE_MATERIALS = 1;
    const RESOURCE_FOOD = 2;
    const RESOURCE_WEALTH = 3;

    const RESOURCE_NAMES = [
        self::RESOURCE_MATERIALS => 'materials',
        self::RESOURCE_FOOD => 'food',
        self::RESOURCE_WEALTH => 'wealth',
        /*4 => 'adamantine',
        5 => 'quicksilver',
        6 => 'elderwood',
        7 => 'iron_sand',
        8 => 'obsidian',*/
    ];
    const PATROL_NAMES = [
        1 => "Patrol",
        2 => "Veteran Patrol",
        3 => "Elite Patrol",
        4 => "War Hero",
    ];
    const PATROL_CHANCE = [
        1 => 65,
        2 => 25,
        3 => 10,
        4 => 0,
    ];
    const PATROL_RESPAWN_TIME = 600;
    const BASE_LOOT_CAPACITY = 50;
    const MAX_PATROL_TIER = 3;
    const YEN_PER_RESOURCE = 20;
    const RESOURCES_PER_STAT = 10;
    const RESOURCES_PER_REPUTATION = 10;

    const REGION_ORIGINAL_VILLAGE = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 1,
        7 => 1,
        8 => 1,
        9 => 2,
        10 => 2,
        11 => 2,
        12 => 3,
        13 => 3,
        14 => 3,
        15 => 4,
        16 => 4,
        17 => 4,
        18 => 5,
        19 => 5,
        20 => 5
    ];

    const INITIAL_VICTORY_SCORE_PERCENT_REQUIRED = 100; // 2:1 ratio of war score for victory
    const FINAL_VICTORY_SCORE_PERCENT_REQUIRED = 50; // 1.5:1 ratio of war score for victory
    const MIN_WAR_DURATION_DAYS = 3;
    const MAX_WAR_DURATION_DAYS = 7;

    private System $system;
    private User $user;

    /** @var VillageRelation[][] */
    public array $village_relations_by_village_ids;

    public function __construct(System $system, User $user)
    {
        $this->system = $system;
        $this->user = $user;

        $this->village_relations_by_village_ids = VillageManager::getAllRelationsByVillageIds($system);
    }

    /**
     * @throws RuntimeException
     */
    public function getWarActionById(int $war_action_id): WarAction {
        $war_action_result = $this->system->db->query("SELECT * FROM `war_actions` WHERE `war_action_id` = {$war_action_id} LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("War Action not found");
        }
        $war_action_result = $this->system->db->fetch($war_action_result);
        $war_action = new WarAction($this->system, $this->user, $war_action_result);
        return $war_action;
    }

    /**
     * @throws RuntimeException
     */
    public function processWarAction(int $war_action_id, ?int $status = null): string
    {
        $message = '';
        $war_action = $this->getWarActionById($war_action_id);
        if (!empty($status)) {
            $war_action->status = $status;
        }
        // check war_action target valid
        switch ($war_action->status) {
            case WarAction::WAR_ACTION_ACTIVE:
                if (!$this->checkWarActionValid($war_action)) {
                    $message = "War Action no longer valid!";
                    $this->cancelWarAction();
                    break;
                }
                $message = $war_action->progressActiveWarAction();
                break;
            case WarAction::WAR_ACTION_COMPLETE:
                $message = $war_action->handleCompletion();
                $war_action->updateData();
                break;
            case WarAction::WAR_ACTION_FAILED:
                $war_action->handleFailure();
                $war_action->updateData();
                break;
            default:
                throw new RuntimeException("Invalid war_action status!");
        }
        return $message;
    }

    /**
     * @throws RuntimeException
     */
    public function beginWarAction(int $war_action_type, int $target_id, MapNPC $npc = null) {
        if ($this->user->rank_num <= 2) {
            throw new RuntimeException("Invalid rank!");
        }
        if ($this->user->battle_id > 0) {
            throw new RuntimeException("You are currently in battle!");
        }
        if ($war_action_type != WarAction::WAR_ACTION_LOOT) {
            $target = $this->system->db->query("SELECT `region_locations`.*
            FROM `region_locations`
            WHERE `region_location_id` = {$target_id} LIMIT 1");
            if ($this->system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid war_action target!");
            }
            $target = $this->system->db->fetch($target);
            // must be at target location
            $target_location = new TravelCoords($target['x'], $target['y'], $target['map_id']);
            if ($this->user->location->toString() != $target_location->toString()) {
                throw new RuntimeException("Invalid war_action target!");
            }
        }

        switch ($war_action_type) {
            case WarAction::WAR_ACTION_INFILTRATE:
                // must be neutral or at war
                if ($this->user->village->relations[$target['occupying_village_id']]->relation_type != VillageRelation::RELATION_NEUTRAL && $this->user->village->relations[$target['occupying_village_id']]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid war_action target!");
                }
                WarAction::beginWarAction($this->system, $this->user, $target_id, $war_action_type, $target['occupying_village_id']);
                break;
            case WarAction::WAR_ACTION_REINFORCE:
                // must be owned or ally
                if ($target['occupying_village_id'] != $this->user->village->village_id && $this->user->village->relations[$target['occupying_village_id']]->relation_type != VillageRelation::RELATION_ALLIANCE) {
                    throw new RuntimeException("Invalid war_action target!");
                }
                WarAction::beginWarAction($this->system, $this->user, $target_id, $war_action_type, $target['occupying_village_id']);
                break;
            case WarAction::WAR_ACTION_RAID:
                // must be at war
                if ($this->user->village->relations[$target['occupying_village_id']]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid war_action target!");
                }
                WarAction::beginWarAction($this->system, $this->user, $target_id, $war_action_type, $target['occupying_village_id']);
                break;
            case WarAction::WAR_ACTION_LOOT:
                // must be neutral or at war
                if ($this->user->village->relations[$npc->village_id]->relation_type != VillageRelation::RELATION_NEUTRAL && $this->user->village->relations[$npc->village_id]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid war_action target!");
                }
                WarAction::beginWarAction($this->system, $this->user, $npc->id, $war_action_type, $npc->village_id);
                break;
            default:
                throw new RuntimeException("Invalid war_action type!");
        }
    }

    /**
     * @throws RuntimeException
     */
    public function checkWarActionValid(WarAction $war_action): bool {
        if ($this->user->battle_id > 0) {
            return false;
        }
        if ($war_action->type != WarAction::WAR_ACTION_LOOT) {
            $target = $this->system->db->query("SELECT `region_locations`.*
            FROM `region_locations`
            WHERE `region_location_id` = {$war_action->target_id} LIMIT 1");
            if ($this->system->db->last_num_rows == 0) {
                return false;
            }
            $target = $this->system->db->fetch($target);
            // must be at target location
            $target_location = new TravelCoords($target['x'], $target['y'], $target['map_id']);
            if ($this->user->location->toString() != $target_location->toString()) {
                return false;
            }
        }
        switch ($war_action->type) {
            case WarAction::WAR_ACTION_INFILTRATE:
                // must be neutral or at war
                if ($this->user->village->isAlly($target['occupying_village_id'])) {
                    return false;
                }
                break;
            case WarAction::WAR_ACTION_REINFORCE:
                // must be owned or ally
                if ($target['occupying_village_id'] != $this->user->village->village_id && !$this->user->village->isAlly($target['occupying_village_id'])) {
                    return false;
                }
                break;
            case WarAction::WAR_ACTION_RAID:
                // must be at war
                if (!$this->user->village->isEnemy($target['occupying_village_id'])) {
                    return false;
                }
                break;
            case WarAction::WAR_ACTION_LOOT:
                // must be neutral or at war
                if ($this->user->village->isAlly($war_action->target_village)) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    public function cancelWarAction() {
        WarAction::cancelWarAction($this->system, $this->user);
    }

    /**
     * @return array
     */
    public function getValidWarActions(bool $for_display = false): array {
        $valid_war_actions = [];

        // exit if war disabled
        if (!$this->system->war_enabled) {
            return $valid_war_actions;
        }
        // exit if rank below Chuunin
        if ($this->user->rank_num <= 2) {
            return $valid_war_actions;
        }

        // get region location where location = player location
        $target_location = $this->system->db->query("SELECT `region_locations`.*
            FROM `region_locations`
            WHERE `x` = {$this->user->location->x}
            AND `y` = {$this->user->location->y}
            AND `map_id` = {$this->user->location->map_id} LIMIT 1");
        if ($this->system->db->last_num_rows > 0) {
            $target_location = $this->system->db->fetch($target_location);
            // check each war action type, return array
            if ($target_location['occupying_village_id'] == $this->user->village->village_id) {
                $valid_war_actions = [
                    WarAction::WAR_ACTION_REINFORCE => System::unSlug(WarAction::WAR_ACTION_TYPE[WarAction::WAR_ACTION_REINFORCE]),
                ];
                if ($for_display) {
                    $health_gain = floor($this->user->level / 2);
                    $valid_war_actions[WarAction::WAR_ACTION_REINFORCE] .= "<br><span class='reinforce_button_text'>{$health_gain} health</span>";
                }
            } else {
                switch ($this->user->village->relations[$target_location['occupying_village_id']]->relation_type) {
                    case VillageRelation::RELATION_NEUTRAL:
                        $valid_war_actions = [
                            WarAction::WAR_ACTION_INFILTRATE => System::unSlug(WarAction::WAR_ACTION_TYPE[WarAction::WAR_ACTION_INFILTRATE]),
                        ];
                        break;
                    case VillageRelation::RELATION_ALLIANCE:
                        $valid_war_actions = [
                            WarAction::WAR_ACTION_REINFORCE => System::unSlug(WarAction::WAR_ACTION_TYPE[WarAction::WAR_ACTION_REINFORCE])
                        ];
                        if ($for_display) {
                            $health_gain = floor($this->user->level / 2);
                            $valid_war_actions[WarAction::WAR_ACTION_REINFORCE] .= "<br><span class='reinforce_button_text'>{$health_gain} health</span>";
                        }
                        break;
                    case VillageRelation::RELATION_WAR:
                        $valid_war_actions = [
                            WarAction::WAR_ACTION_INFILTRATE => System::unSlug(WarAction::WAR_ACTION_TYPE[WarAction::WAR_ACTION_INFILTRATE]),
                            WarAction::WAR_ACTION_RAID => System::unSlug(WarAction::WAR_ACTION_TYPE[WarAction::WAR_ACTION_RAID]),
                        ];
                        if ($for_display) {
                            $defense_reduction = min($target_location['defense'] / 100, 1);
                            $damage = intval($this->user->level * (1 - $defense_reduction));
                            $damage = max($damage, 0);
                            $valid_war_actions[WarAction::WAR_ACTION_RAID] .= "<br><span class='raid_button_text'>{$damage} damage</span>";
                        }
                        break;
                    case 'default':
                        break;
                }
            }
        }

        // get active caravans
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
                $valid_war_actions = [
                    WarAction::WAR_ACTION_LOOT => System::unSlug(WarAction::WAR_ACTION_TYPE[WarAction::WAR_ACTION_LOOT]),
                ];
            }
        }

        return $valid_war_actions;
    }

    function tryBeginPatrolBattle(MapNPC $patrol) {
        $patrol_location = new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id);
        // if rank below chuunin
        if ($this->user->rank_num < 3) {
            return;
        }
        // if already in battle
        if ($this->user->battle_id) {
            return;
        }
        // if patrol non-hostile
        if ($this->user->village->village_id == $patrol->village_id || $this->user->village->relations[$patrol->village_id]->relation_type == VillageRelation::RELATION_ALLIANCE) {
            return;
        }
        // if not at same location
        if ($this->user->location->toString() != $patrol_location->toString()) {
            return;
        }
        // if no AI set
        if (empty($patrol->ai_id)) {
            return;
        }
        // if in a special mission
        if ($this->user->special_mission_id > 0) {
            return;
        }
        if ($this->user->war_action_id > 0) {
            $this->cancelWarAction();
        }
        $ai = $this->getRandomPatrolAI($patrol->tier, $patrol->ai_id);
        $ai = new NPC($this->system, $ai);
        $ai->loadData();
        $ai->health = $ai->max_health;
        $battle_background = TravelManager::getLocationBattleBackgroundLink($this->system, $this->user->location);
        if (empty($battle_background)) {
            $battle_background = $this->user->region->battle_background_link;
        }
        if ($this->system->USE_NEW_BATTLES) {
            BattleV2::start($this->system, $this->user, $ai, BattleV2::TYPE_AI_WAR, $patrol->id, $battle_background);
        } else {
            Battle::start($this->system, $this->user, $ai, Battle::TYPE_AI_WAR, $patrol->id, $battle_background);
        }
    }

    /**
     * @throws RuntimeException
     */
    public function processLoot(): string
    {
        $message = '';
        // get loot
        $loot_result = $this->system->db->query("SELECT * FROM `loot` WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL");
        $loot_result = $this->system->db->fetch_all($loot_result);
        $loot_gained = [];
        foreach ($loot_result as $loot) {
            // count loot totals
            if (empty($loot_gained[$loot['resource_id']])) {
                $loot_gained[$loot['resource_id']] = 1;
            } else {
                $loot_gained[$loot['resource_id']] += 1;
            }
        }
        // update village resources
        $first = true;
        $yen_gain = 0;
        $total_resources = 0;
        foreach ($loot_gained as $resource_id => $count) {
            if ($first) {
                $message .= "Deposited";
                $first = false;
            } else {
                $message .= ",";
            }
            WarLogManager::logAction($this->system, $this->user, $count, WarLogManager::WAR_LOG_RESOURCES_CLAIMED, $this->user->village->village_id);
            $message .= " " . $count . " " . System::unSlug(WarManager::RESOURCE_NAMES[$resource_id]);
            $this->user->village->addResource($resource_id, $count);
            $total_resources += $count;
        }
        $yen_gain = $total_resources * self::YEN_PER_RESOURCE;
        $stat_gain = floor($total_resources / self::RESOURCES_PER_STAT);
        $rep_gain = floor($total_resources / self::RESOURCES_PER_REPUTATION);
        $this->user->village->updateResources();
        $message .= "!";
        // add yen
        $message .= "\nGained \u{00a5}{$yen_gain}!";
        $this->user->addMoney($yen_gain, "Resource");
        // add stat
        $stat_to_gain = $this->user->getTrainingStatForArena();
        if ($stat_to_gain != null && $stat_gain > 0) {
            $stat_gained = $this->user->addStatGain($stat_to_gain, $stat_gain);
            if (!empty($stat_gained)) {
                $message .= "\n" . $stat_gained;
            }
        }
        // Add reputation
        if ($this->user->reputation->canGain(UserReputation::ACTIVITY_TYPE_WAR)) {
            $rep_gain = $this->user->reputation->addRep(
                amount: $rep_gain,
                activity_type: UserReputation::ACTIVITY_TYPE_WAR
            );
            if ($rep_gain > 0) {
                $message .= "\nGained " . $rep_gain . " village reputation!";
            }
        }
        // Daily Task
        if ($this->user->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_WAR)) {
            $this->user->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_WAR, $rep_gain);
        }
        $message .= '!';
        // update loot table
        $time = time();
        $this->system->db->query("UPDATE `loot` SET `claimed_village_id` = {$this->user->village->village_id}, `claimed_time` = {$time} WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL");
        return $message;
    }

    /**
     * @return string
     */
    public function handleWinAgainstPatrol(int $patrol_id): string {
        $message = '';
        $time = time();
        $patrol_result = $this->system->db->query("SELECT * FROM `patrols` WHERE `id` = {$patrol_id} AND `start_time` < {$time}");
        $patrol_result = $this->system->db->fetch($patrol_result);
        if ($this->system->db->last_num_rows == 0) {
            return $message;
        }
        WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_PATROLS_DEFEATED, $patrol_result['village_id']);
        $x = mt_rand(1, 100);
        if ($x <= self::PATROL_CHANCE[3]) {
            $tier = 3;
        } else if ($x <= self::PATROL_CHANCE[3] + self::PATROL_CHANCE[2]) {
            $tier = 2;
        } else {
            $tier = 1;
        }
        $tier = min($tier + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER);
        $name = self::PATROL_NAMES[$tier];
        // AI is now set dynamically on battle start but we maintain this field for forward/backward compatability as mechanics change
        $ai_id = $this->getRandomPatrolAI($tier, $patrol_result['ai_id']);
        $respawn_time = time() + round(self::PATROL_RESPAWN_TIME * (100 / (100 + $this->user->village->policy->patrol_respawn)), 1);
        $this->system->db->query("UPDATE `patrols` SET `start_time` = {$respawn_time}, `name` = '{$name}', `ai_id` = {$ai_id}, `tier` = {$tier} WHERE `id` = {$patrol_id}");
        // decrease region location defense in region that matches patrol's village
        $location_result = $this->system->db->query("SELECT `region_locations`.*
            FROM `region_locations`
            WHERE `region_locations`.`region_id` = {$patrol_result['region_id']}
        ");
        $location_result = $this->system->db->fetch_all($location_result);
        foreach ($location_result as $location) {
            if ($location['defense'] > 0 && $location['occupying_village_id'] == $patrol_result['village_id']) {
                $location['defense']--;
                $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location['defense']} WHERE `region_location_id` = {$location['region_location_id']}");
            }
        }
        $message = 'Enemy region Defense decreased by 1.<br>';
        return $message;
    }

    public function handleLossAgainstPatrol(int $patrol_id): string
    {
        $message = '';
        // get patrol
        $result = $this->system->db->query("SELECT * FROM `patrols` WHERE `id` = {$patrol_id} LIMIT 1");
        $patrol = $this->system->db->fetch($result);
        $village = VillageManager::getVillageByID($this->system, $patrol['village_id']);
        // get loot
        $loot_result = $this->system->db->query("SELECT * FROM `loot` WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL");
        $loot_result = $this->system->db->fetch_all($loot_result);
        if ($this->system->db->last_num_rows > 0) {
            $message .= "<br>Your loot was taken by the enemy patrol and returned to their village.";
        }
        $loot_gained = [];
        foreach ($loot_result as $loot) {
            // count loot totals
            if (empty($loot_gained[$loot['resource_id']])) {
                $loot_gained[$loot['resource_id']] = 1;
            } else {
                $loot_gained[$loot['resource_id']] += 1;
            }
        }
        // update village resources
        foreach ($loot_gained as $resource_id => $count) {
            $village->addResource($resource_id, $count);
        }
        $village->updateResources();
        // update loot table
        $time = time();
        $this->system->db->query("UPDATE `loot` SET `claimed_village_id` = {$village->village_id}, `claimed_time` = {$time}, `battle_id` = NULL, `user_id` = 0 WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL");
        return $message;
    }

    /**
     * Gets random patrol AI based on params, using previously set AI as fallback
     * @param System $system
     * @param int $patrol_tier
     * @param int $previous_ai
     * @return int
     */
    public function getRandomPatrolAI(int $patrol_tier, int $previous_ai): int {
        switch ($patrol_tier) {
            case 1:
                $ai_difficulty = NPC::DIFFICULTY_EASY;
                break;
            case 2:
                $ai_difficulty = NPC::DIFFICULTY_NORMAL;
                break;
            case 3:
                $ai_difficulty = NPC::DIFFICULTY_HARD;
                break;
            default:
                return $previous_ai;
        }
        $ai_result = $this->system->db->query("SELECT `ai_id` FROM `ai_opponents`
            WHERE `is_patrol` = 1
            AND `difficulty_level` = '{$ai_difficulty}'
            AND `rank` = {$this->user->rank_num}
            ORDER BY RAND() LIMIT 1
        ");
        $ai_result = $this->system->db->fetch($ai_result);
        if (empty($ai_result['ai_id'])) {
            return $previous_ai;
        } else {
            return $ai_result['ai_id'];
        }
    }

    /**
     * Returns raid targets the player might want to attack or defend.
     * * Enemy towns/castles that allies are raiding
     * * Ally towns/castles that enemies are raiding
     *
     * @param System $system
     * @param User   $player
     * @return RaidTargetDto[]
     */
    public static function getPlayerAttackOrDefendRaidTargets(System $system, User $player): array {
        // only get recently updated raids, prevent something like starting a raid and logging out = perpetual notif
        $num_raid_cycles = 100 / WarAction::BASE_WAR_ACTION_INTERVAL_PROGRESS_PERCENT;
        $max_raid_duration_ms = $num_raid_cycles * WarAction::BASE_WAR_ACTION_INTERVAL_SECONDS * 1000;
        $oldest_active_raid_time = (microtime(true) * 1000) - $max_raid_duration_ms;

        $result = $system->db->query("SELECT
            `war_actions`.`user_village` as `attacking_user_village`,
            `war_actions`.`target_id`,
            `war_actions`.`target_village`,
            `region_locations`.`x`,
            `region_locations`.`y`,
            `region_locations`.`map_id`,
            `region_locations`.`name`
            FROM `war_actions`
            INNER JOIN `region_locations` ON `region_locations`.`region_location_id` = `war_actions`.`target_id`
            AND `user_id` != {$player->user_id}
            AND `last_update_ms` > {$oldest_active_raid_time}
            AND `status` = " . WarAction::WAR_ACTION_ACTIVE . "
            AND `war_actions`.`type` = " . WarAction::WAR_ACTION_RAID . "
            GROUP BY `war_actions`.`target_id`, `war_actions`.`target_village`, `attacking_user_village`");
        $raw_raid_targets = $system->db->fetch_all($result);

        $raid_targets = [];
        foreach($raw_raid_targets as $target) {
            if(!isset($raid_targets[$target['target_id']])) {
                // if ally being raided
                if ($player->village->isAlly($target['target_village'])) {
                    $raid_targets[$target['target_id']] = new RaidTargetDto(
                        name: $target['name'],
                        location: new TravelCoords(x: $target['x'], y: $target['y'], map_id: $target['map_id']),
                        is_ally_location: true,
                    );
                }
                // if ally raiding
                else if ($player->village->isAlly($target['attacking_user_village'])) {
                    $raid_targets[$target['target_id']] = new RaidTargetDto(
                        name: $target['name'],
                        location: new TravelCoords(x: $target['x'], y: $target['y'], map_id: $target['map_id']),
                        is_ally_location: false,
                    );
                }
            }
        }

        return $raid_targets;
    }

    public function villagesAreAllies(int $village1_id, int $village2_id): bool {
        return $village1_id == $village2_id ||
            $this->village_relations_by_village_ids[$village1_id][$village2_id]->relation_type == VillageRelation::RELATION_ALLIANCE;
    }

    public static function getVictoryPercentRequired(System $system, VillageRelation $village_relation): int {
        // calculate war duration
        if (isset($village_relation->relation_end)) {
            $war_duration = $village_relation->relation_end - $village_relation->relation_start;
        } else {
            $war_duration = time() - $village_relation->relation_start;
        }
        $min_duration = self::MIN_WAR_DURATION_DAYS * 86400;
        $max_duration = self::MAX_WAR_DURATION_DAYS * 86400;
        if ($war_duration <= $min_duration) {
            return self::INITIAL_VICTORY_SCORE_PERCENT_REQUIRED;
        }
        else if ($war_duration >= $max_duration) {
            return self::FINAL_VICTORY_SCORE_PERCENT_REQUIRED;
        }
        else {
            $duration_percent = ($war_duration - $min_duration) / ($max_duration - $min_duration);
            return self::INITIAL_VICTORY_SCORE_PERCENT_REQUIRED - ($duration_percent * (self::INITIAL_VICTORY_SCORE_PERCENT_REQUIRED - self::FINAL_VICTORY_SCORE_PERCENT_REQUIRED));
        }
    }
}

class RaidTargetDto {
    public function __construct(
        public string $name,
        public TravelCoords $location,
        public bool $is_ally_location,
    ) {}
}