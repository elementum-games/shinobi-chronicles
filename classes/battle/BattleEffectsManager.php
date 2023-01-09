<?php

require_once __DIR__ . '/BattleEffect.php';

class BattleEffectsManager {
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

    public function setEffect(Fighter $effect_user, $target_id, Jutsu $jutsu, $raw_damage): void {
        if(!$jutsu->combat_id) {
            $jutsu->setCombatId($effect_user->combat_id);
        }
        if($jutsu->effect == 'release_genjutsu') {
            $this->releaseGenjutsu($effect_user, $jutsu);
            return;
        }

        $apply_effect = true;

        $debuff_power = ($jutsu->power <= 0) ? 0 : $raw_damage / $jutsu->power / 15;

        if($this->system->debug['battle_effects']) {
            echo sprintf("JP: %s (%s)<br />", $jutsu->power, $jutsu->effect);
            echo sprintf("%s / %s<br />", $raw_damage, $debuff_power);
        }

        if($jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU && !empty($jutsu->parent_jutsu)) {
            $parent_genjutsu_id = $effect_user->combat_id . ':J' . $jutsu->parent_jutsu;
            if(!empty($this->active_effects[$parent_genjutsu_id]->layer_active)) {
                $this->active_effects[$parent_genjutsu_id]->layer_active = true;
                $this->active_effects[$parent_genjutsu_id]->power *= 1.1;
            }
            $jutsu->power *= 1.1;
            $jutsu->effect_amount *= 1.1;
        }

        switch($jutsu->effect) {
            case 'residual_damage':
            case 'ninjutsu_boost':
            case 'taijutsu_boost':
            case 'genjutsu_boost':
            case 'ninjutsu_nerf':
            case 'taijutsu_nerf':
            case 'genjutsu_nerf':
            case 'ninjutsu_resist':
            case 'taijutsu_resist':
            case 'genjutsu_resist':
                $jutsu->effect_amount = round($raw_damage * ($jutsu->effect_amount / 100), 2);
                break;
            case 'absorb_chakra':
            case 'absorb_stamina':
                $jutsu->effect_amount = round($raw_damage * ($jutsu->effect_amount / 600), 2);
                break;
            case 'drain_chakra':
            case 'drain_stamina':
                $jutsu->effect_amount = round($raw_damage * ($jutsu->effect_amount / 300), 2);
                break;
            case 'speed_boost':
            case 'cast_speed_boost':
            case 'speed_nerf':
            case 'cast_speed_nerf':
                // No changes needed to base number, calculated in applyPassiveEffects
                break;
            case 'intelligence_boost':
            case 'willpower_boost':
            case 'intelligence_nerf':
            case 'willpower_nerf':
                $jutsu->effect_amount = round($debuff_power * ($jutsu->effect_amount / 100), 2);
                break;
            case Jutsu::USE_TYPE_BARRIER:
                $jutsu->effect_amount = $raw_damage;
                break;
            default:
                $apply_effect = false;
                break;
        }

        if($apply_effect) {
            $effect_id = $jutsu->combat_id;
            if($jutsu->use_type == Jutsu::USE_TYPE_BARRIER) {
                $effect_id = self::barrierId($effect_user);
            }
            else if($jutsu->is_weapon) {
                $effect_id = $effect_user->combat_id . ':WE:' . $jutsu->effect;
            }

            $this->active_effects[$effect_id] = BattleEffect::fromArray([
                'user' => $effect_user->combat_id,
                'target' => $target_id,
                'turns' => $jutsu->effect_length,
                'effect' => $jutsu->effect,
                'effect_amount' => $jutsu->effect_amount,
                'effect_type' => $jutsu->jutsu_type
            ]);
            if($jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU) {
                $intelligence = ($effect_user->intelligence + $effect_user->intelligence_boost - $effect_user->intelligence_nerf);
                if($intelligence <= 0) {
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
    
    public function applyArmorEffects(Fighter $fighter) {
        if(!empty($fighter->equipped_armor)) {
            foreach($fighter->equipped_armor as $item_id) {
                if($fighter->hasItem($item_id)) {
                    $effect = new BattleEffect(
                        $fighter->combat_id,
                        $fighter->combat_id,
                        1,
                        $fighter->items[$item_id]['effect'],
                        $fighter->items[$item_id]['effect_amount'],
                        BattleEffect::TYPE_BLOODLINE
                    );
                    $this->applyPassiveEffect($fighter, $effect);
                }
            }
        }
    }

    public function applyPassiveEffect(Fighter $target, BattleEffect $effect): bool {
        // Buffs
        if($effect->effect == 'ninjutsu_boost') {
            $target->ninjutsu_boost += $effect->effect_amount;
        }
        else if($effect->effect == 'taijutsu_boost') {
            $target->taijutsu_boost += $effect->effect_amount;
        }
        else if($effect->effect == 'genjutsu_boost') {
            $target->genjutsu_boost += $effect->effect_amount;
        }
        else if($effect->effect == 'cast_speed_boost') {
            $target->cast_speed_boost += $target->cast_speed * ($effect->effect_amount / 100);
        }
        else if($effect->effect == 'speed_boost' or $effect->effect == 'lighten') {
            $target->speed_boost += $target->speed * ($effect->effect_amount / 100);
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

        // Debuffs
        $effect_amount = $effect->effect_amount - $target->getDebuffResist();
        if($effect_amount < $effect->effect_amount * Battle::MIN_DEBUFF_RATIO) {
            $effect_amount = $effect->effect_amount * Battle::MIN_DEBUFF_RATIO;
        }

        if($effect->effect == 'ninjutsu_nerf') {
            $target->ninjutsu_nerf += $effect_amount;
        }
        else if($effect->effect == 'taijutsu_nerf') {
            $target->taijutsu_nerf += $effect_amount;
        }
        else if($effect->effect == 'genjutsu_nerf') {
            $target->genjutsu_nerf += $effect_amount;
        }
        else if($effect->effect == 'cast_speed_nerf') {
            $target->cast_speed_nerf += $target->cast_speed * ($effect_amount / 100);
        }
        else if($effect->effect == 'speed_nerf' or $effect->effect == 'cripple') {
            $target->speed_nerf += $target->speed * ($effect_amount / 100);
        }
        else if($effect->effect == 'intelligence_nerf' or $effect->effect == 'daze') {
            $target->intelligence_nerf += $effect_amount;
        }
        else if($effect->effect == 'willpower_nerf') {
            $target->willpower_nerf += $effect_amount;
        }
        return false;
    }

    /**
     * @param Fighter $player1
     * @param Fighter $player2
     * @throws Exception
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
                    throw new Exception("Invalid effect target {$effect->target}");
                }

                if($effect->user == $player1->combat_id) {
                    $effect_user =& $player1;
                }
                else if($effect->user == $player2->combat_id) {
                    $effect_user =& $player2;
                }
                else {
                    throw new Exception("Invalid effect user {$effect->user}");
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
                        $fighter->combat_id,
                        $fighter->combat_id,
                        1,
                        $effect['effect'],
                        $effect['effect_amount'],
                        Jutsu::TYPE_TAIJUTSU
                    )
                );
            }
        }
    }
    
    public function applyActiveEffect(Fighter $target, Fighter $attacker, BattleEffect $effect): bool {
        if($target->health <= 0) {
            return false;
        }

        if($effect->effect == 'residual_damage' || $effect->effect == 'bleed') {
            $damage = $target->calcDamageTaken($effect->effect_amount, $effect->effect_type, true);
            $this->addDisplay($target, $target->getName() . " takes $damage residual damage");

            $target->health -= $damage;
            if($target->health < 0) {
                $target->health = 0;
            }
        }
        else if($effect->effect == 'heal') {
            $heal = $effect->effect_amount;
            $this->addDisplay($target, $target->getName() . " heals $heal health");

            $target->health += $heal;
            if($target->health > $target->max_health) {
                $target->health = $target->max_health;
            }
        }
        else if($effect->effect == 'drain_chakra') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->effect_type);
            $this->addDisplay($target,
                $attacker->getName() . " drains $drain of " . $target->getName() . "'s chakra-"
            );

            $target->chakra -= $drain;
            if($target->chakra < 0) {
                $target->chakra = 0;
            }
        }
        else if($effect->effect == 'drain_stamina') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->effect_type);
            $this->addDisplay($target,
                $attacker->getName() . " drains $drain of " . $target->getName() . "'s stamina-"
            );

            $target->stamina -= $drain;
            if($target->stamina < 0) {
                $target->stamina = 0;
            }
        }
        else if($effect->effect == 'absorb_chakra') {
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->effect_type);
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
            $drain = $target->calcDamageTaken($effect->effect_amount, $effect->effect_type);
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
            $barrier_jutsu->effect = Jutsu::USE_TYPE_BARRIER;
            $barrier_jutsu->effect_length = 1;
            $this->setEffect($fighter, $fighter->combat_id, $barrier_jutsu, $fighter->barrier);
        }
    }

    public function getAnnouncementText(string $effect) : string{
        $announcement_text = "";
        switch($effect){
            case 'taijutsu_nerf':
                $announcement_text = "[opponent]'s Taijutsu offense is being lowered";
                break;
            case 'ninjutsu_nerf':
                $announcement_text = "[opponent]'s Ninjutsu offense is being lowered";
                break;
            case 'genjutsu_nerf':
                $announcement_text = "[opponent]'s Genjutsu is being lowered";
                break;
            case 'intelligence_nerf':
            case 'daze':
                $announcement_text = "[opponent]'s Intelligence is being lowered";
                break;
            case 'willpower_nerf':
                $announcement_text = "[opponent]'s Willpower is being lowered";
                break;
            case 'cast_speed_nerf':
                $announcement_text = "[opponent]'s Cast Speed is being lowered";
                break;
            case 'speed_nerf':
            case 'cripple':
                $announcement_text = "[opponent]'s Speed is being lowered";
                break;
            case 'residual_damage':
                $announcement_text = "[opponent] is taking Residual Damage";
                break;
            case 'drain_chakra':
                $announcement_text = "[opponent]'s Chakra is being drained";
                break;
            case 'drain_stamina':
                $announcement_text = "[opponent]'s Stamina is being drained";
                break;
            case 'taijutsu_boost':
                $announcement_text = "[player]'s Taijutsu offense is being increased";
                break;
            case 'ninjutsu_boost':
                $announcement_text = "[player]'s Ninjutsu offense is being increased";
                break;
            case 'genjutsu_boost':
                $announcement_text = "[player]'s Genjutsu offense is being increased";
                break;
            case 'speed_boost':
                $announcement_text = "[player]'s Speed is being increased";
                break;
            case 'cast_speed_boost':
                $announcement_text = "[player]'s Cast Speed is being increased";
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
     * @throws Exception
     */
    public function assertParentGenjutsuActive($fighter, Jutsu $fighter_jutsu) {
        if($fighter_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU) {
            return;
        }

        $parent_genjutsu_id = $fighter->combat_id . ':J' . $fighter_jutsu->parent_jutsu;
        $parent_jutsu = $fighter->jutsu[$fighter_jutsu->parent_jutsu];
        if(!isset($this->active_genjutsu[$parent_genjutsu_id]) or
            $this->active_genjutsu[$parent_genjutsu_id]['turns'] == $parent_jutsu->effect_length) {
            throw new Exception($parent_jutsu->name .
                ' must be active for 1 turn before using this jutsu!'
            );
        }
    }

    public function hasDisplays(Fighter $fighter): bool {
        return count($this->displays[$fighter->combat_id] ?? []) > 0;
    }

    public function getDisplayText(Fighter $fighter): string {
        return $this->system->clean(
            implode(
                '[br]',
                array_map(function($text) {
                    return "-{$text}-";
                }, $this->displays[$fighter->combat_id])
            )
        );
    }

    public function addDisplay(Fighter $fighter, string $display) {
        if(!isset($this->displays[$fighter->combat_id])) {
            $this->displays[$fighter->combat_id] = [];
        }

        $this->displays[$fighter->combat_id][] = $display;
    }
}