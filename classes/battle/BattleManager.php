<?php

use DDTrace\Trace;

require_once __DIR__ . '/Battle.php';
// require_once __DIR__ . '/BattleField.php';
require_once __DIR__ . '/BattleEffectsManager.php';
require_once __DIR__ . '/BattleAttack.php';
require_once __DIR__ . '/LegacyFighterAction.php';

class BattleManager {
    const SPEED_DAMAGE_REDUCTION_RATIO = 0.47;
    const CAST_SPEED_DAMAGE_REDUCTION_RATIO = 0.47;
    const MAX_EVASION_DAMAGE_REDUCTION = 0.35;

    private System $system;

    private int $battle_id;

    private Battle $battle;

    public User $player;
    public Fighter $opponent;

    public bool $is_retreat = false;
    public int $turn_count = 0;

    public string $player_side;
    public string $opponent_side;

    public array $player_jutsu_used = [];

    // Components
    // private BattleField $field;
    private BattleEffectsManager $effects;

    /** @var Jutsu[] */
    public array $default_attacks;

    public bool $spectate = false;

    // INITIALIZATION

    /**
     * BattleManager constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @param bool   $spectate
     * @param bool   $load_fighters
     * @throws RuntimeException
     */
    #[Trace]
    public function __construct(System $system, User $player, int $battle_id, bool $spectate = false, bool $load_fighters = true) {
        $this->system = $system;
        $this->battle_id = $battle_id;
        $this->player = $player;
        $this->spectate = $spectate;
        $this->battle = new Battle($system, $player, $battle_id);
        $this->is_retreat = $this->battle->is_retreat;
        $this->turn_count = $this->battle->turn_count;

        $this->default_attacks = $this->getDefaultAttacks();

        // $this->field = new BattleField($system, json_decode($this->battle->raw_field, true));

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
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @param bool   $spectate
     * @param bool   $load_fighters
     * @return BattleManager
     * @throws RuntimeException
     */
    #[Trace]
    public static function init(System $system, User $player, int $battle_id, bool $spectate = false, bool $load_fighters = true): BattleManager {
        return new BattleManager($system, $player, $battle_id, $spectate, $load_fighters);
    }

    /**
     * @throws RuntimeException
     */
    #[Trace]
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
        $result = $this->system->db->query($query);
        while($row = $this->system->db->fetch($result)) {
            $default_attacks[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
        }
        return $default_attacks;
    }


    // PUBLIC MUTATION API

    /**
     * @return string|null
     * @throws RuntimeException
     */
    #[Trace]
    public function checkInputAndRunTurn(): ?string {
        // If someone is not in battle, this will be set
        if($this->battle->winner) {
            return $this->battle->winner;
        }
        if($this->spectate) {
            return $this->battle->winner;
        }

        if($this->battle->isPreparationPhase()) {
            try {
                if (isset($_POST['attack'])) {
                    $item_id = $_POST['item_id'] ?? null;
                    if ($item_id && $this->player->itemQuantity($item_id) > 0) {
                        $item = $this->player->items[$item_id];

                        $max_health = $this->player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100);

                        if ($this->player->health >= $max_health) {
                            throw new RuntimeException("You can't heal any further!");
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
                if (isset($_POST['retreat'])) {
                    $this->battle->is_retreat = true;
                    $this->is_retreat = true;
                    if ($this->player->id == $this->battle->player1_id) {
                        $this->battle->winner = Battle::TEAM2;
                    } else if ($this->player->id == $this->battle->player2_id) {
                        $this->battle->winner = Battle::TEAM1;
                    }
                    $this->updateData();
                }
            }
            catch(RuntimeException $e) {
                $this->system->message($e->getMessage());
            }
            return false;
        }
        // If turn is still active and user hasn't submitted their move, check for action
        if($this->battle->timeRemaining($this->player->id) > 0 && !$this->playerActionSubmitted()) {
            if(!empty($_POST['attack'])) {
                // Run player attack
                try {
                    $jutsu_type = $_POST['jutsu_type'];

                    // Check for handseals if ninjutsu/genjutsu
                    if($jutsu_type == Jutsu::TYPE_NINJUTSU or $jutsu_type == Jutsu::TYPE_GENJUTSU) {
                        if(!$_POST['hand_seals']) {
                            throw new RuntimeException("Please enter hand seals!");
                        }

                        $player_jutsu = $this->getJutsuFromHandSeals($this->player, $_POST['hand_seals']);

                        // Layered genjutsu check
                        /*if($player_jutsu && $player_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU && !empty($player_jutsu->parent_jutsu)) {
                            $this->effects->assertParentGenjutsuActive($this->player, $player_jutsu);
                        }*/
                    }

                    // Check jutsu ID if taijutsu
                    else if($jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                        $jutsu_id = (int)$_POST['jutsu_id'];

                        $player_jutsu = $this->getJutsuFromId($this->player, $jutsu_id);
                    }
                    // Check BL jutsu ID if bloodline jutsu
                    else if($jutsu_type == 'bloodline_jutsu' && $this->player->bloodline_id) {
                        $jutsu_id = (int)$_POST['jutsu_id'];

                        $player_jutsu = null;
                        if(isset($this->player->bloodline->jutsu[$jutsu_id])) {
                            $player_jutsu = $this->player->bloodline->jutsu[$jutsu_id];
                            $player_jutsu->setCombatId($this->player->combat_id);
                        }
                    }
                    else {
                        throw new RuntimeException("Invalid jutsu selection!");
                    }

                    // Check jutsu cooldown
                    if(!$player_jutsu) {
                        throw new RuntimeException("Invalid jutsu!");
                    }
                    if(isset($this->battle->jutsu_cooldowns[$player_jutsu->combat_id])) {
                        throw new RuntimeException("Cannot use that jutsu, it is on cooldown for " . $this->battle->jutsu_cooldowns[$player_jutsu->combat_id] . " more turns!");
                    }

                    $result = $this->player->useJutsu($player_jutsu);
                    if($result->failed) {
                        throw new RuntimeException($result->error_message);
                    }

                    // Check for weapon if non-BL taijutsu
                    $weapon_id = 0;
                    $weapon_element = Jutsu::ELEMENT_NONE;
                    if($jutsu_type == Jutsu::TYPE_TAIJUTSU && !empty($_POST['weapon_id'])) {
                        $weapon_id = (int)$this->system->db->clean($_POST['weapon_id']);
                        if($weapon_id && $this->player->hasItem($weapon_id)) {
                            if(!in_array($weapon_id, $this->player->equipped_weapon_ids)) {
                                $weapon_id = 0;
                            }
                        }
                        else {
                            $weapon_id = 0;
                        }

                        $weapon_element = $this->system->db->clean($_POST['weapon_element'] ?? "None");
                        if(!in_array($weapon_element, $this->player->elements)) {
                            $weapon_element = Jutsu::ELEMENT_NONE;
                        }
                    }

                    // Log jutsu used
                    $this->setPlayerAction($this->player, $player_jutsu, $weapon_id, $weapon_element);

                    //update player turn time
                    $this->battle->updatePlayerTime($this->player->id);

                    if($this->opponent instanceof NPC) {
                        $this->chooseAndSetAIAction($this->opponent);
                    }
                } catch (Exception $e) {
                    $this->system->message($e->getMessage());
                }
            }
            else if(!empty($_POST['forfeit'])) {
                $this->player->health = 0;
            }
        }

        // If time is up or both people have submitted moves, RUN TURN
        $player1_submitted = isset($this->battle->fighter_actions[$this->battle->player1->combat_id]);
        $player2_submitted = isset($this->battle->fighter_actions[$this->battle->player2->combat_id]);
        if(($this->battle->timeRemaining($this->battle->player1_id) <= 0 || $player1_submitted) && ($this->battle->timeRemaining($this->battle->player2_id) <= 0 || $player2_submitted)) {
            if(!empty($this->battle->fighter_actions)) {
                $this->runActions();
                // if a player did not submit action, update timer
                if (!$player1_submitted) {
                    $this->battle->updatePlayerTime($this->battle->player1_id, min: true);
                }
                if (!$player2_submitted) {
                    $this->battle->updatePlayerTime($this->battle->player2_id, min: true);
                }
            }
            // If neither player moved, update turn timer only
            else {
                $this->battle->turn_time = time();
                $this->battle->updatePlayerTime($this->battle->player1_id, min: true);
                $this->battle->updatePlayerTime($this->battle->player2_id, min: true);
            }
        }

        $this->checkForWinner();
        $this->updateData();

        return $this->battle->winner;
    }


    // PUBLIC VIEW API

    /**
     * @throws RuntimeException
     */
    public function renderBattle(): void {
        global $self_link;

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

        $refresh_link = $this->spectate ? "{$self_link}&battle_id={$this->battle->battle_id}" : $self_link;

        $spectate = $this->spectate;
        $battleManager = $this;
        $battle = $this->battle;
        $system = $this->system;

        require 'templates/battle/battle_interface.php';
    }

    public function isComplete(): bool {
        $complete = $this->battle->isComplete();
        // TEMP FIX
        if ($complete) {
            $_SESSION['ai_logic']['special_move_used'] = false;
        }
        return $complete;
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
     * @throws RuntimeException
     */
    public function isPlayerWinner(): bool {
        if(!$this->isComplete()) {
            throw new RuntimeException("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->battle->winner === $this->player_side;
    }

    /**
     * @throws RuntimeException
     */
    public function isOpponentWinner(): bool {
        if(!$this->isComplete()) {
            throw new RuntimeException("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->battle->winner === $this->opponent_side;
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function isDraw(): bool {
        if(!$this->isComplete()) {
            throw new RuntimeException("Cannot call isDraw() check before battle is complete!");
        }

        return $this->battle->winner === Battle::DRAW;
    }

    public function getBattleType(): int {
        return $this->battle->battle_type;
    }


    // PRIVATE API - VIEW HELPERS

    private function parseCombatText(string $text, Fighter $attacker, Fighter $target): string {
        return str_replace(
            [
                '[player]',
                '[opponent]',
                '[gender]',
                '[gender2]',
                '[targetGender]',
                '[targetGender2]'
            ],
            [
                $attacker->getName(),
                $target->getName(),
                $attacker->getSingularPronoun(),
                $attacker->getPossessivePronoun(),
                $target->getSingularPronoun(),
                $target->getPossessivePronoun(),
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
     * @throws RuntimeException
     */
    #[Trace]
    protected function runActions(): void {
        $this->processTurnEffects();

        $this->battle->battle_text = '';

        /** @var ?BattleAttack $player1_attack */
        $player1_attack = null;
        /** @var ?BattleAttack $player2_attack */
        $player2_attack = null;

        if(isset($this->battle->fighter_actions[$this->battle->player1->combat_id])) {
            $player1_attack = $this->setupFighterAttack(
                $this->battle->player1,
                $this->battle->fighter_actions[$this->battle->player1->combat_id]
            );
        }
        if(isset($this->battle->fighter_actions[$this->battle->player2->combat_id])) {
            $player2_attack = $this->setupFighterAttack(
                $this->battle->player2,
                $this->battle->fighter_actions[$this->battle->player2->combat_id]
            );
        }

        if($this->system->debug['battle']) {
            echo 'P1: ' . $player1_attack->raw_damage . ' / P2: ' . $player2_attack->raw_damage . '<br />';
        }

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
            $this->battle->battle_text .= '[br][hr]' . $this->system->db->clean($collision_text);
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

        // Update battle
        $this->finishTurn();
    }

    #[Trace]
    private function finishTurn() {
        $this->battle->turn_time = time();
        $this->battle->turn_count++;
        $this->turn_count = $this->battle->turn_count;

        $this->battle->fighter_actions = [];

        $this->battle->fighter_health[$this->battle->player1->combat_id] = $this->battle->player1->health;
        $this->battle->fighter_health[$this->battle->player2->combat_id] = $this->battle->player2->health;

        $this->battle->player1->updateData();
        $this->battle->player1->updateInventory();

        $this->battle->player2->updateData();
        $this->battle->player2->updateInventory();
    }

    #[Trace]
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
     * @throws RuntimeException
     */
    #[Trace]
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

    #[Trace]
    protected function stopBattle() {
        $this->battle->winner = Battle::STOP;
        $this->updateData();
    }

    #[Trace]
    public function updateData() {
        if($this->spectate) {
            return;
        }

        $this->battle->raw_active_effects = json_encode($this->effects->active_effects);
        $this->battle->raw_active_genjutsu = json_encode($this->effects->active_genjutsu);
        // $this->battle->raw_field = json_encode($this->field->exportToDb())

        $this->battle->updateData();
    }

    // PRIVATE API - ATTACK PROCESSING

    /**
     * @param Fighter       $fighter
     * @param LegacyFighterAction $action
     * @return BattleAttack
     * @throws RuntimeException
     */
    #[Trace]
    protected function setupFighterAttack(Fighter $fighter, LegacyFighterAction $action): BattleAttack {
        $attack = new BattleAttack();
        if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
            $attack->jutsu = $this->default_attacks[$action->jutsu_id];
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_PURCHASABLE) {
            $attack->jutsu = $fighter->jutsu[$action->jutsu_id];
        }
        else if ($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
            $attack->jutsu = $fighter->jutsu[$action->jutsu_id];
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
            $attack->jutsu = $fighter->bloodline->jutsu[$action->jutsu_id];
        }
        else {
            throw new RuntimeException("Invalid jutsu purchase type {$action->jutsu_purchase_type} for fighter {$fighter->combat_id}");
        }

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
                $attack->jutsu->element = $action->weapon_element;
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

    #[Trace]
    protected function applyAttack(BattleAttack $attack, Fighter $user, Fighter $target) {
        $attack_damage = $attack->raw_damage;
        if(empty($attack->jutsu->effect_only)) {
            $attack_damage = $target->calcDamageTaken($attack->raw_damage, $attack->jutsu->jutsu_type);
            $attack_damage_raw = $target->calcDamageTaken($attack->raw_damage, $attack->jutsu->jutsu_type, apply_resists : false);
            $damage_resisted = round($attack_damage_raw - $attack_damage, 2);

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
        $has_element = ($attack->jutsu->element != Jutsu::ELEMENT_NONE && $attack->jutsu->element != "none");
        $element_text = ' with ' . $attack->jutsu->element;

        if(empty($attack->jutsu->effect_only)) {
              if($damage_resisted > 0 ) {
                    $text .= "<p style=\"font-weight:bold;\">
                            {$user->getName()} deals
                                <span style=\"color:{$attack_jutsu_color}\">
                                    " . sprintf('%.2f', $attack_damage) . " damage
                                </span>
                                    to {$target->getName()}" . ($has_element ? $element_text : "") . ".
                                <span style=\"font-weight:bold;\">
                                    (resists
                                 <span style=\"color:{$attack_jutsu_color}\">
                                    " . sprintf('%.2f', $damage_resisted) . "
                                </span>
                                 damage)
                            </p>"; }
                else {
                        $text .= "<p style=\"font-weight:bold;\">
                            {$user->getName()} deals
                                <span style=\"color:{$attack_jutsu_color}\">
                                    " . sprintf('%.2f', $attack_damage) . " damage
                                </span>
                                    to {$target->getName()}" . ($has_element ? $element_text : "") . ".
                                </p>"; }
                    }

        if($this->effects->hasDisplays($user)) {
            $text .= '<p>' . $this->effects->getDisplayText($user) . '</p>';
        }

        if($attack->jutsu->hasEffect()){
            $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                $this->system->db->clean($this->effects->getAnnouncementText($attack->jutsu->effect)) .
                "</p>";
        }


        if($attack->jutsu->weapon_id) {
            $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                $this->system->db->clean($this->effects->getAnnouncementText($attack->jutsu->weapon_effect->effect)) .
                "</p>";
        }

        $this->battle->battle_text .= $this->parseCombatText($text, $user, $target);


    }

    /**
     * @throws RuntimeException
     */
    #[Trace]
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
                    $collision_text .= "[opponent] cast [targetGender2] jutsu before [player] cast, negating " .
                        ($damage_reduction * 100) . "% of [player]'s damage!";
                }
            }
        }

        return $this->parseCombatText($collision_text, $player1, $player2);
    }

    /**
     * @throws RuntimeException
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
                throw new RuntimeException("Invalid jutsu type!");
        }

        return $evasion_stat_amount;
    }

    // PRIVATE API - PLAYER ACTIONS

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

    #[Trace]
    protected function setPlayerAction(Fighter $player, Jutsu $jutsu, int $weapon_id, string $weapon_element) {
        $this->battle->fighter_actions[$player->combat_id] = new LegacyFighterAction(
            $jutsu->id,
            $jutsu->purchase_type,
            $weapon_id,
            $weapon_element
        );

        if(isset($this->player_jutsu_used[$jutsu->combat_id])) {
            $this->battle->fighter_jutsu_used[$player->combat_id][$jutsu->combat_id]['count']++;
        }
        else {
            $this->player_jutsu_used[$jutsu->combat_id] = array();
            $this->player_jutsu_used[$jutsu->combat_id]['jutsu_type'] = $jutsu->jutsu_type;
            $this->player_jutsu_used[$jutsu->combat_id]['count'] = 1;
        }
    }

    /**
     * @param Fighter $ai
     * @throws RuntimeException
     */
    #[Trace]
    protected function chooseAndSetAIAction(Fighter $ai) {
        if(!($ai instanceof NPC)) {
            throw new RuntimeException("Calling chooseAndSetAIAction on non-AI!");
        }

        $jutsu = $ai->chooseAttack();

        $this->battle->fighter_actions[$ai->combat_id] = new LegacyFighterAction(
            $jutsu->id,
            Jutsu::PURCHASE_TYPE_PURCHASABLE,
            0,
            Jutsu::ELEMENT_NONE
        );
    }
}