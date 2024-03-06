<?php

/*
 * Represents an attack taking place in a fight
 */

class BattleAttack {
    public float $damage = 0;

    // Jutsu values
    protected Jutsu $jutsu;

    public ?int $weapon_id = null;
    public ?Jutsu $weapon_effect = null;

    public string $name;
    public string $battle_text;
    public JutsuOffenseType $jutsu_type;
    public string $use_type;
    public Element $element;
    public float $power;
    public int $cooldown;

    public bool $effect_only = false;

    // Combat values
    public float $raw_damage = 0;

    public float $piercing_percent = 0;
    public float $substitution_percent = 0;
    public float $counter_percent = 0;

    public float $reflect_percent = 0;
    public float $reflect_duration = 0;

    public float $immolate_percent = 0;
    public float $immolate_raw_damage = 0;

    public float $recoil_percent = 0;
    public float $recoil_raw_damage = 0;

    public float $countered_percent = 0; // opponent's counter
    public float $countered_raw_damage = 0; // damage dealt from opponent
    public ?JutsuOffenseType $countered_jutsu_type = null; // jutsu type of opponent's counter

    public float $reflected_percent = 0; // opponent's reflect
    public float $reflected_raw_damage = 0; // damage dealt from opponent
    public ?JutsuOffenseType $reflected_jutsu_type = null; // jutsu type of opponent's reflect

    /** @var Effect[] */
    public array $effects = [];
    
    public function __construct(Jutsu $jutsu, float $raw_damage) {
        $this->jutsu = $jutsu;
        $this->raw_damage = $raw_damage;
        $this->damage = $raw_damage;

        // Cloned jutsu values
        $this->name = $jutsu->name;
        $this->battle_text = $jutsu->battle_text;
        $this->jutsu_type = $jutsu->jutsu_type;
        $this->use_type = $jutsu->use_type;
        $this->element = $jutsu->element;
        $this->power = $jutsu->power;
        $this->cooldown = $jutsu->cooldown;

        $this->effects = [...$this->jutsu->effects];

        if($this->isAllyTargetType() || $this->use_type == Jutsu::USE_TYPE_INDIRECT) {
            $this->effect_only = true;
        }
    }

    public function isDirectDamage(): bool {
        return in_array($this->jutsu->use_type, Jutsu::$attacking_use_types);
    }

    public function setWeapon(int $weapon_id, $effect, $effect_amount): Jutsu {
        $this->weapon_id = $weapon_id;
        $this->weapon_effect = new Jutsu(
            id: $weapon_id * -1,
            name: $this->jutsu->name,
            rank: $this->jutsu->rank,
            jutsu_type: JutsuOffenseType::TAIJUTSU,
            base_power: $this->jutsu->power,
            range: 0,
            effect_1: $effect,
            base_effect_amount_1: $effect_amount,
            effect_length_1: 2,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: $this->jutsu->description,
            battle_text: $this->jutsu->battle_text,
            cooldown: $this->cooldown,
            use_type: $this->use_type,
            target_type: $this->jutsu->target_type,
            use_cost: $this->jutsu->use_cost,
            purchase_cost: $this->jutsu->purchase_cost,
            purchase_type: $this->jutsu->purchase_type,
            parent_jutsu: $this->jutsu->parent_jutsu,
            element: $this->element,
            hand_seals: $this->jutsu->hand_seals
        );
        $this->weapon_effect->is_weapon = true;

        return $this->weapon_effect;
    }

    public function isAllyTargetType(): bool {
        return in_array($this->use_type, [Jutsu::USE_TYPE_BUFF, Jutsu::USE_TYPE_BARRIER]);
    }

    public function jutsuCombatId(): string {
        return $this->jutsu->combat_id;
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

    public function applyElementalClash(float $elemental_damage_modifier, float $elemental_effect_modifier): void {
        $this->damage *= $elemental_damage_modifier;

        foreach ($this->effects as $effect) {
            if(in_array($effect->effect, BattleEffectsManager::DAMAGE_EFFECTS)) {
                continue;
            }

            $effect->effect_amount *= $elemental_effect_modifier;
        }

        $this->piercing_percent *= $elemental_effect_modifier;
        $this->substitution_percent *= $elemental_effect_modifier;
        $this->counter_percent *= $elemental_effect_modifier;

        $this->reflect_percent *= $elemental_effect_modifier;
        $this->immolate_percent *= $elemental_effect_modifier;
    }
}