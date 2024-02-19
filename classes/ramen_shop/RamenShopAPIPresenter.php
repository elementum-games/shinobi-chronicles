<?php

class RamenShopAPIPresenter {
    public static function ramenShopOwnerResponse(System $system, User $player) {
        $owner = RamenShopManager::loadRamenOwnerDetails($player->village);
        return [
            "name" => $owner->name,
            "image" => $owner->image,
            "background" => $owner->background,
            "description" => $owner->description,
            "dialogue" => $owner->dialogue,
        ];
    }
    public static function getMysteryRamenResponse(System $system, User $player) {
        $mysteryRamen = RamenShopManager::loadMysteryRamenDetails();
        return [
            "mystery_ramen_enabled" => $mysteryRamen->mystery_ramen_enabled,
        ];
    }
    public static function getBasicRamenResponse(System $system, User $player) {
        return [];
    }
    public static function getSpecialRamenResponse(System $system, User $player) {
        return [];
    }
}