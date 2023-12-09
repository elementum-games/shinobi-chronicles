<?php

require_once __DIR__ . '/BattleEffect.php';

class BattleEffectsManager {
    const MAX_SPEED_REDUCTION = 50;

    protected System $system;

    /** @var BattleEffect[]  */
    public array $active_effects;

    /** @var BattleEffect[]  */
    public array $active_genjutsu;

    /** @var String[][] */
    public array $displays = [];

    /**
     * BattleEffectsManager constructor.
     * @param System $system
     * @param array  $raw_active_effects
     * @param array  $raw_active_genjutsu
     */
    public function __construct(System $system, array $raw_active_effects, array $raw_active_genjutsu) {
        $this->system = $system;
        $this->active_effects = array_map(function($effect) {
            return BattleEffect::fromArray($effect);
        }, $raw_active_effects);
        $this->active_genjutsu = array_map(function($effect) {
            return BattleEffect::fromArray($effect);
        }, $raw_active_genjutsu);
    }

    public function setEffect(Fighter $effect_user, $target_id, Jutsu $jutsu, Effect $effect, int $effect_num, $raw_damage): void {
        if(!$jutsu->combat_id) {
            $jutsu->setCombatId($effect_user->combat_id);
        }

        if ($effect->effect == 'release_genjutsu') {
            $this->releaseGenjutsu($effect_user, $jutsu);
            return;
        }

        $apply_effect = true;

        $debuff_power = ($jutsu->power <= 0) ? 0 : $raw_damage / $jutsu->power / 15;

        if ($this->system->debug['battle_effects']) {
            echo sprintf("JP: %s (%s)<br />", $jutsu->power, $effect->effect);
            echo sprintf("%s / %s<br />", $raw_damage, $debuff_power);
        }

        switch ($effect->effect) {
            case 'residual_damage':
            case 'delayed_residual':
            case 'ninjutsu_nerf':
            case 'taijutsu_nerf':
            case 'genjutsu_nerf':
            case 'daze':
            case 'ninjutsu_resist':
            case 'taijutsu_resist':
            case 'genjutsu_resist':
                $effect->effect_amount = round($raw_damage * ($effect->effect_amount / 100), 2);
                break;
            case 'absorb_chakra':
            case 'absorb_stamina':
                $effect->effect_amount = round($raw_damage * ($effect->effect_amount / 600), 2);
                break;
            case 'drain_chakra':
            case 'drain_stamina':
                $effect->effect_amount = round($raw_damage * ($effect->effect_amount / 300), 2);
                break;
            case 'ninjutsu_boost':
            case 'taijutsu_boost':
            case 'genjutsu_boost':
            case 'speed_boost':
            case 'cast_speed_boost':
            case 'speed_nerf':
            case 'cripple':
            case 'evasion_boost':
            case 'evasion_nerf':
            case 'resist_boost':
            case 'vulnerability':
            case 'offense_nerf':
            case 'substitution':
            case 'counter':
            case 'piercing':
            case 'immolate':
            case 'recoil':
            case 'reflect':
            case 'fire_boost':
            case 'wind_boost':
            case 'lightning_boost':
            case 'earth_boost':
            case 'water_boost':
            case 'fire_vulnerability':
            case 'wind_vulnerability':
            case 'lightning_vulnerability':
            case 'earth_vulnerability':
            case 'water_vulnerability':
                // No changes needed to base number, calculated in applyPassiveEffects
                break;
            case 'intelligence_boost':
            case 'willpower_boost':
            case 'intelligence_nerf':
            case 'willpower_nerf':
                $effect->effect_amount = round($debuff_power * ($effect->effect_amount / 100), 2);
                break;
            case Jutsu::USE_TYPE_BARRIER:
                $effect->effect_amount = $raw_damage;
                break;
            case 'reflect_damage':
                // No changes need to base number, calculated in jutsu collision
                break;
            default:
                $apply_effect = false;
                break;
        }

        if ($apply_effect) {
            $effect_id = $jutsu->combat_id;
            if ($jutsu->use_type == Jutsu::USE_TYPE_BARRIER) {
                $effect_id = self::barrierId($effect_user);
            } else if ($jutsu->is_weapon) {
                $effect_id = $effect_user->combat_id . ':WE:' . $effect->effect;
            }

            $effect_id = $effect_id . "_" . $effect_num;
            $this->active_effects[$effect_id] = new BattleEffect(
                user: $effect_user->combat_id,
                target: $target_id,
                turns: $effect->effect_length,
                effect: $effect->effect,
                effect_amount: $effect->effect_amount,
                damage_type: $jutsu->jutsu_type
            );

            if ($jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU) {
                $intelligence = ($effect_user->intelligence + $effect_user->intelligence_boost - $effect_user->intelligence_nerf);
                if ($intelligence <= 0) {
                    $intelligence = 1;
                }
                $this->active_effects[$effect_id]->power = $intelligence * $jutsu->power;
                $this->active_effects[$effect_id]->first_turn = true;
            }
        }
    }

