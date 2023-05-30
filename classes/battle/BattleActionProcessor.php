<?php

require_once __DIR__ . '/AttackCollision.php';

class BattleActionProcessor {
    private System $system;
    private BattleV2 $battle;

    private BattleField $field;
    private BattleEffectsManagerV2 $effects;

    private Closure $debug_closure;
    private array $default_attacks;

    public function __construct(
        System $system,
        BattleV2 $battle,
        BattleField $field,
        BattleEffectsManagerV2 $effects,
        Closure $debug_closure,
        array $default_attacks
    ) {
        $this->system = $system;
        $this->battle = $battle;
        $this->field = $field;
        $this->effects = $effects;

        $this->debug_closure = $debug_closure;
        $this->default_attacks = $default_attacks;
    }

    /**
     * @throws Exception
     */
    public function runMovementPhaseActions(): void {
        $player1_action = $this->battle->fighter_actions[$this->battle->player1->combat_id] ?? null;
        $player2_action = $this->battle->fighter_actions[$this->battle->player2->combat_id] ?? null;

        if($player1_action instanceof FighterMovementAction) {
            $this->field->moveFighterTo(
                fighter_id: $this->battle->player1->combat_id,
                target_tile: $player1_action->target_tile
            );
            $this->battle->current_turn_log->addFighterActionDescription(
                $this->battle->player1,
                $this->battle->player1->getName() . ' moved to tile ' . $player1_action->target_tile . '.'
            );
        }

        if($player2_action instanceof FighterMovementAction) {
            $this->field->moveFighterTo(
                fighter_id: $this->battle->player2->combat_id,
                target_tile: $player2_action->target_tile
            );
            $this->battle->current_turn_log->addFighterActionDescription(
                $this->battle->player2,
                $this->battle->player2->getName() . ' moved to tile ' . $player2_action->target_tile . '.'
            );
        }

        $this->field->reInit();
    }

    /**
     * @throws Exception
     */
    public function runAttackPhaseActions(): void {
        $player1_attack = $this->getFighterAttackFromActions($this->battle->player1->combat_id);
        $player2_attack = $this->getFighterAttackFromActions($this->battle->player2->combat_id);

        $this->debug(
            BattleManagerV2::DEBUG_DAMAGE,
            'Raw damage',
            'P1: ' . $player1_attack->starting_raw_damage . ' / P2: ' . $player2_attack->starting_raw_damage
        );

        // Set attack tiles
        $this->setAttackPath($this->battle->player1, $player1_attack);
        $this->setAttackPath($this->battle->player2, $player2_attack);

        // Find collisions
        $collisions = $this->findCollisions($player1_attack, $player2_attack, $this->debug_closure);

        // Walk through path and apply results of each collision
        $this->processCollisions($collisions);

        // walk through paths and find hits
        $this->findAttackHits($this->battle->player1, $player1_attack);
        $this->findAttackHits($this->battle->player2, $player2_attack);

        // For all attacks, have a cast/travel time and do stat checks against it
        // (e.g. attack vs replacement, raise/lower damage % taken)

        // Cast time
        // travel time

        // Apply remaining barrier
        if($player1_attack) {
            $this->effects->updateBarrier($this->battle->player1, $player1_attack->jutsu);
        }
        if($player2_attack) {
            $this->effects->updateBarrier($this->battle->player2, $player2_attack->jutsu);
        }

        // Apply damage/effects and set display
        if($player1_attack) {
            $this->battle->current_turn_log->addFighterActionDescription(
                $this->battle->player1,
                BattleLogV2::parseCombatText(
                    $player1_attack->jutsu->battle_text, $this->battle->player1, $this->battle->player2
                )
            );
            $this->battle->current_turn_log->addFighterAttackJutsuInfo($this->battle->player1, $player1_attack->jutsu);
            $this->battle->current_turn_log->setFighterAttackPathSegments(
                $this->battle->player1,
                $player1_attack->path_segments
            );

            foreach($player1_attack->hits as $hit) {
                $this->applyAttackHit(
                    attack: $player1_attack,
                    hit: $hit,
                );
            }
        }
        else {
            $this->battle->current_turn_log->addFighterActionDescription(
                $this->battle->player1,
                $this->battle->player1->getName() . ' stood still and did nothing.'
            );
        }

        /*if($collision_text) {
            $collision_text = $this->parseCombatText($collision_text, $this->battle->player1, $this->battle->player2);
            $this->battle->battle_text .= '[hr]' . $this->system->clean($collision_text);
        }*/

        // Apply damage/effects and set display
        if($player2_attack) {
            $this->battle->current_turn_log->addFighterActionDescription(
                $this->battle->player2,
                BattleLogV2::parseCombatText(
                    $player2_attack->jutsu->battle_text, $this->battle->player2, $this->battle->player1
                )
            );
            $this->battle->current_turn_log->addFighterAttackJutsuInfo($this->battle->player2, $player2_attack->jutsu);
            $this->battle->current_turn_log->setFighterAttackPathSegments(
                $this->battle->player2,
                $player2_attack->path_segments
            );

            foreach($player2_attack->hits as $hit) {
                $this->applyAttackHit(
                    attack: $player2_attack,
                    hit: $hit,
                );
            }
        }
        else {
            $this->battle->current_turn_log->addFighterActionDescription(
                $this->battle->player2,
                $this->battle->player2->getName() . ' stood still and did nothing.'
            );
        }
    }

