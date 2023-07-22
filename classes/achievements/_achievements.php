<?php

require_once __DIR__ . '/AchievementsManager.php';
require_once __DIR__ . '/AchievementReward.php';
require_once __DIR__ . '/../event/LanternEvent.php';
require_once __DIR__ . '/../UserReputation.php';

/*
 * REPUTATION ACHIEVEMENTS
 *
 * DO NOT CHANGE THE VALUES OF THESE CONSTANTS. These are used to track which achievements the player has, changing one would
 * potentially allow for receiving the achievement's rewards a second time.
 */
const FIRST_TO_REP_TIER_2 = 'first_to_rep_tier_2';
const FIRST_TO_REP_TIER_3 = 'first_to_rep_tier_3';
const FIRST_TO_REP_TIER_4 = 'first_to_rep_tier_4';
const FIRST_TO_REP_TIER_5 = 'first_to_rep_tier_5';
const FIRST_TO_REP_TIER_6 = 'first_to_rep_tier_6';
const FIRST_TO_REP_TIER_7 = 'first_to_rep_tier_7';
const FIRST_TO_REP_TIER_8 = 'first_to_rep_tier_8';
const FIRST_TO_REP_TIER_9 = 'first_to_rep_tier_9';

$reputation_rewards = [
    FIRST_TO_REP_TIER_2 => [new AchievementReward(AchievementReward::TYPE_MONEY, 20_000)],
    FIRST_TO_REP_TIER_3 => [new AchievementReward(AchievementReward::TYPE_MONEY, 30_000)],
    FIRST_TO_REP_TIER_4 => [new AchievementReward(AchievementReward::TYPE_MONEY, 40_000)],
    FIRST_TO_REP_TIER_5 => [new AchievementReward(AchievementReward::TYPE_MONEY, 50_000)],
    FIRST_TO_REP_TIER_6 => [new AchievementReward(AchievementReward::TYPE_MONEY, 60_000)],
    FIRST_TO_REP_TIER_7 => [new AchievementReward(AchievementReward::TYPE_MONEY, 70_000)],
    FIRST_TO_REP_TIER_8 => [new AchievementReward(AchievementReward::TYPE_MONEY, 80_000)],
    FIRST_TO_REP_TIER_9 => [new AchievementReward(AchievementReward::TYPE_MONEY, 90_000)],
];

$REPUTATION_ACHIEVEMENTS = [
    FIRST_TO_REP_TIER_2 => new Achievement(
        id: FIRST_TO_REP_TIER_2,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[2]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[2]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_2],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 2) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_2);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_3 => new Achievement(
        id: FIRST_TO_REP_TIER_3,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[3]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[3]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_3],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 3) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_3);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_4 => new Achievement(
        id: FIRST_TO_REP_TIER_4,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[4]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[4]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_4],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 4) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_4);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_5 => new Achievement(
        id: FIRST_TO_REP_TIER_5,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[5]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[5]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_5],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 5) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_5);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_6 => new Achievement(
        id: FIRST_TO_REP_TIER_6,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[6]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[6]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_6],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 6) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_6);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_7 => new Achievement(
        id: FIRST_TO_REP_TIER_7,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[7]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[7]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_7],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 7) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_7);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_8 => new Achievement(
        id: FIRST_TO_REP_TIER_8,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[8]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[8]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_8],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 8) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_8);
        },
        is_world_first: true,
    ),
    FIRST_TO_REP_TIER_9 => new Achievement(
        id: FIRST_TO_REP_TIER_9,
        rank: Achievement::RANK_ELITE,
        name: 'First ' . UserReputation::$VillageRep[9]['title'],
        prompt: "Become the first player to reach the reputation of " . UserReputation::$VillageRep[9]['title'],
        rewards: $reputation_rewards[FIRST_TO_REP_TIER_9],
        criteria_check_closure: function(System $system, User $player) {
            if($player->reputation->rank < 9) {
                return false;
            }

            return !AchievementsManager::isWorldFirstAlreadyAchieved($system, FIRST_TO_REP_TIER_9);
        },
        is_world_first: true,
    ),
];

/*
 * LANTERN EVENT ACHIEVEMENTS
 *
 * DO NOT CHANGE THE VALUES OF THESE CONSTANTS. These are used to track which achievements the player has, changing one would
 * potentially allow for receiving the achievement's rewards a second time.
 */
