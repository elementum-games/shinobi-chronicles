<?php

require_once __DIR__ . "/RamenOwnerDto.php";
require_once __DIR__ . "/MysteryRamenDto.php";
require_once __DIR__ . "/BasicRamenDto.php";
require_once __DIR__ . "/SpecialRamenDto.php";


class RamenShopManager {
    /* ramen shop owner names indexed by village id */
    const RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE = [
        1 => "Suika",
        2 => "Suika",
        3 => "Suika",
        4 => "Suika",
        5 => "Suika",
    ];
    /* ramen shop owner images indexed by village id */
    const RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE = [
        1 => "images/ramen/Suika.png",
        2 => "images/ramen/Suika.png",
        3 => "images/ramen/Suika.png",
        4 => "images/ramen/Suika.png",
        5 => "images/ramen/Suika.png",
    ];
    /* ramen shop owner background images indexed by village id */
    const RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE = [
        1 => "images/ramen/RamenStand.jpg",
        2 => "images/ramen/RamenStand.jpg",
        3 => "images/ramen/RamenStand.jpg",
        4 => "images/ramen/RamenStand.jpg",
        5 => "images/ramen/RamenStand.jpg",
    ];
    /* ramen shop owner descriptions indexed by village id */
    const RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE = [
        1 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner [keyword]Menma[/keyword] is preparing to step down as his daughter [keyword]Suika[/keyword] has taken over the business.",
        2 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        3 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        4 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
        5 => "A traditional ramen stand proudly run by the Ichikawa family for generations. The owner Menma is preparing to step down as his daughter Suika has taken over the business.",
    ];
    /* ramen shop owner dialogue options indexed by village id */
    const RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE = [
        1 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        2 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        3 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        4 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
        5 => [
            "Ah, the sweet symphony of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my language, and every dish is a love letter to those who savor it. What flavor today?",
            "In this kitchen, it's all about love for food and people. What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments. What dish can elevate your day?",
        ],
    ];
    /* ramen shop names indexed by village id */
    const RAMEN_SHOP_NAMES_BY_VILLAGE = [
        1 => "Ichikawa ramen",
        2 => "Ichikawa ramen",
        3 => "Ichikawa ramen",
        4 => "Ichikawa ramen",
        5 => "Ichikawa ramen",
    ];

    const BASIC_RAMEN_SMALL = "SHIO_S";
    const BASIC_RAMEN_MEDIUM = "SHIO_M";
    const BASIC_RAMEN_LARGE = "SHIO_L";
    const SPECIAL_RAMEN_SHOYU = "SHOYU";
    const SPECIAL_RAMEN_KING = "KING";
    const SPECIAL_RAMEN_SPICY_MISO = "SPICY_MISO";
    const SPECIAL_RAMEN_WARRIOR = "WARRIOR";

