<?php

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . '/BattleV2.php';
require_once __DIR__ . '/BattleApiPresenter.php';
require_once __DIR__ . '/BattleField.php';
require_once __DIR__ . '/BattleEffectsManagerV2.php';
require_once __DIR__ . '/BattleAttackV2.php';
require_once __DIR__ . '/BattleAttackHit.php';
require_once __DIR__ . '/BattleActionProcessor.php';
require_once __DIR__ . '/AttackTarget.php';
require_once __DIR__ . '/FighterAction.php';
require_once __DIR__ . '/../ActionResult.php';

/*
 *
 * - what turn is it
 * - collect actions
 *      - movements
 *      - attacks (ID/seals, target ID(single) or target tile or target direction(AoE))
 * - end when actions are collected or time elapsed
 *
 *
 * BATTLEFIELD
 * - handle:
 *   - movement
 *     - does the movement trigger an effect (tile status effect, or backstab bonus?)
 *     - if players move past each other, stop movement
 *   - player positions
 *   - do attacks hit
 *   - do attacks interact with anything while flying
 *
 * MOVEMENT PHASE
 * - allow same tile
 * - max 2 people from team per tile
 * - opportunity attacks to disincentivize running through opponent
 *
 * ATTACK PHASE
 * - activate conditions (genjutsu)
 * - cast time (nin/gen)
 *   - is interrupted?
 *
 */

// Attack not casting is due to casting it after turn time passes limit, then turn needs to reset

/* Types of ninjutsu
- melee
- projectile (single target)
- projectile AoE
- 360 defense
- buff (cloak)
*/

class BattleManagerV2 {
    const SPEED_DAMAGE_REDUCTION_RATIO = 0.4;
    const CAST_SPEED_DAMAGE_REDUCTION_RATIO = 0.4;
    const MAX_EVASION_DAMAGE_REDUCTION = 0.35;

    private System $system;

    public int $battle_id;

    private BattleV2 $battle;

    public User $player;
    public Fighter $opponent;

    public string $player_side;
    public string $opponent_side;

    public array $player_jutsu_used = [];

    // Components
    public BattleField $field;
    private BattleEffectsManagerV2 $effects;
    private BattleActionProcessor $actions;

    /** @var Jutsu[] */
    public array $default_attacks;

    public bool $spectate = false;

    const DEBUG_PLAYER_ACTION = 'player_action';
    const DEBUG_OPPONENT_ACTION = 'opponent_action';
    const DEBUG_DAMAGE = 'damage';
    const DEBUG_ATTACK_COLLISION = 'attack_collision';

    public array $debug = [
        self::DEBUG_PLAYER_ACTION => true,
        self::DEBUG_OPPONENT_ACTION => true,
        self::DEBUG_DAMAGE => true,
        self::DEBUG_ATTACK_COLLISION => false,
    ];

    // INITIALIZATION

    /**
     * BattleManagerV2 constructor.
     *
     * @param System $system
     * @param User   $player
     * @param BattleV2 $battle
     * @param array  $default_attacks
     * @param bool   $spectate
     */
    public function __construct(
        System $system,
        User $player,
        BattleV2 $battle,
        array $default_attacks,
        bool $spectate,
    ) {
        $this->system = $system;
        $this->battle = $battle;
        $this->battle_id = $battle->battle_id;
        $this->player = $player;
        $this->spectate = $spectate;

        $this->default_attacks = $default_attacks;

        $this->field = new BattleField(
            $system,
            $this->battle,
        );

        $this->effects = new BattleEffectsManagerV2(
            $system,
            json_decode($this->battle->raw_active_effects, true),
            json_decode($this->battle->raw_active_genjutsu, true)
        );

        $debug_closure = function(string $category, string $label, string $content) {
            $this->debug($category, $label, $content);
        };

        $this->actions = new BattleActionProcessor(
            system: $system,
            battle: $this->battle,
            field: $this->field,
            effects: $this->effects,
            debug_closure: $debug_closure,
            default_attacks: $this->default_attacks,
        );
    }