const LANTERN_EVENT_OBTAIN_VIOLET_LANTERN = 'LANTERN_EVENT_OBTAIN_VIOLET_LANTERN';
const LANTERN_EVENT_OBTAIN_GOLD_LANTERN = 'LANTERN_EVENT_OBTAIN_GOLD_LANTERN';
const LANTERN_EVENT_OBTAIN_SHADOW_ESSENCE = 'LANTERN_EVENT_OBTAIN_SHADOW_ESSENCE';
const LANTERN_EVENT_COMPLETE_ALL_MISSIONS = 'LANTERN_EVENT_COMPLETE_ALL_MISSIONS';
const LANTERN_EVENT_OBTAIN_ALL_ITEMS = 'LANTERN_EVENT_OBTAIN_ALL_ITEMS';

$LANTERN_EVENT_ACHIEVEMENTS = [
    LANTERN_EVENT_OBTAIN_VIOLET_LANTERN => new Achievement(
        id: LANTERN_EVENT_OBTAIN_VIOLET_LANTERN,
        rank: Achievement::RANK_COMMON,
        name: 'Violet Lantern Holder',
        prompt: 'Obtain a Violet Lantern',
        rewards: [
            new AchievementReward(AchievementReward::TYPE_MONEY, 5_000)
        ],
        criteria_check_closure: function (System $system, User $player) {
            return $player->hasItem(LanternEvent::$static_item_ids['violet_lantern_id']);
        },
    ),
    LANTERN_EVENT_OBTAIN_GOLD_LANTERN => new Achievement(
        id: LANTERN_EVENT_OBTAIN_GOLD_LANTERN,
        rank: Achievement::RANK_GREATER,
        name: 'Gold Lantern Holder',
        prompt: 'Obtain a Gold Lantern',
        rewards: [
            new AchievementReward(AchievementReward::TYPE_MONEY, 10_000)
        ],
        criteria_check_closure: function (System $system, User $player) {
            return $player->hasItem(LanternEvent::$static_item_ids['gold_lantern_id']);
        },
    ),
    LANTERN_EVENT_OBTAIN_SHADOW_ESSENCE => new Achievement(
        id: LANTERN_EVENT_OBTAIN_SHADOW_ESSENCE,
        rank: Achievement::RANK_GREATER,
        name: 'Shadow Holder',
        prompt: 'Obtain Shadow Essence',
        rewards: [
            new AchievementReward(AchievementReward::TYPE_MONEY, 10_000)
        ],
        criteria_check_closure: function (System $system, User $player) {
            return $player->hasItem(LanternEvent::$static_item_ids['shadow_essence_id']);
        },
    ),
    LANTERN_EVENT_COMPLETE_ALL_MISSIONS => new Achievement(
        id: LANTERN_EVENT_COMPLETE_ALL_MISSIONS,
        rank: Achievement::RANK_GREATER,
        name: 'Way of the Lantern',
        prompt: 'Complete all the Festival of Shadows missions',
        rewards: [
            new AchievementReward(AchievementReward::TYPE_MONEY, 10_000)
        ],
        criteria_check_closure: function (System $system, User $player) {
            $progress = AchievementsManager::fetchPlayerProgress(
                system: $system,
                player: $player,
                achievement_id: LANTERN_EVENT_COMPLETE_ALL_MISSIONS
            );

            if($progress == null) {
                return false;
            }
            if(empty($progress->progress_data['mission_ids'])) {
                return false;
            }

            foreach(LanternEvent::$static_mission_ids as $mission_id) {
                if(!in_array($mission_id, $progress->progress_data['mission_ids'])) {
                    return false;
                }
            }

            return true;
        },
    ),
    LANTERN_EVENT_OBTAIN_ALL_ITEMS => new Achievement(
        id: LANTERN_EVENT_OBTAIN_ALL_ITEMS,
        rank: Achievement::RANK_GREATER,
        name: 'Lantern Collector',
        prompt: 'Collect all items from the Festival of Shadows event.',
        rewards: [
            new AchievementReward(AchievementReward::TYPE_MONEY, 10_000)
        ],
        criteria_check_closure: function (System $system, User $player) {
            $progress = AchievementsManager::fetchPlayerProgress(
                system: $system,
                player: $player,
                achievement_id: LANTERN_EVENT_OBTAIN_ALL_ITEMS
            );

            if($progress == null) {
                return false;
            }
            if(empty($progress->progress_data['item_ids'])) {
                return false;
            }

            foreach(LanternEvent::$static_item_ids as $item_id) {
                if(!in_array($item_id, $progress->progress_data['item_ids'])) {
                    return false;
                }
            }

            return true;
        },
    ),
];

return array_merge(
    $REPUTATION_ACHIEVEMENTS,
    $LANTERN_EVENT_ACHIEVEMENTS
);