<?php

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . '/Battle.php';
require_once __DIR__ . '/BattleApiPresenter.php';
require_once __DIR__ . '/BattleField.php';
require_once __DIR__ . '/BattleEffectsManager.php';
require_once __DIR__ . '/BattleAttack.php';
require_once __DIR__ . '/AttackTarget.php';
require_once __DIR__ . '/FighterAction.php';

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

class BattleManager {
    const SPEED_DAMAGE_REDUCTION_RATIO = 0.4;
    const CAST_SPEED_DAMAGE_REDUCTION_RATIO = 0.4;
    const MAX_EVASION_DAMAGE_REDUCTION = 0.35;

    private System $system;

    public int $battle_id;

    private Battle $battle;

    private bool $is_api_request;

    public User $player;
    public Fighter $opponent;

    public string $player_side;
    public string $opponent_side;

    public array $player_jutsu_used = [];

    // Components
    public BattleField $field;
    private BattleEffectsManager $effects;

    /** @var Jutsu[] */
    public array $default_attacks;

    public bool $spectate = false;

    const DEBUG_PLAYER_ACTION = 'player_action';

    public array $debug = [
        self::DEBUG_PLAYER_ACTION => false
    ];

    // INITIALIZATION

    /**
     * BattleManager constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @param bool   $spectate
     * @param bool   $load_fighters
     * @param bool   $is_api_request
     * @throws Exception
     */
    public function __construct(
        System $system,
        User $player,
        int $battle_id,
        bool $spectate = false,
        bool $load_fighters = true,
        bool $is_api_request = false
    ) {
        $this->system = $system;
        $this->battle_id = $battle_id;
        $this->player = $player;
        $this->spectate = $spectate;
        $this->battle = new Battle($system, $player, $battle_id);
        $this->is_api_request = $is_api_request;

        $this->default_attacks = $this->getDefaultAttacks();

        $this->field = new BattleField(
             $system,
             $this->battle,
        );

        $this->effects = new BattleEffectsManager(
            $system,
            json_decode($this->battle->raw_active_effects, true),
            json_decode($this->battle->raw_active_genjutsu, true)
        );

        if($load_fighters) {
            $this->loadFighters();

            $this->effects->applyPassiveEffects($this->battle->player1, $this->battle->player2);
        }
    }