    /**
     * The database calls necessary for spinning up a working instance of BattleManagerV2 are captured in this method,
     * so that for testing we can inject mocks.
     *
     * @throws Exception
     */
    public static function init(
        System $system,
        User $player,
        int $battle_id,
        bool $spectate = false,
        bool $load_fighters = true
    ): BattleManagerV2 {
        $battle = BattleV2::loadFromId($system, $player, $battle_id);
        $default_attacks = BattleManagerV2::getDefaultAttacks($system);

        $battleManager = new BattleManagerV2(
            system: $system,
            player: $player,
            battle: $battle,
            default_attacks: $default_attacks,
            spectate: $spectate,
        );

        if($load_fighters) {
            $battleManager->loadFighters();
            $battleManager->effects->applyPassiveEffects($battle->player1, $battle->player2);
        }

        return $battleManager;
    }

    /**
     * @throws Exception
     */
    protected function loadFighters(): void {
        if($this->player->id == $this->battle->player1_id) {
            $this->player_side = BattleV2::TEAM1;
            $this->opponent_side = BattleV2::TEAM2;

            $this->battle->player1 = $this->player;
            $this->battle->player1->combat_id = BattleV2::combatId(BattleV2::TEAM1, $this->battle->player1);

            if(!isset($this->battle->fighter_jutsu_used[$this->battle->player1->combat_id])) {
                $this->battle->fighter_jutsu_used[$this->battle->player1->combat_id] = [];
            }
            $this->player_jutsu_used =& $this->battle->fighter_jutsu_used[$this->battle->player1->combat_id];
        }
        else if($this->player->id == $this->battle->player2_id) {
            $this->player_side = BattleV2::TEAM2;
            $this->opponent_side = BattleV2::TEAM1;

            $this->battle->player2 = $this->player;
            $this->battle->player2->combat_id = BattleV2::combatId(BattleV2::TEAM2, $this->battle->player2);

            if(!isset($this->battle->fighter_jutsu_used[$this->battle->player2->combat_id])) {
                $this->battle->fighter_jutsu_used[$this->battle->player2->combat_id] = [];
            }
            $this->player_jutsu_used =& $this->battle->fighter_jutsu_used[$this->battle->player2->combat_id];
        }
        else {
            $this->player_side = BattleV2::TEAM1;
            $this->opponent_side = BattleV2::TEAM2;
        }

        $this->battle->loadFighters();

        if($this->player_side == BattleV2::TEAM1) {
            $this->opponent =& $this->battle->player2;
        }
        else {
            $this->opponent =& $this->battle->player1;
        }

        if(!$this->spectate && !$this->battle->isComplete()) {
            if($this->battle->player1 instanceof User && $this->battle->player1->battle_id != $this->battle_id) {
                $this->system->log(
                    'debug',
                    'battle_stopped',
                    "Battle #{$this->battle_id} stopped - "
                    . "P1 battle ID #{$this->battle->player1->battle_id} - "
                    . "P2 battle ID #{$this->battle->player2->battle_id}"
                );
                $this->stopBattle();
                return;
            }
            if($this->battle->player2 instanceof User && $this->battle->player2->battle_id != $this->battle_id) {
                $this->system->log(
                    'debug',
                    'battle_stopped',
                    "Battle #{$this->battle_id} stopped - "
                    . "P2 battle ID #{$this->battle->player2->battle_id} - "
                    . "P1 battle ID #{$this->battle->player1->battle_id}"
                );
                $this->stopBattle();
                return;
            }
        }
    }


    // PUBLIC MUTATION API

    /**
     * This is the primary method most usages of battle should call. This triggers progresses the battle if the
     * required inputs have been gathered.
     *
     * @return string|null
     * @throws Exception
     */
    public function checkInputAndRunTurn(): ?string {
        // If someone is not in battle, this will be set
        if($this->battle->winner) {
            return $this->battle->winner;
        }
        if($this->spectate) {
            return $this->battle->winner;
        }

        if(!empty($_POST['forfeit'])) {
            $this->player->health = 0;

            $this->battle->current_turn_log->addFighterActionDescription(
                $this->player,
                $this->player->getName() . ' forfeits.'
            );

            $this->checkForWinner();
            $this->updateData();

            return $this->battle->winner;
        }

        if($this->battle->isPreparationPhase()) {
            $this->runPlayerHealItemAction($_POST);
            return false;
        }

        // If turn is still active and user hasn't submitted their move, check for action
        if(!$this->playerActionSubmitted() &&
            ($this->battle->timeRemaining() > 0 || !$this->opponentActionSubmitted())
        ) {
            $player_action = $this->collectPlayerAction($_POST);
            $this->debug(self::DEBUG_PLAYER_ACTION, 'playerActionSubmitted', print_r($player_action, true));

            if($player_action != null) {
                $this->setPlayerAction($this->player, $player_action);

                if($this->opponent instanceof NPC) {
                    $this->chooseAndSetNPCAction($this->opponent);
                }
            }
        }



        // If time is up or both people have submitted moves, RUN TURN
        if($this->battle->timeRemaining() <= 0 || $this->allActionsSubmitted()) {
            if(!empty($this->battle->fighter_actions)) {
                $this->runActions();
            }
        }

        $this->checkForWinner();
        $this->updateData();

        return $this->battle->winner;
    }


