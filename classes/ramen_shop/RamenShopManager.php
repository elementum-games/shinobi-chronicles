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
        3 => "Hideo",
        4 => "Kataba & Ryoba",
        5 => "Kouji",
        0 => "Nobu",
        "mystery" => "Shokunyan Hige",
        "colosseum" => "Zetsuka",
    ];
    /* ramen shop owner images indexed by village id */
    const RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE = [
        1 => "images/ramen/Tomomi.png",
        2 => "images/ramen/Suika.png",
        3 => "images/ramen/Hideo.png",
        4 => "images/ramen/KatabaRyoba.png",
        5 => "images/ramen/Kouji.png",
        0 => "images/ramen/Nobu.png",
        "mystery" => "images/ramen/Higa.png",
        "colosseum" => "images/ramen/Zetsuka.png",
    ];
    /* ramen shop owner background images indexed by village id */
    const RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE = [
        1 => "images/ramen/RamenStand.jpg",
        2 => "images/ramen/RamenStand.jpg",
        3 => "images/ramen/RamenStand.jpg",
        4 => "images/ramen/RamenStand.jpg",
        5 => "images/ramen/RamenStand.jpg",
        0 => "images/ramen/RamenStandOutlaw.jpg",
        "colosseum" => "images/ramen/RamenStandUC.jpg",
    ];
    /* ramen shop owner descriptions indexed by village id */
    const RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE = [
        1 => "A traditional ramen stand proudly run by the [keyword]Ichikawa[/keyword] family for generations. As the main chef of a village often in war, [keyword]Tomomi[/keyword] knows first hand the importance of a well fed shinobi force and takes her job seriously.",
        2 => "A traditional ramen stand proudly run by the [keyword]Ichikawa[/keyword] family for generations. As years have passed the Ichikawa Ramen has become a successful restaurant chain, now led by [keyword]Suika[/keyword].",
        3 => "A traditional ramen stand proudly run by the [keyword]Ichikawa[/keyword] family for generations. The family�s culinary genius [keyword]Hideo[/keyword] expects nothing less but perfection from his dishes.",
        4 => "A traditional ramen stand proudly run by the [keyword]Ichikawa[/keyword] family for generations. [keyword]Kataba[/keyword] and [keyword]Ryoba[/keyword] have put their fighting days behind them after Kataba�s injury, but carry on as a duo in a new battlefield of cooking.",
        5 => "A traditional ramen stand proudly run by the [keyword]Ichikawa[/keyword] family for generations. Many travel to Mist from far to see [keyword]Kouji[/keyword] cook. His slow and meticulous cooking and soothing voice has made watching him work a meditative experience. Offers free life advice.",
        0 => "A traveling ramen cart that appears whenever you need it the most. It's difficult to make a living outside villages, but [keyword]Nobu[/keyword] gets by with his skills - and loyal customers.",
        "colosseum" => "[keyword]Bite Down[/keyword] serves the visitors with a quick, although somewhat expensive meal to down before their next bout. Its owner [keyword][/keyword] not only runs the stand, but is said to know a thing or two about gambling...",
    ];
    /* ramen shop owner basic menu description indexed by village id */
    const RAMEN_SHOP_BASIC_MENU_DESCRIPTIONS_BY_VILLAGE = [
        1 => "Light savory broth with eggs and noodles, perfect with sake.",
        2 => "Light savory broth with eggs and noodles, perfect with sake.",
        3 => "Light savory broth with eggs and noodles, perfect with sake.",
        4 => "Light savory broth with eggs and noodles, perfect with sake.",
        5 => "Light savory broth with eggs and noodles, perfect with sake.",
        0 => "Light savory broth with eggs and noodles, perfect with sake.",
        "colosseum" => "Delicate slices of fresh fish atop seasoned rice, served with wasabi and soy sauce.",
    ];
    /* ramen shop owner dialogue options indexed by village id */
    const RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE = [
        1 => [
            "[quote]Irasshaimase! Just a minute.[/quote]",
            "[quote]Hectic as usual. Decided what to order?[/quote]",
            "[quote]Training hard, huh. You keep that up and we'll win next time for sure.[/quote]",
            "[quote]What I wouldn't do to have more hands working here...[/quote]",
            "Watching Tomomi work you are reminded of a battle with how fast and intense her movements are. She clearly has battle experience.",
            "[quote]A shinobi doesn't fight with their techniques or strength alone. You need to take care of your body, too.[/quote]",
            "[quote]War makes it difficult to get ingredients if not for you guys out there. Thanks for helping us out.[/quote]",
            "[quote]Before you next head out, make sure you have a bowl of Warrior's Ramen![/quote]",
            "Tomomi's expression turns serious as she takes and prepares orders.",
            "[quote]Everyone's working so hard for all of our sakes. You and I must work hard, too.[/quote]",
            "[quote]... Hey. Don't go and get yourself injured, alright?[/quote]",
        ],
        2 => [
            "[quote]Irasshaimase!!![/quote]",
            "[quote]Hehe, you can never be too enthusiastic about a friendly face.[/quote]",
            "[quote]Ah, the sweet sounds of sizzling pans and laughter! I'm your chef, and this kitchen is where love for food meets love for people.[/quote]",
            "[quote]Food is my love language, and every dish is a love letter to those who savor it.[/quote]",
            "[quote]What culinary delight can I create for you today?[/quote]",
            "[quote]In my kitchen, it's all about savoring life's moments.[/quote]",
        ],
        3 => [
            "[quote]... Oh. Irasshai. What will you have?[/quote]",
            "[quote]Oh please sit anywhere you like. I'll be with you in a moment.[/quote]",
            "[quote]The texture is there but the consistency... The flavors aren't quite matching well.[/quote]",
            "[quote]How does Suika get the flavor just right.[/quote]",
            "[quote]They say it's good but it needs to be better than good. Ichikawa's future depends on me.[/quote]",
            "[quote]Grandpa used to say that I have a prodigious sense of what makes a good dish, but I'm not so sure.[/quote]",
            "[quote]Is it the broth? Or is it the noodles? Too much spice? Too little? Argh.[/quote]",
            "[quote]If you see new dishes out there, could you tell me about them? I... want to learn. I have a lot to learn.[/quote]",
            "[quote]Um. Excuse me. Could you...tell your honest opinion... Uh. Um. Nevermind.[/quote]",
            "[quote]Feel like I'm never going to be worth the Ichikawa name...[/quote]",
        ],
        4 => [
            "[quote]Oi! Customer! Irasshai!",
            "[quote]These may be old Ichikawa's recipes but we're working on coming up with our own.",
            "[quote]Ryoooobaaaa, where's the order?!",
            "[quote]I can't make any sense out of your scribbles, Kataba! What's the order meant to be?",
            "[quote]Forget the norms; we're about to break culinary boundaries.",
            "[quote]You're a shinobi, right? Could ya show us how you use a kunai? Y'know, for reasons.",
            "[quote]Ryoba, you think senbon would work when making skewers?[br]Kataba, the only thing we're meant to skewer are enemies. We make RAMEN!",
            "[quote]We're a team here. Kataba sets 'em up with service; I knock 'em down with my dish.",
            "[quote]In the future we'll start our own ramen chain for sure. You can be our first customer.",
            "[quote]Ryoba's Ramen. I like the sound of that.",
        ],
        5 => [
            "[quote]Irasshai.",
            "[quote]Rough day, eh? Have a seat.",
            "[quote]Here for your usual? Got it prepped up, had a feeling you'd come visit.",
            "[quote]Ever feel like you need to shut the world out for a bit? I do. And that's what my ramen offers.",
            "[quote]Stay as long as you need. There's no rush.",
            "[quote]Ever tried fishing? You should.",
            "[quote]Maybe I'll take you fishing with me some time. We all deserve some time off.",
            "[quote]You take care of yourself out there, yeah?",
            "[quote]Just think of your enemies out there as little fish grinding their little teeth. Heh.",
            "[quote]You look like you have a lot on your mind. Have a meal to take your mind off of things.",
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
            "[quote]Heh. Suppose you've got no choice out here but me.[/quote]",
            "[quote]I'm starting to think I should just deliver food instead...[/quote]",
        ],
        "mystery" => [
            "[quote]The great Shokunyan has arrived.[/quote]",
            "[quote]I have come to offer a gift from my brethren.[/quote]",
            "[quote]I am borrowing this stand for the duration of my stay. Yes, borrowing. Yes, with permission.[/quote]",
            "[quote]A feast for the eyes, a feast for the soul.[/quote] Hige nods profoundly",
            "[quote]Taste the heaven, taste the earth. Taste the blessing of life itself.[/quote]",
            "You notice Hige's eyes darting towards your coin purse every now and then.",
            "[quote]You do not need to know what's in the ramen - only that it's worth your money.[/quote]",
            "[quote]Nekogakure thanks you for your continued patronage.[/quote]",
            "Hige's eyes your coin purse greedily before resuming his stoic pose.",
            "[quote]Purrhaps I may in future find further use for your coin... and services.[/quote]",
            "[quote]A village of shinobi... Not much different than a village of cats when you think about it...[/quote]",
            "[quote]Nya.[/quote] ... Hige looks flustered for a moment before clearing his throat.",
            "[quote]I smell opportunities here.[/quote]",
        ],
        "colosseum" => [
            "[quote]Irasshai.[/quote]",
            "[quote]Better eat quick, the crowd is waiting for you.[/quote]",
            "[quote]Think you've got enough time to eat that? Next challenger awaits.[/quote]",
            "[quote]I had a bet on you going on. I'll let you guess the outcome.[/quote]",
            "[quote]I don't care if you win or lose as long as you make the fight interesting.[/quote]",
            "[quote]When you come here, you are not only another brawler, but also an entertainer.[/quote]",
            "[quote]Saw you fight out there. Ever considered... alternative strategies?[/quote]",
            "[quote]Fighting fair is boring. Fighting dirty is what we're here for.[/quote]",
            "[quote]You can use any weapons you bring with you but you can't take the chopsticks, alright?[/quote]",
            "[quote]Considering your appetite, I think I'll put my next bet on you.[/quote]",
            "[quote]I didn't expect much of you to be honest, but...[/quote]",
            "[quote]Wouldn't mind seeing you in the ring more often.[/quote]",
            "[quote]Make it a good show.[/quote]",
            "[quote]Show us what you're worth.[/quote]",
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
        "colosseum" => "Bite Down",
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
        self::SPECIAL_RAMEN_KING => 1250,
        self::SPECIAL_RAMEN_SPICY_MISO => 3000,
        self::SPECIAL_RAMEN_WARRIOR => 3000,
    ];

    /* ramen effect descriptions indexed by ramen key */
    const RAMEN_EFFECT_DESCRIPTIONS = [
        self::SPECIAL_RAMEN_SHOYU => "+2 Stealth.",
        self::SPECIAL_RAMEN_KING => "+1 Reputation gain (PvP excluded).",
        self::SPECIAL_RAMEN_SPICY_MISO => "+25% base regen.",
        self::SPECIAL_RAMEN_WARRIOR => "Immunity to post-battle fatigue.",
    ];

    const MYSTERY_RAMEN_COST_MULTIPLIER = 1;
    const MYSTERY_RAMEN_DURATION_MULTIPLIER = 2;
    const BASE_RAMEN_DURATION_MINUTES = 10;
    const BASE_MYSTERY_RAMEN_CHANCE = 5;
    const MYSTERY_RAMEN_CHANCE_PER_PURCHASE = 5;
    const UC_BASIC_RAMEN_COST_MULTIPLIER = 5;
    const UC_SPECIAL_RAMEN_COST_MULTIPLIER_PERCENT = 15;

    /**
     * Load the ramen shop owner details for the given village
     * @param Village $village
     * @return RamenOwnerDto
     */
    public static function getRamenOwnerDetails(System $system, User $player): RamenOwnerDto {
        if ($player->location->equals($player->village_location)) {
            $ramenOwnerDto = new RamenOwnerDto(
                name: self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE[$player->village->village_id],
                image: self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE[$player->village->village_id],
                background: self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE[$player->village->village_id],
                shop_description: self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE[$player->village->village_id],
                dialogue: self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$player->village->village_id][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[$player->village->village_id])],
                shop_name: self::RAMEN_SHOP_NAMES_BY_VILLAGE[$player->village->village_id],
                basic_menu_description: self::RAMEN_SHOP_BASIC_MENU_DESCRIPTIONS_BY_VILLAGE[$player->village->village_id],
            );
        } else {
            $ramenOwnerDto = new RamenOwnerDto(
                name: self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE[0],
                image: self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE[0],
                background: self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE[0],
                shop_description: self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE[0],
                dialogue: self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[0][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE[0])],
                shop_name: self::RAMEN_SHOP_NAMES_BY_VILLAGE[0],
                basic_menu_description: self::RAMEN_SHOP_BASIC_MENU_DESCRIPTIONS_BY_VILLAGE[0],
            );
        }

        $result = $system->db->query("SELECT * FROM `maps_locations` WHERE `name` = 'Underground Colosseum'");
		$location_result = $system->db->fetch($result);
		$colosseum_coords = new TravelCoords($location_result['x'], $location_result['y'], 1);
        if ($player->location->equals($colosseum_coords)) {
            $ramenOwnerDto->name = self::RAMEN_SHOP_OWNER_NAMES_BY_VILLAGE["colosseum"];
            $ramenOwnerDto->image = self::RAMEN_SHOP_OWNER_IMAGES_BY_VILLAGE["colosseum"];
            $ramenOwnerDto->dialogue = self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE["colosseum"][array_rand(self::RAMEN_SHOP_DIALOGUE_OPTIONS_BY_VILLAGE["colosseum"])];
            $ramenOwnerDto->shop_description = self::RAMEN_SHOP_DESCRIPTIONS_BY_VILLAGE["colosseum"];
            $ramenOwnerDto->shop_name = self::RAMEN_SHOP_NAMES_BY_VILLAGE["colosseum"];
            $ramenOwnerDto->background = self::RAMEN_SHOP_OWNER_BACKGROUNDS_BY_VILLAGE["colosseum"];
            $ramenOwnerDto->basic_menu_description = self::RAMEN_SHOP_BASIC_MENU_DESCRIPTIONS_BY_VILLAGE["colosseum"];
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
    public static function getBasicRamen(System $system, User $player): array
    {
        $ramen_cost_multiplier = 1 - ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST] / 100);
        $rankManager = new RankManager($system);
        $rankManager->loadRanks();

        $player_rank_max_health = $rankManager->healthForRankAndLevel(
            $player->rank_num,
            $rankManager->ranks[$player->rank_num]->max_level
        );

        $result = $system->db->query("SELECT * FROM `maps_locations` WHERE `name` = 'Underground Colosseum'");
		$location_result = $system->db->fetch($result);
		$colosseum_coords = new TravelCoords($location_result['x'], $location_result['y'], 1);
        if ($player->location->equals($colosseum_coords)) {
            $basic_ramen[self::BASIC_RAMEN_SMALL] = new BasicRamenDto(
                key: self::BASIC_RAMEN_SMALL,
                cost: ceil($player->rank_num * 5 * $ramen_cost_multiplier * self::UC_BASIC_RAMEN_COST_MULTIPLIER),
                health_amount: $player_rank_max_health * 0.1,
                label: 'Sushi S',
                image: "images/ramen/SushiS.png",
            );
            $basic_ramen[self::BASIC_RAMEN_MEDIUM] = new BasicRamenDto(
                key: self::BASIC_RAMEN_MEDIUM,
                cost: ceil($player->rank_num * 25 * $ramen_cost_multiplier * self::UC_BASIC_RAMEN_COST_MULTIPLIER),
                health_amount: $player_rank_max_health * 0.5,
                label: 'Sushi M',
                image: "images/ramen/SushiM.png",
            );
            $basic_ramen[self::BASIC_RAMEN_LARGE] = new BasicRamenDto(
                key: self::BASIC_RAMEN_LARGE,
                cost: ceil($player->rank_num * 50 * $ramen_cost_multiplier * self::UC_BASIC_RAMEN_COST_MULTIPLIER),
                health_amount: $player_rank_max_health * 1,
                label: 'Sushi L',
                image: "images/ramen/SushiL.png",
            );
        } else {
            $basic_ramen[self::BASIC_RAMEN_SMALL] = new BasicRamenDto(
                key: self::BASIC_RAMEN_SMALL,
                cost: ceil($player->rank_num * 5 * $ramen_cost_multiplier),
                health_amount: $player_rank_max_health * 0.1,
                label: 'Shio S',
                image: "images/ramen/ShioS.png",
            );
            $basic_ramen[self::BASIC_RAMEN_MEDIUM] = new BasicRamenDto(
                key: self::BASIC_RAMEN_MEDIUM,
                cost: ceil($player->rank_num * 25 * $ramen_cost_multiplier),
                health_amount: $player_rank_max_health * 0.5,
                label: 'Shio M',
                image: "images/ramen/ShioM.png",
            );
            $basic_ramen[self::BASIC_RAMEN_LARGE] = new BasicRamenDto(
                key: self::BASIC_RAMEN_LARGE,
                cost: ceil($player->rank_num * 50 * $ramen_cost_multiplier),
                health_amount: $player_rank_max_health * 1,
                label: 'Shio L',
                image: "images/ramen/ShioL.png",
            );
        }

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
    public static function getSpecialRamen(System $system, User $player): array {
        $special_ramen = [];
        $ramen_cost_multiplier = 1 - ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST] / 100);
        $ramen_duration_bonus = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION];
        $result = $system->db->query("SELECT * FROM `maps_locations` WHERE `name` = 'Underground Colosseum'");
		$location_result = $system->db->fetch($result);
		$colosseum_coords = new TravelCoords($location_result['x'], $location_result['y'], 1);
        if ($player->location->equals($colosseum_coords)) {
            if ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE]) {
                $special_ramen[self::SPECIAL_RAMEN_SHOYU] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_SHOYU,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_SHOYU] * ($player->rank_num + 1)) * $ramen_cost_multiplier * (1 + (self::UC_SPECIAL_RAMEN_COST_MULTIPLIER_PERCENT / 100))),
                    label: 'Tempura',
                    image: "images/ramen/Tempura.png",
                    description: "A lightly battered mix of seafood or vegetables, known for its delicate, crisp texture and golden color.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_SHOYU],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
                $special_ramen[self::SPECIAL_RAMEN_KING] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_KING,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_KING] * ($player->rank_num + 1)) * $ramen_cost_multiplier * (1 + (self::UC_SPECIAL_RAMEN_COST_MULTIPLIER_PERCENT / 100))),
                    label: 'Wagyu',
                    image: "images/ramen/Wagyu.png",
                    description: "Celebrated for its buttery texture and complex flavors, often served in small portions to savor its quality.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_KING],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
                $special_ramen[self::SPECIAL_RAMEN_SPICY_MISO] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_SPICY_MISO,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_SPICY_MISO] * ($player->rank_num + 1)) * $ramen_cost_multiplier * (1 + (self::UC_SPECIAL_RAMEN_COST_MULTIPLIER_PERCENT / 100))),
                    label: 'Karaage',
                    image: "images/ramen/Karaage.png",
                    description: "Known for its juicy interior and flavorful, slightly garlic-ginger seasoned crust.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_SPICY_MISO],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
                $special_ramen[self::SPECIAL_RAMEN_WARRIOR] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_WARRIOR,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_WARRIOR] * ($player->rank_num + 1)) * $ramen_cost_multiplier * (1 + (self::UC_SPECIAL_RAMEN_COST_MULTIPLIER_PERCENT / 100))),
                    label: 'Gyoza',
                    image: "images/ramen/Gyoza.png",
                    description: "Dumpling filled with ground meat and vegetables, often enjoyed with a spicy dipping sauce.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_WARRIOR],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
            }
        }
        else {
            if ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE]) {
                $special_ramen[self::SPECIAL_RAMEN_SHOYU] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_SHOYU,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_SHOYU] * ($player->rank_num + 1)) * $ramen_cost_multiplier),
                    label: 'Shoyu',
                    image: "images/ramen/Shoyu.png",
                    description: "Delicious ramen in soy sauce broth with strong flavors.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_SHOYU],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
                $special_ramen[self::SPECIAL_RAMEN_KING] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_KING,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_KING] * ($player->rank_num + 1)) * $ramen_cost_multiplier),
                    label: 'King',
                    image: "images/ramen/King.png",
                    description: "Topped with every meat and vegetable. Eat your fill.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_KING],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
                $special_ramen[self::SPECIAL_RAMEN_SPICY_MISO] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_SPICY_MISO,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_SPICY_MISO] * ($player->rank_num + 1)) * $ramen_cost_multiplier),
                    label: 'Spicy Miso',
                    image: "images/ramen/SpicyMiso.png",
                    description: "Spices are balanced with a savory broth and bits of sweet corn.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_SPICY_MISO],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
                $special_ramen[self::SPECIAL_RAMEN_WARRIOR] = new SpecialRamenDto(
                    key: self::SPECIAL_RAMEN_WARRIOR,
                    cost: ceil((self::BASE_RAMEN_COSTS[self::SPECIAL_RAMEN_WARRIOR] * ($player->rank_num + 1)) * $ramen_cost_multiplier),
                    label: 'Warrior',
                    image: "images/ramen/Warrior.png",
                    description: "The burning spiciness is enough to embolden anyone.",
                    effect: self::RAMEN_EFFECT_DESCRIPTIONS[self::SPECIAL_RAMEN_WARRIOR],
                    duration: self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus,
                );
            }
        }
        return $special_ramen;
    }

    /**
     * Load the mystery ramen details for the given $player
     * @param Village $village
     * @return MysteryRamenDto
     */
    public static function getMysteryRamen(User $player): MysteryRamenDto {
        if ($player->ramen_data->mystery_ramen_available) {
            $cost = 0;
            $effects = [];
            $ramen_cost_multiplier = 1 - ($player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST] / 100);
            $ramen_duration_bonus = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION];
            $duration = (self::BASE_RAMEN_DURATION_MINUTES + $ramen_duration_bonus) * self::MYSTERY_RAMEN_DURATION_MULTIPLIER;
            foreach ($player->ramen_data->mystery_ramen_effects as $effect) {
                $cost += self::BASE_RAMEN_COSTS[$effect] * (1 + $player->rank_num);
                $effects[] = self::RAMEN_EFFECT_DESCRIPTIONS[$effect];
            }
            $cost *= $ramen_cost_multiplier;
            return new MysteryRamenDto(
                cost: ceil($cost),
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
        $ramen_options = self::getBasicRamen($system, $player);
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
        $ramen_options = self::getSpecialRamen($system, $player);
        $ramen = $ramen_options[$ramen_key];
        if (!isset($ramen)) {
            return ActionResult::failed("Invalid ramen selection!");
        }
        if ($player->getMoney() < $ramen->cost) {
            return ActionResult::failed("You do not have enough money!");
        }
        $player->subtractMoney($ramen->cost, "Purchased {$ramen_key} health");
        $player->ramen_data->buff_duration = $ramen->duration * 60;
        $player->ramen_data->buff_effects = [$ramen->key];
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
        $mystery_ramen = self::getMysteryRamen($player);
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
            $special_ramen_options = self::getSpecialRamen($system, $player);
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