    /**
     * Load the ramen shop owner details for the given village
     * @param Village $village
     * @return RamenOwnerDto
     */
    public static function loadRamenOwnerDetails(Village $village): RamenOwnerDto {
        return new RamenOwnerDto(
            name: self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE[$village->village_id],
            image: self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE[$village->village_id],
            background: self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE[$village->village_id],
            shop_description: self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE[$village->village_id],
            dialogue: self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$village->village_id][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$village->village_id])],
            shop_name: self::RAMEN_SHOP_NAMES_BY_VILLAGE[$village->village_id],
        );
    }

    /**
     * Load the basic ramen details for the given player
     * @param System $system
     * @param User $player
     * @return BasicRamenDto[]
     */
    public static function loadBasicRamen(System $system, User $player): array {
        $ramen_cost_multiplier = 1 - ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST] / 100);
        $rankManager = new RankManager($system);
        $rankManager->loadRanks();

        $health[1] = $rankManager->healthForRankAndLevel(1, $rankManager->ranks[1]->max_level);
        $health[2] = $rankManager->healthForRankAndLevel(2, $rankManager->ranks[2]->max_level);
        $health[3] = $rankManager->healthForRankAndLevel(3, $rankManager->ranks[3]->max_level);
        $health[4] = $rankManager->healthForRankAndLevel(4, $rankManager->ranks[4]->max_level);
        // $health[5] = $rankManager->healthForRankAndLevel(5, $rankManager->ranks[5]->max_level);

        $basic_ramen[self::BASIC_RAMEN_SMALL] = new BasicRamenDto(
            key: self::BASIC_RAMEN_SMALL,
            cost: !$player->location->equals($player->village_location) ? $player->rank_num * 5 * 5 : ($player->rank_num * 5 * $ramen_cost_multiplier),
            health_amount: $health[$player->rank_num] * 0.1,
            label: 'Shio S',
            image: "images/ramen/ShioS.png",
        );
        $basic_ramen[self::BASIC_RAMEN_MEDIUM] = new BasicRamenDto(
            key: self::BASIC_RAMEN_MEDIUM,
            cost: !$player->location->equals($player->village_location) ? $player->rank_num * 25 * 5 : ($player->rank_num * 25 * $ramen_cost_multiplier),
            health_amount: $health[$player->rank_num] * 0.5,
            label: 'Shio M',
            image: "images/ramen/ShioM.png",
        );
        $basic_ramen[self::BASIC_RAMEN_LARGE] = new BasicRamenDto(
            key: self::BASIC_RAMEN_LARGE,
            cost: !$player->location->equals($player->village_location) ? $player->rank_num * 50 * 5 : ($player->rank_num * 50 * $ramen_cost_multiplier),
            health_amount: $health[$player->rank_num] * 1,
            label: 'Shio L',
            image: "images/ramen/ShioL.png",
        );

        if ($system->isDevEnvironment()) {
            $ramen_choices['vegetable']['cost'] = 0;
            $ramen_choices['pork']['cost'] = 0;
            $ramen_choices['deluxe']['cost'] = 0;
        }

        return $basic_ramen;
    }

    public static function loadSpecialRamen(User $player): array {
        $special_ramen = [];
        $ramen_cost_multiplier = 1 - ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST] / 100);
        $ramen_duration_bonus = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION];
        if ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE]) {
            $special_ramen[self::SPECIAL_RAMEN_SHOYU] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_SHOYU,
                cost: (2500 + 2500 * $player->rank_num) * $ramen_cost_multiplier,
                label: 'Shoyu',
                image: "images/ramen/Shoyu.png",
                description: "Delicious ramen in soy sauce broth with strong flavors.",
                effect: "+2 Stealth",
                duration: 10 + $ramen_duration_bonus,
            );
            $special_ramen[self::SPECIAL_RAMEN_KING] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_KING,
                cost: 2500 + 2500 * $player->rank_num * $ramen_cost_multiplier,
                label: 'King',
                image: "images/ramen/King.png",
                description: "Topped with every meat and vegetable. Eat your fill.",
                effect: "+1 Reputation gain (PvP excluded)",
                duration: 10 + $ramen_duration_bonus,
            );
            $special_ramen[self::SPECIAL_RAMEN_SPICY_MISO] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_SPICY_MISO,
                cost: 5000 + 5000 * $player->rank_num * $ramen_cost_multiplier,
                label: 'Spicy Miso',
                image: "images/ramen/SpicyMiso.png",
                description: "Spices are balanced with a savoury broth and bits of sweet corn.",
                effect: "+25% Regen",
                duration: 10 + $ramen_duration_bonus,
            );
            $special_ramen[self::SPECIAL_RAMEN_WARRIOR] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_WARRIOR,
                cost: 10000 + 10000 * $player->rank_num * $ramen_cost_multiplier,
                label: 'Warrior',
                image: "images/ramen/Warrior.png",
                description: "The burning spiciness is enough to embolden anyone.",
                effect: "Heal to full after winning a battle.",
                duration: 10 + $ramen_duration_bonus,
            );
        }
        return $special_ramen;
    }

    /**
     * Load the mystery ramen details for the given village
     * @param Village $village
     * @return MysteryRamenDto
     */
    public static function loadMysteryRamenDetails(): MysteryRamenDto {
        return new MysteryRamenDto(
            mystery_ramen_enabled: false,
        );
    }

    public static function purchaseBasicRamen(System $system, User $player, string $ramen_key): ActionResult {
        $rankManager = new RankManager($system);
        $rankManager->loadRanks();

        $ramen_options = self::loadBasicRamen($system, $player);
        $ramen = $ramen_options[$ramen_key];
        if (!isset($ramen)) {
            return ActionResult::failed("Invalid ramen selection!");
        }
        if ($player->getMoney() < $ramen->cost) {
            return ActionResult::failed("You do not have enough money!");
        }
        if ($player->health >= $player->max_health) {
            return ActionResult::failed("Your health is already maxed out!");
        }
        if (!$system->isDevEnvironment()) {
            $player->subtractMoney($ramen->cost, "Purchased {$ramen_key} health");
        }
        $player->health += $ramen->health_amount;
        if ($player->health > $player->max_health) {
            $player->health = $player->max_health;
        }
        $player->updateData();
        return ActionResult::succeeded("");
    }
}