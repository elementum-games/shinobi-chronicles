<?php

require_once __DIR__ . "/RamenOwnerDto.php";
require_once __DIR__ . "/MysteryRamenDto.php";
require_once __DIR__ . "/BasicRamenDto.php";
require_once __DIR__ . "/SpecialRamenDto.php";
require_once __DIR__ . "/CharacterRamenData.php";

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

    /* ramen keys */
    const BASIC_RAMEN_SMALL = "SHIO_S";
    const BASIC_RAMEN_MEDIUM = "SHIO_M";
    const BASIC_RAMEN_LARGE = "SHIO_L";
    const SPECIAL_RAMEN_SHOYU = "SHOYU";
    const SPECIAL_RAMEN_KING = "KING";
    const SPECIAL_RAMEN_SPICY_MISO = "SPICY_MISO";
    const SPECIAL_RAMEN_WARRIOR = "WARRIOR";

    /* base ramen costs indexed by ramen key */
    const BASE_RAMEN_COSTS = [
        self::BASIC_RAMEN_SMALL => 5,
        self::BASIC_RAMEN_MEDIUM => 25,
        self::BASIC_RAMEN_LARGE => 50,
        self::SPECIAL_RAMEN_SHOYU => 1250,
        self::SPECIAL_RAMEN_KING => 2500,
        self::SPECIAL_RAMEN_SPICY_MISO => 5000,
        self::SPECIAL_RAMEN_WARRIOR => 10000,
    ];

    /* ramen effect descriptions indexed by ramen key */
    const RAMEN_EFFECT_DESCRIPTIONS = [
        self::SPECIAL_RAMEN_SHOYU => "+2 Stealth",
        self::SPECIAL_RAMEN_KING => "+1 Reputation gain (PvP excluded)",
        self::SPECIAL_RAMEN_SPICY_MISO => "+25% Regen",
        self::SPECIAL_RAMEN_WARRIOR => "Heal to full after winning a battle.",
    ];

    const MYSTERY_RAMEN_COST_MULTIPLIER = 1;
    const MYSTERY_RAMEN_DURATION_MULTIPLIER = 5;
    const BASE_RAMEN_DURATION_MINUTES = 10;
    const BASE_MYSTERY_RAMEN_CHANCE = 5;
    const MYSTERY_RAMEN_CHANCE_PER_PURCHASE = 5;

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
            foreach ($basic_ramen as $ramen) {
                $ramen->cost = 0;
            }
        }

        return $basic_ramen;
    }

    /**
     * Load the special ramen details for the given player
     * @param User $player
     * @return SpecialRamenDto[]
     */
    public static function loadSpecialRamen(User $player): array {
        $special_ramen = [];
        $ramen_cost_multiplier = 1 - ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST] / 100);
        $ramen_duration_bonus = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION];
        if ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE]) {
            $special_ramen[self::SPECIAL_RAMEN_SHOYU] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_SHOYU,
                cost: (self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_SHOYU] * ($player->rank_num + 1)) * $ramen_cost_multiplier,
                label: 'Shoyu',
                image: "images/ramen/Shoyu.png",
                description: "Delicious ramen in soy sauce broth with strong flavors.",
                effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_SHOYU],
                duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
            );
            $special_ramen[self::SPECIAL_RAMEN_KING] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_KING,
                cost: (self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_KING] * ($player->rank_num + 1)) * $ramen_cost_multiplier,
                label: 'King',
                image: "images/ramen/King.png",
                description: "Topped with every meat and vegetable. Eat your fill.",
                effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_KING],
                duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
            );
            $special_ramen[self::SPECIAL_RAMEN_SPICY_MISO] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_SPICY_MISO,
                cost: (self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_SPICY_MISO] * ($player->rank_num + 1)) * $ramen_cost_multiplier,
                label: 'Spicy Miso',
                image: "images/ramen/SpicyMiso.png",
                description: "Spices are balanced with a savory broth and bits of sweet corn.",
                effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_SPICY_MISO],
                duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
            );
            $special_ramen[self::SPECIAL_RAMEN_WARRIOR] = new SpecialRamenDto(
                key: self::SPECIAL_RAMEN_WARRIOR,
                cost: (self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_WARRIOR] * ($player->rank_num + 1)) * $ramen_cost_multiplier,
                label: 'Warrior',
                image: "images/ramen/Warrior.png",
                description: "The burning spiciness is enough to embolden anyone.",
                effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_WARRIOR],
                duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
            );
        }
        return $special_ramen;
    }

    /**
     * Load the mystery ramen details for the given $player
     * @param Village $village
     * @return MysteryRamenDto
     */
    public static function loadMysteryRamen(User $player): MysteryRamenDto {
        if ($player->ramen_data->mystery_ramen_available) {
            $cost = 0;
            $effects = [];
            $duration = self::MYSTERY_RAMEN_DURATION_MULTIPLIER * $player->rank_num;
            foreach ($player->ramen_data->mystery_ramen_effects as $effect) {
                $cost += self::BASE_RAMEN_COSTS[$effect] * (1 + $player->rank_num);
                $effects[] = self::RAMEN_EFFECT_DESCRIPTIONS[$effect];
            }
            return new MysteryRamenDto(
                cost: $cost,
                label: "Mystery",
                duration: $duration,
                image: "images/ramen/Mystery.png",
                effects: $effects,
                mystery_ramen_unlocked: $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_ENABLED],
            );
        } else {
            return new MysteryRamenDto(
                cost: 0,
                label: "Mystery",
                duration: 0,
                image: "images/ramen/Mystery.png",
                effects: [],
                mystery_ramen_unlocked: $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_ENABLED],
            );
        }
    }

    /**
     * Purchase a basic ramen for the given player
     * @param System $system
     * @param User $player
     * @param string $ramen_key
     * @return ActionResult
     */
    public static function purchaseBasicRamen(System $system, User $player, string $ramen_key): ActionResult {
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

    /**
     * Purchase a special ramen for the given player
     * @param System $system
     * @param User $player
     * @param string $ramen_key
     * @return ActionResult
     */
    public static function purchaseSpecialRamen(System $system, User $player, string $ramen_key): ActionResult {
        $ramen_options = self::loadSpecialRamen($player);
        $ramen = $ramen_options[$ramen_key];
        if (!isset($ramen)) {
            return ActionResult::failed("Invalid ramen selection!");
        }
        if ($player->getMoney() < $ramen->cost) {
            return ActionResult::failed("You do not have enough money!");
        }
        if (!$system->isDevEnvironment()) {
            $player->subtractMoney($ramen->cost, "Purchased {$ramen_key} health");
        }
        $player->ramen_data->buff_duration = $ramen->duration * 60;
        $player->ramen_data->buff_effects[] = $ramen->effect;
        $player->ramen_data->purchase_count_since_last_mystery++;
        self::rollMysteryRamen($system, $player->ramen_data);
        self::updateCharacterRamenData($system, $player->ramen_data);
        $player->updateData();
        return ActionResult::succeeded("");
    }

    /**
     * Purchase a mystery ramen for the given player
     * @param System $system
     * @param User $player
     * @return ActionResult
     */
    public static function purchaseMysteryRamen(System $system, User $player): ActionResult {
        $mystery_ramen = self::loadMysteryRamen($player);
        if (!$mystery_ramen->mystery_ramen_unlocked) {
            return ActionResult::failed("Mystery ramen is not available!");
        }
        if (!$player->ramen_data->mystery_ramen_available) {
            return ActionResult::failed("Mystery ramen is not available!");
        }
        if ($player->getMoney() < $mystery_ramen->cost) {
            return ActionResult::failed("You do not have enough money!");
        }
        if (!$system->isDevEnvironment()) {
            $player->subtractMoney($mystery_ramen->cost, "Purchased mystery ramen");
        }
        $player->ramen_data->buff_duration = $mystery_ramen->duration * 60;
        $player->ramen_data->buff_effects = $mystery_ramen->effects;
        $player->ramen_data->purchase_count_since_last_mystery = 0;
        $player->ramen_data->mystery_ramen_available = false;
        self::updateCharacterRamenData($system, $player->ramen_data);
        $player->updateData();
        return ActionResult::succeeded("");
    }

    /**
     * Get the chance for a mystery ramen for the given player
     * @param System $system
     * @param User $player
     * @return int
     */
    public static function getMysteryRamenChance(System $system, User $player): int {
        $chance = 0;
        $chance += self::BASE_MYSTERY_RAMEN_CHANCE;
        $chance += $player->ramen_data->purchase_count_since_last_mystery * self::MYSTERY_RAMEN_CHANCE_PER_PURCHASE;
        $chance += $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_CHANCE];
        return $chance;
    }

    /**
     * Roll for a mystery ramen for the given player
     * @param System $system
     * @param User $player
     */
    public static function rollMysteryRamen(System $system, User $player) {
        $chance = self::getMysteryRamenChance($system, $player);
        $roll = mt_rand(1, 100);
        if ($roll <= $chance) {
            $special_ramen_options = self::loadSpecialRamen($player);
            $player->ramen_data->mystery_ramen_available = true;
            $player->ramen_data->mystery_ramen_effects = [];
            $keys = array_keys($special_ramen_options);
            shuffle($keys);
            $keys = array_slice($keys, 0, 2);
            foreach ($keys as $key) {
                $player->ramen_data->mystery_ramen_effects[] = $key;
            }
        }
    }

    /**
     * Get the character ramen data for the given player
     * @param System $system
     * @param int $player_id
     * @return CharacterRamenData
     */
    public static function getCharacterRamenData(System $system, int $player_id): CharacterRamenData {
        $query = "SELECT * FROM `character_ramen` WHERE `user_id` = {$player_id} LIMIT 1";
        $result = $system->db->query($query);
        $result = $system->db->fetch($result);
        if ($system->db->last_num_rows == 0) {
            $query = "INSERT INTO `character_ramen` (`user_id`) VALUES ({$player_id})";
            $system->db->query($query);
            return new CharacterRamenData(
                id: $system->db->last_insert_id,
                user_id: $player_id,
                buff_duration: 0,
                purchase_time: 0,
                buff_effects: [],
                mystery_ramen_available: false,
                mystery_ramen_effects: [],
                purchase_count_since_last_mystery: 0,
            );
        }
        return new CharacterRamenData(
            id: (int) $result['id'],
            user_id: (int) $result['user_id'],
            buff_duration: (int) $result['buff_duration'],
            purchase_time: (int) $result['purchase_time'],
            buff_effects: json_decode($result['buff_effects']),
            mystery_ramen_available: (bool) $result['mystery_ramen_available'],
            mystery_ramen_effects: json_decode($result['mystery_ramen_effects']),
            purchase_count_since_last_mystery: (int) $result['purchase_count_since_last_mystery'],
        );
    }

    /**
     * Update the character ramen data for the given player
     * @param System $system
     * @param CharacterRamenData $ramen_data
     */
    public static function updateCharacterRamenData(System $system, CharacterRamenData $ramen_data) {
        $system->db->query("
            UPDATE `character_ramen` SET
                `buff_duration` = {$ramen_data->buff_duration},
                `purchase_time` = {$ramen_data->purchase_time},
                `buff_effects` = '" . json_encode($ramen_data->buff_effects) . "',
                `mystery_ramen_available` = " . ($ramen_data->mystery_ramen_available ? "1" : "0") . ",
                `mystery_ramen_effects` = '" . json_encode($ramen_data->mystery_ramen_effects) . "',
                `purchase_count_since_last_mystery` = {$ramen_data->purchase_count_since_last_mystery}
            WHERE `id` = {$ramen_data->id}
        ");
    }
}