    // PUBLIC UTILS

    /**
     * @param Fighter             $fighter
     * @param FighterAttackAction $action
     * @return Jutsu
     * @throws Exception
     */
    public function getJutsuFromAttackAction(Fighter $fighter, FighterAttackAction $action): Jutsu {
        $jutsu = null;

        if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
            $jutsu = $this->default_attacks[$action->jutsu_id] ?? null;
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_PURCHASABLE) {
            $jutsu = $fighter->jutsu[$action->jutsu_id] ?? null;
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
            $jutsu = $fighter->bloodline->jutsu[$action->jutsu_id] ?? null;
        }
        else {
            throw new Exception(
                "Invalid jutsu purchase type {$action->jutsu_purchase_type} for fighter {$fighter->combat_id}"
            );
        }

        if($jutsu == null) {
            $this->debug(BattleManagerV2::DEBUG_PLAYER_ACTION, "getJutsuFromAttackAction", print_r($action, true));
            throw new Exception(
                "Invalid type {$action->jutsu_purchase_type} jutsu {$action->jutsu_id} for fighter {$fighter->getName()}"
            );
        }

        return $jutsu;
    }

    // PRIVATE PROCESSING
    // (some methods may be marked as public to enable testing)

    private function debug(string $category, string $label, string $content): void {
        ($this->debug_closure)($category, $label, $content);
    }

    /**
     * @param string $combat_id
     * @return BattleAttackV2|null
     * @throws Exception
     */
    protected function getFighterAttackFromActions(string $combat_id): ?BattleAttackV2 {
        $fighter = $this->battle->getFighter($combat_id);
        if($fighter == null) {
            return null;
        }

        $fighter_action = $this->battle->fighter_actions[$combat_id] ?? null;
        if($fighter_action instanceof FighterAttackAction) {
            return $this->setupFighterAttack(
                $fighter,
                $fighter_action
            );
        }

        return null;
    }

    /**
     * @param Fighter             $fighter
     * @param FighterAttackAction $action
     * @return BattleAttackV2
     * @throws Exception
     */
    protected function setupFighterAttack(Fighter $fighter, FighterAttackAction $action): BattleAttackV2 {
        $jutsu = $this->getJutsuFromAttackAction($fighter, $action);
        $jutsu->setCombatId($fighter->combat_id);

        $attack = new BattleAttackV2(
            attacker_id: $action->fighter_id,
            target: $action->target,
            jutsu: $jutsu,
            turn: $this->battle->turn_count,
            starting_raw_damage: $fighter->calcDamage($jutsu),
        );

        // Set weapon data into jutsu
        if($attack->jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU && $action->weapon_id) {
            // Apply element to jutsu
            if($fighter->items[$action->weapon_id]->effect == 'element') {
                $attack->jutsu->element = $fighter->elements['first'];
                $attack->starting_raw_damage *= 1 + ($fighter->items[$action->weapon_id]->effect_amount / 100);
            }
            // Set effect in jutsu
            else {
                $attack->jutsu->setWeapon(
                    $action->weapon_id,
                    $fighter->items[$action->weapon_id]->effect,
                    $fighter->items[$action->weapon_id]->effect_amount,
                );
            }
        }

        if($attack->jutsu->isAllyTargetType()) {
            $attack->jutsu->weapon_id = 0;
            $attack->jutsu->effect_only = true;
        }

        if($attack->jutsu->use_type == Jutsu::USE_TYPE_BARRIER) {
            $attack->jutsu->effect_amount = $attack->starting_raw_damage;
            $fighter->barrier += $attack->starting_raw_damage;
            $attack->starting_raw_damage = 0;
        }

        return $attack;
    }

    /**
     * @throws Exception
     */
    public function setAttackPath(Fighter $attacker, BattleAttackV2 $attack): void {
        switch($attack->jutsu->use_type) {
            case Jutsu::USE_TYPE_MELEE:
            case Jutsu::USE_TYPE_PROJECTILE:
                if($attack->target instanceof AttackTileTarget) {
                    $this->setupTileAttack($attacker, $attack, $attack->target);
                }
                else if($attack->target instanceof AttackDirectionTarget) {
                    $this->setupDirectionAttack($attacker, $attack, $attack->target);
                }
                else if($attack->target instanceof AttackFighterIdTarget) {
                    throw new Exception("setAttackPath: Unsupported target type!");
                }
                else {
                    throw new Exception("setAttackPath: Invalid target type!");
                }
                // TODO
                break;
            case Jutsu::USE_TYPE_PROJECTILE_AOE:
            case Jutsu::USE_TYPE_REMOTE_SPAWN:
            case Jutsu::USE_TYPE_BUFF:
            case Jutsu::USE_TYPE_BARRIER:
            default:
                throw new Exception("setAttackPath: Invalid jutsu use type!");
        }
    }

    /**
     * @param Fighter               $attacker
     * @param BattleAttackV2        $attack
     * @param AttackDirectionTarget $target
     * @return BattleAttackV2
     * @throws Exception
     */
    public function setupDirectionAttack(
        Fighter $attacker, BattleAttackV2 $attack, AttackDirectionTarget $target
    ): BattleAttackV2 {
        if(!isset($this->field->fighter_locations[$attacker->combat_id])) {
            throw new Exception("Invalid attacker location!");
        }

        $tiles = $this->field->getTiles();

        $starting_tile_index = $this->field->getFighterLocation($attacker->combat_id) +
            ($target->isDirectionLeft() ? -1 : 1);
        $starting_tile = $tiles[$starting_tile_index] ?? null;
        if(!$this->field->tileIsInBounds($starting_tile_index) || $tiles[$starting_tile_index] == null) {
            throw new Exception("Invalid starting tile! {$starting_tile_index}");
        }

        $attack->first_tile = $starting_tile;

        $attack->path_segments = [];
        $index = $starting_tile_index;
        for($count = 0; $count < $attack->jutsu->range; $count++) {
            $tile = $this->field->getTiles()[$index] ?? null;

            $distance_from_start = abs($index - $starting_tile_index);
            if($distance_from_start >= $attack->jutsu->range) {
                break;
            }

            // +1 to include starting tile
            $time_arrived = ceil(
                ($distance_from_start + 1) / $attack->jutsu->travel_speed
            );

            $attack->addPathSegment(
                tile: $tile,
                raw_damage: $attack->starting_raw_damage,
                time_arrived: $time_arrived
            );

            $index += $target->isDirectionLeft() ? -1 : 1;
            if(!$this->field->tileIsInBounds($index)) {
                break;
            }
        }

        // sort collisions by time occurrence, process
        // if a collision takes place on a path segment that doesn't exist anymore, remove it

        /*
        const USE_TYPE_MELEE = 'physical';
        const USE_TYPE_PROJECTILE = 'projectile';
        const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
        const USE_TYPE_REMOTE_SPAWN = 'spawn';
        const USE_TYPE_BUFF = 'buff';
        const USE_TYPE_BARRIER = 'barrier';
        */

        $attack->is_path_setup = true;

        return $attack;
    }

    /**
     * @param Fighter          $attacker
     * @param BattleAttackV2   $attack
     * @param AttackTileTarget $target
     * @return BattleAttackV2
     * @throws Exception
     */
    public function setupTileAttack(
        Fighter $attacker, BattleAttackV2 $attack, AttackTileTarget $target
    ): BattleAttackV2 {
        if(!isset($this->field->fighter_locations[$attacker->combat_id])) {
            throw new Exception("Invalid attacker location!");
        }

        $tile = $this->field->getTiles()[$target->tile_index] ?? null;
        if($tile == null) {
            throw new Exception("setupTileAttack: Invalid tile!");
        }

        $attack->first_tile = $tile;
        $attack->addPathSegment(
            tile: $tile,
            raw_damage: $attack->starting_raw_damage,
            time_arrived: $attack->jutsu->travel_speed,
        );

        /*
        const USE_TYPE_MELEE = 'physical';
        const USE_TYPE_PROJECTILE = 'projectile';
        const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
        const USE_TYPE_REMOTE_SPAWN = 'spawn';
        const USE_TYPE_BUFF = 'buff';
        const USE_TYPE_BARRIER = 'barrier';
        */

        $attack->is_path_setup = true;

        return $attack;
    }

    /**
     * @throws Exception
     */
    public function findAttackHits(Fighter $attacker, BattleAttackV2 $attack): BattleAttackV2 {
        if(!$attack->is_path_setup) {
            throw new Exception("Attack path not setup!");
        }

        $attacker_team = BattleV2::fighterTeam($attacker);

        foreach($attack->path_segments as $path_segment) {
            foreach($path_segment->tile->fighter_ids as $fighter_id) {
                $fighter = $this->battle->getFighter($fighter_id);
                if($fighter === null) {
                    continue;
                }

                // TODO: Buff attacks
                if(BattleV2::fighterTeam($fighter) === $attacker_team) {
                    continue;
                }

                $attack->hits[] = new BattleAttackHit(
                    attacker: $attacker,
                    target: $fighter,
                    raw_damage: $path_segment->raw_damage,
                    time_occurred: $path_segment->time_arrived
                );
            }
        }

        $attack->are_hits_calculated = true;

        return $attack;
    }

    /**
     * @param AttackCollision[] $collisions
     * @return void
     * @throws Exception
     */
    public function processCollisions(array $collisions): void {
        /*
         We want to run collisions in order of time they occurred. This is mostly for team fights where we can have
          situations like the following with multiple attacks from team A hitting one attack from team B (X marks collision)

          A1     - - - - - >
          B1       < - X - X - -
          A2 - - - - - >

          Where the A1 / B1 collision should be processed first, then the B1 / A2 collision.
         */
        uasort($collisions, function (AttackCollision $a, AttackCollision $b) {
            return $a->time_occurred <=> $b->time_occurred;
        });

        $this->debug(
            BattleManagerV2::DEBUG_ATTACK_COLLISION,
            'collisions',
            json_encode(
                array_map(
                    function(AttackCollision $collision) { return $collision->toArray(); },
                    $collisions
                )
            )
        );

        $attacks_with_collision = [];

        foreach($collisions as $collision) {
            $attack1 =& $collision->attack1;
            $attack2 =& $collision->attack2;

            $attacks_with_collision[$attack1->id] = $attack1;
            $attacks_with_collision[$attack2->id] = $attack2;

            $attack1_segment = $collision->attack1_segment;
            $attack2_segment = $collision->attack2_segment;

            $attack1_user = $this->battle->getFighter($attack1->attacker_id);
            $attack2_user = $this->battle->getFighter($attack2->attacker_id);
            if($attack1_user == null || $attack2_user == null) {
                throw new Exception("Attack had invalid user!");
            }

            $attack1_damage = $attack1_segment->raw_damage;
            $attack2_damage = $attack2_segment->raw_damage;

            // Elemental interactions
            if(!empty($attack1->jutsu->element) && !empty($attack2->jutsu->element)) {
                // Fire > Wind > Lightning > Earth > Water > Fire
                if($attack1->jutsu->element == Jutsu::ELEMENT_FIRE) {
                    if($attack2->jutsu->element == Jutsu::ELEMENT_WIND) {
                        $attack2_damage *= 0.8;
                    }
                    else if($attack2->jutsu->element == Jutsu::ELEMENT_WATER) {
                        $attack1_damage *= 0.8;
                    }
                }
                else if($attack1->jutsu->element == Jutsu::ELEMENT_WIND) {
                    if($attack2->jutsu->element == Jutsu::ELEMENT_LIGHTNING) {
                        $attack2_damage *= 0.8;
                    }
                    else if($attack2->jutsu->element == Jutsu::ELEMENT_FIRE) {
                        $attack1_damage *= 0.8;
                    }
                }
                else if($attack1->jutsu->element == Jutsu::ELEMENT_LIGHTNING) {
                    if($attack2->jutsu->element == Jutsu::ELEMENT_EARTH) {
                        $attack2_damage *= 0.8;
                    }
                    else if($attack2->jutsu->element == Jutsu::ELEMENT_WIND) {
                        $attack1_damage *= 0.8;
                    }
                }
                else if($attack1->jutsu->element == Jutsu::ELEMENT_EARTH) {
                    if($attack2->jutsu->element == Jutsu::ELEMENT_WATER) {
                        $attack2_damage *= 0.8;
                    }
                    else if($attack2->jutsu->element == Jutsu::ELEMENT_LIGHTNING) {
                        $attack1_damage *= 0.8;
                    }
                }
                else if($attack1->jutsu->element == Jutsu::ELEMENT_WATER) {
                    if($attack2->jutsu->element == Jutsu::ELEMENT_FIRE) {
                        $attack2_damage *= 0.8;
                    }
                    else if($attack2->jutsu->element == Jutsu::ELEMENT_EARTH) {
                        $attack1_damage *= 0.8;
                    }
                }
            }

            $collision_text = "";
            // Barriers
            /* if($attack1_user->barrier && $attack2->jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU) {
                 // Block damage from opponent's attack
                 if($attack1_user->barrier >= $attack2_damage) {
                     $block_amount = $attack2_damage;
                 }
                 else {
                     $block_amount = $attack1_user->barrier;
                 }
 
                 $block_percent = ($attack2_damage >= 1) ? ($block_amount / $attack2_damage) * 100 : 100;
                 $attack1_user->barrier -= $block_amount;
                 $attack2_damage -= $block_amount;
 
                 if($attack1_user->barrier < 0) {
                     $attack1_user->barrier = 0;
                 }
                 if($attack2_damage < 0) {
                     $attack2_damage = 0;
                 }
 
                 // Set display
                 $block_percent = round($block_percent, 1);
                 $collision_text .= "[player]'s barrier blocked $block_percent% of [opponent]'s damage![br]";
             }
             if($attack2_user->barrier && $attack1->jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU) {
                 // Block damage from opponent's attack
                 if($attack2_user->barrier >= $attack1_damage) {
                     $block_amount = $attack1_damage;
                 }
                 else {
                     $block_amount = $attack2_user->barrier;
                 }
 
                 $block_percent = ($attack1_damage >= 1) ? ($block_amount / $attack1_damage) * 100 : 100;
                 $attack2_user->barrier -= $block_amount;
                 $attack1_damage -= $block_amount;
 
                 if($attack2_user->barrier < 0) {
                     $attack2_user->barrier = 0;
                 }
                 if($attack1_damage < 0) {
                     $attack1_damage = 0;
                 }
 
                 // Set display
                 $block_percent = round($block_percent, 1);
                 $collision_text .= "[opponent]'s barrier blocked $block_percent% of [player]'s damage![br]";
             }*/

            // Weapon diffuse (tai diffuse nin)
            /*
            if($attack1->jutsu->weapon_id && $attack1->jutsu->weapon_effect->effect == 'diffuse' && $attack2->jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                    if($attack2_damage <= 0) {
                        $attack1_diffuse_percent = 0;
                    }
                    else {
                        $attack1_diffuse_percent = round(
                            $attack1_damage / $attack2_damage * ($attack1->jutsu->weapon_effect->effect_amount / 100),
                            1
                        );

                        if($attack1_diffuse_percent > BattleV2::MAX_DIFFUSE_PERCENT) {
                            $attack1_diffuse_percent = BattleV2::MAX_DIFFUSE_PERCENT;
                        }
                    }
                }
                if($attack2->jutsu->weapon_id && $attack2->jutsu->weapon_effect->effect == 'diffuse' && $attack1->jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                    if($attack1_damage <= 0) {
                        $attack2_diffuse_percent = 0;
                    }
                    else {
                        $attack2_diffuse_percent = round(
                            $attack2_damage / $attack1_damage * ($attack2->jutsu->weapon_effect->effect_amount / 100),
                            1
                        );
                    }

                    if($attack2_diffuse_percent > BattleV2::MAX_DIFFUSE_PERCENT) {
                        $attack2_diffuse_percent = BattleV2::MAX_DIFFUSE_PERCENT;
                    }
                }
                if(!empty($attack1_diffuse_percent)) {
                    $attack2_damage *= 1 - $attack1_diffuse_percent;
                    $collision_text .= "[player] diffused " . ($attack1_diffuse_percent * 100) . "% of [opponent]'s damage![br]";
                }
                if(!empty($attack2_diffuse_percent)) {
                    $attack1_damage *= 1 - $attack2_diffuse_percent;
                    $collision_text .= "[opponent] diffused " . ($attack2_diffuse_percent * 100) . "% of [player]'s damage![br]";
                }
            */

            // Apply buffs/nerfs
            $attack1_speed = $attack1_user->speed + $attack1_user->speed_boost - $attack1_user->speed_nerf;
            $attack1_speed = 50 + ($attack1_speed * 0.5);
            if($attack1_speed <= 0) {
                $attack1_speed = 1;
            }

            $attack1_cast_speed = $attack1_user->cast_speed + $attack1_user->cast_speed_boost - $attack1_user->cast_speed_nerf;
            $attack1_cast_speed = 50 + ($attack1_cast_speed * 0.5);
            if($attack1_cast_speed <= 0) {
                $attack1_cast_speed = 1;
            }

            $attack2_speed = $attack2_user->speed + $attack2_user->speed_boost - $attack2_user->speed_nerf;
            $attack2_speed = 50 + ($attack2_speed * 0.5);
            if($attack2_speed <= 0) {
                $attack2_speed = 1;
            }

            $attack2_cast_speed = $attack2_user->cast_speed + $attack2_user->cast_speed_boost - $attack2_user->cast_speed_nerf;
            $attack2_cast_speed = 50 + ($attack2_cast_speed * 0.5);
            if($attack2_cast_speed <= 0) {
                $attack2_cast_speed = 1;
            }

            $this->debug(
                BattleManagerV2::DEBUG_ATTACK_COLLISION,
                'speed',
                "Player1({$attack1_user->getName()}): {$attack1_user->speed} ({$attack1_user->speed_boost} - {$attack1_user->speed_nerf})<br />"
                . "Player2({$attack2_user->getName()}): {$attack2_user->speed} ({$attack2_user->speed_boost} - {$attack2_user->speed_nerf})<br />"
            );

            // Ratios for damage reduction
            $speed_ratio = 0.8;
            $cast_speed_ratio = 0.8;
            $max_damage_reduction = 0.5;
            if($attack1->jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                // Nin vs Nin
                if($attack2->jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                    if($attack1_cast_speed >= $attack2_cast_speed) {
                        $damage_reduction = ($attack1_cast_speed / $attack2_cast_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack2_damage *= 1 - $damage_reduction;
                            $collision_text .= "[player] cast [gender2] jutsu before [opponent] cast, negating " .
                                ($damage_reduction * 100) . "% of [opponent]'s damage!";
                        }
                    }
                    else {
                        $damage_reduction = ($attack2_cast_speed / $attack1_cast_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack1_damage *= 1 - $damage_reduction;
                            $collision_text .= "[opponent] cast their jutsu before [player] cast, negating " .
                                ($damage_reduction * 100) . "% of [player]'s damage!";
                        }
                    }
                }
                // Nin vs Tai
                else if($attack2->jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                    if($attack1_cast_speed >= $attack2_speed) {
                        $damage_reduction = ($attack1_cast_speed / $attack2_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack2_damage *= 1 - $damage_reduction;
                            $collision_text .= "[player] cast [gender2] jutsu before [opponent] attacked, negating " . ($damage_reduction * 100) .
                                "% of [opponent]'s damage!";
                        }
                    }
                    else {
                        $damage_reduction = ($attack2_speed / $attack1_cast_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack1_damage *= 1 - $damage_reduction;
                            $collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
                        }
                    }
                }
            }

            // Taijutsu clash
            else if($attack1->jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                // Tai vs Tai
                if($attack2->jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                    if($attack1_speed >= $attack2_speed) {
                        $damage_reduction = ($attack1_speed / $attack2_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack2_damage *= 1 - $damage_reduction;
                            $collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";
                        }
                    }
                    else {
                        $damage_reduction = ($attack2_speed / $attack1_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack1_damage *= 1 - $damage_reduction;
                            $collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
                        }
                    }
                }
                else if($attack2->jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                    if($attack1_speed >= $attack2_cast_speed) {
                        $damage_reduction = ($attack1_speed / $attack2_cast_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack2_damage *= 1 - $damage_reduction;
                            $collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";
                        }
                    }
                    else {
                        $damage_reduction = ($attack2_cast_speed / $attack1_speed) - 1.0;
                        $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                        if($damage_reduction > $max_damage_reduction) {
                            $damage_reduction = $max_damage_reduction;
                        }
                        if($damage_reduction >= 0.01) {
                            $attack1_damage *= 1 - $damage_reduction;
                            $collision_text .= "[opponent] cast their jutsu before [player] attacked, negating " . ($damage_reduction * 100) .
                                "% of [player]'s damage!";
                        }
                    }
                }
            }

            // Apply results of collision
            if($attack1_damage < $attack1_segment->raw_damage) {
                for($i = $attack1_segment->index; $i < count($attack1->path_segments); $i++) {
                    $attack1->path_segments[$i]->raw_damage = $attack1_damage;
                }
            }
            if($attack2_damage < $attack2_segment->raw_damage) {
                for($i = $attack2_segment->index; $i < count($attack2->path_segments); $i++) {
                    $attack2->path_segments[$i]->raw_damage = $attack2_damage;
                }
            }
        }

        foreach($attacks_with_collision as $attack) {
            $attack->are_collisions_applied = true;
        }
    }

    protected function applyAttackHit(BattleAttackV2 $attack, BattleAttackHit $hit): void {
        $user = $hit->attacker;
        $target = $hit->target;
        $raw_damage = $hit->raw_damage;

        $attack_damage = $raw_damage;
        if(empty($attack->jutsu->effect_only)) {
            $attack_damage = $target->calcDamageTaken($attack->starting_raw_damage, $attack->jutsu->jutsu_type);
            $target->health -= $attack_damage;
            if($target->health < 0) {
                $target->health = 0;
            }
        }

        // Weapon effect for taijutsu (IN PROGRESS)
        if($attack->jutsu->weapon_id) {
            if($user->items[$attack->jutsu->weapon_id]->effect != 'diffuse') {
                $this->effects->setEffect(
                    $user,
                    $target->combat_id,
                    $attack->jutsu->weapon_effect,
                    $attack->starting_raw_damage
                );
            }
        }

        // Set cooldowns
        if($attack->jutsu->cooldown > 0) {
            $this->battle->jutsu_cooldowns[$attack->jutsu->combat_id] = $attack->jutsu->cooldown;
        }

        // Effects
        if($attack->jutsu->hasEffect()) {
            if($attack->jutsu->use_type == Jutsu::USE_TYPE_BUFF || in_array(
                    $attack->jutsu->effect, BattleEffect::$buff_effects
                )) {
                $target_id = $user->combat_id;
            }
            else {
                $target_id = $target->combat_id;
            }

            $this->effects->setEffect(
                $user,
                $target_id,
                $attack->jutsu,
                $attack->starting_raw_damage
            );
        }

        if(empty($attack->jutsu->effect_only)) {
            $tag = "{$attack->jutsu->jutsu_type}_damage";
            $this->battle->current_turn_log->addFighterAttackHit(
                attacker: $user,
                target: $target,
                damage_type: $attack->jutsu->jutsu_type,
                damage: $attack_damage,
                time_occurred: $hit->time_occurred
            );
        }

        if($attack->jutsu->hasEffect()) {
            $this->battle->current_turn_log->addFighterEffectAnnouncement(
                caster: $user,
                target: $target,
                announcement_text: $this->effects->getAnnouncementText($attack->jutsu->effect)
            );
        }

        if($attack->jutsu->weapon_id) {
            $this->battle->current_turn_log->addFighterEffectAnnouncement(
                caster: $user,
                target: $target,
                announcement_text: $this->effects->getAnnouncementText($attack->jutsu->weapon_effect->effect)
            );
        }
    }

    /**
     * The purpose of this function is to enable deterministic collision IDs whenever two attacks hit each other,
     * regardless of which order the collision is being checked. In other words, both of these should return the same
     * result:
     *
     * collisionId(attack1, attack2)
     *
     * collisionId(attack2, attack1)
     *
     * @param BattleAttackV2 $attack1
     * @param BattleAttackV2 $attack2
     * @return string
     * @throws Exception
     */
    public static function collisionId(BattleAttackV2 $attack1, BattleAttackV2 $attack2): string {
        if($attack1->id == $attack2->id) {
            throw new Exception("Can't collide the same attack!");
        }

        if(strcmp($attack1->id, $attack2->id) < 0) {
            return $attack1->id . ':' . $attack2->id;
        }
        else {
            return $attack2->id . ':' . $attack1->id;
        }
    }

    /**
     * Note: A key design feature of collisions is that if two attacks touch the same tile at any point in their path,
     * they should be considered to have collided. We intentionally do not want to fully realistically simulate time,
     * but instead use the time of arrival as a variable to:
     * 1) Set the collision location as close as possible to where it should take place, so that subsequent tiles of
     *   the attack are weakened in a logical manner from the player's POV
     * 2) Calculate time-based effects.
     *
     * @param BattleAttackV2 $fighter1Attack
     * @param BattleAttackV2 $fighter2Attack
     * @param Closure|null   $debug_closure
     * @return AttackCollision[]
     * @throws Exception
     */
    public static function findCollisions(BattleAttackV2 $fighter1Attack, BattleAttackV2 $fighter2Attack, ?Closure $debug_closure): array {
        $debug = function(string $category, string $label, string $content) use($debug_closure) {
            if($debug_closure == null) {
                return;
            }

            ($debug_closure)($category, $label, $content);
        };

        if(!$fighter1Attack->is_path_setup) {
            throw new Exception("Attack $fighter1Attack->id path not setup!");
        }
        if(!$fighter2Attack->is_path_setup) {
            throw new Exception("Attack $fighter2Attack->id path not setup!");
        }

        if(count($fighter1Attack->path_segments) < 1) {
            error_log("Fighter 1 attack {$fighter1Attack->id} has no path segments, cannot collide with anything!");
            return [];
        }
        if(count($fighter2Attack->path_segments) < 1) {
            error_log("Fighter 1 attack {$fighter2Attack->id} has no path segments, cannot collide with anything!");
            return [];
        }

        /** @var TileAttackSegment[][] $segments_by_tile_and_attack */
        $segments_by_tile_and_attack = [];
        $collisions = [];

        foreach([$fighter1Attack, $fighter2Attack] as $attack) {
            foreach($attack->path_segments as $segment) {
                if(!isset($segments_by_tile_and_attack[$segment->tile->index])) {
                    $segments_by_tile_and_attack[$segment->tile->index] = [];
                }

                // TODO: how to handle multi attacks from same team?
                $segments_by_tile_and_attack[$segment->tile->index][$attack->id] =
                    new TileAttackSegment($attack, $segment);
            }
        }

        $debug(
            BattleManagerV2::DEBUG_ATTACK_COLLISION,
            'segments_by_tile_and_attack',
            json_encode(
                array_map(function($tile_segments_by_attack) {
                    return implode(
                        ' ',
                        array_map(function(TileAttackSegment $segment) {
                            return "Attack: " . $segment->attack->id . '(Time: ' . $segment->segment->time_arrived . ')';
                        }, $tile_segments_by_attack)
                    );
                }, $segments_by_tile_and_attack)
            )
        );

        // Find intersecting attacks
        $colliding_attack_pairs = [];
        foreach($segments_by_tile_and_attack as $segments_by_attack) {
            if(count($segments_by_attack) < 2) {
                continue;
            }
            if(count($segments_by_attack) > 2) {
                throw new Exception("3-way collisions are currently not supported!");
            }

            $colliding_attack_pairs[] = array_values(
                array_map(
                    function(TileAttackSegment $segment) {
                        return $segment->attack;
                    },
                    $segments_by_attack
                )
            );
        }

        /*
         * For a pair of intersecting attacks, find their collision points (so the rest of the attack can be weakened)
         */
        foreach($colliding_attack_pairs as $colliding_attack_pair) {
            /** @var BattleAttackV2 $attack1 */
            $attack1 = $colliding_attack_pair[0];
            /** @var BattleAttackV2 $attack2 */
            $attack2 = $colliding_attack_pair[1];

            $collision_id = self::collisionId($attack1, $attack2);
            if(isset($collisions[$collision_id])) {
                continue;
            }

            $attack1_collision_point = self::findNextTileCollisionPoint($attack1, $attack2, $segments_by_tile_and_attack);
            $attack2_collision_point = self::findNextTileCollisionPoint($attack2, $attack1, $segments_by_tile_and_attack);

            $debug(BattleManagerV2::DEBUG_ATTACK_COLLISION, 'initial_collision_points', json_encode([
                'attack1' => $attack1_collision_point,
                'attack2' => $attack2_collision_point,
            ]));

            /* Scenarios we need this:
                #1: If the attacks start on the same tile, when they move to their next tiles
                    they have no overlapping segments. We then need to run a same-tile collision check.
                           1 2 3 >
                     < 3 2 1

                #2: One of the attacks is direction and the other is tile-based. "Next-tile" collision fundamentally
                    doesn't make sense for single-tile attacks, so this collision needs to be calculated entirely
                    from the direction attack's side, where next-tile collision will work as the tile-based attack will
                    start at time arrived = 1. e.g.
                       1
                 < 4 3 2 1

            */
            if($attack1_collision_point === null) {
                $attack1_collision_point = self::findSameTileCollisionPoint($attack1, $attack2, $segments_by_tile_and_attack);
            }
            if($attack2_collision_point === null) {
                $attack2_collision_point = self::findSameTileCollisionPoint($attack2, $attack1, $segments_by_tile_and_attack);
            }

            $debug(BattleManagerV2::DEBUG_ATTACK_COLLISION, 'second_collision_points', json_encode([
                'attack1' => $attack1_collision_point,
                'attack2' => $attack2_collision_point,
            ]));

            /*
             * Only known scenario for this - Crossing direction attacks where they do not intersect at close enough
               times for one attack (top in this example) to find a next tile collision point (because they happen after
                the top attack is finished traveling). But we want attacks to still collide in all cases they overlap,
                so we need to set a collision point on the top one.

                Best way I can think of to do this is use the other attack's collision point, then adjust to the closest
                tile actually found in the other attack.

                   1 2 3 >
                 < 7 6 5 4 3 2 1
             */
            if($attack1_collision_point === null && $attack2_collision_point === null) {
                throw new Exception("No collision points found!");
            }

            if($attack1_collision_point === null && $attack2_collision_point !== null) {
                $attack1_collision_point = self::findClosestTileInAttackPath($attack2_collision_point, $attack1);

                $debug(BattleManagerV2::DEBUG_ATTACK_COLLISION, 'half_collision_fixed', json_encode([
                    'attack1' => $attack1_collision_point,
                    'attack2' => $attack2_collision_point,
                ]));
            }
            else if($attack2_collision_point === null && $attack1_collision_point !== null) {
                $attack2_collision_point = self::findClosestTileInAttackPath($attack1_collision_point, $attack2);

                $debug(BattleManagerV2::DEBUG_ATTACK_COLLISION, 'half_collision_fixed', json_encode([
                    'attack1' => $attack1_collision_point,
                    'attack2' => $attack2_collision_point,
                ]));
            }

            /*
                check for a collision range (collision valid across multiple tiles)

                example scenarios:
                - Collision points at tiles 4 and 7: 4 5 6 7 (should be: 5 and 6)
                - Collision points at tiles 4 and 8: 4 5 6 7 8 (should be: 6)
            */

            $collision_distance = abs($attack1_collision_point - $attack2_collision_point);
            if($collision_distance > 1) {
                /* Move each attack's collision point halfway towards the other.
                In the 4 to 8 case, this results in an even number (2), so each attack collision is adjusted 2 tiles:

                4 5 6 7 8
                > > 6 < <

                In the 4 to 7 case from above,
                 there is no single center tile, so we remove the extra 0.5 from the number. E.g.

                  distance: 7 - 4 = 3
                  distance to adjust: 3 / 2 = 1.5
                  final distance to adjust: floor(1.5) = 1

                   4 5 6 7
                   > 5 6 <
                */
                // $distance_to_adjust = floor($collision_distance / 2);
                $distance_to_adjust = 0;

                // Attack 2 is to the right
                if($attack2_collision_point > $attack1_collision_point) {
                    $attack1_collision_point += $distance_to_adjust;
                    $attack2_collision_point -= $distance_to_adjust;
                }
                // Attack 1 is to the right
                else if($attack1_collision_point > $attack2_collision_point) {
                    $attack1_collision_point -= $distance_to_adjust;
                    $attack2_collision_point += $distance_to_adjust;
                }
                else {
                    throw new Exception("unexpected: No distance between collision points!");
                }
            }

            $debug(BattleManagerV2::DEBUG_ATTACK_COLLISION, 'final_collision_points', json_encode([
                'attack1' => $attack1_collision_point,
                'attack2' => $attack2_collision_point,
            ]));

            $attack1_colliding_segment = $segments_by_tile_and_attack[$attack1_collision_point][$attack1->id]->segment;
            $attack2_colliding_segment = $segments_by_tile_and_attack[$attack2_collision_point][$attack2->id]->segment;
            $attack1_collision_time = $attack1_colliding_segment->time_arrived;
            $attack2_collision_time = $attack2_colliding_segment->time_arrived;

            // SHARED - Persist collision
            $collisions[$collision_id] = new AttackCollision(
                id: $collision_id,
                attack1: $attack1,
                attack2: $attack2,
                attack1_collision_point: $attack1_collision_point,
                attack2_collision_point: $attack2_collision_point,
                attack1_segment: $attack1_colliding_segment,
                attack2_segment: $attack2_colliding_segment,
                time_occurred: min($attack1_collision_time, $attack2_collision_time)
            );
        }

        return $collisions;
    }

    /**
     * Collision algorithm Type 2 - For each tile, see if attack on next tile is <= time + 1
     *
     * @param BattleAttackV2        $attack
     * @param BattleAttackV2        $other_attack
     * @param TileAttackSegment[][] $segments_by_tile_and_attack
     * @return ?int
     * @throws Exception
     */
    public static function findNextTileCollisionPoint(BattleAttackV2 $attack, BattleAttackV2 $other_attack, array $segments_by_tile_and_attack): ?int {
        // Tile attacks do not have a next target
        if($attack->target instanceof AttackTileTarget) {
            return null;
        }

        foreach($attack->path_segments as $segment) {
            if($attack->isFacingRight()) {
                $next_tile_index = $segment->tile->index + 1;
            }
            else {
                $next_tile_index = $segment->tile->index - 1;
            }

            $other_attack_on_next_tile = $segments_by_tile_and_attack[$next_tile_index][$other_attack->id] ?? null;
            if($other_attack_on_next_tile != null) {
                $other_segment = $other_attack_on_next_tile->segment;

                if($other_segment->time_arrived <= $segment->time_arrived + 1) {
                    return $segment->tile->index;
                }
            }
        }

        return null;
    }

    /**
     * Collision algorithm Type 1 - For each tile, see if attack on next tile is == time
     *
     * @param BattleAttackV2        $attack
     * @param BattleAttackV2        $other_attack
     * @param TileAttackSegment[][] $segments_by_tile_and_attack
     * @return ?int
     * @throws Exception
     */
    public static function findSameTileCollisionPoint(BattleAttackV2 $attack, BattleAttackV2 $other_attack, array $segments_by_tile_and_attack): ?int {
        foreach($attack->path_segments as $segment) {
            $other_attack_on_same_tile = $segments_by_tile_and_attack[$segment->tile->index][$other_attack->id] ?? null;
            if($other_attack_on_same_tile == null) {
                continue;
            }

            if($other_attack_on_same_tile->segment->time_arrived == $segment->time_arrived) {
                return $segment->tile->index;
            }
            /* Because we want every overlapping attack to collide regardless of time (see findCollisions documentation),
              tile-based attacks always collide if another attack touches their tile */
            else if($attack->target instanceof AttackTileTarget) {
                return $segment->tile->index;
            }
        }

        return null;
    }

    public static function findClosestTileInAttackPath(int $tile_index, BattleAttackV2 $attack): int {
        $first_segment = $attack->path_segments[0];
        $last_segment = $attack->path_segments[array_key_last($attack->path_segments)];

        $min_segment_tile = min($last_segment->tile->index, $first_segment->tile->index);
        $max_segment_tile = max($last_segment->tile->index, $first_segment->tile->index);

        if($tile_index > $max_segment_tile) {
            return $max_segment_tile;
        }
        else if($tile_index < $min_segment_tile) {
            return $min_segment_tile;
        }
        else {
            return $tile_index;
        }
    }
}

class TileAttackSegment {
    public function __construct(
        public BattleAttackV2 $attack,
        public AttackPathSegment $segment,
    ) {}
}