    /** @noinspection DuplicatedCode */
    public function applyPassiveEffects(Fighter $player1, Fighter $player2) {
        // Apply passive effects
        $effect_target = null;
        $effect_user = null;

        // Jutsu passive effects
        foreach($this->active_effects as $id => $effect) {
            if($this->system->debug['battle']) {
                echo "[$id] " . $effect->effect . '(' . $effect->effect_amount . ') ->' .
                    $effect->target . '(' . $effect->turns . ' turns left)<br />';
            }

            if($effect->target == $player1->combat_id) {
                $effect_target =& $player1;
            }
            else {
                $effect_target =& $player2;
            }
            if($effect->user == $player1->combat_id) {
                $effect_user =& $player1;
            }
            else {
                $effect_user =& $player2;
            }
            $this->applyPassiveEffect($effect_target, $effect);
        }
        unset($effect_target);
        unset($effect_user);

        // Apply genjutsu passive effects
        foreach($this->active_genjutsu as $id => $genjutsu) {
            if($this->system->debug['battle']) {
                echo "[$id] " . $genjutsu->effect . '(' . $genjutsu->effect_amount . ') ->' .
                    $genjutsu->target . '(' . $genjutsu->turns . ' turns left)<br />';
            }

            if($genjutsu->target == $player1->combat_id) {
                $effect_target =& $player1;
            }
            else {
                $effect_target =& $player2;
            }

            $this->applyPassiveEffect($effect_target, $genjutsu);
        }

        // Apply item passive effects
        $this->applyArmorEffects($player1);
        $this->applyArmorEffects($player2);
    }

    public function applyArmorEffects(Fighter $fighter): void {
        if(!empty($fighter->equipped_armor)) {
            foreach($fighter->equipped_armor as $item_id) {
                if($fighter->hasItem($item_id)) {
                    $effect = new BattleEffect(
                        $fighter->combat_id,
                        $fighter->combat_id,
                        1,
                        $fighter->items[$item_id]->effect,
                        $fighter->items[$item_id]->effect_amount
                    );
                    $this->applyPassiveEffect($fighter, $effect);
                }
            }
        }
    }

