<?php

class UserApiPresenter {
    public static function playerDataResponse(User $player, array $rank_names): array {
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
            'avatar_size' => $player->getAvatarSize(),
            'money' => $player->getMoney(),
            'premiumCredits' => $player->getPremiumCredits(),
            'villageName' => $player->village->name,
            'clanId' => $player->clan?->id,
            'clanName' => $player->clan?->name,
            'teamId' => $player->team?->id,
            'teamName' => $player->team?->name,
            'forbiddenSealName' => $player->forbidden_seal->name,
            'forbiddenSealTimeLeft' => $player->forbidden_seal->level > 0
                ? $player->system->time_remaining($player->forbidden_seal->seal_end_time - time())
                : null,
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
        ];
    }

    public static function playerSettingsResponse(User $player): array {
        return [
            'avatar_style' => $player->getAvatarStyle(),
            'sidebar_position' => $player->getSidebarPosition(),
            'enable_alerts' => $player->getEnableAlerts(),
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
}
