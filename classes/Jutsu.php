<?php

use JetBrains\PhpStorm\Pure;

class Jutsu {
    const ELEMENT_NONE = 'None';
    const ELEMENT_FIRE = 'Fire';
    const ELEMENT_EARTH = 'Earth';
    const ELEMENT_WIND = 'Wind';
    const ELEMENT_WATER = 'Water';
    const ELEMENT_LIGHTNING = 'Lightning';

    const PURCHASE_TYPE_DEFAULT = 1;
    const PURCHASE_TYPE_PURCHASABLE = 2;
    const PURCHASE_TYPE_NON_PURCHASABLE = 3;
    const PURCHASE_TYPE_BLOODLINE = 4;

    const TYPE_NINJUTSU = 'ninjutsu';
    const TYPE_TAIJUTSU = 'taijutsu';
    const TYPE_GENJUTSU = 'genjutsu';

    const USE_TYPE_MELEE = 'physical';
    const USE_TYPE_PROJECTILE = 'projectile';
    const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
    const USE_TYPE_REMOTE_SPAWN = 'spawn';
    const USE_TYPE_BUFF = 'buff';
    const USE_TYPE_BARRIER = 'barrier';

    const TARGET_TYPE_FIGHTER_ID = 'fighter_id';
    const TARGET_TYPE_TILE = 'tile';
    const TARGET_TYPE_DIRECTION = 'direction';

    const POWER_PER_LEVEL_PERCENT = 0.3;
    const BL_POWER_PER_LEVEL_PERCENT = 0.5;
    const EFFECT_PER_LEVEL_PERCENT = 0.2;

    public static array $elements = [    
        self::ELEMENT_FIRE,
        self::ELEMENT_EARTH,
        self::ELEMENT_WIND,
        self::ELEMENT_WATER,
        self::ELEMENT_LIGHTNING,
    ];
    public static array $use_types = [
        self::USE_TYPE_MELEE,
        self::USE_TYPE_PROJECTILE,
        self::USE_TYPE_PROJECTILE_AOE,
        self::USE_TYPE_REMOTE_SPAWN,
        self::USE_TYPE_BUFF,
        self::USE_TYPE_BARRIER,
    ];

    public static array $attacking_use_types = [
        self::USE_TYPE_MELEE,
        self::USE_TYPE_PROJECTILE,
    ];

    public int $id;
    public string $name;
    public int $rank;
    public string $jutsu_type;

    public float $base_power;
    public float $power;

    public int $range;

    public ?string $effect;

    private float $base_effect_amount;
    public float $effect_amount;

    public int $effect_length;

    public string $description;
    public string $battle_text;

    public int $cooldown;

    public string $use_type;
    public string $target_type;

    public int $use_cost;
    public int $purchase_cost;
    public int $purchase_type;

    public ?int $parent_jutsu;

    // TODO: Upgrade to enum when PHP 8.1 releases
    public string $element;

    public string $hand_seals;

    public int $travel_speed = 1;

    // Dynamic vars
    public bool $is_bloodline = false;
    public bool $is_weapon = false;

    public int $level = 0;
    public int $exp = 0;

    public ?int $weapon_id = null;
    public ?Jutsu $weapon_effect = null;
    public bool $effect_only = false;

    public ?string $combat_id = null;

    /**
     * Jutsu constructor.
     * @param int         $id
     * @param string      $name
     * @param int         $rank
     * @param string      $jutsu_type
     * @param float       $base_power
     * @param string|null $effect
     * @param float|null  $base_effect_amount
     * @param int|null    $effect_length
     * @param string      $description
     * @param string      $battle_text
     * @param int         $cooldown
     * @param string      $use_type
     * @param string      $target_type
     * @param int         $use_cost
     * @param int         $purchase_cost
     * @param int         $purchase_type
     * @param int|null    $parent_jutsu
     * @param string      $element
     * @param string      $hand_seals
     */
    public function __construct(int $id, string $name, int $rank, string $jutsu_type, float $base_power, int $range,
        ?string $effect, ?float $base_effect_amount, ?int $effect_length, string $description, string $battle_text, int $cooldown,
        string $use_type, string $target_type, int $use_cost, int $purchase_cost, int $purchase_type, ?int $parent_jutsu, string $element,
        string $hand_seals
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->rank = $rank;
        $this->jutsu_type = $jutsu_type;

        $this->base_power = $base_power;
        $this->power = $this->base_power;

        $this->range = $range;
        if($this->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            $this->range = 1;
        }
        if($this->jutsu_type == Jutsu::TYPE_GENJUTSU && in_array($use_type, self::$attacking_use_types)) {
            $this->base_power = $this->base_power * 0.55;
            $this->power = round($this->base_power, 2);
            // $this->effect_only = true; // toggle this if you turn the power back to 1
        }

        $this->effect = $effect;

        $this->base_effect_amount = $base_effect_amount ?? 0;
        $this->effect_amount = $this->base_effect_amount;

        $this->effect_length = $effect_length ?? 0;

        $this->description = $description;
        $this->battle_text = $battle_text;

        $this->cooldown = $cooldown;

        $this->use_type = $use_type;
        $this->target_type = $target_type;
        $this->use_cost = $use_cost;
        $this->purchase_cost = $purchase_cost;
        $this->purchase_type = $purchase_type;

        $this->parent_jutsu = $parent_jutsu;
        $this->element = $element;
        $this->hand_seals = $hand_seals;
    }

