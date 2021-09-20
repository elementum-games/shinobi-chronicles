<?php

abstract class Fighter {
    public System $system;

    public string $combat_id;

    // Energy
    public float $health;
    public float $max_health;
    public float $stamina;
    public float $max_stamina;
    public float $chakra;
    public float $max_chakra;

    public string $avatar_link = '';

    // Stats
    public float $ninjutsu_skill;
    public float $genjutsu_skill;
    public float $taijutsu_skill;
    public float $bloodline_skill;

    public float $cast_speed;
    public float $speed;
    public float $intelligence;
    public float $willpower;

    public array $elements = [
        'first' => Jutsu::ELEMENT_NONE
    ];

    // Inventory

    /** @var Jutsu[] */
    public array $jutsu;

    public array $items;

    public array $equipped_jutsu;
    public array $equipped_weapons;
    public array $equipped_armor;

    public int $bloodline_id;
    public ?Bloodline $bloodline;

    public array $bloodline_offense_boosts;
    public array $bloodline_defense_boosts;

    public $gender;

    // In-combat vars
    public $ninjutsu_boost;
    public $taijutsu_boost;
    public $genjutsu_boost;

    public $cast_speed_boost;
    public $speed_boost;
    public $intelligence_boost;
    public $willpower_boost;

    public $ninjutsu_resist;
    public $taijutsu_resist;
    public $genjutsu_resist;

    public $defense_boost;

    public $barrier;

    // Combat nerfs
    public $ninjutsu_nerf;
    public $taijutsu_nerf;
    public $genjutsu_nerf;

    public $cast_speed_nerf;
    public $speed_nerf;
    public $intelligence_nerf;
    public $willpower_nerf;

    // Getters
    abstract public function getName(): string;

    abstract public function getAvatarSize(): int;

    abstract public function getInventory();

    // abstract public function hasJutsu(int $jutsu_id): bool;
    abstract public function hasItem(int $item_id): bool;

    public function getSingularPronoun(): string {
        if($this->gender == 'Male') {
            return 'he';
        }
        else if($this->gender == 'Female') {
            return 'she';
        }
        else {
            return 'they';
        }
    }

    public function getPossessivePronoun(): string {
        if($this->gender == 'Male') {
            return 'his';
        }
        else if($this->gender == 'Female') {
            return 'her';
        }
        else {
            return 'their';
        }
    }

    /**
     * function calcDamage() CONTAINS TEMP NUMBER FIX
     *	Calculates raw damage based on player stats and jutsu or item strength
     *
     * @param Jutsu  $attack  Copy of the attack data.
     * @param string $attack_type (default_jutsu, equipped_jutsu, item, bloodline_jutsu)
     * @return float|int
     * @throws Exception
     */
    public function calcDamage(Jutsu $attack, $attack_type = 'default_jutsu') {
        switch($attack_type) {
            case 'default_jutsu':
            case 'equipped_jutsu':
                $offense = 35 + ($this->{$attack->jutsu_type . '_skill'} * 0.10);
                break;
            case 'bloodline_jutsu':
                $offense = 35 + ($this->{$attack->jutsu_type . '_skill'} * 0.08) + ($this->bloodline_skill * 0.08);
                break;
            default:
                throw new Exception("Invalid jutsu type!");
        }
        $offense_boost = 0;

        if(!empty($this->bloodline_offense_boosts)) {
            foreach($this->bloodline_offense_boosts as $id => $boost) {
                $boost_type = explode('_', $boost['effect'])[0];
                if($boost_type != $attack->jutsu_type) {
                    continue;
                }

                if($attack_type == 'bloodline_jutsu') {
                    $multiplier = 0.5;
                }
                else {
                    $multiplier = 1.0;
                }

                $effect_amount = round($boost['effect_amount'] * $multiplier, 2);
                $offense_boost += $effect_amount;
            }
        }

        if($this->system->debug['damage'])  {
            echo "Off: $offense +($offense_boost) -> " . ($offense + $offense_boost) . "<br />";
        }

        $offense += $offense_boost;
        $offense = round($offense, 2);
        if($offense < 0) {
            $offense = 0;
        }

        // TEMP FIX
        if($offense > 900) {
            $extra_offense = $offense - 900;
            $extra_offense *= 0.6;
            $offense = $extra_offense + 900;
        }

        $min = 25;
        $max = 45;
        $rand = (int)(($min + $max) / 2);
        // $rand = mt_rand($min, $max);

        $damage = $offense * $attack->power * $rand;

        // Add non-BL damage boosts
        $damage_boost = $this->{$attack->jutsu_type . '_boost'} - $this->{$attack->jutsu_type . '_nerf'};
        if($this->system->debug['damage']) {
            echo 'Damage/boost: ' . $damage . ' / ' . $damage_boost . '<br />';
        }
        $damage = round($damage + $damage_boost, 2);
        if($damage < 0) {
            $damage = 0;
        }

        return $damage;
    }

    /**
     * @param $raw_damage
     * @param string $defense_type ninjutsu, genjutsu, taijutsu
     * @return float|int
     */
    public function calcDamageTaken($raw_damage, string $defense_type) {
        $defense = 50 * (1 + $this->defense_boost);

        if($defense <= 0) {
            $defense = 1;
        }

        if(!empty($this->bloodline_defense_boosts)) {
            foreach($this->bloodline_defense_boosts as $id => $boost) {
                $boost_type = explode('_', $boost['effect'])[0];
                if($boost_type != $defense_type) {
                    continue;
                }

                $boost_amount = $boost['effect_amount'] * 35;
                if($raw_damage < $boost_amount) {
                    $this->bloodline_defense_boosts[$id]['effect_amount'] -= ($raw_damage / 35);
                    $raw_damage = 0;
                }
                else {
                    $raw_damage -= $boost_amount;
                    unset($this->bloodline_defense_boosts[$id]);
                }
            }
        }

        $def_multiplier = 0.03;
        if($this instanceof AI) {
            $def_multiplier = 0.01;
        }

        switch($defense_type) {
            case 'ninjutsu':
                $defense += System::diminishing_returns($this->ninjutsu_skill * $def_multiplier, 50);
                $raw_damage -= $this->ninjutsu_resist;
                break;
            case 'genjutsu':
                $defense += System::diminishing_returns($this->genjutsu_skill * $def_multiplier, 50);
                $raw_damage -= $this->genjutsu_resist;
                break;
            case 'taijutsu':
                $defense += System::diminishing_returns($this->taijutsu_skill * $def_multiplier, 50);
                $raw_damage -= $this->taijutsu_resist;
                break;
            default:
                error_log("Invalid defense type! {$defense_type}");
        }

        if($this instanceof AI && $defense_type == 'genjutsu') {
            $defense *= 0.8;
        }

        $damage = round($raw_damage / $defense, 2);
        if($damage < 0.0) {
            $damage = 0;
        }
        return $damage;
    }


    // Actions
    abstract public function useJutsu(Jutsu $jutsu, $purchase_type);

    abstract public function updateInventory();

    abstract public function updateData();
}