    // PUBLIC VIEW API

    /**
     * @throws Exception
     */
    public function renderBattle(): void {
        global $self_link;

        $refresh_link = $this->spectate ? "{$self_link}&battle_id={$this->battle->battle_id}" : $self_link;

        $spectate = $this->spectate;
        $battleManager = $this;
        $battle = $this->battle;
        $system = $this->system;

        $player = $this->player;
        $opponent = $this->opponent;

        // require 'templates/battle/battle_interface.php';
        require 'templates/battle/battle_interface_v2.php';
    }

    public function getApiResponse(): array {
        if($this->player === $this->battle->player1) {
            $player = $this->battle->player1;
            $opponent = $this->battle->player2;
        }
        else if($this->player === $this->battle->player2) {
            $player = $this->battle->player2;
            $opponent = $this->battle->player1;
        }
        else {
            $player = $this->battle->player1;
            $opponent = $this->battle->player2;
        }

        return BattleApiPresenter::buildResponse(
            battle: $this->battle,
            battle_field: $this->field,
            player: $player,
            opponent: $opponent,
            is_spectating: $this->spectate,
            player_action_submitted: $this->playerActionSubmitted(),
            player_default_attacks: BattleManagerV2::getDefaultAttacks($this->system),
            player_equipped_jutsu: $player->equipped_jutsu
        );
    }

    #[Pure]
    public function isComplete(): bool {
        return $this->battle->isComplete();
    }

    public function playerActionSubmitted(): bool {
        return $this->battle->isFighterActionSubmitted($this->player);
    }

    public function opponentActionSubmitted(): bool {
        return $this->battle->isFighterActionSubmitted($this->opponent);
    }

    public function allActionsSubmitted(): bool {
        return $this->battle->isFighterActionSubmitted($this->battle->player1) &&
            $this->battle->isFighterActionSubmitted($this->battle->player2);
    }

    /**
     * @throws Exception
     */
    public function isPlayerWinner(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->battle->winner === $this->player_side;
    }

    /**
     * @throws Exception
     */
    public function isOpponentWinner(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->battle->winner === $this->opponent_side;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isDraw(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isDraw() check before battle is complete!");
        }

        return $this->battle->winner === BattleV2::DRAW;
    }

    public function getBattleType(): int {
        return $this->battle->battle_type;
    }

    #[Pure]
    public function getPhaseLabel(): string {
        if($this->battle->isMovementPhase()) {
            return "Movement";
        }
        else if($this->battle->isAttackPhase()) {
            return "Attack";
        }
        else {
            return "[Invalid]";
        }
    }

    // PRIVATE API - TURN LIFECYCLE

    /**
     * @throws Exception
     */
    protected function runActions(): void {
        $this->processTurnEffects();

        if($this->battle->isMovementPhase()) {
            $this->actions->runMovementPhaseActions();
        }
        else if($this->battle->isAttackPhase()) {
            $this->actions->runAttackPhaseActions();
        }

        $this->setEffectDescriptions();

        // Update battle
        $this->finishTurn();
    }

    private function finishTurn() {
        $this->battle->turn_time = time();
        $this->battle->turn_count++;
        $this->battle->turn_type = $this->nextTurnPhase();

        $this->battle->fighter_actions = [];

        $this->battle->fighter_health[$this->battle->player1->combat_id] = $this->battle->player1->health;
        $this->battle->fighter_health[$this->battle->player2->combat_id] = $this->battle->player2->health;

        $this->battle->player1->updateData();
        $this->battle->player1->updateInventory();

        $this->battle->player2->updateData();
        $this->battle->player2->updateInventory();
    }