    #[Pure]
    public static function fromArray(int $id, array $jutsu_data): Jutsu {
        return new Jutsu(
            id: $id,
            name: $jutsu_data['name'],
            rank: $jutsu_data['rank'],
            jutsu_type: $jutsu_data['jutsu_type'],
            base_power: $jutsu_data['power'],
            range: $jutsu_data['range'],
            effect: $jutsu_data['effect'],
            base_effect_amount: $jutsu_data['effect_amount'],
            effect_length: $jutsu_data['effect_length'],
            description: $jutsu_data['description'],
            battle_text: $jutsu_data['battle_text'],
            cooldown: $jutsu_data['cooldown'],
            use_type: $jutsu_data['use_type'],
            target_type: $jutsu_data['target_type'] ?? Jutsu::TARGET_TYPE_TILE,
            use_cost: $jutsu_data['use_cost'],
            purchase_cost: $jutsu_data['purchase_cost'],
            purchase_type: $jutsu_data['purchase_type'],
            parent_jutsu: $jutsu_data['parent_jutsu'],
            element: $jutsu_data['element'],
            hand_seals: $jutsu_data['hand_seals']
        );
    }
    
    public function setLevel(int $level, int $exp) {
        $this->level = $level;
        $this->exp = $exp;

        $level_power_multiplier = $this->is_bloodline ?
            self::BL_POWER_PER_LEVEL_PERCENT / 100 : self::POWER_PER_LEVEL_PERCENT / 100;
        $level_effect_multiplier = self::EFFECT_PER_LEVEL_PERCENT / 100;


        $this->power = $this->base_power * (1 + ($this->level * $level_power_multiplier));
        $this->power = round($this->power, 2);

        if($this->effect && $this->effect != 'none') {
            $this->effect_amount = $this->base_effect_amount *
                (1 + round($this->level * $level_effect_multiplier, 3));
        }
    }

    public function setWeapon(int $weapon_id, $effect, $effect_amount): Jutsu {
        $this->weapon_id = $weapon_id;
        $this->weapon_effect = new Jutsu(
            id: $weapon_id * -1,
            name: $this->name,
            rank: $this->rank,
            jutsu_type: Jutsu::TYPE_TAIJUTSU,
            base_power: $this->power,
            range: 0,
            effect: $effect,
            base_effect_amount: $effect_amount,
            effect_length: 2,
            description: $this->description,
            battle_text: $this->battle_text,
            cooldown: $this->cooldown,
            use_type: $this->use_type,
            target_type: $this->target_type,
            use_cost: $this->use_cost,
            purchase_cost: $this->purchase_cost,
            purchase_type: $this->purchase_type,
            parent_jutsu: $this->parent_jutsu,
            element: $this->element,
            hand_seals: $this->hand_seals
        );
        $this->weapon_effect->is_weapon = true;

        return $this->weapon_effect;
    }

    public function setCombatId(string $fighter_combat_id) {
        $prefix = $this->is_bloodline ? 'BL_J' : 'J';
        $this->combat_id = $prefix . $this->id . ':' . $fighter_combat_id;
    }

    public function hasEffect(): bool {
        return $this->effect && $this->effect != 'none';
    }

    #[Pure]
    public function isAllyTargetType(): bool {
        return in_array($this->use_type, [Jutsu::USE_TYPE_BUFF, Jutsu::USE_TYPE_BARRIER]);
    }

    // TODO: Replace public usages of level with this, privatize level
    /*public function getLevel() {
        return $this->level;
    }*/

    /**
     * @param System $system
     * @return Jutsu[]
     */
    public static function fetchAll(System $system): array {
        $result = $system->query("SELECT * FROM `jutsu` ORDER BY `rank` ASC, `purchase_cost` ASC");

        $jutsu = [];
        while($jutsu_db = $system->db_fetch($result)) {
            $jutsu[$jutsu_db['jutsu_id']] = Jutsu::fromArray($jutsu_db['jutsu_id'], $jutsu_db);
        }

        return $jutsu;
    }
}