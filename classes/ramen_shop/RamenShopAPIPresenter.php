<?php

class RamenShopAPIPresenter {
    public static function ramenShopOwnerResponse(System $system, User $player) {
        $owner = RamenShopManager::loadRamenOwnerDetails($player->village);
        return [
            "name" => $owner->name,
            "image" => $owner->image,
            "background" => $owner->background,
            "shop_description" => $owner->shop_description,
            "dialogue" => $owner->dialogue,
            "shop_name" => $owner->shop_name
        ];
    }
    public static function getMysteryRamenResponse(System $system, User $player) {
        $mysteryRamen = RamenShopManager::loadMysteryRamenDetails();
        return [
            "mystery_ramen_enabled" => $mysteryRamen->mystery_ramen_enabled,
        ];
    }
    public static function getBasicRamenResponse(System $system, User $player) {
        return array_map(
            function (BasicRamenDto $ramen) {
                return [
                    "key" => $ramen->key,
                    "cost" => $ramen->cost,
                    "health_amount" => $ramen->health_amount,
                    "label" => $ramen->label,
                    "image" => $ramen->image,
                ];
            },
            RamenShopManager::loadBasicRamen($system, $player)
        );
    }
    public static function getSpecialRamenResponse(System $system, User $player) {
        return [];
    }
}