    #[Pure]
    private function nextTurnPhase(): string {
        if($this->battle->isMovementPhase()) {
            return BattleV2::TURN_TYPE_ATTACK;
        }
        else {
            return BattleV2::TURN_TYPE_MOVEMENT;
        }
    }

    private function checkForWinner(): string {
        if($this->battle->isComplete()) {
            return $this->battle->winner;
        }

        if($this->battle->player1->health > 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = BattleV2::TEAM1;
        }
        else if($this->battle->player2->health > 0 && $this->battle->player1->health <= 0) {
            $this->battle->winner = BattleV2::TEAM2;
        }
        else if($this->battle->player1->health <= 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = BattleV2::DRAW;
        }

        if($this->battle->winner && !$this->spectate) {
            $this->player->updateInventory();
        }

        return $this->battle->winner;
    }

    /**
     * @throws Exception
     */
    private function processTurnEffects(): void {
        // Run turn effects
        $this->effects->applyActiveEffects($this->battle->player1, $this->battle->player2);

        // Decrement cooldowns
        if(!empty($this->battle->jutsu_cooldowns)) {
            foreach($this->battle->jutsu_cooldowns as $id => $cooldown) {
                $this->battle->jutsu_cooldowns[$id]--;
                if($this->battle->jutsu_cooldowns[$id] == 0) {
                    unset($this->battle->jutsu_cooldowns[$id]);
                }
            }
        }
    }

    private function setEffectDescriptions(): void {
        if($this->effects->hasEffectHits($this->battle->player1)) {
            foreach($this->effects->getEffectHits($this->battle->player1) as $effect_hit) {
                /** @var EffectHitLog $effect_hit */
                $effect_hit->description = BattleLogV2::parseCombatText(
                    text: $effect_hit->description,
                    attacker: $this->battle->getFighter($effect_hit->caster_id),
                    target: $this->battle->player1,
                );

                $this->battle->current_turn_log->addFighterEffectHit($this->battle->player1, $effect_hit);
            }
        }
        if($this->effects->hasEffectHits($this->battle->player2)) {
            foreach($this->effects->getEffectHits($this->battle->player2) as $effect_hit) {
                /** @var EffectHitLog $effect_hit */
                $effect_hit->description = BattleLogV2::parseCombatText(
                    text: $effect_hit->description,
                    attacker: $this->battle->getFighter($effect_hit->caster_id),
                    target: $this->battle->player2,
                );

                $this->battle->current_turn_log->addFighterEffectHit($this->battle->player2, $effect_hit);
            }
        }
    }


    protected function stopBattle() {
        $this->battle->winner = BattleV2::DRAW;
        $this->updateData();
    }

    public function updateData() {
        if($this->spectate) {
            return;
        }

        $this->battle->raw_active_effects = json_encode($this->effects->active_effects);
        $this->battle->raw_active_genjutsu = json_encode($this->effects->active_genjutsu);
        $this->battle->raw_field = json_encode($this->field->exportToDb());

        $this->battle->updateData();
    }


    // PRIVATE API - PLAYER ACTIONS

    /**
     * @param Fighter       $player
     * @param FighterAction $action
     * @throws Exception
     */
    protected function setPlayerAction(Fighter $player, FighterAction $action) {
        if($this->battle->isAttackPhase() && $action instanceof FighterAttackAction) {
            $this->battle->fighter_actions[$player->combat_id] = $action;
            $jutsu = $this->actions->getJutsuFromAttackAction($player, $action);

            if(isset($this->player_jutsu_used[$jutsu->combat_id])) {
                $this->battle->fighter_jutsu_used[$player->combat_id][$jutsu->combat_id]['count']++;
            }
            else {
                $this->player_jutsu_used[$jutsu->combat_id] = [];
                $this->player_jutsu_used[$jutsu->combat_id]['jutsu_type'] = $jutsu->jutsu_type;
                $this->player_jutsu_used[$jutsu->combat_id]['count'] = 1;
            }
        }
        else if($this->battle->isMovementPhase() && $action instanceof FighterMovementAction) {
            $this->battle->fighter_actions[$player->combat_id] = $action;
        }
        else {
            throw new Exception(
                "Invalid attack type for current turn phase! " .
                "{$this->battle->turn_type} / {$action->type}"
            );
        }
    }

