<?php

use JetBrains\PhpStorm\Pure;

abstract class Fighter {
    const BASE_OFFENSE = 35;
    const BASE_DEFENSE = 50;

    const SKILL_OFFENSE_RATIO = 0.10;
    const BLOODLINE_OFFENSE_RATIO = self::SKILL_OFFENSE_RATIO * 0.8;
    const BLOODLINE_JUTSU_OFFENSE_RATIO = self::SKILL_OFFENSE_RATIO * 0.9;
    const BLOODLINE_JUTSU_SKILL_RATIO = self::SKILL_OFFENSE_RATIO * 0.6;
    const BLOODLINE_DEFENSE_MULTIPLIER = 50;

    const SPEED_OFFENSE_RATIO = 0.2;

    const MIN_RAND = 33;
    const MAX_RAND = 36;

    const RESIST_SOFT_CAP = 0.5; // caps at 50% evasion
    const RESIST_SOFT_CAP_RATIO = 0.5; // evasion beyond soft cap only 50% as effective
    const RESIST_HARD_CAP = 0.75; // caps at 75% evasion

    public System $system;

    public string $combat_id;
    public int $user_id;

    // Energy
    public float $health = 100;
    public float $max_health = 100;
    public float $stamina = 100;
    public float $max_stamina = 100;
    public float $chakra = 100;
    public float $max_chakra = 100;

