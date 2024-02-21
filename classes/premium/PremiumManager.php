<?php

class PremiumManager {
    const PVP_COUNT_RESET_COST = 20;
    const AI_COUNT_RESET_COST = 10;
    const PVP_AND_AI_COUNT_RESET_COST = 25;
    const NAME_CHANGE_COST = 15;
    const GENDER_CHANGE_COST = 10;
    const ELEMENT_CHANGE_COST = 10;
    const COST_PER_VILLAGE_CHANGE_COST = 5;
    const MAX_VILLAGE_CHANGE_COST = 40;

    public static function loadCosts(User $player): array {
        return [
            'pvp_count_reset' => self::PVP_COUNT_RESET_COST,
            'ai_count_reset' => self::AI_COUNT_RESET_COST,
            'pvp_and_ai_count_reset' => self::PVP_AND_AI_COUNT_RESET_COST,
            'name_change' => self::NAME_CHANGE_COST,
            'gender_change' => self::GENDER_CHANGE_COST,
            'element_change' => self::ELEMENT_CHANGE_COST,
            'village_change' => min(self::MAX_VILLAGE_CHANGE_COST, ($player->village_changes+1) * self::COST_PER_VILLAGE_CHANGE_COST),
        ];
    }
}