    protected function runPlayerHealItemAction(array $FORM_DATA) {
        try {
            if(isset($FORM_DATA['submit_prep_action'])) {
                $item_id = $FORM_DATA['item_id'] ?? null;
                if($item_id && $this->player->hasItem($item_id)) {
                    $item = $this->player->items[$item_id];

                    $max_health = $this->player->max_health * (BattleV2::MAX_PRE_FIGHT_HEAL_PERCENT / 100);

                    if($this->player->health >= $max_health) {
                        throw new Exception("You can't heal any further!");
                    }
                    if($item->effect === 'heal') {
                        if(--$this->player->items[$item_id]->quantity === 0) {
                            unset($this->player->items[$item_id]);
                        }

                        $this->player->health += $item->effect_amount;
                        if($this->player->health >= $max_health) {
                            $this->player->health = $max_health;
                        }

                        $this->player->updateData();
                        $this->player->updateInventory();
                        $this->battle->current_turn_log->addFighterActionDescription(
                            $this->player,
                            sprintf(
                                "%s used a %s and healed for %.2f[br]", $this->player->user_name, $item->name,
                                $item->effect_amount
                            )
                        );
                        $this->updateData();
                    }
                }
            }
        } catch(Exception $e) {
            $this->system->message($e->getMessage());
        }
    }

    /**
     * @param Fighter $npc
     * @throws Exception
     */
    protected function chooseAndSetNPCAction(Fighter $npc): void {
        if(!($npc instanceof NPC)) {
            throw new Exception("Calling chooseAndSetNPCAction on non-NPC!");
        }

        $target_fighter_location = $this->field->getFighterLocation($this->player->combat_id);

        $action = null;
        if($this->battle->isMovementPhase()) {
            $action = $this->chooseNPCMovementAction(
                npc: $npc,
                target_fighter_location: $target_fighter_location
            );
            $this->battle->fighter_actions[$npc->combat_id] = $action;
        }
        else if($this->battle->isAttackPhase()) {
            $action = $this->chooseNPCAttackAction(
                npc: $npc,
                target: $this->player
            );
            $this->battle->fighter_actions[$npc->combat_id] = $action;
        }

        $this->debug(self::DEBUG_OPPONENT_ACTION, 'choosingOpponentAction', print_r($action, true));
    }

    protected function chooseNPCMovementAction(Fighter $npc, int $target_fighter_location): FighterMovementAction {
        $npc_location = $this->field->getFighterLocation($npc->combat_id);

        /*
         * We want to move closer to our target - Imagine the field with 4 tiles and fighter A wants to move to fighter B.
         * By subtracting fighter B's tile from fighter A's tile, you get the adjustment to fighter A's location
         * necessary to move to fighter B.
         *
         * MOVING TO THE RIGHT:
         *
         * 1 2 3 4
         * -------
         * A   B
         *
         * B - A = movement to B
         * 3 - 1 = 2
         *
         * A + movement to B = B's location
         * 1 + 2 = 3
         * 3 is indeed B's location
         *
         * MOVING TO THE LEFT
         *
         * 1 2 3 4
         * -------
         * B   A
         *
         * B - A = movement to B
         * 1 - 3 = -2
         *
         * A + movement to B = B's location
         * 3 + -2 = 1
         *
         * 1 is indeed B's location
         *
         */
        $movement_needed_to_target = $target_fighter_location - $this->field->getFighterLocation($npc->combat_id);
        $movement_is_negative = $movement_needed_to_target < 0;

        $distance_to_target = abs($movement_needed_to_target) - 1; // -1 so we stand next to them, not on their tile
        if($distance_to_target <= 0) {
            return new FighterMovementAction(
                fighter_id: $npc->combat_id,
                target_tile: $npc_location,
            );
        }


        $this->debug(
            self::DEBUG_OPPONENT_ACTION,
            'chooseNPCMovementAction',
            print_r([
                'target_fighter_location' => $target_fighter_location,
                'npc_location' => $npc_location,
                'movement_needed_to_target' => $movement_needed_to_target,
            ], true)
        );

        $distance_to_move = min($distance_to_target, $npc->max_movement_distance);
        $movement_to_do = $distance_to_move * ($movement_is_negative ? -1 : 1);

        return new FighterMovementAction(
            fighter_id: $npc->combat_id,
            target_tile: $npc_location + $movement_to_do,
        );
    }