    public int $level = 1;
    private int $money = 0;

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
        Jutsu::ELEMENT_NONE
    ];

    // Inventory

    /** @var Jutsu[] */
    public array $jutsu;

    public array $items;

    public array $equipped_jutsu;
    public array $equipped_weapon_ids;
    public array $equipped_armor_ids;

    public int $bloodline_id;
    public ?Bloodline $bloodline = null;

    public array $bloodline_offense_boosts = [];
    public array $bloodline_defense_boosts = [];
    public float $bloodline_cast_speed_boost = 0;
    public float $bloodline_speed_boost = 0;

    public string $gender;
    public UserReputation $reputation;

    public string $current_ip;

    public int $max_movement_distance = 2; // not in use yet

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

    public $barrier;

    public $reputation_defense_boost = 0;

    public $evasion_boost = 0;
    public $resist_boost = 0;
    public $fire_boost = 0;
    public $wind_boost = 0;
    public $lightning_boost = 0;
    public $earth_boost = 0;
    public $water_boost = 0;

    public $last_damage_taken = 0;

    // Combat nerfs
    public $ninjutsu_nerf = 0;
    public $taijutsu_nerf = 0;
    public $genjutsu_nerf = 0;

    public $cast_speed_nerf = 0;
    public $speed_nerf = 0;
    public $intelligence_nerf = 0;
    public $willpower_nerf = 0;

    public $evasion_nerf = 0;

    public $taijutsu_weakness = 0;
    public $ninjutsu_weakness = 0;
    public $genjutsu_weakness = 0;
    public $fire_weakness = 0;
    public $wind_weakness = 0;
    public $lightning_weakness = 0;
    public $earth_weakness = 0;
    public $water_weakness = 0;

    // Getters
    abstract public function getName(): string;

    abstract public function getAvatarSize(): int;

    abstract public function getInventory();

    public function getMoney(): int {
        return $this->money;
    }

    public function applyBloodlineBoosts() {
        // Temp number fix inside
        if($this->bloodline != null) {
            if($this->system->debug['bloodline']) {
                echo "Setting passive combat boosts for {$this->getName()}<br />";
                echo "<br />";
            }

            // Apply bloodline passive combat boosts
            $this->bloodline_offense_boosts = array();
            $this->bloodline_defense_boosts = array();
            foreach($this->bloodline->combat_boosts as $jutsu_id => $boost) {
                if($this->system->debug['bloodline']) {
                    echo "[{$boost->effect}] = {$boost->effect_amount}<br />";
                }

                switch($boost->effect) {
                    // Nin/Tai/Gen boost applied in User::calcDamage()
                    case 'ninjutsu_boost':
                    case 'taijutsu_boost':
                    case 'genjutsu_boost':
                        $this->bloodline_offense_boosts[] = [
                            'effect' => $boost->effect,
                            'effect_amount' => $boost->effect_amount
                        ];
                        break;

                    case 'ninjutsu_resist':
                    case 'genjutsu_resist':
                    case 'taijutsu_resist':
                    case 'damage_resist':
                        $this->bloodline_defense_boosts[] = [
                            'effect' => $boost->effect,
                            'effect_amount' => $boost->effect_amount,
                        ];
                        break;

                    case 'cast_speed_boost':
                        $this->bloodline_cast_speed_boost += $boost->effect_amount;
                        $this->cast_speed_boost += $boost->effect_amount;
                        break;
                    case 'speed_boost':
                        $this->bloodline_speed_boost += $boost->effect_amount;
                        $this->speed_boost += $boost->effect_amount;
                        break;
                    case 'intelligence_boost':
                        $this->intelligence_boost += $boost->effect_amount;
                        break;
                    case 'willpower_boost':
                        $this->willpower_boost += $boost->effect_amount;
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

    public function getPrimaryJutsuType(): string {
        // Get total offense value
        $ninjutsu_skill = $this->ninjutsu_skill;
        $taijutsu_skill = $this->taijutsu_skill;
        $genjutsu_skill = $this->genjutsu_skill;
        if ($this->bloodline != null) {
            foreach ($this->bloodline->combat_boosts as $combat_boost) {
                switch ($combat_boost->effect) {
                    case 'ninjutsu_boost':
                        $ninjutsu_skill += $combat_boost->effect_amount;
                        break;
                    case 'taijutsu_boost':
                        $taijutsu_skill += $combat_boost->effect_amount;
                        break;
                    case 'genjutsu_boost':
                        $genjutsu_skill += $combat_boost->effect_amount;
                        break;
                    default:
                        break;
                }
            }
        }

        // First, is one of the offenses higher than the others
        if($ninjutsu_skill > max($taijutsu_skill, $genjutsu_skill)) {
            return 'ninjutsu';
        }
        if($taijutsu_skill > max($ninjutsu_skill, $genjutsu_skill)) {
            return 'taijutsu';
        }
        if($genjutsu_skill > max($ninjutsu_skill, $taijutsu_skill)) {
            return 'genjutsu';
        }

        // What's the offense boost on bloodline, if any
        if($this->bloodline != null) {
            return $this->bloodline->getPrimaryJutsuType();
        }

        // Fuck it, you're a ninja, you use ninjutsu - lmfao
        return 'ninjutsu';
    }

    /**
     * function calcDamage() CONTAINS TEMP NUMBER FIX
     *    Calculates raw damage based on player stats and jutsu or item strength
     *
     * @param Jutsu  $attack      Copy of the attack data.
     * @param bool   $disable_randomness
     * @return float|int
     * @throws RuntimeException
     */
    public function calcDamage(Jutsu $attack, bool $disable_randomness = false): float|int {
        if($this->system->debug['damage'])  {
            echo "<br />Debugging damage for {$this->getName()}<br />";
        }

        $speed = $this->speed + $this->speed_boost;
        $cast_speed = $this->cast_speed + $this->cast_speed_boost;

        switch($attack->jutsu_type) {
            case Jutsu::TYPE_TAIJUTSU:
                $off_skill = $this->taijutsu_skill + ($speed * Fighter::SPEED_OFFENSE_RATIO);
                $off_boost = $this->taijutsu_boost;
                $off_nerf = $this->taijutsu_nerf;
                break;
            case Jutsu::TYPE_GENJUTSU:
                $off_skill = $this->genjutsu_skill + ($cast_speed * Fighter::SPEED_OFFENSE_RATIO);
                $off_boost = $this->genjutsu_boost;
                $off_nerf = $this->genjutsu_nerf;
                break;
            case Jutsu::TYPE_NINJUTSU:
                $off_skill = $this->ninjutsu_skill + ($cast_speed * Fighter::SPEED_OFFENSE_RATIO);
                $off_boost = $this->ninjutsu_boost;
                $off_nerf = $this->ninjutsu_nerf;
                break;
            default:
                throw new RuntimeException("Invalid jutsu type!");
        }

        if ($attack->hasElement()) {
            switch (System::unSlug($attack->element)) {
                case 'Fire':
                    $off_boost += $this->fire_boost;
                    break;
                case 'Wind':
                    $off_boost += $this->wind_boost;
                    break;
                case 'Lightning':
                    $off_boost += $this->lightning_boost;
                    break;
                case 'Earth':
                    $off_boost += $this->earth_boost;
                    break;
                case 'Water':
                    $off_boost += $this->water_boost;
                    break;
                default:
                    break;
            }
        }

        switch($attack->purchase_type) {
            case Jutsu::PURCHASE_TYPE_DEFAULT:
            case Jutsu::PURCHASE_TYPE_PURCHASABLE:
            case Jutsu::PURCHASE_TYPE_EVENT_SHOP:
            case Jutsu::PURCHASE_TYPE_LINKED:
                $offense = self::BASE_OFFENSE + ($off_skill * self::SKILL_OFFENSE_RATIO);
                break;
            case Jutsu::PURCHASE_TYPE_BLOODLINE:
                $offense = self::BASE_OFFENSE +
                    ($off_skill * self::BLOODLINE_JUTSU_OFFENSE_RATIO) + ($this->bloodline_skill * self::BLOODLINE_JUTSU_SKILL_RATIO);
                break;
            default:
                throw new RuntimeException("Invalid jutsu type!");
        }
        $flat_offense_boost = 0;

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
                $flat_offense_boost += $effect_amount;
            }
        }

        if($this->system->debug['damage'])  {
            echo "Base offense: $offense<br />
                Flat boost: $flat_offense_boost<br />";
        }

        $offense += $flat_offense_boost;
        if($offense < 0) {
            $offense = 0;
        }

        // TEMP FIX
        if($offense > 900) {
            $extra_offense = ($offense - 900) * 0.6;
            $offense = $extra_offense + 900;
        }

        // Apply boost/nerf
        $off_modifier = 1 + $off_boost - $off_nerf;
        if($this->system->debug['damage'])  {
            echo "Off boost/nerf: {$off_boost} / {$off_nerf}<br />";
        }

        // if net offense nerf, apply caps
        if ($off_modifier < 1) {
            $nerf_percent = 1 - $off_modifier;

            if ($nerf_percent > BattleManager::OFFENSE_NERF_SOFT_CAP) {
                $nerf_percent = (
                        ($nerf_percent - BattleManager::OFFENSE_NERF_SOFT_CAP) *    BattleManager::OFFENSE_NERF_SOFT_CAP_RATIO
                    ) + BattleManager::OFFENSE_NERF_SOFT_CAP;
            }

            $nerf_percent = min($nerf_percent, BattleManager::OFFENSE_NERF_HARD_CAP);
            $off_modifier = 1 - $nerf_percent;
        }

        if($this->system->debug['damage']) {
            echo "Final offense modifier: {$off_modifier}<br />";
        }

        $offense = round($offense * $off_modifier, 2);

        $rand = mt_rand(self::MIN_RAND, self::MAX_RAND);
        if($disable_randomness) {
            $rand = (self::MIN_RAND + self::MAX_RAND) / 2;
        }

        $damage = round($offense * $attack->power * $rand, 2);
        if($damage < 0) {
            $damage = 0;
        }

        return $damage;
    }

    /**
     * @param        $raw_damage
     * @param string $defense_type ninjutsu, genjutsu, taijutsu
     * @param bool   $residual_damage
     * @param bool   $apply_resists
     * @param string $element
     * @param bool   $is_raw_damage
     * @return float|int
     */
    public function calcDamageTaken(
        $raw_damage,
        string $defense_type,
        bool $residual_damage = false,
        bool $apply_resists = true,
        string $element = Jutsu::ELEMENT_NONE,
        bool $is_raw_damage = true,
        bool $apply_weakness = true
    ): float|int {
        $defense = self::BASE_DEFENSE;

        if($defense <= 0) {
            $defense = 1;
        }
        if($apply_resists) {
            if (!empty($this->bloodline_defense_boosts)) {
                foreach ($this->bloodline_defense_boosts as $id => $boost) {
                    $boost_type = explode('_', $boost['effect'])[0];
                    if ($boost_type != $defense_type) {
                        continue;
                    }

                    $boost_amount = $boost['effect_amount'] * self::BLOODLINE_DEFENSE_MULTIPLIER;
                    if ($raw_damage < $boost_amount) {
                        $this->bloodline_defense_boosts[$id]['effect_amount'] -= ($raw_damage / self::BLOODLINE_DEFENSE_MULTIPLIER);
                        $raw_damage = 0;
                    } else {
                        $raw_damage -= $boost_amount;
                        unset($this->bloodline_defense_boosts[$id]);
                    }
                }
            }
        }

        $weakness_modifier = 0;
        switch($defense_type) {
            case 'ninjutsu':
                // Resist unfairly applies to nin/tai residuals which only have a 25% or so effect amount, so we lower its effectiveness compared to a regular hit
                if ($apply_resists && $is_raw_damage) {
                    $raw_damage -= $residual_damage ? $this->ninjutsu_resist * 0.5 : $this->ninjutsu_resist;
                }
                $weakness_modifier += $this->ninjutsu_weakness;
                break;
            case 'genjutsu':
                if ($apply_resists && $is_raw_damage) {
                    $raw_damage -= $this->genjutsu_resist;
                }
                $weakness_modifier += $this->genjutsu_weakness;
                break;
            case 'taijutsu':
                // Resist unfairly applies to residuals, so we lower its effectiveness compared to a regular hit
                if ($apply_resists && $is_raw_damage) {
                    $raw_damage -= $residual_damage ? $this->taijutsu_resist * 0.5 : $this->taijutsu_resist;
                }
                $weakness_modifier += $this->taijutsu_weakness;
                break;
            default:
                error_log("Invalid defense type! {$defense_type}");
        }
        switch (System::unSlug($element)) {
            case 'Fire':
                $weakness_modifier += $this->fire_weakness;
                break;
            case 'Wind':
                $weakness_modifier += $this->wind_weakness;
                break;
            case 'Lightning':
                $weakness_modifier += $this->lightning_weakness;
                break;
            case 'Earth':
                $weakness_modifier += $this->earth_weakness;
                break;
            case 'Water':
                $weakness_modifier += $this->water_weakness;
                break;
            default:
                break;
        }
        if ($apply_weakness) {
            $raw_damage *= 1 + $weakness_modifier;
        }

        if ($apply_resists && $this->reputation_defense_boost > 0) {
            $raw_damage *= (100 - $this->reputation_defense_boost) / 100;
        }

        if ($is_raw_damage) {
            $damage = round($raw_damage / $defense, 2);
        } else {
            $damage = $raw_damage;
        }

        if ($damage < 0.0) {
            $damage = 0;
        }
        if ($apply_resists) {
            $resist_boost = $this->resist_boost;
            // if higher than soft cap, apply penalty
            if ($resist_boost > BattleManager::RESIST_SOFT_CAP) {
                $resist_boost = (($resist_boost - BattleManager::RESIST_SOFT_CAP) * BattleManager::RESIST_SOFT_CAP_RATIO) + BattleManager::RESIST_SOFT_CAP;
            }
            // if still higher than cap cap, set to hard cap
            if ($resist_boost > BattleManager::RESIST_HARD_CAP) {
                $resist_boost = BattleManager::RESIST_HARD_CAP;
            }
            $damage *= 1 - $resist_boost;
        }
        return $damage;
    }

    public function getCastSpeed(bool $include_bloodline = false): float {
        return $include_bloodline ? $this->cast_speed + $this->bloodline_cast_speed_boost : $this->cast_speed;
    }

    public function getSpeed(bool $include_bloodline = false): float {
        return $include_bloodline ? $this->speed + $this->bloodline_speed_boost : $this->speed;
    }

    public function getBaseStatTotal(): int {
        return max(1, $this->total_stats);
    }

    // Actions
    abstract public function useJutsu(Jutsu $jutsu);

    abstract public function updateInventory();

    abstract public function updateData();
}