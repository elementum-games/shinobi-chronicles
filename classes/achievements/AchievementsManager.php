<?php

require_once __DIR__ . '/AchievementProgress.php';
require_once __DIR__ . '/PlayerAchievement.php';
require_once __DIR__ . '/../Mission.php';

class AchievementsManager {
    /** @var Achievement[] */
    public static array $achievements;

    public static function isWorldFirstAlreadyAchieved(System $system, string $achievement_id): bool {
        $result = $system->db->query(
            "SELECT COUNT(*) as `count` FROM `world_first_achievements` WHERE `achievement_id` = '{$achievement_id}'"
        );
        $count = $system->db->fetch($result)['count'] ?? 0;

        return $count > 0;
    }

    public static function fetchPlayerProgress(
        System $system, User $player, string $achievement_id
    ): ?AchievementProgress {
        $result = $system->db->query("SELECT * FROM `user_achievements_progress` 
         WHERE `achievement_id`='{$achievement_id}'
         AND `user_id`={$player->user_id}
        ");

        $record = $system->db->fetch($result);
        if(!$record) {
            return null;
        }

        return AchievementProgress::fromDb($record);
    }

    public static function fetchPlayerAchievements(System $system, int $user_id): array {
        $result = $system->db->query("SELECT * FROM `user_achievements` WHERE `user_id`={$user_id}");

        $player_achievements = [];
        while($row = $system->db->fetch($result)) {
            $player_achievements[$row['achievement_id']] = new PlayerAchievement(
                achievement: self::$achievements[$row['achievement_id']],
                achieved_at: $row['achieved_at']
            );
        }

        return $player_achievements;
    }

    /**
     * @param System $system
     * @param string $user_id
     * @return AchievementProgress[]
     */
    public static function fetchPlayerAchievementsInProgress(System $system, int $user_id): array {
        $result = $system->db->query("SELECT * FROM `user_achievements_progress` WHERE `user_id`={$user_id}");

        $achievements = [];
        while($row = $system->db->fetch($result)) {
            $achievements[$row['achievement_id']] = AchievementProgress::fromDb($row);
        }

        return $achievements;
    }

    public static function checkForCompletedAchievements(System $system, User $player): void {
        foreach(self::$achievements as $achievement) {
            if(isset($player->achievements[$achievement->id])) {
                continue;
            }

            if($achievement->isCriteriaAchieved($system, $player)) {
                $player->achievements[$achievement->id] = new PlayerAchievement($achievement, time());

                $system->db->query("DELETE FROM `user_achievements_progress` 
                    WHERE `user_id`={$player->user_id} AND `achievement_id`='{$achievement->id}'");

                $system->db->query("INSERT INTO `user_achievements` SET 
                    `achievement_id`='{$achievement->id}',
                    `user_id`={$player->user_id},
                    `achieved_at` = {$player->achievements[$achievement->id]->achieved_at}
                ");

                if($achievement->is_world_first) {
                    $system->db->query("INSERT INTO `world_first_achievements` SET 
                        `achievement_id`='{$achievement->id}',
                        `user_id`={$player->user_id},
                        `achieved_at` = {$player->achievements[$achievement->id]->achieved_at}
                    ");
                }

                // TODO: Notification?
                // TODO: Rewards
            }
        }
    }

    public static function handleMissionCompleted(System $system, User $player, Mission $mission): void {
        foreach(self::$achievements as $achievement) {
            if(isset($player->achievements[$achievement->id])) {
                continue;
            }

            switch($achievement->id) {
                case LANTERN_EVENT_COMPLETE_ALL_MISSIONS:
                    if(!($system->event instanceof LanternEvent)) {
                        break;
                    }

                    // Progress achievement
                    if(in_array($mission->mission_id, $system->event->mission_ids)) {
                        if(!isset($player->achievements_in_progress[LANTERN_EVENT_COMPLETE_ALL_MISSIONS])) {
                            $progress = new AchievementProgress(
                                id: null,
                                achievement_id: $achievement->id,
                                user_id: $player->user_id,
                                progress_data: [
                                    'mission_ids' => []
                                ]
                            );
                            $progress->id = AchievementsManager::createAchievementProgress(
                                $system,
                                $progress
                            );

                            $player->achievements_in_progress[$achievement->id] = $progress;
                        }


                        if(!in_array(
                            $mission->mission_id,
                            $player
                                ->achievements_in_progress[$achievement->id]
                                ->progress_data['mission_ids']
                        )) {
                            $player
                                ->achievements_in_progress[$achievement->id]
                                ->progress_data['mission_ids'][] = $mission->mission_id;

                            AchievementsManager::updateAchievementProgress(
                                $system,
                                $player->achievements_in_progress[$achievement->id]
                            );
                        }
                    }
                    break;
            }
        }
    }

    public static function handleItemAcquired(System $system, User $player, Item $item): void {
        foreach(self::$achievements as $achievement) {
            if(isset($player->achievements[$achievement->id])) {
                continue;
            }

            switch($achievement->id) {
                case LANTERN_EVENT_OBTAIN_ALL_ITEMS:
                    if(!($system->event instanceof LanternEvent)) {
                        break;
                    }

                    // Progress achievement
                    if(in_array($item->id, $system->event->item_ids)) {
                        if(!isset($player->achievements_in_progress[$achievement->id])) {
                            $progress = new AchievementProgress(
                                id: null,
                                achievement_id: $achievement->id,
                                user_id: $player->user_id,
                                progress_data: [
                                    'item_ids' => []
                                ]
                            );
                            $progress->id = AchievementsManager::createAchievementProgress(
                                $system,
                                $progress
                            );

                            $player->achievements_in_progress[$achievement->id] = $progress;
                        }

                        if(!in_array(
                            $item->id,
                            $player
                                ->achievements_in_progress[$achievement->id]
                                ->progress_data['item_ids']
                        )) {
                            $player
                                ->achievements_in_progress[$achievement->id]
                                ->progress_data['item_ids'][] = $item->id;
                            AchievementsManager::updateAchievementProgress(
                                $system,
                                $player->achievements_in_progress[$achievement->id]
                            );
                        }
                    }
                    break;
            }
        }
    }

    private static function createAchievementProgress(System $system, AchievementProgress $achievementProgress): int {
        $system->db->query("INSERT INTO `user_achievements_progress` SET
            `achievement_id`='{$achievementProgress->achievement_id}',
            `user_id`={$achievementProgress->user_id},
            `progress_data`='" . json_encode($achievementProgress->progress_data) . "'
        ");

        return $system->db->last_insert_id;
    }

    private static function updateAchievementProgress(System $system, AchievementProgress $achievementProgress): void {
        if($achievementProgress->id == null) {
            throw new RuntimeException("Must have id to update achievement progress!");
        }
        $system->db->query("UPDATE `user_achievements_progress` SET
            `progress_data`='" . json_encode($achievementProgress->progress_data) . "'
            WHERE `id`={$achievementProgress->id}
        ");
    }
}

AchievementsManager::$achievements = require __DIR__ . '/_achievements.php';