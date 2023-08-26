<?php

class UserApiPresenter {
    public static function playerDataResponse(User $player, array $rank_names): array {
        $forbidden_seal_time_left = null;
        if($player->forbidden_seal->level > 0) {
            $seconds_left = $player->forbidden_seal->seal_end_time - time();
            $days = floor($seconds_left / 86400);
            $hours = floor($seconds_left / 3600);
            $minutes = ceil($seconds_left / 60);

            if($days > 0) {
                $forbidden_seal_time_left = "$days day" . ($days > 1 ? "s" : "");
            }
            else if($hours > 0) {
                $forbidden_seal_time_left = "$hours hour" . ($hours > 1 ? "s" : "");
            }
            else {
                $forbidden_seal_time_left = "$minutes hour" . ($minutes > 1 ? "s" : "");
            }
        }

        return [
            'avatar_link' => $player->avatar_link,
            'user_name' => $player->user_name,
            'rank_name' => $rank_names[$player->rank_num],
            'level' => $player->level,
            'exp' => $player->exp,
            'expForNextLevel' => $player->expForNextLevel(),
            'nextLevelProgressPercent' => $player->nextLevelProgressPercent(),
            'totalStats' => $player->total_stats,
            'totalStatCap' => $player->rank->stat_cap,
            'gender' => $player->gender,
            'elements' => $player->elements,
            'has_bloodline' => isset($player->bloodline),
            'bloodlineName' => $player->bloodline?->name,
            'avatar_size' => $player->getAvatarSize(),
            'money' => $player->currency->getMoney(),
            'premiumCredits' => $player->currency->getPremiumCredits(),
            'premiumCreditsPurchased' => $player->currency->getPremiumPurchased(),
            'villageName' => $player->village->name,
            'villageRepTier' => $player->reputation->rank_name,
            'villageRep' => $player->reputation->getRepAmount(),
            'weeklyRep' => $player->reputation->getWeeklyRepAmount(),
            'maxWeeklyRep' => $player->reputation->weekly_cap,
            'weeklyPvpRep' => $player->reputation->getWeeklyPvpRep(),
            'maxWeeklyPvpRep' => $player->reputation->weekly_pvp_cap,
            'clanId' => $player->clan?->id,
            'clanName' => $player->clan?->name,
            'teamId' => $player->team?->id,
            'teamName' => $player->team?->name,
            'forbiddenSealName' => $player->forbidden_seal->name,
            'forbiddenSealTimeLeft' => $forbidden_seal_time_left,
        ];
    }

    public static function playerStatsResponse(User $player): array {
        return [
            'ninjutsuSkill' => $player->ninjutsu_skill,
            'taijutsuSkill' => $player->taijutsu_skill,
            'genjutsuSkill' => $player->genjutsu_skill,
            'bloodlineSkill' => $player->bloodline_skill,
            'castSpeed' => $player->cast_speed,
            'speed' => $player->speed,
            'intelligence' => $player->intelligence,
            'willpower' => $player->willpower,
        ];
    }

    public static function playerResourcesResponse(User $player): array {
        return [
            'regen_time' => 60 - (time() - $player->last_update),
            'health' => (int) $player->health,
            'max_health' => $player->max_health,
            'chakra' => (int) $player->chakra,
            'max_chakra' => $player->max_chakra,
            'stamina' => (int) $player->stamina,
            'max_stamina' => $player->max_stamina,
            'health_regen' => $player->regen_rate * USER::$HEAL_REGEN_MULTIPLIER[$player->rank_num],
            'pool_regen' => $player->regen_rate
        ];
    }

    public static function playerSettingsResponse(User $player): array {
        return [
            'avatar_style' => $player->getAvatarStyle(),
            'avatar_frame' => $player->getAvatarFrame(),
            'sidebar_position' => $player->getSidebarPosition(),
            'enable_alerts' => $player->getEnableAlerts(),
        ];
    }

    public static function playerAchievementsResponse(User $player): array {

        return [
            'completedAchievements' => array_map(function(PlayerAchievement $playerAchievement) {
                return [
                    'id' => $playerAchievement->achievement->id,
                    'achievedAt' => $playerAchievement->achieved_at,
                    'rank' => $playerAchievement->achievement->getRankLabel(),
                    'name' => $playerAchievement->achievement->name,
                    'prompt' => $playerAchievement->achievement->prompt,
                    'rewards' => array_map(function(AchievementReward $reward) {
                        return [
                            'type' => $reward->type,
                            'amount' => $reward->amount,
                        ];
                    }, $playerAchievement->achievement->rewards),
                    'progressLabel' => '1/1',
                    'progressPercent' => 100,
                ];
            }, array_values($player->achievements)),
        ];
    }

    public static function missionDataResponse(UserAPIManager $userManager): array {
        return array_map(
            function(QuickActionMissionDto $mission) {
                return [
                    'mission_id' => $mission->mission_id,
                    'name' => $mission->name,
                ];
            },
            $userManager->getMissions()
        );
    }

    public static function aiDataResponse(UserAPIManager $userManager): array {
        return array_map(
            function(QuickActionAIDto $ai) {
                return [
                    'ai_id' => $ai->ai_id,
                    'name' => $ai->name,
                ];
            },
            $userManager->getAI()
        );
    }

    /**
     * @param DailyTask[] $dailyTasks
     * @return array[]
     */
    public static function dailyTasksResponse(array $dailyTasks): array {
        return array_map(function(DailyTask $daily_task) {
            return [
                'name' => $daily_task->name,
                'prompt' => $daily_task->getPrompt(),
                'difficulty' => $daily_task->difficulty,
                'rewardYen' => $daily_task->reward,
                'rewardRep' => $daily_task->rep_reward,
                'progressPercent' => $daily_task->getProgressPercent(),
                'progressCaption' => $daily_task->progress . "/" . $daily_task->amount,
                'complete' => $daily_task->complete,
            ];
        }, $dailyTasks);
    }

    public static function playerInventoryResponse(User $player): array {
        if(!$player->inventory_loaded) {
            $player->getInventory();
        }

        return [
            'jutsu' => array_map(function (Jutsu $jutsu) {
                return self::jutsuResponse($jutsu);
            }, array_values($player->jutsu)),
            'jutsuScrolls' => array_map(function (Jutsu $jutsu) {
                return self::jutsuResponse($jutsu);
            }, array_values($player->jutsu_scrolls)),
            'equippedJutsuIds' => $player->equipped_jutsu,
            'items' => array_map(function (Item $item){
                return self::itemResponse($item);
            }, $player->items),
            'equippedArmorIds' => $player->equipped_armor,
            'equippedWeaponIds' => $player->equipped_weapon_ids,
        ];
    }

    public static function jutsuResponse(Jutsu $jutsu): array {
        return [
            'id' => $jutsu->id,
            'name' => $jutsu->name,
            'jutsuType' => $jutsu->jutsu_type,
            'targetType' => $jutsu->target_type,
            'handSeals' => explode('-', $jutsu->hand_seals),
            'range' => $jutsu->range,
            'element' => $jutsu->element,
            // todo later
           /* 'level' => $jutsu->level,
            'exp' => $jutsu->exp,*/
        ];
    }
    
    public static function itemResponse(Item $item): array {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'rank' => $item->rank,
            'purchase_cost' => $item->purchase_cost,
            'purchase_type' => Item::$PURCHASE_TYPE_LABELS[$item->purchase_type],
            'use_type' => Item::$USE_TYPE_LABELS[$item->use_type],
            'effect' => $item->effect,
            'effect_amount' => $item->effect_amount,
            'quantity' => $item->quantity,
        ];
    }
}