    /**
     * @throws Exception
     */
    protected function loadFighters() {
        if($this->player->id == $this->battle->player1_id) {
            $this->player_side = Battle::TEAM1;
            $this->opponent_side = Battle::TEAM2;

            $this->battle->player1 = $this->player;
            $this->battle->player1->combat_id = Battle::combatId(Battle::TEAM1, $this->battle->player1);

            if(!isset($this->battle->fighter_jutsu_used[$this->battle->player1->combat_id])) {
                $this->battle->fighter_jutsu_used[$this->battle->player1->combat_id] = [];
            }
            $this->player_jutsu_used =& $this->battle->fighter_jutsu_used[$this->battle->player1->combat_id];
        }
        else if($this->player->id == $this->battle->player2_id) {
            $this->player_side = Battle::TEAM2;
            $this->opponent_side = Battle::TEAM1;

            $this->battle->player2 = $this->player;
            $this->battle->player2->combat_id = Battle::combatId(Battle::TEAM2, $this->battle->player2);

            if(!isset($this->battle->fighter_jutsu_used[$this->battle->player2->combat_id])) {
                $this->battle->fighter_jutsu_used[$this->battle->player2->combat_id] = [];
            }
            $this->player_jutsu_used =& $this->battle->fighter_jutsu_used[$this->battle->player2->combat_id];
        }
        else {
            $this->player_side = Battle::TEAM1;
            $this->opponent_side = Battle::TEAM2;
        }

        $this->battle->loadFighters();

        if($this->player_side == Battle::TEAM1) {
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

    protected function getDefaultAttacks(): array {
        $default_attacks = [];

        $query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
        $result = $this->system->query($query);
        while($row = $this->system->db_fetch($result)) {
            $default_attacks[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
        }
        return $default_attacks;
    }


    // PUBLIC MUTATION API

    /**
     * This is the primary method most usages of battle should call. This triggers progresses the battle if the required inputs
     * have been gathered.
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

        if($this->battle->isPreparationPhase()) {
            $this->runPlayerHealItemAction($_POST);
            return false;
        }

        // If turn is still active and user hasn't submitted their move, check for action
        if(!$this->playerActionSubmitted() &&
            ($this->battle->timeRemaining() > 0 || !$this->opponentActionSubmitted())
        ) {
            $player_action = $this->collectPlayerAction($_POST);
            $this->renderDebug(self::DEBUG_PLAYER_ACTION, 'playerActionSubmitted', print_r($player_action, true));

            if($player_action != null) {
                $this->setPlayerAction($this->player, $player_action);

                if($this->opponent instanceof NPC) {
                    $this->chooseAndSetNPCAttackAction($this->opponent);
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
            player_default_attacks: $this->getDefaultAttacks(),
            player_equipped_jutsu: $player->equipped_jutsu
        );
    }

    #[Pure]
    public function isComplete(): bool {
        return $this->battle->isComplete();
    }

    public function playerActionSubmitted(): bool {
        return $this->fighterActionSubmitted($this->player);
    }

    public function opponentActionSubmitted(): bool {
        return $this->fighterActionSubmitted($this->opponent);
    }

    public function allActionsSubmitted(): bool {
        return $this->fighterActionSubmitted($this->battle->player1) &&
            $this->fighterActionSubmitted($this->battle->player2);
    }

    public function fighterActionSubmitted(Fighter $fighter): bool {
        return isset($this->battle->fighter_actions[$fighter->combat_id]);
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

        return $this->battle->winner === Battle::DRAW;
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

    // PRIVATE API - VIEW HELPERS

    private function parseCombatText(string $text, Fighter $attacker, Fighter $target): string {
        return str_replace(
            [
                '[player]',
                '[opponent]',
                '[gender]',
                '[gender2]'
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

    private static function getJutsuTextColor($jutsu_type): string {
        switch ($jutsu_type) {
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


    // PRIVATE API - TURN LIFECYCLE

    /**
     * @throws Exception
     */
    protected function runActions(): void {
        $this->processTurnEffects();

        $this->battle->battle_text = '';

        if($this->battle->isMovementPhase()) {
            $player1_action = $this->battle->fighter_actions[$this->battle->player1->combat_id] ?? null;
            $player2_action = $this->battle->fighter_actions[$this->battle->player2->combat_id] ?? null;

            if($player1_action != null && ($player1_action instanceof FighterMovementAction)) {
                $this->field->moveFighterTo(
                    fighter_id: $this->battle->player1->combat_id,
                    target_tile: $player1_action->target_tile
                );
                $this->battle->battle_text .= $this->battle->player1->getName() .
                    ' moved to tile ' . $player1_action->target_tile . '.';
            }

            if($player1_action != null && $player2_action != null) {
                $this->battle->battle_text .= '[br][hr]';
            }

            if($player2_action != null && ($player2_action instanceof FighterMovementAction)) {
                $this->field->moveFighterTo(
                    fighter_id: $this->battle->player1->combat_id,
                    target_tile: $player2_action->target_tile
                );
                $this->battle->battle_text .= $this->battle->player1->getName() .
                    ' moved to tile ' . $player2_action->target_tile . '.';
            }
        }
        else if($this->battle->isAttackPhase()) {
            $player1_attack = $this->getFighterAttackFromActions($this->battle->player1->combat_id);
            $player2_attack = $this->getFighterAttackFromActions($this->battle->player2->combat_id);

            if($this->system->debug['battle']) {
                echo 'P1: ' . $player1_attack->raw_damage . ' / P2: ' . $player2_attack->raw_damage . '<br />';
            }

            // Actions cast
            // Actions have multiple collisions now
            // Cast time
            // travel time

            // Collision
            $collision_text = null;
            if($player1_attack != null && $player2_attack != null) {
                $collision_text = $this->jutsuCollision(
                    $this->battle->player1, $this->battle->player2,
                    $player1_attack->raw_damage, $player2_attack->raw_damage,
                    $player1_attack->jutsu, $player2_attack->jutsu
                );
            }

            // Apply remaining barrier
            if($player1_attack) {
                $this->effects->updateBarrier($this->battle->player1, $player1_attack->jutsu);
            }
            if($player2_attack) {
                $this->effects->updateBarrier($this->battle->player2, $player2_attack->jutsu);
            }

            // Apply damage/effects and set display
            if($player1_attack) {
                $this->applyAttack($player1_attack, $this->battle->player1, $this->battle->player2);
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

            if($collision_text) {
                $collision_text = $this->parseCombatText($collision_text, $this->battle->player1, $this->battle->player2);
                $this->battle->battle_text .= '[br][hr]' . $this->system->clean($collision_text);
            }
            $this->battle->battle_text .= '[br][hr]';

            // Apply damage/effects and set display
            if($player2_attack) {
                $this->applyAttack($player2_attack, $this->battle->player2, $this->battle->player1);
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
            return Battle::TURN_TYPE_ATTACK;
        }
        else {
            return Battle::TURN_TYPE_MOVEMENT;
        }
    }

    private function checkForWinner(): string {
        if($this->battle->isComplete()) {
            return $this->battle->winner;
        }

        if($this->battle->player1->health > 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = Battle::TEAM1;
        }
        else if($this->battle->player2->health > 0 && $this->battle->player1->health <= 0) {
            $this->battle->winner = Battle::TEAM2;
        }
        else if($this->battle->player1->health <= 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = Battle::DRAW;
        }

        if($this->battle->winner && !$this->spectate) {
            $this->player->updateInventory();
        }

        return $this->battle->winner;
    }

    /**
     * @throws Exception
     */
    private function processTurnEffects() {
        // Run turn effects
        $this->effects->applyActiveEffects($this->battle->player1, $this->battle->player2);

        // Decrement cooldowns
        if(!empty($this->battle->jutsu_cooldowns)) {
            foreach($this->battle->jutsu_cooldowns as $id=>$cooldown) {
                $this->battle->jutsu_cooldowns[$id]--;
                if($this->battle->jutsu_cooldowns[$id] == 0) {
                    unset($this->battle->jutsu_cooldowns[$id]);
                }
            }
        }
    }

    protected function stopBattle() {
        $this->battle->winner = Battle::DRAW;
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


    // PRIVATE API - ATTACK PROCESSING

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
        $attack = new BattleAttack();

        $attack->jutsu = $this->getJutsuFromAttackAction($fighter, $action);
        $attack->jutsu->setCombatId($fighter->combat_id);

        $disable_randomness = false;
        switch($this->battle->battle_type) {
            case Battle::TYPE_AI_ARENA:
            case Battle::TYPE_AI_MISSION:
                $disable_randomness = true;
                break;
        }

        $attack->raw_damage = $fighter->calcDamage(attack: $attack->jutsu, disable_randomness: $disable_randomness);

        // Set weapon data into jutsu
        if($attack->jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU && $action->weapon_id) {
            // Apply element to jutsu
            if($fighter->items[$action->weapon_id]->effect == 'element') {
                $attack->jutsu->element = $fighter->elements['first'];
                $attack->raw_damage *= 1 + ($fighter->items[$action->weapon_id]->effect_amount / 100);
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
            $attack->jutsu->effect_amount = $attack->raw_damage;
            $fighter->barrier += $attack->raw_damage;
            $attack->raw_damage = 0;
        }

        return $attack;
    }

    protected function applyAttack(BattleAttack $attack, Fighter $user, Fighter $target) {
        $attack_damage = $attack->raw_damage;
        if($attack->jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($attack->jutsu->effect_only)) {
            $attack_damage = $target->calcDamageTaken($attack->raw_damage, $attack->jutsu->jutsu_type);
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
                    $attack->raw_damage
                );
            }
        }

        // Set cooldowns
        if($attack->jutsu->cooldown > 0) {
            $this->battle->jutsu_cooldowns[$attack->jutsu->combat_id] = $attack->jutsu->cooldown;
        }

        // Effects
        if($attack->jutsu->hasEffect()) {
            if($attack->jutsu->use_type == Jutsu::USE_TYPE_BUFF || in_array($attack->jutsu->effect, BattleEffect::$buff_effects)) {
                $target_id = $user->combat_id;
            }
            else {
                $target_id = $target->combat_id;
            }

            $this->effects->setEffect(
                $user,
                $target_id,
                $attack->jutsu,
                $attack->raw_damage
            );
        }

        $text = $attack->jutsu->battle_text;
        $attack_jutsu_color = BattleManager::getJutsuTextColor($attack->jutsu->jutsu_type);

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

        if($attack->jutsu->hasEffect()){
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

    /**
     * @throws Exception
     */
    public function jutsuCollision(
        Fighter $player1, Fighter $player2, &$player1_damage, &$player2_damage, Jutsu $player1_jutsu, Jutsu $player2_jutsu
    ) {
        $collision_text = '';

        // Elemental interactions
        if(!empty($player1_jutsu->element) && !empty($player2_jutsu->element)) {
            $player1_jutsu->element = strtolower($player1_jutsu->element);
            $player2_jutsu->element = strtolower($player2_jutsu->element);

            // Fire > Wind > Lightning > Earth > Water > Fire
            if($player1_jutsu->element == 'fire') {
                if($player2_jutsu->element == 'wind') {
                    $player2_damage *= 0.8;
                }
                else if($player2_jutsu->element == 'water') {
                    $player1_damage *= 0.8;
                }
            }
            else if($player1_jutsu->element == 'wind') {
                if($player2_jutsu->element == 'lightning') {
                    $player2_damage *= 0.8;
                }
                else if($player2_jutsu->element == 'fire') {
                    $player1_damage *= 0.8;
                }
            }
            else if($player1_jutsu->element == 'lightning') {
                if($player2_jutsu->element == 'earth') {
                    $player2_damage *= 0.8;
                }
                else if($player2_jutsu->element == 'wind') {
                    $player1_damage *= 0.8;
                }
            }
            else if($player1_jutsu->element == 'earth') {
                if($player2_jutsu->element == 'water') {
                    $player2_damage *= 0.8;
                }
                else if($player2_jutsu->element == 'lightning') {
                    $player1_damage *= 0.8;
                }
            }
            else if($player1_jutsu->element == 'water') {
                if($player2_jutsu->element == 'fire') {
                    $player2_damage *= 0.8;
                }
                else if($player2_jutsu->element == 'earth') {
                    $player1_damage *= 0.8;
                }
            }
        }

        // Apply barrier
        $player1_jutsu_is_attack = in_array($player1_jutsu->use_type, Jutsu::$attacking_use_types);
        $player2_jutsu_is_attack = in_array($player2_jutsu->use_type, Jutsu::$attacking_use_types);

        // Barriers
        if($player1->barrier && $player2_jutsu_is_attack && $player2_jutsu->jutsu_type !== Jutsu::TYPE_GENJUTSU) {
            // Block damage from opponent's attack
            if($player1->barrier >= $player2_damage) {
                $block_amount = $player2_damage;
            }
            else {
                $block_amount = $player1->barrier;
            }

            $block_percent = ($player2_damage >= 1) ? ($block_amount / $player2_damage) * 100 : 100;
            $player1->barrier -= $block_amount;
            $player2_damage -= $block_amount;

            if($player1->barrier < 0) {
                $player1->barrier = 0;
            }
            if($player2_damage < 0) {
                $player2_damage = 0;
            }

            // Set display
            $block_percent = round($block_percent, 1);
            $collision_text .= "[player]'s barrier blocked $block_percent% of [opponent]'s damage![br]";
        }
        if($player2->barrier && $player1_jutsu_is_attack && $player1_jutsu->jutsu_type !== Jutsu::TYPE_GENJUTSU) {
            // Block damage from opponent's attack
            if($player2->barrier >= $player1_damage) {
                $block_amount = $player1_damage;
            }
            else {
                $block_amount = $player2->barrier;
            }

            $block_percent = ($player1_damage >= 1) ? ($block_amount / $player1_damage) * 100 : 100;
            $player2->barrier -= $block_amount;
            $player1_damage -= $block_amount;

            if($player2->barrier < 0) {
                $player2->barrier = 0;
            }
            if($player1_damage < 0) {
                $player1_damage = 0;
            }

            // Set display
            $block_percent = round($block_percent, 1);
            $collision_text .= "[opponent]'s barrier blocked $block_percent% of [player]'s damage![br]";
        }

        /* Calculate speed values */
        if($this->system->debug['jutsu_collision']) {
            echo "Player1({$player1->getName()}): {$player1->speed} ({$player1->speed_boost} - {$player1->speed_nerf})<br />";
            echo "Player2({$player2->getName()}): {$player2->speed} ({$player2->speed_boost} - {$player2->speed_nerf})<br />";
        }

        // Player diffuse opponent
        if($player1_jutsu->weapon_id
            && $player1_jutsu->weapon_effect->effect == 'diffuse'
            && $player2_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU
            && $player2_jutsu_is_attack
            && $player2_damage > 0
        ) {
            $player_diffuse_percent = round($player1_jutsu->weapon_effect->effect_amount / 100, 2);

            if($player_diffuse_percent > Battle::MAX_DIFFUSE_PERCENT) {
                $player_diffuse_percent = Battle::MAX_DIFFUSE_PERCENT;
            }

            if($player_diffuse_percent > 0) {
                $player2_damage *= 1 - $player_diffuse_percent;
                $collision_text .= "[player] diffused " . ($player_diffuse_percent * 100) . "% of [opponent]'s damage![br]";
            }
        }

        // Opponent diffuse player
        if($player2_jutsu->weapon_id
            && $player2_jutsu->weapon_effect->effect == 'diffuse'
            && $player1_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU
            && $player1_jutsu_is_attack
            && $player1_damage > 0
        ) {
            $opponent_diffuse_percent = round($player2_jutsu->weapon_effect->effect_amount / 100, 2);

            if($opponent_diffuse_percent > Battle::MAX_DIFFUSE_PERCENT) {
                $opponent_diffuse_percent = Battle::MAX_DIFFUSE_PERCENT;
            }

            if($opponent_diffuse_percent > 0) {
                $player1_damage *= 1 - $opponent_diffuse_percent;
                $collision_text .= "[opponent] diffused " . ($opponent_diffuse_percent * 100) . "% of [player]'s damage![br]";
            }
        }

        $player1_evasion_stat_amount = $this->getEvasionStatAmount($player1, $player1_jutsu);
        $player2_evasion_stat_amount = $this->getEvasionStatAmount($player2, $player2_jutsu);

        if($player1_evasion_stat_amount >= $player2_evasion_stat_amount && $player2_jutsu_is_attack) {
            $damage_reduction = ($player1_evasion_stat_amount / $player2_evasion_stat_amount) - 1.0;

            $damage_reduction = $player1_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU
                ? round($damage_reduction * self::SPEED_DAMAGE_REDUCTION_RATIO, 2)
                : round($damage_reduction * self::CAST_SPEED_DAMAGE_REDUCTION_RATIO, 2);

            if($damage_reduction > self::MAX_EVASION_DAMAGE_REDUCTION) {
                $damage_reduction = self::MAX_EVASION_DAMAGE_REDUCTION;
            }
            if($damage_reduction >= 0.01) {
                $player2_damage *= 1 - $damage_reduction;

                if($player1_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                    $collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";
                }
                else {
                    $collision_text .= "[player] cast [gender2] jutsu before [opponent] cast, negating " .
                        ($damage_reduction * 100) . "% of [opponent]'s damage!";
                }
            }
        }
        else if($player2_evasion_stat_amount >= $player1_evasion_stat_amount && $player1_jutsu_is_attack) {
            $damage_reduction = ($player2_evasion_stat_amount / $player1_evasion_stat_amount) - 1.0;

            $damage_reduction = $player2_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU
                ? round($damage_reduction * self::SPEED_DAMAGE_REDUCTION_RATIO, 2)
                : round($damage_reduction * self::CAST_SPEED_DAMAGE_REDUCTION_RATIO, 2);

            if($damage_reduction > self::MAX_EVASION_DAMAGE_REDUCTION) {
                $damage_reduction = self::MAX_EVASION_DAMAGE_REDUCTION;
            }
            if($damage_reduction >= 0.01) {
                $player1_damage *= 1 - $damage_reduction;

                if($player2_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                    $collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
                }
                else {
                    $collision_text .= "[opponent] cast [gender2] jutsu before [player] cast, negating " .
                        ($damage_reduction * 100) . "% of [player]'s damage!";
                }
            }
        }

        return $this->parseCombatText($collision_text, $player1, $player2);
    }

    /**
     * @throws Exception
     */
    private function getEvasionStatAmount(Fighter $fighter, Jutsu $fighter_jutsu): float|int {
        switch($fighter_jutsu->jutsu_type) {
            case Jutsu::TYPE_TAIJUTSU:
                $evasion_stat_amount = $fighter->speed + $fighter->speed_boost - $fighter->speed_nerf;
                $evasion_stat_amount = 50 + ($evasion_stat_amount * 0.5);
                if($evasion_stat_amount <= 0) {
                    $evasion_stat_amount = 1;
                }
                break;
            case Jutsu::TYPE_GENJUTSU:
            case Jutsu::TYPE_NINJUTSU:
                $evasion_stat_amount = $fighter->cast_speed + $fighter->cast_speed_boost - $fighter->cast_speed_nerf;
                $evasion_stat_amount = 50 + ($evasion_stat_amount * 0.5);
                if($evasion_stat_amount <= 0) {
                    $evasion_stat_amount = 1;
                }
                break;
            default:
                throw new Exception("Invalid jutsu type!");
        }

        return $evasion_stat_amount;
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
            $jutsu = $this->getJutsuFromAttackAction($player, $action);

            if(isset($this->player_jutsu_used[$jutsu->combat_id])) {
                $this->battle->fighter_jutsu_used[$player->combat_id][$jutsu->combat_id]['count']++;
            }
            else {
                $this->player_jutsu_used[$jutsu->combat_id] = array();
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
            if (isset($FORM_DATA['submit_prep_action'])) {
                $item_id = $FORM_DATA['item_id'] ?? null;
                if ($item_id && $this->player->hasItem($item_id)) {
                    $item = $this->player->items[$item_id];

                    $max_health = $this->player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100);

                    if ($this->player->health >= $max_health) {
                        throw new Exception("You can't heal any further!");
                    }
                    if ($item->effect === 'heal') {
                        if (--$this->player->items[$item_id]->quantity === 0) {
                            unset($this->player->items[$item_id]);
                        }

                        $this->player->health += $item->effect_amount;
                        if ($this->player->health >= $max_health) {
                            $this->player->health = $max_health;
                        }

                        $this->player->updateData();
                        $this->player->updateInventory();
                        $this->battle->battle_text .= sprintf("%s used a %s and healed for %.2f[br]", $this->player->user_name, $item->name, $item->effect_amount);
                        $this->updateData();
                    }
                }
            }
        }
        catch(Exception $e) {
            $this->system->message($e->getMessage());
        }
    }

    /**
     * @param Fighter $npc
     * @throws Exception
     */
    protected function chooseAndSetNPCAttackAction(Fighter $npc) {
        if(!($npc instanceof NPC)) {
            throw new Exception("Calling chooseAndSetNPCAttackAction on non-NPC!");
        }

        $jutsu = $npc->chooseAttack();
        $jutsu->setCombatId($npc->combat_id);

        $this->battle->fighter_actions[$npc->combat_id] = new FighterAttackAction(
            fighter_id: $npc->combat_id,
            jutsu_id: $jutsu->id,
            jutsu_purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            weapon_id: null,
            // TODO: real AI targeting
            target: new AttackFighterIdTarget($this->player->combat_id)
        );
    }

    // PRIVATE UTILS - PLAYER ACTIONS

    protected function collectPlayerAction(array $FORM_DATA): ?FighterAction {
        if($this->battle->isAttackPhase() && !empty($FORM_DATA['attack'])) {
            try {
                $jutsu_type = $FORM_DATA['jutsu_type'];

                // Check for handseals if ninjutsu/genjutsu
                if($jutsu_type == Jutsu::TYPE_NINJUTSU or $jutsu_type == Jutsu::TYPE_GENJUTSU) {
                    if(!$FORM_DATA['hand_seals']) {
                        throw new Exception("Please enter hand seals!");
                    }

                    $player_jutsu = $this->getJutsuFromHandSeals($this->player, $FORM_DATA['hand_seals']);

                    // Layered genjutsu check
                    if($player_jutsu && $player_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU && !empty($player_jutsu->parent_jutsu)) {
                        $this->effects->assertParentGenjutsuActive($this->player, $player_jutsu);
                    }
                }

                // Check jutsu ID if taijutsu
                else if($jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                    $jutsu_id = (int)$FORM_DATA['jutsu_id'];

                    $player_jutsu = $this->getJutsuFromId($this->player, $jutsu_id);
                }
                // Check BL jutsu ID if bloodline jutsu
                else if($jutsu_type == 'bloodline_jutsu' && $this->player->bloodline_id) {
                    $jutsu_id = (int)$FORM_DATA['jutsu_id'];

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
                    throw new Exception("Cannot use that jutsu, it is on cooldown for " . $this->battle->jutsu_cooldowns[$player_jutsu->combat_id] . " more turns!");
                }

                if(!$this->player->useJutsu($player_jutsu)) {
                    throw new Exception($this->system->message);
                }

                // Check for weapon if non-BL taijutsu
                $weapon_id = 0;
                if($jutsu_type == Jutsu::TYPE_TAIJUTSU && !empty($FORM_DATA['weapon_id'])) {
                    $weapon_id = (int)$this->system->clean($FORM_DATA['weapon_id']);
                    if($weapon_id && $this->player->hasItem($weapon_id)) {
                        if(array_search($weapon_id, $this->player->equipped_weapon_ids) === false) {
                            $weapon_id = 0;
                        }
                    }
                    else {
                        $weapon_id = 0;
                    }
                }

                // Log jutsu used
                return new FighterAttackAction(
                    fighter_id: $this->player->combat_id,
                    jutsu_id: $player_jutsu->id,
                    jutsu_purchase_type: $player_jutsu->purchase_type,
                    weapon_id: $weapon_id,
                    // TODO: real targeting
                    target: new AttackFighterIdTarget($this->opponent->combat_id)
                );

            } catch (Exception $e) {
                $this->system->message($e->getMessage());
                return null;
            }
        }
        else if($this->battle->isMovementPhase() && !empty($FORM_DATA['submit_movement_action'])) {
            $this->renderDebug(self::DEBUG_PLAYER_ACTION, 'collecting movement action', $FORM_DATA['selected_tile'] ?? null);

            // Run player attack
            try {
                $target_tile = $FORM_DATA['selected_tile'] ?? null;
                if($target_tile == null) {
                    throw new Exception("Invalid tile!");
                }
                if(!$this->field->tileIsInBounds($target_tile)) {
                    throw new Exception("Invalid tile - out of bounds!");
                }

                return new FighterMovementAction(
                    fighter_id: $this->player->combat_id,
                    target_tile: $target_tile
                );
            } catch (Exception $e) {
                $this->system->message($e->getMessage());
                return null;
            }
        }

        return null;
    }

    private function getJutsuFromHandSeals(Fighter $fighter, string $hand_seals): ?Jutsu {
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

        $raw_seals = explode('-', $hand_seals);
        $seals = array();
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
        if($fighter_jutsu) {
            $fighter_jutsu->setCombatId($fighter->combat_id);
        }

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
     * @param Fighter             $fighter
     * @param FighterAttackAction $action
     * @return Jutsu
     * @throws Exception
     */
    private function getJutsuFromAttackAction(Fighter $fighter, FighterAttackAction $action): Jutsu {
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
            throw new Exception("Invalid jutsu purchase type {$action->jutsu_purchase_type} for fighter {$fighter->combat_id}");
        }

        if($jutsu == null) {
            $this->renderDebug(self::DEBUG_PLAYER_ACTION, "getJutsuFromAttackAction", print_r($action, true));
            throw new Exception("Invalid type {$action->jutsu_purchase_type} jutsu {$action->jutsu_id} for fighter {$fighter->getName()}");
        }

        return $jutsu;
    }

    private function renderDebug(string $category, string $label, string $content) {
        if(($this->debug[$category] ?? false) !== true) {
            return;
        }

        if($content == "") {
            $content = "[empty]";
        }

        if($this->is_api_request) {
            $this->system->debugMessage($label . ': ' . $content);
            return;
        }

        echo "<div style='background:#222;
                color:#e0e0e0;
                white-space:pre-wrap;
                padding: 5px 5px 5px 10px;
                margin: 10px;
                border: 1px solid #333;
                '
        >" .
            "<p style='font-weight:bold;margin-top:0;'>{$label}</p>" .
            $content .
        "</div>";
    }

}