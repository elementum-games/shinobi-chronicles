<?php

class HotbarApiPresenter {
    public static function playerDataResponse(User $player, array $rank_names): array {
        return [
            'avatar_link' => $player->avatar_link,
            'user_name' => $player->user_name,
            'rank_name' => $rank_names[$player->rank_num],
            'level' => $player->level,
            'regen_time' => 60 - (time() - $player->last_update),
            'health' => (int)$player->health,
            'max_health' => $player->max_health,
            'chakra' => (int)$player->chakra,
            'max_chakra' => $player->max_chakra,
            'stamina' => (int)$player->stamina,
            'max_stamina' => $player->max_stamina,
            'has_bloodline' => isset($player->bloodline),
            'training' => $player->train_time,
            'special' => $player->special_mission,
            'battle' => $player->battle_id,
        ];
    }

    public static function missionDataResponse(HotbarManager $hotbarManager): array {
        return array_map(
            function(HotbarMissionDto $mission) {
                return [
                    'mission_id' => $mission->mission_id,
                    'name' => $mission->name,
                ];
            },
            $hotbarManager->getMissions()
        );
    }

    public static function aiDataResponse(HotbarManager $hotbarManager): array {
        return array_map(
            function(HotbarAIDto $ai) {
                return [
                    'ai_id' => $ai->ai_id,
                    'name' => $ai->name,
                ];
            },
            $hotbarManager->getAI()
        );
    }
}
