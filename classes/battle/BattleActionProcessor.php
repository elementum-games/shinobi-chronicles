<?php

class BattleActionProcessor {
    private System $system;
    private Battle $battle;

    private BattleField $field;
    private BattleEffectsManager $effects;

    private Closure $debug_closure;
    private array $default_attacks;

    public function __construct(
        System $system,
        Battle $battle,
        BattleField $field,
        BattleEffectsManager $effects,
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
    public function runMovementPhaseActions() {
        $player1_action = $this->battle->fighter_actions[$this->battle->player1->combat_id] ?? null;
        $player2_action = $this->battle->fighter_actions[$this->battle->player2->combat_id] ?? null;

        if($player1_action instanceof FighterMovementAction) {
            $this->field->moveFighterTo(
                fighter_id: $this->battle->player1->combat_id,
                target_tile: $player1_action->target_tile
            );
            $this->battle->battle_text .= $this->battle->player1->getName() .
                ' moved to tile ' . $player1_action->target_tile . '.';
        }

        if($player1_action != null && $player2_action != null) {
            $this->battle->battle_text .= '[hr]';
        }

        if($player2_action instanceof FighterMovementAction) {
            $this->field->moveFighterTo(
                fighter_id: $this->battle->player1->combat_id,
                target_tile: $player2_action->target_tile
            );
            $this->battle->battle_text .= $this->battle->player1->getName() .
                ' moved to tile ' . $player2_action->target_tile . '.';
        }
    }

    /**
     * @throws Exception
     */
    public function runAttackPhaseActions() {
        $player1_attack = $this->getFighterAttackFromActions($this->battle->player1->combat_id);
        $player2_attack = $this->getFighterAttackFromActions($this->battle->player2->combat_id);

        $this->debug(
            BattleManager::DEBUG_DAMAGE,
            'Raw damage',
            'P1: ' . $player1_attack->starting_raw_damage . ' / P2: ' . $player2_attack->starting_raw_damage
        );

        $this->setAttackPath($this->battle->player1, $player1_attack);
        // $this->setAttackPath($this->battle->player2, $player2_attack);

        $collisions = $this->findCollisions($player1_attack, $player2_attack);

        $this->runAttackPath($this->battle->player1, $player1_attack);


        // For all attacks, have a cast/travel time and do stat checks against it
        // (e.g. attack vs replacement, raise/lower damage % taken)

        // put attacks temporarily on all their squares
        // walk through path of attack, find collisions

        // for each collision check
        // - each attack square #
        // - each attack travel speed
        // and find a point of collision, weaken attack after that

        $hits = $player1_attack->hits;

        // squares hit
        // direction

        // Cast time
        // travel time

        // Collision
        // This doesn't apply the same way any more, move logic to field processing
        /*$collision_text = null;
        if($player1_attack != null && $player2_attack != null) {
            $collision_text = $this->jutsuCollision(
                $this->battle->player1, $this->battle->player2,
                $player1_attack->raw_damage, $player2_attack->raw_damage,
                $player1_attack->jutsu, $player2_attack->jutsu
            );
        }*/

        // Apply remaining barrier
        if($player1_attack) {
            $this->effects->updateBarrier($this->battle->player1, $player1_attack->jutsu);
        }
        if($player2_attack) {
            $this->effects->updateBarrier($this->battle->player2, $player2_attack->jutsu);
        }

        // Apply damage/effects and set display
        if($player1_attack) {
            $text = $player1_attack->jutsu->battle_text;
            if(count($player1_attack->hits) === 0) {
                $text .= "[player]'s attack misses.";
            }
            $this->battle->battle_text .= $this->parseCombatText(
                $text, $this->battle->player1, $this->battle->player2
            );

            foreach($player1_attack->hits as $hit) {
                /** @var BattleAttackHit $hit */
                $this->applyAttackHit(
                    attack: $player1_attack,
                    user: $hit->attacker,
                    target: $hit->target,
                    raw_damage: $hit->raw_damage
                );
            }
        }
        else {
            $this->battle->battle_text .= $this->battle->player1->getName() . ' stood still and did nothing.';
            if($this->effects->hasDisplays($this->battle->player1)) {
                $this->battle->battle_text .= '<p>' .
                    $this->parseCombatText(
                        $this->effects->getDisplayText($this->battle->player1),
                        $this->battle->player1,
                        $this->battle->player2
                    ) .
                    '</p>';
            }
        }

        /*if($collision_text) {
            $collision_text = $this->parseCombatText($collision_text, $this->battle->player1, $this->battle->player2);
            $this->battle->battle_text .= '[hr]' . $this->system->clean($collision_text);
        }*/
        $this->battle->battle_text .= '[hr]';

        // Apply damage/effects and set display
        if($player2_attack) {
            $text = $player2_attack->jutsu->battle_text;
            if(count($player2_attack->hits) === 0) {
                $text .= "[player]'s attack misses.";
            }
            $this->battle->battle_text .= $this->parseCombatText(
                $text, $this->battle->player2, $this->battle->player1
            );

            foreach($player2_attack->hits as $hit) {
                /** @var BattleAttackHit $hit */
                $this->applyAttackHit(
                    attack: $player2_attack,
                    user: $hit->attacker,
                    target: $hit->target,
                    raw_damage: $hit->raw_damage
                );
            }
        }
        else {
            $this->battle->battle_text .= $this->battle->player2->getName() . ' stood still and did nothing.';
            if($this->effects->hasDisplays($this->battle->player2)) {
                $this->battle->battle_text .= "<p>" . $this->parseCombatText(
                        $this->effects->getDisplayText($this->battle->player2),
                        $this->battle->player2,
                        $this->battle->player1
                    ) . "</p>";
            }
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
            $this->debug(BattleManager::DEBUG_PLAYER_ACTION, "getJutsuFromAttackAction", print_r($action, true));
            throw new Exception(
                "Invalid type {$action->jutsu_purchase_type} jutsu {$action->jutsu_id} for fighter {$fighter->getName()}"
            );
        }

        return $jutsu;
    }

    // PRIVATE PROCESSING

    private function debug(string $category, string $label, string $content) {
        ($this->debug_closure)($category, $label, $content);
    }

    /**
     * @param string $combat_id
     * @return BattleAttack|null
     * @throws Exception
     */
    protected function getFighterAttackFromActions(string $combat_id): ?BattleAttack {
        $fighter = $this->battle->getFighter($combat_id);
        if($fighter == null) {
            return null;
        }

        $fighter_action = $this->battle->fighter_actions[$combat_id] ?? null;

        if($fighter_action != null && ($fighter_action instanceof FighterAttackAction)) {
            return $this->setupFighterAttack(
                $fighter,
                $fighter_action
            );
        }

        return null;
    }

    /**
     * @param Fighter       $fighter
     * @param FighterAction $action
     * @return BattleAttack
     * @throws Exception
     */
    protected function setupFighterAttack(Fighter $fighter, FighterAttackAction $action): BattleAttack {
        $jutsu = $this->getJutsuFromAttackAction($fighter, $action);
        $jutsu->setCombatId($fighter->combat_id);

        $attack = new BattleAttack(
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
    protected function setAttackPath(Fighter $attacker, BattleAttack $attack) {
        /*const TARGET_TYPE_FIGHTER_ID = 'fighter_id'
        const TARGET_TYPE_TILE = 'tile';
        const TARGET_TYPE_DIRECTION = 'direction';*/

        switch($attack->jutsu->use_type) {
            case Jutsu::USE_TYPE_MELEE:
            case Jutsu::USE_TYPE_PROJECTILE:
                if($attack->target instanceof AttackTileTarget) {
                    $this->field->setupTileAttack($attacker, $attack, $attack->target);
                }
                else if($attack->target instanceof AttackDirectionTarget) {
                    $this->field->setupDirectionAttack($attacker, $attack, $attack->target);
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
     * @throws Exception
     */
    protected function runAttackPath(Fighter $attacker, BattleAttack $attack): BattleAttack {
        if($attack->root_path_segment == null) {
            throw new Exception("runAttackPath: No root path segment!");
        }

        $attacker_team = Battle::fighterTeam($attacker);

        $path_segment = $attack->root_path_segment;

        $count = 0;
        while($path_segment != null) {
            if($count++ > 100) {
                throw new Exception("runAttackPath: Attack path tried over 100 segments, exiting!");
            }

            foreach($path_segment->tile->fighter_ids as $fighter_id) {
                $fighter = $this->battle->getFighter($fighter_id);
                if($fighter === null) {
                    continue;
                }

                // TODO: Buff attacks
                if(Battle::fighterTeam($fighter) === $attacker_team) {
                    continue;
                }

                $attack->hits[] = new BattleAttackHit(
                    attacker: $attacker,
                    target: $fighter,
                    raw_damage: $path_segment->raw_damage,
                );
            }

            $path_segment = $path_segment->next_segment;
        }

        return $attack;
    }

    protected function applyAttackHit(BattleAttack $attack, Fighter $user, Fighter $target, float $raw_damage): void {
        $attack_damage = $raw_damage;
        if($attack->jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($attack->jutsu->effect_only)) {
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

        $text = "";
        $attack_jutsu_color = BattleActionProcessor::getJutsuTextColor($attack->jutsu->jutsu_type);

        if($attack->jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($attack->jutsu->effect_only)) {
            $text .= "<p style=\"font-weight:bold;\">
                            {$user->getName()} deals
                                <span style=\"color:{$attack_jutsu_color}\">
                                    " . sprintf('%.2f', $attack_damage) . " damage
                                </span>
                            to {$target->getName()}.
                        </p>";
        }
        if($this->effects->hasDisplays($user)) {
            $text .= '<p>' . $this->effects->getDisplayText($user) . '</p>';
        }

        if($attack->jutsu->hasEffect()) {
            $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                $this->system->clean($this->effects->getAnnouncementText($attack->jutsu->effect)) .
                "</p>";
        }

        if($attack->jutsu->weapon_id) {
            $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                $this->system->clean($this->effects->getAnnouncementText($attack->jutsu->weapon_effect->effect)) .
                "</p>";
        }

        $this->battle->battle_text .= $this->parseCombatText($text, $user, $target);
    }

    private function parseCombatText(string $text, Fighter $attacker, Fighter $target): string {
        return str_replace(
            [
                '[player]',
                '[opponent]',
                '[gender]',
                '[gender2]',
            ],
            [
                $attacker->getName(),
                $target->getName(),
                $attacker->getSingularPronoun(),
                $attacker->getPossessivePronoun(),
            ],
            $text
        );
    }

    protected static function getJutsuTextColor($jutsu_type): string {
        switch($jutsu_type) {
            case Jutsu::TYPE_NINJUTSU:
                return "blue";
            case Jutsu::TYPE_TAIJUTSU:
                return "red";
            case Jutsu::TYPE_GENJUTSU:
                return "purple";
            case 'none':
            default:
                return "black";
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
     * @param BattleAttack $attack1
     * @param BattleAttack $attack2
     * @return string
     * @throws Exception
     */
    public static function collisionId(BattleAttack $attack1, BattleAttack $attack2): string {
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
     * @param BattleAttack $fighter1Attack
     * @param BattleAttack $fighter2Attack
     * @return AttackCollision[]
     * @throws Exception
     */
    public static function findCollisions(BattleAttack $fighter1Attack, BattleAttack $fighter2Attack): array {
        $tile_attack_map = [];
        $collisions = [];

        foreach([$fighter1Attack, $fighter2Attack] as $attack) {
            foreach($attack->path_segments as $segment) {
                if(!isset($tile_attack_map[$segment->tile->index])) {
                    $tile_attack_map[$segment->tile->index] = [
                        'attack_segments' => []
                    ];
                }

                // TODO: how to handle multi attacks from same team?
                $tile_attack_map[$segment->tile->index]['attack_segments'][$attack->id] = [
                    'attack' => $attack,
                    'segment' => $segment
                ];
            }
        }

        // Find intersecting attacks
        $colliding_attack_pairs = [];
        foreach($tile_attack_map as $tile) {
            if(count($tile['attack_segments']) < 2) {
                continue;
            }
            if(count($tile['attack_segments']) > 2) {
                throw new Exception("3-way collisions are currently not supported!");
            }

            $colliding_attack_pairs[] = array_values(
                array_map(
                    function($segment) {
                        return $segment['attack'];
                    },
                    $tile['attack_segments']
                )
            );
        }

        /*
         * For a pair of intersecting attacks, find their collision points (so the rest of the attack can be weakened)
         */
        foreach($colliding_attack_pairs as $colliding_attack_pair) {
            /** @var BattleAttack $attack1 */
            $attack1 = $colliding_attack_pair[0];
            /** @var BattleAttack $attack2 */
            $attack2 = $colliding_attack_pair[1];


            $collision_id = self::collisionId($attack1, $attack2);
            if(isset($collisions[$collision_id])) {
                continue;
            }

            $attack1_collision_point = null;
            $attack2_collision_point = null;

            // TODO: Find collision point
            /* $attack1_overlapping_segments = array_filter(
                 $attack1->path_segments,
                 function($segment) use ($tile_attack_map, $attack2) {
                     return isset($tile_attack_map[$segment->tile->index]['attack_segments'][$attack2->id];
                 }
             );
             $attack2_overlapping_segments = array_filter(
                 $attack2->path_segments,
                 function($segment) use ($tile_attack_map, $attack1) {
                     return isset($tile_attack_map[$segment->tile->index]['attack_segments'][$attack1->id];
                 }
             );*/

            // TYPE 1 - for each tile, see if attack on same tile is <= same time
            foreach($attack1->path_segments as $segment) {
                $other_attack_on_tile = $tile_attack_map[$segment->tile->index]['attack_segments'][$attack2->id] ?? null;
                if($other_attack_on_tile != null) {
                    /** @var AttackPathSegment $other_segment */
                    $other_segment = $other_attack_on_tile['segment'];

                    if($other_segment->time_arrived <= $segment->time_arrived) {
                        $attack1_collision_point = $segment->tile->index;
                        break;
                    }
                }
            }
            foreach($attack2->path_segments as $segment) {
                $other_attack_on_tile = $tile_attack_map[$segment->tile->index]['attack_segments'][$attack1->id] ?? null;
                if($other_attack_on_tile != null) {
                    /** @var AttackPathSegment $other_segment */
                    $other_segment = $other_attack_on_tile['segment'];
                    if($other_segment->time_arrived <= $segment->time_arrived) {
                        $attack2_collision_point = $segment->tile->index;
                        break;
                    }
                }
            }

            if($attack1_collision_point != null && $attack2_collision_point == null) {
                $attack2_collision_point = $attack1_collision_point;
            }
            if($attack2_collision_point != null && $attack1_collision_point == null) {
                $attack1_collision_point = $attack2_collision_point;
            }

            // TYPE 2 - For each tile, see if attack on next tile is <= time + 1
            $collisions[$collision_id] = new AttackCollision(
                id: $collision_id,
                attack1: $attack1,
                attack2: $attack2,
                attack1_collision_point: $attack1_collision_point,
                attack2_collision_point: $attack2_collision_point
            );
        }

        return $collisions;
    }
}