    public function applyPassiveEffect(Fighter $target, BattleEffect $effect): bool {
        // Buffs
        if($effect->effect == 'ninjutsu_boost') {
            $target->ninjutsu_boost += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'taijutsu_boost') {
            $target->taijutsu_boost += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'genjutsu_boost') {
            $target->genjutsu_boost += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'cast_speed_boost') {
            $target->cast_speed_boost += $target->getCastSpeed(true) * ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'speed_boost') {
            $target->speed_boost += $target->getSpeed(true) * ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'evasion_boost' or $effect->effect == 'lighten') {
            $target->evasion_boost += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'intelligence_boost') {
            $target->intelligence_boost += $effect->effect_amount;
        }
        else if($effect->effect == 'willpower_boost') {
            $target->willpower_boost += $effect->effect_amount;
        }
        else if($effect->effect == 'ninjutsu_resist') {
            $target->ninjutsu_resist += $effect->effect_amount;
        }
        else if($effect->effect == 'genjutsu_resist') {
            $target->genjutsu_resist += $effect->effect_amount;
        }
        else if($effect->effect == 'taijutsu_resist' or $effect->effect == 'harden') {
            $target->taijutsu_resist += $effect->effect_amount;
        }
        else if($effect->effect == Jutsu::USE_TYPE_BARRIER) {
            $target->barrier += $effect->effect_amount;
        }
        else if($effect->effect == 'resist_boost') {
            $target->resist_boost += ($effect->effect_amount / 100);
        }

        // Debuffs - Temp disable, will need reworked later and only impacts NPCs
        /*$effect_amount = $effect->effect_amount - $target->getDebuffResist();
        if($effect_amount < $effect->effect_amount * Battle::MIN_DEBUFF_RATIO) {
            $effect_amount = $effect->effect_amount * Battle::MIN_DEBUFF_RATIO;
        }*/
        $effect_amount = $effect->effect_amount;

        if($effect->effect == 'ninjutsu_nerf') {
            $target->ninjutsu_nerf += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'taijutsu_nerf') {
            $target->taijutsu_nerf += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'genjutsu_nerf') {
            $target->genjutsu_nerf += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'offense_nerf' or $effect->effect == 'daze') {
            $target->ninjutsu_nerf += ($effect->effect_amount / 100);
            $target->taijutsu_nerf += ($effect->effect_amount / 100);
            $target->genjutsu_nerf += ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'speed_nerf') {
            $target->speed_nerf += $target->getSpeed(true) * ($effect->effect_amount / 100);
            $target->cast_speed_nerf += $target->getCastSpeed(true) * ($effect->effect_amount / 100);

            $target->speed_nerf = min($target->speed_nerf, $target->getSpeed(true) * self::MAX_SPEED_REDUCTION);
            $target->cast_speed_nerf = min($target->cast_speed_nerf, $target->getCastSpeed(true) * self::MAX_SPEED_REDUCTION);
        }
        else if($effect->effect == 'evasion_nerf' or $effect->effect == 'cripple') {
            $target->evasion_nerf += ($effect_amount / 100);
        }
        else if($effect->effect == 'intelligence_nerf') {
            $target->intelligence_nerf += $effect_amount;
        }
        else if($effect->effect == 'willpower_nerf') {
            $target->willpower_nerf += $effect_amount;
        }
        else if ($effect->effect == 'vulnerability') {
            $target->ninjutsu_weakness += ($effect->effect_amount / 100);
            $target->taijutsu_weakness += ($effect->effect_amount / 100);
            $target->genjutsu_weakness += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'fire_vulnerability') {
            $target->fire_weakness += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'wind_vulnerability') {
            $target->wind_weakness += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'lightning_vulnerability') {
            $target->lightning_weakness += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'earth_vulnerability') {
            $target->earth_weakness += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'water_vulnerability') {
            $target->water_weakness += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'fire_boost') {
            $target->fire_boost += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'wind_boost') {
            $target->wind_boost += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'lightning_boost') {
            $target->lightning_boost += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'earth_boost') {
            $target->earth_boost += ($effect->effect_amount / 100);
        }
        else if ($effect->effect == 'water_boost') {
            $target->water_boost += ($effect->effect_amount / 100);
        }
        return false;
    }

    /**
     * @param Fighter $player1
     * @param Fighter $player2
     * @throws RuntimeException
     */
    public function applyActiveEffects(Fighter $player1, Fighter $player2) {
        if(!empty($this->active_effects)) {
            foreach($this->active_effects as $id => $effect) {
                if($effect->target == $player1->combat_id) {
                    $effect_target =& $player1;
                }
                elseif($effect->target == $player2->combat_id) {
                    $effect_target =& $player2;
                }
                else {
                    throw new RuntimeException("Invalid effect target {$effect->target}");
                }

                if($effect->user == $player1->combat_id) {
                    $effect_user =& $player1;
                }
                else if($effect->user == $player2->combat_id) {
                    $effect_user =& $player2;
                }
                else {
                    throw new RuntimeException("Invalid effect user {$effect->user}");
                }

                $this->applyActiveEffect(
                    $effect_target,
                    $effect_user,
                    $effect
                );

                $this->active_effects[$id]->turns--;
                if($this->active_effects[$id]->turns <= 0) {
                    unset($this->active_effects[$id]);
                }
            }
        }
        if(!empty($this->active_genjutsu)) {
            foreach($this->active_genjutsu as $id => $genjutsu) {
                if($genjutsu->target == $player1->combat_id) {
                    $effect_target =& $player1;
                }
                else {
                    $effect_target =& $player2;
                }
                if($genjutsu->user == $player1->combat_id) {
                    $effect_user =& $player1;
                }
                else {
                    $effect_user =& $player2;
                }
                $this->applyActiveEffect($effect_target, $effect_user, $genjutsu);
                $this->active_genjutsu[$id]->turns--;
                $this->active_genjutsu[$id]->power *= 0.9;
                if($this->active_genjutsu[$id]->turns <= 0) {
                    unset($this->active_genjutsu[$id]);
                }

                if($genjutsu->first_turn) {
                    $genjutsu->first_turn = false;
                }
            }
        }

        $this->applyBloodlineActiveBoosts($player1);
        $this->applyBloodlineActiveBoosts($player2);
    }