    /**
     * @throws Exception
     */
    protected function chooseNPCAttackAction(NPC $npc, Fighter $target): FighterAttackAction {
        $jutsu = $npc->chooseAttack();
        $jutsu->setCombatId($npc->combat_id);

        // $fighter_id_target = new AttackFighterIdTarget($this->player->combat_id);
        $target_fighter_location = $this->field->getFighterLocation($target->combat_id);
        $target_direction = $this->field->getTileDirectionFromFighter($npc, $target_fighter_location);

        return new FighterAttackAction(
            fighter_id: $npc->combat_id,
            jutsu_id: $jutsu->id,
            jutsu_purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            weapon_id: null,
            target: new AttackDirectionTarget($target_direction)
        );
    }

    // PRIVATE UTILS - PLAYER ACTIONS (public only for unit testing)

    /**
     * @throws Exception
     */
    public function collectPlayerAction(array $FORM_DATA): ?FighterAction {
        if($this->battle->isAttackPhase() && !empty($FORM_DATA['submit_attack'])) {
            $jutsu_category = $FORM_DATA['jutsu_category'];
            $jutsu_id = (int)$FORM_DATA['jutsu_id'] ?? null;
            $hand_seals = $FORM_DATA['hand_seals'] ?? null;
            $weapon_id = (int)$FORM_DATA['weapon_id'] ?? 0;
            $target_tile = (int)$FORM_DATA['target_tile'] ?? null;

            $this->debug(
                self::DEBUG_PLAYER_ACTION,
                'collecting attack action',
                json_encode([
                    'jutsu_type' => $jutsu_category,
                    'jutsu_id' => $jutsu_id,
                    'hand_seals' => $hand_seals,
                    'weapon_id' => $weapon_id,
                    'target_tile' => $target_tile,
                ])
            );

            // Check for handseals if ninjutsu/genjutsu
            if($jutsu_category == Jutsu::TYPE_NINJUTSU or $jutsu_category == Jutsu::TYPE_GENJUTSU) {
                if(!$hand_seals) {
                    throw new Exception("Please enter hand seals!");
                }

                $player_jutsu = $this->getJutsuFromHandSeals($this->player, $hand_seals);

                // Layered genjutsu check
                if($player_jutsu && $player_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU && !empty($player_jutsu->parent_jutsu)) {
                    $this->effects->assertParentGenjutsuActive($this->player, $player_jutsu);
                }
            }

            // Check jutsu ID if taijutsu
            else if($jutsu_category == Jutsu::TYPE_TAIJUTSU) {
                $player_jutsu = $this->getJutsuFromId($this->player, $jutsu_id);
            }
            // Check BL jutsu ID if bloodline jutsu
            else if($jutsu_category == 'bloodline_jutsu' && $this->player->bloodline_id) {
                $player_jutsu = null;
                if(isset($this->player->bloodline->jutsu[$jutsu_id])) {
                    $player_jutsu = $this->player->bloodline->jutsu[$jutsu_id];
                    $player_jutsu->setCombatId($this->player->combat_id);
                }
            }
            else {
                throw new Exception("Invalid jutsu selection!");
            }

            // Check jutsu cooldown
            if(!$player_jutsu) {
                throw new Exception("Invalid jutsu!");
            }
            if(isset($this->battle->jutsu_cooldowns[$player_jutsu->combat_id])) {
                throw new Exception(
                    "Cannot use that jutsu, it is on cooldown for " . $this->battle->jutsu_cooldowns[$player_jutsu->combat_id] . " more turns!"
                );
            }

            $result = $this->player->useJutsu($player_jutsu);
            if($result->failed) {
                throw new Exception($result->error_message);
            }

            // Check for weapon if non-BL taijutsu
            if($jutsu_category == Jutsu::TYPE_TAIJUTSU && !empty($weapon_id)) {
                if($this->player->hasItem($weapon_id)) {
                    if(!in_array($weapon_id, $this->player->equipped_weapon_ids)) {
                        $weapon_id = 0;
                    }
                }
                else {
                    $weapon_id = 0;
                }
            }

            // Check target
            $target = $this->getTarget($this->player, $player_jutsu, $target_tile);

            // Log jutsu used
            return new FighterAttackAction(
                fighter_id: $this->player->combat_id,
                jutsu_id: $player_jutsu->id,
                jutsu_purchase_type: $player_jutsu->purchase_type,
                weapon_id: $weapon_id,
                target: $target,
            );
        }
        else if($this->battle->isMovementPhase() && !empty($FORM_DATA['submit_movement_action'])) {
            $target_tile = $FORM_DATA['selected_tile'] ?? null;

            $this->debug(self::DEBUG_PLAYER_ACTION, 'collecting movement action', $target_tile);

            // Run player attack
            if($target_tile === null) {
                throw new Exception("Invalid tile!");
            }
            if(!$this->field->tileIsInBounds($target_tile)) {
                throw new Exception("Invalid tile - out of bounds!");
            }

            return new FighterMovementAction(
                fighter_id: $this->player->combat_id,
                target_tile: $target_tile
            );
        }

        return null;
    }

