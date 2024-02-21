<?php

class RamenShopAPIPresenter {
    public static function ramenShopOwnerResponse(System $system, User $player) {
        $owner = RamenShopManager::loadRamenOwnerDetails($player);
        return [
            "name" => $owner->name,
            "image" => $owner->image,
            "background" => $owner->background,
            "shop_description" => $owner->shop_description,
            "dialogue" => $owner->dialogue,
            "shop_name" => $owner->shop_name
        ];
    }
    public static function getCharacterRamenResponse(System $system, User $player) {
        $characterRamenData = RamenShopManager::getCharacterRamenData($system, $player->user_id);
        return [
            "id" => $characterRamenData->id,
            "user_id" => $characterRamenData->user_id,
            "buff_duration" => $characterRamenData->buff_duration,
            "purchase_time" => $characterRamenData->purchase_time,
            "buff_effects" => array_map(
            function (string $effect) {
                    return [
                        "effect" => $effect
                    ];
                },
                $characterRamenData->buff_effects
            ),
            "mystery_ramen_available" => $characterRamenData->mystery_ramen_available,
            "mystery_ramen_effects" => array_map(
                function (string $effect) {
                    return [
                        "effect" => $effect
                    ];
                },
                $characterRamenData->mystery_ramen_effects
            ),
            "purchase_count_since_last_mystery" => $characterRamenData->purchase_count_since_last_mystery
        ];
    }
    public static function getBasicRamenResponse(System $system, User $player) {
        return array_map(
            function (BasicRamenDto $ramen) {
                return [
                    "ramen_key" => $ramen->key,
                    "cost" => $ramen->cost,
                    "health_amount" => $ramen->health_amount,
                    "label" => $ramen->label,
                    "image" => $ramen->image,
                ];
            },
            array_values(RamenShopManager::loadBasicRamen($system, $player))
        );
    }
    public static function getSpecialRamenResponse(User $player) {
        return array_map(
            function (SpecialRamenDto $ramen) {
                return [
                    "ramen_key" => $ramen->key,
                    "cost" => $ramen->cost,
                    "label" => $ramen->label,
                    "image" => $ramen->image,
                    "description" => $ramen->description,
                    "effect" => $ramen->effect,
                    "duration" => $ramen->duration,
                ];
            },
            array_values(RamenShopManager::loadSpecialRamen($player))
        );
    }
    public static function getMysteryRamenResponse(User $player) {
        $mystery_ramen = RamenShopManager::loadMysteryRamen($player);
        return [
            "cost" => $mystery_ramen->cost,
            "label" => $mystery_ramen->label,
            "duration" => $mystery_ramen->duration,
            "image" => $mystery_ramen->image,
            "effects" => array_map(
                function (string $effect) {
                    return [
                        "effect" => $effect
                    ];
                },
                $mystery_ramen->effects
            ),
            "mystery_ramen_unlocked" => $mystery_ramen->mystery_ramen_unlocked
        ];
    }
}