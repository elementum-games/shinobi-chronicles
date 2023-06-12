<?php

class UserApiPresenter {
    public static function playerDataResponse(User $player, array $rank_names): array {
        return [
            'avatar_link' => $player->avatar_link,
            'user_name' => $player->user_name,
            'rank_name' => $rank_names[$player->rank_num],
            'level' => $player->level,
            'has_bloodline' => isset($player->bloodline),
            'avatar_size' => $player->getAvatarSize(),
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
            'sidebar_position' => $player->getAvatarStyle()
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