    public function applyBloodlineActiveBoosts(Fighter $fighter) {
        if(!empty($fighter->bloodline->combat_boosts)) {
            foreach($fighter->bloodline->combat_boosts as $id=>$effect) {
                $this->applyActiveEffect(
                    $fighter,
                    $fighter,
                    new BattleEffect(
                        user: $fighter->combat_id,
                        target: $fighter->combat_id,
                        turns: 1,
                        effect: $effect['effect'],
                        effect_amount: $effect['effect_amount'],
                        damage_type: Jutsu::TYPE_TAIJUTSU
                    )
                );
            }
        }
    }

    public function applyActiveEffect(Fighter $target, Fighter $attacker, BattleEffect $effect): bool {
        if($target->health <= 0) {
            return false;
        }

        if($effect->effect == 'residual_damage' || $effect->effect == 'bleed' || $effect->effect == 'delayed_residual' || $effect->effect == 'reflect_damage') {
            $damage = $target->calcDamageTaken($effect->effect_amount, $effect->damage_type, true);
            $residual_damage_raw = $target->calcDamageTaken($effect->effect_amount, $effect->damage_type, true, apply_resists: false);
            $residual_damage_resisted = $residual_damage_raw - $damage;
            $attack_jutsu_color = BattleManager::getJutsuTextColor($effect->damage_type);

            if ($residual_damage_resisted > 0) {
                $this->addDisplay($target, $target->getName() . " takes " . "<span class=\"battle_text_{$effect->damage_type}\" style=\"color:{$attack_jutsu_color}\">" . round($damage) . "</span>" . " residual damage (resists " . "<span class=\"battle_text_{$effect->damage_type}\" style=\"color:{$attack_jutsu_color}\">" . round($residual_damage_resisted) . "</span>" . " residual damage)");
            } else {
                $this->addDisplay($target, $target->getName() . " takes " . "<span class=\"battle_text_{$effect->damage_type}\" style=\"color:{$attack_jutsu_color}\">" . round($damage) . "</span>" . " residual damage");
            }

            $target->health -= $damage;
            if ($target->health < 0) {
                $target->health = 0;
            }
        }
        else if($effect->effect == 'heal') {
            $heal = $effect->effect_amount;

            $this->addDisplay($target, $target->getName() . " heals " . "<span class=\"battle_text_heal\" style=\"color:green\">" . round($heal) . "</span>" . " health");

            $target->health += $heal;
        }
        else if($effect->effect == 'drain_chakra') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->damage_type);
            $this->addDisplay($target,
                $attacker->getName() . " drains $drain of " . $target->getName() . "'s chakra-"
            );

