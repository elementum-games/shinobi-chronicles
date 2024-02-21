<?php

require_once __DIR__ . "/RamenOwnerDto.php";
require_once __DIR__ . "/MysteryRamenDto.php";
require_once __DIR__ . "/BasicRamenDto.php";
require_once __DIR__ . "/SpecialRamenDto.php";
require_once __DIR__ . "/CharacterRamenData.php";

class RamenShopManager {
    /* ramen shop owner names indexed by village id */
    const RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE = [
        1 => "Tomomi",
        2 => "Suika",
        3 => "Suika",
        4 => "Kataba & Ryoba",
        5 => "Kouji",
        0 => "Nobu",
        "mystery" => "Whisperwhip",
    ];
    /* ramen shop owner images indexed by village id */
    const RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE = [
        1 => "images/ramen/Tomomi.png",
        2 => "images/ramen/Suika.png",
        3 => "images/ramen/Hideo.png",
        4 => "images/ramen/KatabaRyoba.png",
        5 => "images/ramen/Kouji.png",
        0 => "images/ramen/Nobu.png",
        "mystery" => "images/ramen/Whisperwhip.png",
    ];
    /* ramen shop owner background images indexed by village id */
    const RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE = [
        1 => "images/ramen/RamenStand.jpg",
        2 => "images/ramen/RamenStand.jpg",
        3 => "images/ramen/RamenStand.jpg",
        4 => "images/ramen/RamenStand.jpg",
        5 => "images/ramen/RamenStand.jpg",
        0 => "images/ramen/RamenStand.jpg",
    ];
    /* ramen shop owner descriptions indexed by village id */
    const RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE = [
        1 => "A traditional ramen stand proudly run by the Ichikawa family for generations. As the main chef of a village often in war, [keyword]Tomomi[/keyword] knows first hand the importance of a well fed shinobi force and takes her job seriously.",
        2 => "A traditional ramen stand proudly run by the Ichikawa family for generations. A traditional ramen stand proudly run by Ichikawa family for generations. As years have passed the Ichikawa Ramen has become a successful restaurant chain, now led by [keyword]Suika[/keyword].",
        3 => "A traditional ramen stand proudly run by the Ichikawa family for generations.",
        4 => "A traditional ramen stand proudly run by the Ichikawa family for generations.",
        5 => "A traditional ramen stand proudly run by Ichikawa family for generations. Many travel to Mist from far to see Kouji cook. His slow and meticulous cooking and soothing voice has made watching him work a meditative experience. Offers free life advice.",
        0 => "A traveling ramen cart that appears whenever you need it the most. It's difficult to make a living outside villages, but [keyword]Nobu[/keyword] gets by with his skills - and loyal customers.",
    ];
    /* ramen shop owner dialogue options indexed by village id */
    const RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE = [
        1 => [
            "Irasshaimase! Just a minute.",
            "Hectic as usual. Decided what to order?",
            "Training hard, huh. You keep that up and we'll win next time for sure.",
            "What I wouldn't do to have more hands working here...",
            "Watching Tomomi work you are reminded of a battle with how fast and intense her movements are. She clearly has battle experience.",
            "A shinobi doesn't fight with their techniques or strength alone. You need to take care of your body, too.",
            "War makes it difficult to get ingredients if not for you guys out there. Thanks for helping us out.",
            "Before you next head out, make sure you have a bowl of Warrior's Ramen!",
            "Tomomi's expression turns serious as she takes and prepares orders.",
            "Everyone's working so hard for all of our sakes. You and I must work hard, too.",
            "... Hey. Don't go and get yourself injured, alright?",
        ],
        2 => [
            "Irasshaimase!!!",
            "Hehe, you can never be too enthusiastic about a friendly face.",
            "Ah, the sweet sounds of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.",
            "Food is my love language, and every dish is a love letter to those who savor it.",
            "What culinary delight can I create for you today?",
            "In my kitchen, it's all about savoring life's moments.",
        ],
        3 => [
            "... Oh. Irasshai. What will you have?",
            "Oh please sit anywhere you like. I'll be with you in a moment.",
            "The texture is there but the consistency…The flavors aren't quite matching well…",
            "How does Suika get the flavor just right…",
            "They say it's good but it needs to be better than good. Ichikawa's future depends on me.",
            "Grandpa used to say that I have a prodigious sense of what makes a good dish, but I'm not so sure…",
            "Is it the broth? Or is it the noodles? Too much spice? Too little? Argh…",
            "If you see new dishes out there, could you tell me about them? I…want to learn. I have a lot to learn.",
            "Um. Excuse me. Could you…tell your honest opinion… Uh. Um. Nevermind.",
            "Feel like I'm never going to be worth the Ichikawa name…",
        ],
        4 => [
            "Oi! Customer! Irasshai!",
            "These may be old Ichikawa's recipes but we're working on coming up with our own.",
            "Ryoooobaaaa, where's the order?!",
            "I can't make any sense out of your scribbles, Kataba! What's the order meant to be?",
            "Forget the norms; we're about to break culinary boundaries.",
            "You're a shinobi, right? Could ya show us how you use a kunai? Y'know, for…reasons.",
            "Ryoba, you think senbon would work when making skewers?[br]Kataba, the only thing we're meant to skewer are enemies. We make RAMEN!",
            "We're a team here. Kataba sets 'em up with service; I knock 'em down with my dish.",
            "In the future we'll start our own ramen chain for sure. You can be our first customer.",
            "Ryoba's Ramen. I like the sound of that.",
        ],
        5 => [
            "Irasshai.",
            "Rough day, eh? Have a seat.",
            "Here for your usual? Got it prepped up, had a feeling you'd come visit.",
            "Ever feel like you need to shut the world out for a bit? I do. And that's what my ramen offers.",
            "Stay as long as you need. There's no rush.",
            "Ever tried fishing? You should.",
            "Maybe I'll take you fishing with me some time. We all deserve some time off.",
            "You take care of yourself out there, yeah?",
            "Just think of your enemies out there as little fish grinding their little teeth. Heh.",
            "You look like you have a lot on your mind. Have a meal to take your mind off of things.",
        ],
        0 => [
            "[quote]Yo.[/quote]",
            "[quote]...[/quote]",
            "[quote]You decide what to order yet?[/quote]",
            "[quote]Don't got much in the way of ingredients...[/quote]",
            "[quote]I can whip something up if you give me some time.[/quote]",
            "[quote]I just throw things together and hope for the best. So, what will it be?[/quote]",
            "Nobu begins to prepare ingredients you aren't all too sure are edible. You watch as they seem to throw things at random into a pan.[/quote]",
            "[quote]Can't be wasteful when there's not many ways to get any proper stuff. You have to learn to use all you've got if you want to survive out here.",
            "[quote]Some folks call me an outlaw in the kitchen. I guess that's accurate.[/quote]",
            "[quote]I never really learnt how to cook properly, but folks keep coming back for more.[/quote]",
            "Among the sounds of chopping ingredients and bubbling broth, you hear Nobu humming. They seem happier when there's an opportunity to cook.",
            "[quote]Heh. ‘Suppose you've got no choice out here but me.[/quote]",
            "[quote]I'm starting to think I should just deliver food instead...[/quote]",
        ],
        "mystery" => [
            "The great Shokunyan has arrived.",
            "I have come to offer a gift from my brethren.",
            "I am borrowing this stand for the duration of my stay. Yes, borrowing. Yes, with permission.",
            "A feast for the eyes, a feast for the soul. Hige nods profoundly",
            "Taste the heaven, taste the earth. Taste the blessing of life itself.",
        ],
    ];
    /* ramen shop names indexed by village id */
    const RAMEN_SHOP_NAMES_BY_VILLAGE = [
        1 => "Ichikawa ramen",
        2 => "Ichikawa ramen",
        3 => "Ichikawa ramen",
        4 => "Ichikawa ramen",
        5 => "Ichikawa ramen",
        0 => "Ichikawa ramen",
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
        self::SPECIAL_RAMEN_SHOYU => "+2 Stealth.",
        self::SPECIAL_RAMEN_KING => "+1 Reputation gain (PvP excluded).",
        self::SPECIAL_RAMEN_SPICY_MISO => "+25% base regen.",
        self::SPECIAL_RAMEN_WARRIOR => "Heal to full after winning a battle",
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
    public static function loadRamenOwnerDetails(User $player): RamenOwnerDto {
        if ($player->location->equals($player->village_location)) {
            $ramenOwnerDto = new RamenOwnerDto(
                name: self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE[$player->village->village_id],
                image: self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE[$player->village->village_id],
                background: self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE[$player->village->village_id],
                shop_description: self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE[$player->village->village_id],
                dialogue: self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$player->village->village_id][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$player->village->village_id])],
                shop_name: self::RAMEN_SHOP_NAMES_BY_VILLAGE[$player->village->village_id],
            );
        } else {
            $ramenOwnerDto = new RamenOwnerDto(
                name: self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE[0],
                image: self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE[0],
                background: self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE[0],
                shop_description: self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE[0],
                dialogue: self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[0][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[0])],
                shop_name: self::RAMEN_SHOP_NAMES_BY_VILLAGE[0],
            );
        }
        if ($player->ramen_data->mystery_ramen_available) {
            $ramenOwnerDto->name = self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE["mystery"];
            $ramenOwnerDto->image = self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE["mystery"];
            $ramenOwnerDto->dialogue = self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE["mystery"][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE["mystery"])];
        }
        return $ramenOwnerDto;
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
        $player->subtractMoney($ramen->cost, "Purchased {$ramen_key} health");
        $player->ramen_data->buff_duration = $ramen->duration * 60;
        $player->ramen_data->buff_effects = [];
        $player->ramen_data->buff_effects[] = $ramen->key;
        $player->ramen_data->purchase_count_since_last_mystery++;
        $player->ramen_data->purchase_time = time();
        self::rollMysteryRamen($system, $player);
        self::updateCharacterRamenData($system, $player->ramen_data);
        $player->updateData();
        return ActionResult::succeeded("Purchased {$ramen->label} Ramen!");
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
        $player->subtractMoney($mystery_ramen->cost, "Purchased mystery ramen");
        $player->ramen_data->buff_duration = $mystery_ramen->duration * 60;
        $player->ramen_data->buff_effects = [];
        $player->ramen_data->buff_effects = $mystery_ramen->effects;
        $player->ramen_data->purchase_count_since_last_mystery = 0;
        $player->ramen_data->mystery_ramen_available = false;
        $player->ramen_data->purchase_time = time();
        self::updateCharacterRamenData($system, $player->ramen_data);
        $player->updateData();
        return ActionResult::succeeded("Purchased Mystery Ramen!");
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