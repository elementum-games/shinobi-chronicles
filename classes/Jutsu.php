<?php

class Jutsu {
    const ELEMENT_NONE = 'None';
    const ELEMENT_FIRE = 'Fire';
    const ELEMENT_EARTH = 'Earth';
    const ELEMENT_WIND = 'Wind';
    const ELEMENT_WATER = 'Water';
    const ELEMENT_LIGHTNING = 'Lightning';

    const PURCHASE_TYPE_DEFAULT = 1;
    const PURCHASE_TYPE_PURCHASEABLE = 2;
    const PURCHASE_TYPE_NON_PURCHASEABLE = 3;
    const PURCHASE_TYPE_BLOODLINE = 4;

    const TYPE_NINJUTSU = 'ninjutsu';
    const TYPE_TAIJUTSU = 'taijutsu';
    const TYPE_GENJUTSU = 'genjutsu';

    const USE_TYPE_PHYSICAL = 'physical';
    const USE_TYPE_PROJECTILE = 'projectile';
    const USE_TYPE_BUFF = 'buff';
    const USE_TYPE_BARRIER = 'barrier';

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
    
    public int $id;
    public string $name;
    public int $rank;
    public string $jutsu_type;

    public float $base_power;
    public float $power;

    public ?string $effect;

    private float $base_effect_amount;
    public float $effect_amount;

    public int $effect_length;

    public string $description;
    public string $battle_text;

    public int $cooldown;

    public string $use_type;
    public int $use_cost;
    public int $purchase_cost;
    public int $purchase_type;

    public ?int $parent_jutsu;

    // TODO: Upgrade to enum when PHP 8.1 releases
    public string $element;

    public string $hand_seals;

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
     * @param float       $base_effect_amount
     * @param int         $effect_length
     * @param string      $description
     * @param string      $battle_text
     * @param int         $cooldown
     * @param string      $use_type
     * @param int         $use_cost
     * @param int         $purchase_cost
     * @param int         $purchase_type
     * @param int|null    $parent_jutsu
     * @param string      $element
     * @param string      $hand_seals
     */
    public function __construct(int $id, string $name, int $rank, string $jutsu_type, float $base_power, ?string $effect,
        ?float $base_effect_amount, ?int $effect_length, string $description, string $battle_text, int $cooldown,
        string $use_type, int $use_cost, int $purchase_cost, int $purchase_type, ?int $parent_jutsu, string $element,
        string $hand_seals
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->rank = $rank;
        $this->jutsu_type = $jutsu_type;

        $this->base_power = $base_power;
        $this->power = $this->base_power;

        $this->effect = $effect;

        $this->base_effect_amount = $base_effect_amount ?? 0;
        $this->effect_amount = $this->base_effect_amount;

        $this->effect_length = $effect_length ?? 0;

        $this->description = $description;
        $this->battle_text = $battle_text;

        $this->cooldown = $cooldown;

        $this->use_type = $use_type;
        $this->use_cost = $use_cost;
        $this->purchase_cost = $purchase_cost;
        $this->purchase_type = $purchase_type;

        $this->parent_jutsu = $parent_jutsu;
        $this->element = $element;
        $this->hand_seals = $hand_seals;
    }

    public static function fromArray(int $id, array $jutsu_data): Jutsu {
        return new Jutsu(
            $id,
            $jutsu_data['name'],
            $jutsu_data['rank'],
            $jutsu_data['jutsu_type'],
            $jutsu_data['power'],
            $jutsu_data['effect'],
            $jutsu_data['effect_amount'],
            $jutsu_data['effect_length'],
            $jutsu_data['description'],
            $jutsu_data['battle_text'],
            $jutsu_data['cooldown'],
            $jutsu_data['use_type'],
            $jutsu_data['use_cost'],
            $jutsu_data['purchase_cost'],
            $jutsu_data['purchase_type'],
            $jutsu_data['parent_jutsu'],
            $jutsu_data['element'],
            $jutsu_data['hand_seals']
        );
    }
    
    public function setLevel(int $level, int $exp) {
        $this->level = $level;
        $this->exp = $exp;

        $level_power_multiplier = $this->is_bloodline ?
            self::BL_POWER_PER_LEVEL_PERCENT / 100 : self::POWER_PER_LEVEL_PERCENT / 100;
        $level_effect_multiplier = self::EFFECT_PER_LEVEL_PERCENT / 100;

        $this->power = $this->base_power * (1 + round($this->level * $level_power_multiplier, 2));
        if($this->effect && $this->effect != 'none') {
            $this->effect_amount = $this->base_effect_amount *
                (1 + round($this->level * $level_effect_multiplier, 3));
        }
    }

    public function setWeapon(int $weapon_id, $effect, $effect_amount): Jutsu {
        $this->weapon_id = $weapon_id;
        $this->weapon_effect = new Jutsu(
            $weapon_id * -1,
             $this->name,
             $this->rank,
             Jutsu::TYPE_TAIJUTSU,
             $this->power,
             $effect,
             $effect_amount,
             2,
             $this->description,
             $this->battle_text,
             $this->cooldown,
             $this->use_type,
             $this->use_cost,
             $this->purchase_cost,
             $this->purchase_type,
             $this->parent_jutsu,
             $this->element,
             $this->hand_seals
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

    public function isAllyTargetType(): bool {
        return in_array($this->use_type, [Jutsu::USE_TYPE_BUFF, Jutsu::USE_TYPE_BARRIER]);
    }

    // TODO: Replace public usages of level with this, privatize level
    /*public function getLevel() {
        return $this->level;
    }*/
}