            $target->chakra -= $drain;
            if($target->chakra < 0) {
                $target->chakra = 0;
            }
        }
        else if($effect->effect == 'drain_stamina') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->damage_type);
            $this->addDisplay($target,
                $attacker->getName() . " drains $drain of " . $target->getName() . "'s stamina-"
            );

            $target->stamina -= $drain;
            if($target->stamina < 0) {
                $target->stamina = 0;
            }
        }
        else if($effect->effect == 'absorb_chakra') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->damage_type);
            $this->addDisplay($target,
                $attacker->getName() . " absorbs $drain of " . $target->getName() . "'s chakra-"
            );

            $target->chakra -= $drain;
            if($target->chakra < 0) {
                $target->chakra = 0;
            }
            $attacker->chakra += $drain;
            if($attacker->chakra > $attacker->max_chakra) {
                $attacker->chakra = $attacker->max_chakra;
            }
        }
        else if($effect->effect == 'absorb_stamina') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->damage_type);
            $this->addDisplay($target,
                $attacker->getName() . " absorbs $drain of " . $target->getName() . "'s stamina-"
            );

            $target->stamina -= $drain;
            if($target->stamina < 0) {
                $target->stamina = 0;
            }
            $attacker->stamina += $drain;
            if($attacker->stamina > $attacker->max_stamina) {
                $attacker->stamina = $attacker->max_stamina;
            }
        }

        return true;
    }

    public function updateBarrier(Fighter $fighter, Jutsu $fighter_jutsu) {
        if(isset($this->active_effects[self::barrierId($fighter)])) {
            if($fighter->barrier) {
                $this->active_effects[self::barrierId($fighter)]->effect_amount = $fighter->barrier;
            }
            else {
                unset($this->active_effects[self::barrierId($fighter)]);
            }
        }
        else if($fighter_jutsu->use_type == Jutsu::USE_TYPE_BARRIER && $fighter->barrier) {
            $barrier_jutsu = $fighter_jutsu;
            $barrier_jutsu->effects[0]->effect = Jutsu::USE_TYPE_BARRIER;
            $barrier_jutsu->effects[0]->effect_length = 1;
            $this->setEffect($fighter, $fighter->combat_id, $barrier_jutsu, $barrier_jutsu->effects[0], 0, $fighter->barrier);
        }
    }

    public function getAnnouncementText(Effect $effect) : string{
        $announcement_text = "";
        $effect_details = " (" . round($effect->display_effect_amount, 0) . "%, " . $effect->effect_length . ($effect->effect_length > 1 ? " turns" : " turn") . ")";
        switch($effect->effect){
            case 'taijutsu_nerf':
                $announcement_text = "[opponent]'s Taijutsu offense is being lowered" . $effect_details;
                break;
            case 'ninjutsu_nerf':
                $announcement_text = "[opponent]'s Ninjutsu offense is being lowered" . $effect_details;
                break;
            case 'daze':
            case 'genjutsu_nerf':
                $announcement_text = "[opponent]'s Genjutsu is being lowered" . $effect_details;
                break;
            case 'intelligence_nerf':
                $announcement_text = "[opponent]'s Intelligence is being lowered" . $effect_details;
                break;
            case 'willpower_nerf':
                $announcement_text = "[opponent]'s Willpower is being lowered" . $effect_details;
                break;
            case 'cast_speed_nerf':
                $announcement_text = "[opponent]'s Cast Speed is being lowered" . $effect_details;
                break;
            case 'speed_nerf':
            case 'cripple':
                $announcement_text = "[opponent]'s Speed is being lowered" . $effect_details;
                break;
            case 'residual_damage':
            case 'delayed_residual':
                $announcement_text = "[opponent] is taking Residual Damage" . $effect_details;
                break;
            case 'drain_chakra':
                $announcement_text = "[opponent]'s Chakra is being drained" . $effect_details;
                break;
            case 'drain_stamina':
                $announcement_text = "[opponent]'s Stamina is being drained" . $effect_details;
                break;
            case 'taijutsu_boost':
                $announcement_text = "[player]'s Taijutsu offense is being increased" . $effect_details;
                break;
            case 'ninjutsu_boost':
                $announcement_text = "[player]'s Ninjutsu offense is being increased" . $effect_details;
                break;
            case 'genjutsu_boost':
                $announcement_text = "[player]'s Genjutsu offense is being increased" . $effect_details;
                break;
            case 'speed_boost':
                $announcement_text = "[player]'s Speed is being increased" . $effect_details;
                break;
            case 'cast_speed_boost':
                $announcement_text = "[player]'s Cast Speed is being increased" . $effect_details;
                break;
            case 'vulnerability':
                $announcement_text = "[opponent] is taking increased damage" . $effect_details;
                break;
            case 'fire_vulnerability':
                $announcement_text = "[opponent] is vulnerable to Fire" . $effect_details;
                break;
            case 'wind_vulnerability':
                $announcement_text = "[opponent] is vulnerable to Wind" . $effect_details;
                break;
            case 'lightning_vulnerability':
                $announcement_text = "[opponent] is vulnerable to Lightning" . $effect_details;
                break;
            case 'earth_vulnerability':
                $announcement_text = "[opponent] is vulnerable to Earth" . $effect_details;
                break;
            case 'water_vulnerability':
                $announcement_text = "[opponent] is vulnerable to Water" . $effect_details;
                break;
            case 'fire_boost':
                $announcement_text = "[player]'s Fire jutsu are empowered" . $effect_details;
                break;
            case 'wind_boost':
                $announcement_text = "[player]'s Wind jutsu are empowered" . $effect_details;
                break;
            case 'lightning_boost':
                $announcement_text = "[player]'s Lightning jutsu are empowered" . $effect_details;
                break;
            case 'earth_boost':
                $announcement_text = "[player]'s Earth jutsu are empowered" . $effect_details;
                break;
            case 'water_boost':
                $announcement_text = "[player]'s Water jutsu are empowered" . $effect_details;
                break;
            case 'evasion_boost':
                $announcement_text = "[player]'s Evasion is being increased" . $effect_details;
                break;
            case 'evasion_nerf':
                $announcement_text = "[opponent]'s Evasion is being lowered" . $effect_details;
                break;
            case 'offense_nerf':
                $announcement_text = "[opponent]'s offense is being lowered" . $effect_details;
                break;
            case 'resist_boost':
                $announcement_text = "[player]'s defenses are being increased" . $effect_details;
                break;
            default:
                break;
        }

        return $announcement_text;
    }

    public static function barrierId(Fighter $fighter): string {
        return $fighter->combat_id . ':BARRIER';
    }

    public function releaseGenjutsu(Fighter $fighter, Jutsu $fighter_jutsu) {
        $intelligence = ($fighter->intelligence + $fighter->intelligence_boost - $fighter->intelligence_nerf);
        if($intelligence <= 0) {
            $intelligence = 1;
        }

        $release_power = $intelligence * $fighter_jutsu->power;
        foreach($this->active_genjutsu as $id => $genjutsu) {
            if($genjutsu['target'] == $fighter->combat_id && !isset($genjutsu['first_turn'])) {
                $r_power = $release_power * mt_rand(9, 11);
                $g_power = $genjutsu['power'] * mt_rand(9, 11);
                if($r_power > $g_power) {
                    unset($this->active_genjutsu[$id]);
                    $this->addDisplay($fighter,
                        $fighter->getName() . " broke free from [opponent]'s Genjutsu!");
                }
            }
        }
    }

    /**
     * @param       $fighter
     * @param Jutsu $fighter_jutsu
     * @throws RuntimeException
     */
    public function assertParentGenjutsuActive($fighter, Jutsu $fighter_jutsu) {
        if($fighter_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU) {
            return;
        }

        $parent_genjutsu_id = $fighter->combat_id . ':J' . $fighter_jutsu->parent_jutsu;
        $parent_jutsu = $fighter->jutsu[$fighter_jutsu->parent_jutsu];
        if(!isset($this->active_genjutsu[$parent_genjutsu_id]) or
            $this->active_genjutsu[$parent_genjutsu_id]['turns'] == $parent_jutsu->effect_length) {
            throw new RuntimeException($parent_jutsu->name .
                ' must be active for 1 turn before using this jutsu!'
            );
        }
    }

    public function hasDisplays(Fighter $fighter): bool {
        return count($this->displays[$fighter->combat_id] ?? []) > 0;
    }

    public function getDisplayText(Fighter $fighter): string {
        return htmlspecialchars_decode($this->system->db->clean(
            implode(
                '[br]',
                array_map(function($text) {
                    return "-{$text}-";
                }, $this->displays[$fighter->combat_id])
            )
        ));
    }

    public function addDisplay(Fighter $fighter, string $display) {
        if(!isset($this->displays[$fighter->combat_id])) {
            $this->displays[$fighter->combat_id] = [];
        }

        $this->displays[$fighter->combat_id][] = $display;
    }

    public function processImmolate(BattleAttack $battleAttack, Fighter $target): int {
        $immolate_raw_damage = 0;
        foreach ($this->active_effects as $index => $effect) {
            if (($effect->effect == 'residual_damage' || $effect->effect == 'bleed' || $effect->effect == 'delayed_residual' || $effect->effect == 'reflect_damage') && $effect->target == $target->combat_id) {
                $immolate_raw_damage += ($effect->turns * $effect->effect_amount);
                unset($this->active_effects[$index]);
            }
        }
        return $immolate_raw_damage;
    }
}