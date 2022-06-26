<?php

use JetBrains\PhpStorm\Pure;

abstract class Fighter {
    const BASE_OFFENSE = 35;

    const SKILL_OFFENSE_RATIO = 0.10;
    CONST BLOODLINE_OFFENSE_RATIO = self::SKILL_OFFENSE_RATIO * 0.8;

    const MIN_RAND = 33;
    const MAX_RAND = 37;

    public System $system;

    public string $combat_id;

    // Energy
    public float $health;
    public float $max_health;
    public float $stamina;
    public float $max_stamina;
    public float $chakra;
    public float $max_chakra;

    public int $level = 1;
    public int $money = 0;

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
    public array $equipped_weapon_ids;
    public array $equipped_armor;

    public int $bloodline_id;
    public ?Bloodline $bloodline;

    public array $bloodline_offense_boosts;
    public array $bloodline_defense_boosts;

    public string $gender;

    // In-combat vars
    public $ninjutsu_boost = 0;
    public $taijutsu_boost = 0;
    public $genjutsu_boost = 0;

    public $cast_speed_boost = 0;
    public $speed_boost = 0;
    public $intelligence_boost = 0;
    public $willpower_boost = 0;

    public $ninjutsu_resist = 0;
    public $taijutsu_resist = 0;
    public $genjutsu_resist = 0;

    public $defense_boost = 0;

    public $barrier;

    // Combat nerfs
    public $ninjutsu_nerf = 0;
    public $taijutsu_nerf = 0;
    public $genjutsu_nerf = 0;

    public $cast_speed_nerf = 0;
    public $speed_nerf = 0;
    public $intelligence_nerf = 0;
    public $willpower_nerf = 0;

    // Getters
    abstract public function getName(): string;

    abstract public function getAvatarSize(): int;

    abstract public function getInventory();

    public function applyBloodlineBoosts() {
        // Temp number fix inside
        if($this->bloodline_id) {
            if($this->system->debug['bloodline']) {
                echo "Setting passive combat boosts for {$this->getName()}<br />";
                echo "<br />";
            }

            // Apply bloodline passive combat boosts
            $this->bloodline_offense_boosts = array();
            $this->bloodline_defense_boosts = array();
            foreach($this->bloodline->combat_boosts as $jutsu_id => $effect) {
                if($this->system->debug['bloodline']) {
                    echo "[{$effect['effect']}] = {$effect['effect_amount']}<br />";
                }

                switch($effect['effect']) {
                    // Nin/Tai/Gen boost applied in User::calcDamage()
                    case 'ninjutsu_boost':
                    case 'taijutsu_boost':
                    case 'genjutsu_boost':
                        $x = count($this->bloodline_offense_boosts);
                        $this->bloodline_offense_boosts[$x]['effect'] = $effect['effect'];
                        $this->bloodline_offense_boosts[$x]['effect_amount'] = $effect['effect_amount'];
                        break;

                    case 'ninjutsu_resist':
                    case 'genjutsu_resist':
                    case 'taijutsu_resist':
                        $x = count($this->bloodline_defense_boosts);
                        $this->bloodline_defense_boosts[$x]['effect'] = $effect['effect'];
                        $this->bloodline_defense_boosts[$x]['effect_amount'] = $effect['effect_amount'];
                        break;

                    case 'cast_speed_boost':
                        $this->cast_speed_boost += $effect['effect_amount'];
                        break;
                    case 'speed_boost':
                        $this->speed_boost += $effect['effect_amount'];
                        break;
                    case 'intelligence_boost':
                        $this->intelligence_boost += $effect['effect_amount'];
                        break;
                    case 'willpower_boost':
                        $this->willpower_boost += $effect['effect_amount'];
                        break;
                }
            }

            if($this->system->debug['bloodline']) {
                echo "<br />";
            }
        }
    }

    abstract public function hasJutsu(int $jutsu_id): bool;
    abstract public function hasItem(int $item_id): bool;

    abstract public function hasEquippedJutsu(int $jutsu_id): bool;

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
    
    #[Pure]
    public function getDebuffResist(): float {
        $willpower = ($this->willpower + $this->willpower_boost - $this->willpower_nerf);

        $initial_willpower = $willpower;
        $extra_willpower = 0;
        if($willpower > 1000) {
            $initial_willpower = 1000;
            $extra_willpower = $willpower - 1000;
        }

        // Make first 1000 count extra to offset base offense
        $final_amount = ($initial_willpower * 1.35) + $extra_willpower;

        $avg_rand = floor((self::MIN_RAND + self::MAX_RAND) / 2);

        return $final_amount * (self::SKILL_OFFENSE_RATIO * 2) * $avg_rand;
    }

