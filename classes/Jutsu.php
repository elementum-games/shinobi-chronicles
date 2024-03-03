<?php

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . "/Effect.php";
require_once __DIR__ . '/enum/Element.php';

enum JutsuOffenseType: string {
    case NINJUTSU = 'ninjutsu';
    case TAIJUTSU = 'taijutsu';
    case GENJUTSU = 'genjutsu';

    public static function values(): array {
        return array_map(function ($case){
            return $case->value;
        }, self::cases());
    }
}

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
    const PURCHASE_TYPE_EVENT_SHOP = 5;
    const PURCHASE_TYPE_LINKED = 6;

    const MAX_LEVEL = 100;
    const REFUND_AMOUNT = 0.1;

    const TYPE_NINJUTSU = 'ninjutsu';
    const TYPE_TAIJUTSU = 'taijutsu';
    const TYPE_GENJUTSU = 'genjutsu';

    const USE_TYPE_MELEE = 'physical';
    const USE_TYPE_PROJECTILE = 'projectile';
    const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
    const USE_TYPE_REMOTE_SPAWN = 'spawn';
    const USE_TYPE_BUFF = 'buff';
    const USE_TYPE_BARRIER = 'barrier';
    const USE_TYPE_INDIRECT = 'indirect';

    const TARGET_TYPE_FIGHTER_ID = 'fighter_id';
    const TARGET_TYPE_TILE = 'tile';
    const TARGET_TYPE_DIRECTION = 'direction';

    const POWER_PER_LEVEL_PERCENT = 0.3;
    const EFFECT_PER_LEVEL_PERCENT = 0.2;

    const GENIN_CHUUNIN_SCALE_MULTIPLIER = 1.4; // 2.9 => 3.9 = +34.4%
    const GENIN_JONIN_SCALE_MULTIPLIER = 1.75; // 2.9 => 4.9 = +69%
    const CHUUNIN_JONIN_SCALE_MULTIPLIER = 1.25;

    const BALANCE_BASELINE_POWER = 4.4;
    const BALANCE_EFFECT_RATIOS = [
        'offense_boost' => 1.75,
        'elemental_boost' => 1.15,
        'evasion_nerf' => 2.,
        'offense_nerf' => 1.9,
        'erosion' => 0.35,
        'vulnerability' => 2,
        'elemental_vulnerability' => 1.25,
        'hybrid_elemental_vulnerability' => 1.375,
        'resist_boost' => 1.85,
        'evasion_boost' => 2,
        'speed_boost' => 1,
        'piercing' => 0.75,
        'counter' => 3.25,
        'reflect' => 3.25,
        'substitution' => 2.5,
        'immolate' => 2.5,
        'recoil' => 0,
    ];

    /* Genjutsu gets declared with full power and effect instead of a tradeoff between them, we balance in code
    const GENJUTSU_ATTACK_POWER_MODIFIER = 0.55;*/

    public static array $elements = [
        self::ELEMENT_NONE,
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
        self::USE_TYPE_INDIRECT,
    ];

    public static array $attacking_use_types = [
        self::USE_TYPE_MELEE,
        self::USE_TYPE_PROJECTILE,
    ];

    public int $id;
    public string $name;
    public int $rank;
    public JutsuOffenseType $jutsu_type;

    public float $base_power;
    public float $power;

    public int $range;

    public ?string $effect;

    private float $base_effect_amount;
    public float $effect_amount;

    public int $effect_length;

    /** @var Effect[] */
    public array $effects;

    public string $description;
    public string $battle_text;

    public int $cooldown;

    public string $use_type;
    public string $target_type;

    public int $use_cost;
    public int $purchase_cost;
    public int $purchase_type;

    public ?int $parent_jutsu;

    public Element $element;

    public string $hand_seals;

    public int $travel_speed = 1;

    // Dynamic vars
    public bool $is_bloodline = false;
    public bool $is_weapon = false;

    public int $level = 0;
    public int $exp = 0;

    public bool $effect_only = false;

    public ?string $combat_id = null;

    public int $linked_jutsu_id = 0;

    private bool $rank_scaling_applied = false;

    public function __construct(
        int $id,
        string $name,
        int $rank,
        JutsuOffenseType $jutsu_type,
        float $base_power,
        int $range,
        ?string $effect_1,
        ?float $base_effect_amount_1,
        ?int $effect_length_1,
        ?string $effect_2,
        ?float $base_effect_amount_2,
        ?int $effect_length_2,
        string $description,
        string $battle_text,
        int $cooldown,
        string $use_type,
        string $target_type,
        int $use_cost,
        int $purchase_cost,
        int $purchase_type,
        ?int $parent_jutsu,
        Element $element,
        string $hand_seals,
        int $linked_jutsu_id = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->rank = $rank;
        $this->jutsu_type = $jutsu_type;

        $this->base_power = $base_power;
        $this->power = $this->base_power;

        $this->range = $range;
        if($this->jutsu_type == JutsuOffenseType::TAIJUTSU) {
            $this->range = 1;
        }

        // legacy
        $this->effect = $effect_1;
        $this->base_effect_amount = $base_effect_amount_1;
        $this->effect_amount = $this->base_effect_amount;
        $this->effect_length = $effect_length_1 ?? 0;

        // new effect array
        $this->effects[] = new Effect(
            effect: $effect_1,
            effect_amount: $base_effect_amount_1,
            effect_length: $effect_length_1,
            damage_type: $jutsu_type
        );
        $this->effects[] = new Effect(
            effect: $effect_2,
            effect_amount: $base_effect_amount_2,
            effect_length: $effect_length_2,
            damage_type: $jutsu_type
        );

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

        $this->linked_jutsu_id = $linked_jutsu_id;

        if($this->purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP && $this->purchase_cost === 0) {
            $this->purchase_cost = 1;
        }
    }

    public static function fromArray(int $id, array $jutsu_data): Jutsu {
        return new Jutsu(
            id: $id,
            name: $jutsu_data['name'],
            rank: $jutsu_data['rank'],
            jutsu_type: JutsuOffenseType::from($jutsu_data['jutsu_type']),
            base_power: $jutsu_data['power'],
            range: $jutsu_data['range'],
            effect_1: $jutsu_data['effect'] ?? 'none',
            base_effect_amount_1: $jutsu_data['effect_amount'] ?? 0,
            effect_length_1: $jutsu_data['effect_length'] ?? 0,
            effect_2: $jutsu_data['effect2'],
            base_effect_amount_2: $jutsu_data['effect2_amount'],
            effect_length_2: $jutsu_data['effect2_length'],
            description: $jutsu_data['description'],
            battle_text: $jutsu_data['battle_text'],
            cooldown: $jutsu_data['cooldown'],
            use_type: $jutsu_data['use_type'],
            target_type: $jutsu_data['target_type'] ?? Jutsu::TARGET_TYPE_TILE,
            use_cost: $jutsu_data['use_cost'],
            purchase_cost: $jutsu_data['purchase_cost'],
            purchase_type: $jutsu_data['purchase_type'],
            parent_jutsu: $jutsu_data['parent_jutsu'],
            element: Element::from($jutsu_data['element']),
            hand_seals: $jutsu_data['hand_seals'],
            linked_jutsu_id: $jutsu_data['linked_jutsu_id'],
        );
    }

    public function setLevel(int $level, int $exp): void {
        $this->level = $level;
        $this->exp = $exp;

        $this->recalculatePower();

        $level_effect_multiplier = self::EFFECT_PER_LEVEL_PERCENT / 100;
        foreach ($this->effects as $index => $effect) {
            if ($effect->effect && $effect->effect != 'none') {
                $this->effects[$index]->effect_amount = $effect->base_effect_amount *
                    (1 + round($this->level * $level_effect_multiplier, 3));
                $this->effects[$index]->display_effect_amount = $this->effects[$index]->effect_amount;
            }
        }
    }

    public function recalculatePower(): void {
        $level_power_multiplier = self::POWER_PER_LEVEL_PERCENT / 100;

        $this->power = $this->base_power * (1 + ($this->level * $level_power_multiplier));
        $this->power = round($this->power, 2);
    }

    public function setCombatId(string $fighter_combat_id) {
        $prefix = $this->is_bloodline ? 'BL_J' : 'J';
        $this->combat_id = $prefix . $this->id . ':' . $fighter_combat_id;
    }

    public function hasEffect(): bool {
        $has_effect = false;
        foreach ($this->effects as $effect) {
            if ($effect && $effect->effect != 'none') {
                $has_effect = true;
            }
        }
        return $has_effect;
    }

    public function hasElement(): bool {
        if (isset($this->element) && $this->element != self::ELEMENT_NONE && $this->element != 'none') {
           return true;
        }
        return false;
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
     * @throws DatabaseDeadlockException
     */
    public static function fetchAll(System $system): array {
        $result = $system->db->query("SELECT * FROM `jutsu` ORDER BY `rank` ASC, `purchase_cost` ASC");

        $jutsu = [];
        while($jutsu_db = $system->db->fetch($result)) {
            $jutsu[$jutsu_db['jutsu_id']] = Jutsu::fromArray($jutsu_db['jutsu_id'], $jutsu_db);
        }

        return $jutsu;
    }


    public function applyRankScaling(int $player_rank_num): void {
        if($this->rank_scaling_applied) {
            // Applying this twice to the same jutsu would cause it to be super overpowered
            throw new RuntimeException("Rank scaling already applied!");
        }
        $this->rank_scaling_applied = true;

        if ($this->purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
            if ($player_rank_num == 3) {
                $this->use_cost *= 2;
                $this->base_power *= Jutsu::GENIN_CHUUNIN_SCALE_MULTIPLIER;
                $this->power *= Jutsu::GENIN_CHUUNIN_SCALE_MULTIPLIER;
            }
            else if ($player_rank_num == 4) {
                $this->use_cost *= 3;
                $this->base_power *= Jutsu::GENIN_JONIN_SCALE_MULTIPLIER;
                $this->power *= Jutsu::GENIN_JONIN_SCALE_MULTIPLIER;
            }
        }
        else if($this->purchase_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
            if ($player_rank_num >= 3 && $this->rank < 3) {
                $this->power *= 1.4;
            }
            if ($player_rank_num >= 4 && $this->rank < 4) {
                $this->power *= 1.25;
            }
        }
    }

    public function getBalanceMaxUtility(): float {
        $level_power_multiplier = self::POWER_PER_LEVEL_PERCENT / 100;
        $capped_power = $this->base_power * (1 + (self::MAX_LEVEL * $level_power_multiplier));
        if(!$this->rank_scaling_applied) {
            if ($this->purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
                $capped_power *= Jutsu::GENIN_JONIN_SCALE_MULTIPLIER;
            }
            else if($this->purchase_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
                if ($this->rank == 2) {
                    $capped_power *= Jutsu::GENIN_JONIN_SCALE_MULTIPLIER;
                }
                else if ($this->rank == 3) {
                    $capped_power *= Jutsu::CHUUNIN_JONIN_SCALE_MULTIPLIER;
                }
            }
        }
        if($this->use_type == Jutsu::USE_TYPE_BUFF) {
            $capped_power = 0;
        }

        $cr_discount_per_turn_multiplier = 0.005; // 0.5% discount

        // For calculating resid/vuln synergy
        $residual_effect_info = [];
        $vuln_effect_info = [];

        $residual_effect_percent = 0;
        $compound_residual_effect_percent = 0;
        $compound_residual_discount = 0;
        $recoil_effect_percent = 0;
        $piercing_effect_percent = 0;

        $total_effect_utility = 0;
        
        $level_effect_multiplier = self::EFFECT_PER_LEVEL_PERCENT / 100;

        $total_elemental_vuln = 0;
        $count_elemental_vulns = 0;

        foreach ($this->effects as $effect) {
            if(!$effect->effect || $effect->effect == 'none') continue;

            $capped_effect_amount = $effect->base_effect_amount * (1 + (self::MAX_LEVEL * $level_effect_multiplier));
            $capped_effect_amount = round($capped_effect_amount / 100, 4);

            switch($effect->effect) {
                case 'none':
                case 'barrier':
                    break;
                case 'residual_damage':
                case 'delayed_residual':
                    $residual_effect_percent += $capped_effect_amount * $effect->effect_length;

                    $residual_effect_info[] = [
                        'total_amount' => $residual_effect_percent,
                        'length' => $effect->effect_length,
                    ];
                    break;
                case 'compound_residual':
                    $compound_residual_effect_percent += $capped_effect_amount * $effect->effect_length;

                    $max_damage_multiplier = pow(
                        1 + BattleEffectsManager::COMPOUND_RESIDUAL_INCREASE,
                        $effect->effect_length - 1
                    );

                    $extra_effective_percent = $compound_residual_effect_percent * ($max_damage_multiplier - 1) * 0.5;
                    $compound_residual_effect_percent += $extra_effective_percent;

                    $residual_effect_info[] = [
                        'total_amount' => $compound_residual_effect_percent,
                        'length' => $effect->effect_length,
                    ];

                    // Discount
                    $compound_residual_discount += $compound_residual_effect_percent * ($cr_discount_per_turn_multiplier * $effect->effect_length);
                    break;
                case 'recoil':
                    $recoil_effect_percent += $capped_effect_amount * $effect->effect_length;
                    break;
                case 'immolate':
                    $total_effect_utility += max(0, self::BALANCE_EFFECT_RATIOS['immolate'] * ($capped_effect_amount - 1));
                    break;
                case 'counter':
                case 'substitution':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS[$effect->effect] * $capped_effect_amount;
                    break;
                case 'reflect':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS[$effect->effect] * $capped_effect_amount;
                    $residual_effect_info[] = [
                        'total_amount' => $total_effect_utility / 2, // only half the effect is residual damage dealt
                        'length' => $effect->effect_length,
                    ];
                    break;
                case 'piercing':
                    $piercing_effect_percent += $capped_effect_amount;
                    break;

                case 'ninjutsu_boost':
                case 'taijutsu_boost':
                case 'genjutsu_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['offense_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'speed_boost':
                case 'cast_speed_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['speed_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'fire_boost':
                case 'wind_boost':
                case 'lightning_boost':
                case 'earth_boost':
                case 'water_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['elemental_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'evasion_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['evasion_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'resist_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['resist_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'ninjutsu_nerf':
                case 'taijutsu_nerf':
                case 'genjutsu_nerf':
                case 'offense_nerf':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['offense_nerf'] * $capped_effect_amount * $effect->effect_length;
                    break;

                case 'cast_speed_nerf':
                case 'speed_nerf':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['speed_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;

                case 'vulnerability':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['vulnerability'] * $capped_effect_amount * $effect->effect_length;

                    $vuln_effect_info[] = [
                        'amount' => $capped_effect_amount,
                        'length' => $effect->effect_length,
                    ];
                    break;
                case 'fire_vulnerability':
                case 'wind_vulnerability':
                case 'lightning_vulnerability':
                case 'earth_vulnerability':
                case 'water_vulnerability':
                    $total_elemental_vuln += $capped_effect_amount * $effect->effect_length;
                    $count_elemental_vulns++;
                    break;
                case 'evasion_nerf':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['evasion_nerf'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'erosion':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['erosion'] * $capped_effect_amount * $effect->effect_length;
                    break;
            }
        }

        if($count_elemental_vulns >= 2) {
            $total_effect_utility += ($total_elemental_vuln / $count_elemental_vulns) * self::BALANCE_EFFECT_RATIOS['hybrid_elemental_vulnerability'];
        }
        else if($count_elemental_vulns == 1) {
            $total_effect_utility += $total_elemental_vuln * self::BALANCE_EFFECT_RATIOS['elemental_vulnerability'];
        }

        $residual_power = $capped_power * $residual_effect_percent;
        $compound_residual_power = $capped_power * $compound_residual_effect_percent;

        $recoil_power = $recoil_effect_percent * ($capped_power + $residual_power + $compound_residual_power);
        $recoil_self_damage = $recoil_effect_percent * $capped_power;

        // Resid+Vuln synergy
        $resid_vuln_extra_power = 0;
        if(count($residual_effect_info) > 0 && count($vuln_effect_info) > 0) {
            foreach($residual_effect_info as $residual_effect) {
                foreach($vuln_effect_info as $vuln_effect) {
                    $duration_multiplier = min(1, $vuln_effect['length'] / $residual_effect['length']);
                    $resid_vuln_extra_power += $capped_power * $residual_effect['total_amount'] * $vuln_effect['amount'] * $duration_multiplier;
                }
            }
        }

        // Final power
        $total_effective_power = $capped_power + $residual_power + $compound_residual_power + $recoil_power + $resid_vuln_extra_power;
        $final_effect_utility = $total_effect_utility * self::BALANCE_BASELINE_POWER;

        // Debug
        /* echo "
            Capped Power: {$capped_power}<br />
            Residual: $residual_power<br />
            CR: $compound_residual_power<br />
            Recoil Power: $recoil_power<br />
            Resid+Vuln Power: $resid_vuln_extra_power<br />
            Total Power: {$total_effective_power}<br />
            <br />
            Effects: $final_effect_utility<br />
            CR Discount: -$compound_residual_discount<br />
            Recoil Self Damage: -$recoil_self_damage<br />
        ";*/

        // Damage
        $total_utility = $total_effective_power;
        // Piercing
        if($piercing_effect_percent > 0) {
            $total_utility += $total_effective_power * $piercing_effect_percent * self::BALANCE_EFFECT_RATIOS['piercing'];
        }
        // Effects
        $total_utility += $final_effect_utility;

        // Discounts
        $total_utility -= $compound_residual_discount;
        $total_utility -= $recoil_self_damage;

        return $total_utility;

    }
}