    /**
     * @param Fighter  $fighter
     * @param Jutsu    $jutsu
     * @param int|null $target_tile
     * @return AttackTarget
     * @throws Exception
     */
    private function getTarget(Fighter $fighter, Jutsu $jutsu, ?int $target_tile): AttackTarget {
        if($target_tile !== null) {
            $distance_to_target = $this->field->distanceFromFighter($fighter->combat_id, $target_tile);
            if($distance_to_target > $jutsu->range) {
                throw new Exception("getTarget: Target is not in range!");
            }

            if($jutsu->target_type === Jutsu::TARGET_TYPE_DIRECTION) {
                $direction = $this->field->getTileDirectionFromFighter(
                    fighter: $this->player, target_tile: $target_tile
                );
                return new AttackDirectionTarget($direction);
            }
            else if($jutsu->target_type === Jutsu::TARGET_TYPE_TILE) {
                return new AttackTileTarget($target_tile);
            }
            else {
                throw new Exception("getTarget: Unsupported target type!");
            }
        }
        else {
            // TODO: Attack a fighter directly
            throw new Exception("getTarget: Invalid target type!");
        }
    }

    private function getJutsuFromHandSeals(Fighter $fighter, ?array $hand_seals): ?Jutsu {
        /*if(is_array($_POST['hand_seals'])) {
            $seals = array();
            foreach($_POST['hand_seals'] as $seal) {
                if(!is_numeric($seal)) {
                    break;
                }
                $seals[] = $seal;
            }
            $seal_string = implode('-', $seals);
        }*/

        $raw_seals = $hand_seals;
        $seals = [];
        foreach($raw_seals as $seal) {
            if(!is_numeric($seal)) {
                break;
            }
            $seals[] = $seal;
        }
        $seal_string = implode('-', $seals);

        $fighter_jutsu = null;
        foreach($this->default_attacks as $id => $attack) {
            if($attack->hand_seals == $seal_string) {
                $fighter_jutsu = $attack;
                break;
            }
        }
        foreach($fighter->jutsu as $id => $jutsu) {
            if($jutsu->hand_seals == $seal_string) {
                $fighter_jutsu = $jutsu;
                break;
            }
        }
        $fighter_jutsu?->setCombatId($fighter->combat_id);

        return $fighter_jutsu;
    }

    private function getJutsuFromId(Fighter $fighter, int $jutsu_id): ?Jutsu {
        $fighter_jutsu = null;
        if(isset($this->default_attacks[$jutsu_id]) && $this->default_attacks[$jutsu_id]->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            $fighter_jutsu = $this->default_attacks[$jutsu_id];
        }
        if($fighter->hasEquippedJutsu($jutsu_id) && $fighter->jutsu[$jutsu_id]->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            $fighter_jutsu = $fighter->jutsu[$jutsu_id];
        }

        if($fighter_jutsu) {
            $fighter_jutsu->setCombatId($fighter->combat_id);
        }

        return $fighter_jutsu;
    }

    /**
     * @param string $category
     * @param string $label
     * @param string $content
     * @return void
     */
    private function debug(string $category, string $label, string $content): void {
        if(($this->debug[$category] ?? false) !== true) {
            return;
        }

        if($content == "") {
            $content = "[empty]";
        }

        $this->system->debugMessage($label . ': ' . $content);
    }

    // STATIC UTILS

    protected static function getDefaultAttacks(System $system): array {
        $default_attacks = [];

        $query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
        $result = $system->query($query);
        while($row = $system->db_fetch($result)) {
            $default_attacks[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
        }
        return $default_attacks;
    }

}