    /**
     * function calcDamage() CONTAINS TEMP NUMBER FIX
     *    Calculates raw damage based on player stats and jutsu or item strength
     *
     * @param Jutsu  $attack      Copy of the attack data.
     * @param bool   $disable_randomness
     * @return float|int
     * @throws Exception
     */
    public function calcDamage(Jutsu $attack, bool $disable_randomness = false): float|int {
        if($this->system->debug['damage'])  {
            echo "Debugging damage for {$this->getName()}<br />";
        }

        switch($attack->jutsu_type) {
            case Jutsu::TYPE_TAIJUTSU:
                $off_skill = $this->taijutsu_skill;
                $off_boost = $this->taijutsu_boost;
                $off_nerf = $this->taijutsu_nerf;
                break;
            case Jutsu::TYPE_GENJUTSU:
                $off_skill = $this->genjutsu_skill;
                $off_boost = $this->genjutsu_boost;
                $off_nerf = $this->genjutsu_nerf;
                break;
            case Jutsu::TYPE_NINJUTSU:
                $off_skill = $this->ninjutsu_skill;
                $off_boost = $this->ninjutsu_boost;
                $off_nerf = $this->ninjutsu_nerf;
                break;
            default:
                throw new Exception("Invalid jutsu type!");
        }

        switch($attack->purchase_type) {
            case Jutsu::PURCHASE_TYPE_DEFAULT:
            case Jutsu::PURCHASE_TYPE_PURCHASABLE:
                $offense = self::BASE_OFFENSE + ($off_skill * self::SKILL_OFFENSE_RATIO);
                break;
            case Jutsu::PURCHASE_TYPE_BLOODLINE:
                $offense = self::BASE_OFFENSE +
                    ($off_skill * self::BLOODLINE_OFFENSE_RATIO) + ($this->bloodline_skill * self::BLOODLINE_OFFENSE_RATIO);
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

                if($attack->purchase_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
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

        $rand = mt_rand(self::MIN_RAND, self::MAX_RAND);
        $disable_randomness = true;
        if($disable_randomness) {
            $rand = (self::MIN_RAND + self::MAX_RAND) / 2;
        }

        $damage = $offense * $attack->power * $rand;

        // Add non-BL damage boosts
        $damage_boost = $off_boost - $off_nerf;
        if($this->system->debug['damage']) {
            echo "Damage/boost/nerf/final boost: $damage / {$off_boost} / {$off_nerf} / {$damage_boost} <br />";
        }

        $damage = round($damage + $damage_boost, 2);
        if($damage < 0) {
            $damage = 0;
        }

        return $damage;
    }

    /**
     * @param        $raw_damage
     * @param string $defense_type ninjutsu, genjutsu, taijutsu
     * @param bool   $residual_damage
     * @return float|int
     */
    public function calcDamageTaken($raw_damage, string $defense_type, bool $residual_damage = false): float|int {
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

        $def_multiplier = 0.003;
        if($this instanceof NPC) {
            $def_multiplier = 0.001;
        }

        switch($defense_type) {
            case 'ninjutsu':
                $defense += System::diminishing_returns($this->ninjutsu_skill * $def_multiplier, 50);
                $raw_damage -= $residual_damage ? $this->ninjutsu_resist * 0.5 : $this->ninjutsu_resist;
                break;
            case 'genjutsu':
                $defense += System::diminishing_returns($this->genjutsu_skill * $def_multiplier, 50);
                $raw_damage -= $residual_damage ? $this->genjutsu_resist * 1 : $this->genjutsu_resist;
                break;
            case 'taijutsu':
                $defense += System::diminishing_returns($this->taijutsu_skill * $def_multiplier, 50);
                $raw_damage -= $residual_damage ? $this->taijutsu_resist * 0.5 : $this->taijutsu_resist;
                break;
            default:
                error_log("Invalid defense type! {$defense_type}");
        }

        if($this instanceof NPC && $defense_type == 'genjutsu') {
            $defense *= 0.8;
        }

        $damage = round($raw_damage / $defense, 2);
        if($damage < 0.0) {
            $damage = 0;
        }
        return $damage;
    }


    // Actions
    abstract public function useJutsu(Jutsu $jutsu);

    abstract public function updateInventory();

    abstract public function updateData();
}