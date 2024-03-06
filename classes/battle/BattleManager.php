<?php /** @noinspection PhpUnnecessaryLocalVariableInspection */

use DDTrace\Trace;

require_once __DIR__ . '/Battle.php';
// require_once __DIR__ . '/BattleField.php';
require_once __DIR__ . '/BattleEffectsManager.php';
require_once __DIR__ . '/BattleAttack.php';
require_once __DIR__ . '/LegacyFighterAction.php';

class BattleManager {
    const SPEED_DAMAGE_REDUCTION_RATIO = 1; // e.g. 10% of your stats in speed = 10% evasion
    const CAST_SPEED_DAMAGE_REDUCTION_RATIO = 1; // e.g. 10% of your stats in speed = 10% evasion
    const MAX_EVASION_DAMAGE_REDUCTION = 0.5; // LEGACY
    const EVASION_SOFT_CAP = 0.35; // caps at 50% evasion
    const EVASION_SOFT_CAP_RATIO = 0.65; // evasion beyond soft cap only 65% as effective
    const EVASION_HARD_CAP = 0.65; // caps at 75% evasion

    const RESIST_DIMINISHING_RETURN_SCALE = 0.3;

    const RESIST_SOFT_CAP = 0.3; // caps at 35% resist
    const RESIST_SOFT_CAP_RATIO = 0.6; // resist beyond soft cap only 65% as effective
    const RESIST_HARD_CAP = 0.65; // caps at 65% resist

    const OFFENSE_NERF_SOFT_CAP = 0.35; // caps at 35% reduced damage
    const OFFENSE_NERF_SOFT_CAP_RATIO = 0.65; // nerf beyond soft cap only 65% as effective
    const OFFENSE_NERF_HARD_CAP = 0.65; // caps at 65% reduced damage

    const HEAL_SOFT_CAP = 0.35; // caps at 35% previous turn damage heal
    const HEAL_SOFT_CAP_RATIO = 0.65; // heal beyond soft cap only 65% as effective
    const HEAL_HARD_CAP = 0.65; // caps at 65% previous turn damage heal

    const GENJUTSU_BARRIER_PENALTY = 37.5; // 37.5% reduction against Genjutsu (62.5% strength)

    const ELEMENTAL_CLASH_MODIFIER = 0.15; // 15% damage loss and gain
    const ELEMENTAL_CLASH_EFFECT_MODIFIER = 0.1; // 10% effect loss and gain

    // Extra boost to recoil damage to offset the typical losses when facing a high-speed build
    const RECOIL_DMG_MULTIPLIER = 1.05;

    private System $system;

    private int $battle_id;

    protected Battle $battle;

    public User $player;
    public Fighter $opponent;

    public bool $is_retreat = false;
    public int $turn_count = 0;

    public string $player_side;
    public string $opponent_side;

    public array $player_jutsu_used = [];

    // Components
    // private BattleField $field;
    protected BattleEffectsManager $effects;

    public bool $spectate = false;

    // track AI jutsu
    public ?Jutsu $ai_jutsu_used = null;

    // INITIALIZATION

    /**
     * BattleManager constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @param bool   $spectate
     * @param bool   $load_fighters
     * @throws RuntimeException
     * @throws DatabaseDeadlockException
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

        // $this->field = new BattleField($system, json_decode($this->battle->raw_field, true));

        $this->effects = new BattleEffectsManager(
            $system,
            json_decode($this->battle->raw_active_effects, true),
            json_decode($this->battle->raw_active_genjutsu, true)
        );

        if($load_fighters) {
            $this->loadFighters();

            $this->effects->applyPassiveEffects(
                player1: $this->battle->player1,
                player2: $this->battle->player2,
                battle_type: $this->battle->battle_type
            );
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
    public static function init(
        System $system, User $player, int $battle_id, bool $spectate = false, bool $load_fighters = true
    ): BattleManager {
        return new BattleManager($system, $player, $battle_id, $spectate, $load_fighters);
    }

    /**
     * @throws RuntimeException
     * @throws DatabaseDeadlockException
     */
    #[Trace]
    protected function loadFighters(): void {
        $default_attacks = $this->getDefaultAttacks();

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

        foreach($default_attacks as $id => $attack) {
            // Have to work on a copy so the combat ID doesn't get polluted by both players
            $this->battle->player1->default_jutsu[$id] = clone $attack;
            $this->battle->player1->default_jutsu[$id]->setCombatId($this->battle->player1->combat_id);

            $this->battle->player2->default_jutsu[$id] = clone $attack;
            $this->battle->player2->default_jutsu[$id]->setCombatId($this->battle->player2->combat_id);
        }

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

    /**
     * @throws DatabaseDeadlockException
     */
    protected function getDefaultAttacks(): array {
        $default_attacks = [];

        $query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
        $result = $this->system->db->query($query);
        while($row = $this->system->db->fetch($result)) {
            $default_attacks[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
            $default_attacks[$row['jutsu_id']]->setLevel(100, 0);
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

                        $max_health = $this->player->maxConsumableHealAmount();

                        if ($this->player->health >= $max_health) {
                            throw new RuntimeException("You can't heal any further!");
                        }
                        if ($item->effect === 'heal') {
                            if (--$this->player->items[$item_id]->quantity === 0) {
                                unset($this->player->items[$item_id]);
                            }

                            $this->player->health += ($item->effect_amount / 100) * $this->player->max_health;
                            if ($this->player->health >= $max_health) {
                                $this->player->health = $max_health;
                            }

                            $this->player->updateData();
                            $this->player->updateInventory();
                            $this->battle->battle_text .= sprintf("%s used a %s and healed for %.0f%% HP[br]", $this->player->user_name, $item->name, $item->effect_amount);
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
                    if($jutsu_type == JutsuOffenseType::NINJUTSU->value or $jutsu_type == JutsuOffenseType::GENJUTSU->value) {
                        if(!$_POST['hand_seals']) {
                            throw new RuntimeException("Please enter hand seals!");
                        }

                        $player_jutsu = $this->getJutsuFromHandSeals($this->player, $_POST['hand_seals']);

                        // Layered genjutsu check
                        /*if($player_jutsu && $player_jutsu->jutsu_type == JutsuOffenseType::GENJUTSU && !empty($player_jutsu->parent_jutsu)) {
                            $this->effects->assertParentGenjutsuActive($this->player, $player_jutsu);
                        }*/
                    }

                    // Check jutsu ID if taijutsu
                    else if($jutsu_type == JutsuOffenseType::TAIJUTSU->value) {
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

                    // Log jutsu used
                    $this->setPlayerAction($this->player, $player_jutsu);

                    //update player turn time
                    $this->battle->updatePlayerTime($this->player->id);

                    if($this->opponent instanceof NPC) {
                        $this->chooseAndSetAIAction($this->opponent);
                    }
                } catch (RuntimeException $e) {
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

        $this->updateData();
        $this->checkForWinner();
        $this->battle->fetchPlayerInventories();

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

    public function getPatrolId(): int {
        return $this->battle->patrol_id;
    }

    /**
     * @return BattleEffect[]
     */
    public function getEffects(): array {
        return $this->effects->active_effects;
    }

    public function simulateAIAttack(Jutsu $ai_jutsu): array {
        // clone battlers for simulation
        $simulated_player = clone $this->battle->player1;
        $simulated_ai = clone $this->battle->player2;

        // setup attacks
        $player_simulated_attack = $this->setupFighterAttack(
            $simulated_player,
            $simulated_ai,
            $this->battle->fighter_actions[$simulated_player->combat_id],
            simulation: true
        );
        $ai_simulated_action = new LegacyFighterAction($ai_jutsu->id, Jutsu::PURCHASE_TYPE_DEFAULT, null, null);
        $ai_simulated_attack = $this->setupFighterAttack(
            $simulated_ai,
            $simulated_player,
            $ai_simulated_action,
            jutsu: $ai_jutsu,
            simulation: true
        );

        // run jutsu collision
        $this->jutsuCollision(
            player1: $simulated_player,
            player2: $simulated_ai,
            player1_attack: $player_simulated_attack,
            player2_attack: $ai_simulated_attack
        );

        // apply attacks
        $simulated_player->last_damage_taken = 0;
        $simulated_ai->last_damage_taken = 0;
        $this->simulateApplyAttack($player_simulated_attack, $simulated_player, $simulated_ai);
        $this->simulateApplyAttack($ai_simulated_attack, $simulated_ai, $simulated_player);

        // return results for AI logic
        // we can send a lot more in this array if necessary
        $return_arr = [
            'ai_simulated_damage_taken' => $simulated_ai->last_damage_taken,
            'player_simulated_damage_taken' => $simulated_player->last_damage_taken,
        ];
        return $return_arr;
    }

    public function getCooldowns(): array {
        return $this->battle->jutsu_cooldowns;
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

    public static function getJutsuTextColor($jutsu_type): string {
        switch ($jutsu_type) {
            case JutsuOffenseType::NINJUTSU:
                return "blue";
            case JutsuOffenseType::TAIJUTSU:
                return "red";
            case JutsuOffenseType::GENJUTSU:
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
        /** @var ?BattleAttack $player1_attack */
        $player1_attack = null;
        /** @var ?BattleAttack $player2_attack */
        $player2_attack = null;

        if (isset($this->battle->fighter_actions[$this->battle->player1->combat_id])) {
            $player1_attack = $this->setupFighterAttack(
                $this->battle->player1,
                $this->battle->player2,
                $this->battle->fighter_actions[$this->battle->player1->combat_id]
            );
        }
        if (isset($this->battle->fighter_actions[$this->battle->player2->combat_id])) {
            $player2_attack = $this->setupFighterAttack(
                $this->battle->player2,
                $this->battle->player1,
                $this->battle->fighter_actions[$this->battle->player2->combat_id],
                $this->ai_jutsu_used
            );
        }

        $this->processTurnEffects();

        $this->battle->battle_text = '';

        if($this->system->debug['battle']) {
            echo 'P1: ' . $player1_attack->raw_damage . ' / P2: ' . $player2_attack->raw_damage . '<br />';
        }

        // Collision
        $collision_text = null;
        if($player1_attack != null && $player2_attack != null) {
            $collision_text = $this->jutsuCollision(
                player1: $this->battle->player1,
                player2: $this->battle->player2,
                player1_attack: $player1_attack,
                player2_attack: $player2_attack
            );
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
        $this->battle->battle_text .= '[br][hr][br]';

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

        if ($this->battle->player1->health > $this->battle->player1->max_health) {
            $this->battle->player1->health = $this->battle->player1->max_health;
        }
        if ($this->battle->player2->health > $this->battle->player2->max_health) {
            $this->battle->player2->health = $this->battle->player2->max_health;
        }

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
            $this->battle->winner = $this->handleRoundCompletion(Battle::TEAM1);
            $this->updateData();
        }
        else if($this->battle->player2->health > 0 && $this->battle->player1->health <= 0) {
            $this->battle->winner = $this->handleRoundCompletion(Battle::TEAM2);
            $this->updateData();
        }
        else if($this->battle->player1->health <= 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = $this->handleRoundCompletion(Battle::DRAW);
            $this->updateData();
        }

        if($this->battle->winner && !$this->spectate) {
            $this->player->updateInventory();
        }

        return $this->battle->winner;
    }

    #[Trace]
    private function handleRoundCompletion(string $round_winner): string {
        // if round winner is team1, increment wins
        if ($round_winner == Battle::TEAM1) {
            $this->battle->team1_wins++;
        }
        // if round winner is team2, increment wins
        if ($round_winner == Battle::TEAM2) {
            $this->battle->team2_wins++;
        }
        // if single-round battle and round is draw
        if ($this->battle->rounds <= 1 && $round_winner == Battle::DRAW) {
            return Battle::DRAW;
        }
        // if team1 majority of wins (total rounds)
        if ($this->battle->team1_wins > floor($this->battle->rounds / 2) || ($this->battle->round_count > $this->battle->rounds && $this->battle->team1_wins > $this->battle->team2_wins)) {
            return Battle::TEAM1;
        }
        // if team2 majority of wins (total rounds)
        if ($this->battle->team2_wins > floor($this->battle->rounds / 2) || ($this->battle->round_count > $this->battle->rounds && $this->battle->team2_wins > $this->battle->team1_wins)) {
            return Battle::TEAM2;
        }
        // if more rounds to go
        if ($this->battle->round_count < $this->battle->rounds) {
            $this->battle->round_count++;
            $this->resetBattle();
            return '';
        }
        // if rounds completed but no winner
        if ($this->battle->team1_wins == $this->battle->team2_wins) {
            $this->battle->round_count++;
            $this->resetBattle();
            return '';
        }
        // return no winner as failsafe
        return '';
    }

    #[Trace]
    private function resetBattle() {
        $this->battle->player1->health = $this->battle->player1->max_health;
        $this->battle->fighter_health[$this->battle->player1->combat_id] = $this->battle->player1->max_health;
        $this->battle->player1_last_damage_taken = 0;
        $this->battle->player2->health = $this->battle->player2->max_health;
        $this->battle->fighter_health[$this->battle->player2->combat_id] = $this->battle->player2->max_health;
        $this->battle->player2_last_damage_taken = 0;
        $this->effects->active_effects = [];
        $this->battle->jutsu_cooldowns = [];
        $this->battle->turn_count = 0;
        $this->battle->turn_time = time();
        $this->battle->player1_time = Battle::MAX_TURN_LENGTH;
        $this->battle->player2_time = Battle::MAX_TURN_LENGTH;
        $this->battle->player1->updateData();
        $this->battle->player2->updateData();
        $this->battle->start_time = time();
    }

    /**
     * @throws RuntimeException
     */
    #[Trace]
    private function processTurnEffects() {
        // Run turn effects
        $this->effects->applyActiveEffects($this->battle->player1, $this->battle->player2);

        // Clear previous turn damage tracking
        $this->battle->player1->last_damage_taken = 0;
        $this->battle->player2->last_damage_taken = 0;

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
     * @param Fighter             $fighter
     * @param Fighter             $target
     * @param LegacyFighterAction $action
     * @param Jutsu|null          $jutsu
     * @param bool                $simulation
     * @return BattleAttack
     */
    #[Trace]
    public function setupFighterAttack(
        Fighter $fighter,
        Fighter $target,
        LegacyFighterAction $action,
        ?Jutsu $jutsu = null,
        bool $simulation = false
    ): BattleAttack {
        $disable_randomness = false;
        switch ($this->battle->battle_type) {
            case Battle::TYPE_AI_ARENA:
            case Battle::TYPE_AI_MISSION:
                $disable_randomness = true;
                break;
        }

        // if jutsu is already given, use instead
        if (!empty($jutsu)) {
            $attack_jutsu = $jutsu;
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
            print_r($fighter->default_jutsu);
            $attack_jutsu = $fighter->default_jutsu[$action->jutsu_id];
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_PURCHASABLE) {
            $attack_jutsu = $fighter->jutsu[$action->jutsu_id];
        }
        else if ($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
            $attack_jutsu = $fighter->jutsu[$action->jutsu_id];
        }
        else if($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
            $attack_jutsu = $fighter->bloodline->jutsu[$action->jutsu_id];
        }
        else if ($action->jutsu_purchase_type == Jutsu::PURCHASE_TYPE_LINKED) {
            $attack_jutsu = $fighter->jutsu[$action->jutsu_id];
        }
        else {
            throw new RuntimeException("Invalid jutsu purchase type {$action->jutsu_purchase_type} for fighter {$fighter->combat_id}");
        }

        $attack_jutsu->setCombatId($fighter->combat_id);

        $attack = new BattleAttack(
            jutsu: $attack_jutsu,
            raw_damage: $fighter->calcDamage(attack: $attack_jutsu, disable_randomness: $disable_randomness)
        );

        // Setup clash effects
        foreach ($attack->effects as $effect) {
            switch ($effect->effect) {
                case 'piercing':
                    $attack->piercing_percent += $effect->effect_amount / 100;
                    break;
                case 'substitution':
                    $attack->substitution_percent += $effect->effect_amount / 100;
                    break;
                case 'counter':
                    $attack->counter_percent += $effect->effect_amount / 100;
                    break;
                case 'recoil':
                    $recoil_percent = ($effect->effect_amount / 100);

                    $attack->recoil_percent = $recoil_percent;
                    $attack->recoil_raw_damage = $attack->raw_damage * $recoil_percent;

                    $attack->raw_damage *= 1 + ($recoil_percent * self::RECOIL_DMG_MULTIPLIER);
                    break;
                case 'immolate':
                    $attack->immolate_percent += $effect->effect_amount / 100;
                    $attack->immolate_raw_damage += $this->effects->processImmolate($attack, $target, $simulation);
                    break;
                case 'reflect':
                    $attack->reflect_percent += $effect->effect_amount / 100;
                    $attack->reflect_duration = max($effect->effect_length, 1);
                    break;
                default:
                    break;
            }
        }

        if($attack->use_type == Jutsu::USE_TYPE_BARRIER) {
            $fighter->barrier += $attack->raw_damage;
            $attack->raw_damage = 0;
        }

        $attack->damage = $attack->raw_damage;

        return $attack;
    }

    #[Trace]
    public function applyAttack(BattleAttack $attack, Fighter $user, Fighter $target): void {
        $attack_damage = $attack->damage;
        $counter_damage = 0;
        $counter_damage_raw = 0;
        $recoil_damage = 0;
        $recoil_damage_raw = 0;
        $immolate_damage = 0;
        $immolate_damage_raw = 0;

        if ($attack->countered_percent > 0) {
            $counter_damage = $user->calcDamageTaken($attack->countered_raw_damage, $attack->countered_jutsu_type);
            $counter_damage_raw = $user->calcDamageTaken(
                raw_damage: $attack->countered_raw_damage,
                defense_type: $attack->countered_jutsu_type,
                apply_resists: false
            );
            $counter_damage_resisted = round($counter_damage_raw - $counter_damage, 2);
            $user->last_damage_taken += $counter_damage;
            $user->health -= $counter_damage;
            if ($user->health < 0) {
                $user->health = 0;
            }
        }

        if ($attack->immolate_raw_damage > 0) {
            $immolate_damage = $target->calcDamageTaken($attack->immolate_raw_damage, $attack->jutsu_type);
            $immolate_damage_raw = $target->calcDamageTaken(
                raw_damage: $attack->immolate_raw_damage,
                defense_type: $attack->jutsu_type,
                apply_resists: false
            );
            $immolate_damage_resisted = round($immolate_damage_raw - $immolate_damage, 2);
            $target->last_damage_taken += $immolate_damage;
            $target->health -= $immolate_damage;
            if ($target->health < 0) {
                $target->health = 0;
            }
        }

        if (!$attack->effect_only) {
            $attack_damage = $target->calcDamageTaken(
                $attack->damage, $attack->jutsu_type, element: $attack->element, apply_resists: true
            );
            $attack_damage_raw = $target->calcDamageTaken(
                $attack->damage, $attack->jutsu_type, element: $attack->element, apply_resists: false
            );
            $damage_resisted = round($attack_damage_raw - $attack_damage, 2);

            $target->last_damage_taken += $attack_damage;
            $target->health -= $attack_damage;
            if($target->health < 0) {
                $target->health = 0;
            }

            if ($attack->recoil_raw_damage > 0) {
                $recoil_damage = $user->calcDamageTaken($attack->recoil_raw_damage, $attack->jutsu_type);
                $recoil_damage_raw = $user->calcDamageTaken(
                    $attack->recoil_raw_damage, $attack->jutsu_type, apply_resists: false
                );
                $recoil_damage_resisted = round($recoil_damage_raw - $recoil_damage, 2);
                $user->last_damage_taken += $recoil_damage;
                $user->health -= $recoil_damage;
                if ($user->health < 0) {
                    $user->health = 0;
                }
            }
        }

        // Set cooldowns
        if($attack->cooldown > 0) {
            echo "Set cooldown for {$user->getName()} jutsu {$attack->jutsuCombatId()}<br />";
            $this->battle->jutsu_cooldowns[$attack->jutsuCombatId()] = $attack->cooldown;
        }

        // Effects
        if($attack->hasEffect()) {
            foreach($attack->effects as $index => $effect) {
                if(in_array($effect->effect, BattleEffect::$buff_effects)) {
                    $target_id = $user->combat_id;
                }
                else {
                    $target_id = $target->combat_id;
                }

                $this->effects->setEffect(
                    effect_user: $user,
                    target_id: $target_id,
                    effect: $effect,
                    jutsu_combat_id: $attack->jutsuCombatId(),
                    effect_num: $index,
                    raw_damage: $attack->damage
                );
            }
        }

        $text = '';
        $attack_jutsu_color = BattleManager::getJutsuTextColor($attack->jutsu_type);
        if (!str_contains($attack->name, "Move ")) {
            if ($attack->element != Element::NONE) {
                $text .= "<b><span class=\"battle_text_{$attack->jutsu_type->value}\" style=\"color:{$attack_jutsu_color}\"><i>"
                    . System::unSlug($attack->element->value) . " Style: " . System::unSlug($attack->name) . '</i></span></b></br>';
            }
            else {
                $text .= "<b><span class=\"battle_text_{$attack->jutsu_type->value}\" style=\"color:{$attack_jutsu_color}\"><i>"
                    . System::unSlug($attack->name) . '</i></span></b></br>';
            }
        }
        $text .= $attack->battle_text;
        $has_element = ($attack->element != Element::NONE && $attack->element != "none");
        $element_text = ' with ' . $attack->element->value;

        if(empty($attack->effect_only)) {
            if($damage_resisted > 0) {
                $text .= "<p style=\"font-weight:bold;\">
                        {$user->getName()} deals
                            <span class=\"battle_text_{$attack->jutsu_type->value}\" style=\"color:{$attack_jutsu_color}\">
                                " . BattleManager::formatNumber($attack_damage) . " damage
                            </span>
                                to {$target->getName()}" . ($has_element ? $element_text : "") . ".
                            <span style=\"font-weight:bold;\">
                                (resists
                             <span class=\"battle_text_{$attack->jutsu_type->value}\" style=\"color:{$attack_jutsu_color}\">
                                " . BattleManager::formatNumber($damage_resisted) . "
                            </span>
                             damage)
                        </p>";
            }
            else {
                $text .= "<p style=\"font-weight:bold;\">
                {$user->getName()} deals
                    <span class=\"battle_text_{$attack->jutsu_type->value}\" style=\"color:{$attack_jutsu_color}\">
                        " . BattleManager::formatNumber($attack_damage) . " damage
                    </span>
                        to {$target->getName()}" . ($has_element ? $element_text : "") . ".
                    </p>";
            }
        }

        if ($attack->recoil_raw_damage > 0) {
            if ($recoil_damage_resisted > 0) {
                $text .= "<span>-" . $user->getName() . " takes <span class=\"battle_text_{$attack->jutsu_type->value}\">"
                    . BattleManager::formatNumber($recoil_damage) . "</span> recoil damage- (resists "
                    . "<span class=\"battle_text_{$attack->jutsu_type->value}\">" . BattleManager::formatNumber($recoil_damage_resisted) . "</span>"
                    . " recoil damage)" . '</span></br>';
            } else {
                $text .= "<span>-" . $user->getName() . " takes <span class=\"battle_text_{$attack->jutsu_type->value}\">"
                    . BattleManager::formatNumber($recoil_damage) . "</span> recoil damage-" . '</span></br>';
            }
        }
        if ($attack->countered_percent > 0) {
            if ($counter_damage_resisted > 0) {
                $text .= "<span>-" . $user->getName() . " takes <span class=\"battle_text_{$attack->countered_jutsu_type->value}\">"
                    . BattleManager::formatNumber($counter_damage) . "</span> counter damage- (resists "
                    . "<span class=\"battle_text_{$attack->countered_jutsu_type->value}\">" . BattleManager::formatNumber($counter_damage_resisted) . "</span>"
                    . " counter damage)" . '</span></br>';
            } else {
                $text .= "<span>-" . $user->getName() . " takes <span class=\"battle_text_{$attack->countered_jutsu_type->value}\">"
                    . BattleManager::formatNumber($counter_damage) . "</span> counter damage-" . '</span></br>';
            }
        }
        if ($attack->immolate_raw_damage > 0) {
            if ($immolate_damage_resisted > 0) {
                $text .= "<span>-" . $target->getName() . " takes <span class=\"battle_text_{$attack->jutsu_type->value}\">"
                    . BattleManager::formatNumber($immolate_damage)
                    . "</span> immolation damage- (resists "
                    . "<span class=\"battle_text_{$attack->jutsu_type->value}\">"
                    . BattleManager::formatNumber($immolate_damage_resisted)
                    . "</span>" . " immolation damage)"
                . '</span></br>';
            } else {
                $text .= "<span>-" . $target->getName() . " takes <span class=\"battle_text_{$attack->jutsu_type->value}\">"
                    . BattleManager::formatNumber($immolate_damage) . "</span> immolation damage-"
                . '</span></br>';
            }
        }

        // simulate damage of jutsu effect
        foreach ($attack->effects as $effect) {
            if (BattleEffectsManager::isDamageOverTime($effect)) {
                $effect_power = $effect->effect_amount * $effect->effect_length;
                $effect->potential_damage = $target->calcDamageTaken(
                    raw_damage: $effect_power,
                    defense_type: $attack->jutsu_type,
                    apply_resists: false,
                    apply_weakness: false
                );
            }
        }

        if($attack->hasEffect()){
            foreach ($attack->effects as $effect) {
                if ($effect && $effect->effect != 'none') {
                    $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                        $this->system->db->clean($this->effects->getAnnouncementText($effect, $attack->jutsu_type)) .
                        "</p>";
                }
            }
        }

        if($this->effects->hasDisplays($user)) {
            $text .= '<p>' . $this->effects->getDisplayText($user) . '</p>';
        }

        $this->battle->battle_text .= $this->parseCombatText($text, $user, $target);
    }

    public function simulateApplyAttack(BattleAttack $attack, Fighter $user, Fighter $target): void {
        // simulate counter damage
        if ($attack->countered_percent > 0) {
            $counter_damage = $user->calcDamageTaken($attack->countered_raw_damage, $attack->countered_jutsu_type);
            $user->last_damage_taken += $counter_damage;
            $user->health -= $counter_damage;
            if ($user->health < 0) {
                $user->health = 0;
            }
        }

        // simulate direct damage
        if (!$attack->effect_only) {
            $attack_damage = $target->calcDamageTaken($attack->damage, $attack->jutsu_type, element: $attack->element);
            $target->last_damage_taken += $attack_damage;
            $target->health -= $attack_damage;
            if ($target->health < 0) {
                $target->health = 0;
            }

            // simulate recoil damage
            if ($attack->recoil_raw_damage > 0) {
                $recoil_damage = $user->calcDamageTaken(
                    raw_damage: $attack->recoil_raw_damage,
                    defense_type: $attack->jutsu_type
                );
                $recoil_damage_pre_resist = $user->calcDamageTaken(
                    raw_damage: $attack->recoil_raw_damage,
                    defense_type: $attack->jutsu_type,
                    apply_resists: false,
                );

                $recoil_damage_resisted = round($recoil_damage_pre_resist - $recoil_damage, 2);
                $user->last_damage_taken += $recoil_damage;
                $user->health -= $recoil_damage;
                if ($user->health < 0) {
                    $user->health = 0;
                }
            }
        }

        // simulate reflect damage
        if ($attack->reflected_percent > 0) {
            $reflect_damage = $user->calcDamageTaken($attack->reflected_raw_damage, $attack->reflected_jutsu_type);
            $user->last_damage_taken += $reflect_damage;
            $user->health -= $reflect_damage;
            if ($user->health < 0) {
                $user->health = 0;
            }
        }

        // simulate immolate effects
        if ($attack->immolate_raw_damage > 0) {
            $immolate_damage = $target->calcDamageTaken($attack->immolate_raw_damage, $attack->jutsu_type);
            $immolate_damage_raw = $target->calcDamageTaken(
                $attack->immolate_raw_damage, $attack->jutsu_type, apply_resists: false
            );
            $immolate_damage_resisted = round($immolate_damage_raw - $immolate_damage, 2);
            $target->last_damage_taken += $immolate_damage;
            $target->health -= $immolate_damage;
            if ($target->health < 0) {
                $target->health = 0;
            }
        }

        // simulate residual effects
        foreach ($attack->effects as $effect) {
            if ($effect->effect == "residual_damage" || $effect->effect == "delayed_residual") {
                $effect_power = $attack->damage * ($effect->effect_amount / 100) * $effect->effect_length;
                $residual_damage = $target->calcDamageTaken($effect_power, $attack->jutsu_type);
                $target->last_damage_taken += $residual_damage;
                $target->health -= $residual_damage;
                if ($target->health < 0) {
                    $target->health = 0;
                }
            }
        }

        return;
    }

    /**
     * @throws RuntimeException
     */
    #[Trace]
    public function jutsuCollision(
        Fighter $player1, Fighter $player2, BattleAttack &$player1_attack, BattleAttack &$player2_attack
    ): string {
        $collision_displays = [];

        $p1_elemental_damage_modifier = $this->getElementalModifier(
            $player1_attack, $player2_attack,self::ELEMENTAL_CLASH_MODIFIER
        );
        $p1_elemental_effect_modifier = $this->getElementalModifier(
            $player1_attack, $player2_attack, self::ELEMENTAL_CLASH_EFFECT_MODIFIER
        );
        $player1->barrier *= $p1_elemental_damage_modifier;
        $player1_attack->applyElementalClash($p1_elemental_damage_modifier, $p1_elemental_effect_modifier);

        $p2_elemental_damage_modifier = $this->getElementalModifier(
            $player2_attack, $player1_attack, self::ELEMENTAL_CLASH_MODIFIER
        );
        $p2_elemental_effect_modifier = $this->getElementalModifier(
            $player2_attack, $player1_attack,self::ELEMENTAL_CLASH_EFFECT_MODIFIER
        );
        $player2->barrier *= $p2_elemental_damage_modifier;
        $player2_attack->applyElementalClash($p2_elemental_damage_modifier, $p2_elemental_effect_modifier);

        // Output piercing message
        /* if ($player1_attack->piercing_percent > 0) {
            $pierce_percent = round($player1_attack->piercing_percent * 100, 0);
            $player2->resist_boost *= 1 - $player1_attack->piercing_percent;

            $collision_displays[] = "{$player1->getName()} pierces {$pierce_percent}% of {$player2->getName()}'s defenses!";
        }
        if ($player2_attack->piercing_percent > 0) {
            $pierce_percent = round($player2_attack->piercing_percent * 100, 0);
            $player1->resist_boost *= 1 - $player2_attack->piercing_percent;
            $collision_displays[] = "{$player2->getName()} pierces {$pierce_percent}% of {$player1->getName()}'s defenses!";
        }*/

        /* Calculate speed values */
        if($this->system->debug['jutsu_collision']) {
            echo "Player1({$player1->getName()}): {$player1->speed} ({$player1->speed_boost} - {$player1->speed_nerf})<br />";
            echo "Player2({$player2->getName()}): {$player2->speed} ({$player2->speed_boost} - {$player2->speed_nerf})<br />";
        }

        $evasion_display = $this->applyEvasion(
            player1: $player1,
            player2: $player2,
            player1_attack: $player1_attack,
            player2_attack: $player2_attack
        );
        if(strlen($evasion_display) > 0) {
            $collision_displays[] = $evasion_display;
        }

        // Barriers
        if($player1->barrier && $player2_attack->isDirectDamage()) {
            $collision_displays[] = $this->applyBarrier(
                barrier_user: $player1,
                incoming_attack: $player2_attack,
                barrier_user_is_p1: true
            );
        }
        if($player2->barrier && $player1_attack->isDirectDamage()) {
            $collision_displays[] = $this->applyBarrier(
                barrier_user: $player2,
                incoming_attack: $player1_attack,
                barrier_user_is_p1: false
            );
        }

        // Substitution
        if ($player1_attack->substitution_percent > 0 && $player2_attack->isDirectDamage()) {
            $collision_displays[] = $this->applySubstitution(
                fighter_attack: $player1_attack,
                incoming_attack: $player2_attack,
                fighter_is_p1: true
            );
        }
        if ($player2_attack->substitution_percent > 0 && $player1_attack->isDirectDamage()) {
            $collision_displays[] = $this->applySubstitution(
                fighter_attack: $player2_attack,
                incoming_attack: $player1_attack,
                fighter_is_p1: false
            );
        }

        // Counter
        if ($player1_attack->counter_percent > 0 && $player2_attack->isDirectDamage()) {
            $collision_displays[] = $this->applyCounter(
                fighter_attack: $player1_attack,
                incoming_attack: $player2_attack,
                fighter_is_p1: true
            );
        }
        if ($player2_attack->counter_percent > 0 && $player1_attack->isDirectDamage()) {
            $collision_displays[] = $this->applyCounter(
                fighter_attack: $player2_attack,
                incoming_attack: $player1_attack,
                fighter_is_p1: false
            );
        }

        // Reflect
        if ($player1_attack->reflect_percent > 0 && $player2_attack->isDirectDamage()) {
            $collision_displays[] = $this->applyReflect(
                fighter_attack: $player1_attack,
                incoming_attack: $player2_attack,
                fighter_is_p1: true);
        }
        if ($player2_attack->reflect_percent > 0 && $player1_attack->isDirectDamage()) {
            $collision_displays[] = $this->applyReflect(
                fighter_attack: $player2_attack,
                incoming_attack: $player1_attack,
                fighter_is_p1: false
            );
        }

        return $this->parseCombatText(implode("[br]", $collision_displays), $player1, $player2);
    }

    /**
     * @throws RuntimeException
     */
    private function getEvasionPercent(
        Fighter $fighter,
        BattleAttack $fighter_attack,
        int $target_stat_total,
        bool $apply_effects = true
    ): float|int {
        switch($fighter_attack->jutsu_type) {
            case JutsuOffenseType::NINJUTSU:
            case JutsuOffenseType::GENJUTSU:
            case JutsuOffenseType::TAIJUTSU:
                // get speed stat total
                $evasion_stats = $fighter->speed + $fighter->speed_boost + $fighter->cast_speed + $fighter->cast_speed_boost;
                $evasion_percent = $evasion_stats * BattleManager::SPEED_DAMAGE_REDUCTION_RATIO / max($target_stat_total, 1);

                if($apply_effects) {
                    $evasion_percent += $fighter->evasion_boost;
                    $evasion_percent -= $fighter->evasion_nerf;
                }
                break;
            default:
                throw new RuntimeException("Invalid jutsu type!");
        }

        return $evasion_percent;
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
        foreach($fighter->default_jutsu as $id => $attack) {
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
        if (isset($fighter->default_jutsu[$jutsu_id]) && $fighter->default_jutsu[$jutsu_id]->jutsu_type == JutsuOffenseType::TAIJUTSU) {
            $fighter_jutsu = $fighter->default_jutsu[$jutsu_id];
        }
        if ($fighter->hasEquippedJutsu($jutsu_id) && $fighter->jutsu[$jutsu_id]->jutsu_type == JutsuOffenseType::TAIJUTSU) {
            $fighter_jutsu = $fighter->jutsu[$jutsu_id];
        }

        if ($fighter_jutsu) {
            $fighter_jutsu->setCombatId($fighter->combat_id);
        }

        return $fighter_jutsu;
    }

    #[Trace]
    protected function setPlayerAction(Fighter $player, Jutsu $jutsu): void {
        $this->battle->fighter_actions[$player->combat_id] = new LegacyFighterAction($jutsu->id, $jutsu->purchase_type);

        if (isset($this->player_jutsu_used[$jutsu->combat_id])) {
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
    protected function chooseAndSetAIAction(Fighter $ai): void {
        if (!($ai instanceof NPC)) {
            throw new RuntimeException("Calling chooseAndSetAIAction on non-AI!");
        }

        $jutsu = $ai->chooseAttack($this);
        $this->ai_jutsu_used = $jutsu;
        $this->ai_jutsu_used->setCombatId($ai->combat_id);

        $this->battle->fighter_actions[$ai->combat_id] = new LegacyFighterAction($jutsu->id, Jutsu::PURCHASE_TYPE_PURCHASABLE);

        if ($this->ai_jutsu_used->cooldown > 0) {
            $this->battle->jutsu_cooldowns[$this->ai_jutsu_used->combat_id] = $this->ai_jutsu_used->cooldown;
        }
    }

    /**
     * @param BattleAttack $fighter_attack
     * @param BattleAttack $incoming_attack
     * @param float        $modifier_amount
     * @return float
     */
    public function getElementalModifier(BattleAttack $fighter_attack, BattleAttack $incoming_attack, float $modifier_amount): float {
        // Fire > Wind > Lightning > Earth > Water > Fire
        $winning_modifier = 1 + $modifier_amount;
        $losing_modifier = 1 - $modifier_amount;

        switch($fighter_attack->element) {
            case Element::FIRE:
                if($incoming_attack->element === Element::WATER) {
                    return $losing_modifier;
                }
                if($incoming_attack->element == Element::WIND) {
                    return $winning_modifier;
                }
                break;
            case Element::WIND:
                if($incoming_attack->element === Element::FIRE) {
                    return $losing_modifier;
                }
                if($incoming_attack->element === Element::LIGHTNING) {
                    return $winning_modifier;
                }
                break;
            case Element::LIGHTNING:
                if($incoming_attack->element === Element::WIND) {
                    return $losing_modifier;
                }
                if($incoming_attack->element === Element::EARTH) {
                    return $winning_modifier;
                }
                break;
            case Element::EARTH:
                if($incoming_attack->element === Element::LIGHTNING) {
                    return $losing_modifier;
                }
                if($incoming_attack->element === Element::WATER) {
                    return $winning_modifier;
                }
                break;
            case Element::WATER:
                if($incoming_attack->element === Element::EARTH) {
                    return $losing_modifier;
                }
                if($incoming_attack->element === Element::FIRE) {
                    return $winning_modifier;
                }
                break;
        }


        return 1;
    }

    protected function applyEvasion(Fighter $player1, Fighter $player2, BattleAttack $player1_attack, BattleAttack $player2_attack): string {
        $display = '';

        $player1_evasion_percent = $this->getEvasionPercent(
            fighter: $player1,
            fighter_attack: $player1_attack,
            target_stat_total: $player2->getBaseStatTotal()
        );
        $player2_evasion_percent = $this->getEvasionPercent(
            fighter: $player2,
            fighter_attack: $player2_attack,
            target_stat_total: $player1->getBaseStatTotal()
        );

        $player1_base_evasion_percent = $this->getEvasionPercent(
            fighter: $player1,
            fighter_attack: $player1_attack,
            target_stat_total: $player2->getBaseStatTotal(),
            apply_effects: false
        );
        $player2_base_evasion_percent = $this->getEvasionPercent(
            fighter: $player2,
            fighter_attack: $player2_attack,
            target_stat_total: $player1->getBaseStatTotal(),
            apply_effects: false
        );

        // Barriers - We do not apply evasion boosts/nerfs
        if($player1_base_evasion_percent >= $player2_base_evasion_percent) {
            $damage_reduction = $this->getEvasionDamageReduction($player1_base_evasion_percent - $player2_base_evasion_percent);
            if($damage_reduction >= 0.01) {
                $player2->barrier *= 1 - $damage_reduction;
            }
        }
        else if($player2_base_evasion_percent > $player1_base_evasion_percent) {
            $damage_reduction = $this->getEvasionDamageReduction($player2_base_evasion_percent - $player1_base_evasion_percent);
            if($damage_reduction >= 0.01) {
                $player1->barrier *= 1 - $damage_reduction;
            }
        }

        // Attacks
        if($player1_evasion_percent >= $player2_evasion_percent) {
            $damage_reduction = $this->getEvasionDamageReduction($player1_evasion_percent - $player2_evasion_percent);
            if($damage_reduction >= 0.01) {
                $player2_attack->damage *= 1 - $damage_reduction;

                if($player1_attack->jutsu_type == JutsuOffenseType::TAIJUTSU) {
                    $display = "[player] swiftly evaded " . round($damage_reduction * 100, 0) . "% of [opponent]'s damage!";
                }
                else {
                    $display = "[player] cast [gender2] jutsu before [opponent], negating " .
                        round($damage_reduction * 100, 0) . "% of [opponent]'s damage!";
                }
            }
        }
        else if($player2_evasion_percent >= $player1_evasion_percent) {
            $damage_reduction = $this->getEvasionDamageReduction($player2_evasion_percent - $player1_evasion_percent);
            if($damage_reduction >= 0.01) {
                $player1_attack->damage *= 1 - $damage_reduction;

                if($player2_attack->jutsu_type == JutsuOffenseType::TAIJUTSU) {
                    $display = "[opponent] swiftly evaded " . round($damage_reduction * 100) . "% of [player]'s damage!";
                }
                else {
                    $display = "[opponent] cast [targetGender2] jutsu before [player], negating " .
                        round($damage_reduction * 100, 0) . "% of [player]'s damage!";
                }
            }
        }

        return $display;
    }
    protected function getEvasionDamageReduction(float $base_evasion_percent): float {
        $damage_reduction = $base_evasion_percent;

        // if higher than soft cap, apply penalty
        if ($damage_reduction > self::EVASION_SOFT_CAP) {
            $damage_reduction = (($damage_reduction - self::EVASION_SOFT_CAP) * self::EVASION_SOFT_CAP_RATIO) + self::EVASION_SOFT_CAP;
        }
        // if still higher than soft cap, set to hard cap
        if ($damage_reduction > self::EVASION_HARD_CAP) {
            $damage_reduction = self::EVASION_HARD_CAP;
        }

        return round($damage_reduction, 2);
    }

    protected function applyBarrier(Fighter $barrier_user, BattleAttack $incoming_attack, bool $barrier_user_is_p1): string {
        // Apply penalty against Genjutsu
        if ($incoming_attack->jutsu_type == JutsuOffenseType::GENJUTSU) {
            $barrier_user->barrier *= 1 - (self::GENJUTSU_BARRIER_PENALTY / 100);
        }

        // Apply piercing
        $barrier_user->barrier *= (1 - $incoming_attack->piercing_percent);

        // Block damage from opponent's attack
        if($barrier_user->barrier >= $incoming_attack->damage) {
            $block_amount = $incoming_attack->damage;
        }
        else {
            $block_amount = $barrier_user->barrier;
        }

        $block_percent = ($incoming_attack->damage >= 1) ? ($block_amount / $incoming_attack->damage) * 100 : 100;
        $barrier_user->barrier -= $block_amount;
        $incoming_attack->damage -= $block_amount;

        if($barrier_user->barrier < 0) {
            $barrier_user->barrier = 0;
        }
        if($incoming_attack->damage < 0) {
            $incoming_attack->damage = 0;
        }

        // Set display
        $block_percent = round($block_percent, 1);

        return $barrier_user_is_p1
            ? "[player]'s barrier blocked $block_percent% of [opponent]'s damage!"
            : "[opponent]'s barrier blocked $block_percent% of [player]'s damage!";
    }

    protected function applySubstitution(BattleAttack $fighter_attack, BattleAttack $incoming_attack, bool $fighter_is_p1): string {
        $effect_reduced = false;

        $fighter_attack->substitution_percent *= (1 - $incoming_attack->piercing_percent);
        $incoming_attack->damage *= (1 - $fighter_attack->substitution_percent);
        foreach($incoming_attack->effects as $effect) {
            if(in_array($effect->effect, BattleEffectsManager::DEBUFF_EFFECTS)) {
                $effect_reduced = true;
                $effect->effect_amount *= 1 - $fighter_attack->substitution_percent;
            }
        }
        if($incoming_attack->immolate_raw_damage > 0) {
            $incoming_attack->immolate_raw_damage *= 1 -
                ($fighter_attack->substitution_percent * BattleEffectsManager::SUBSTITUTION_IMMO_EFFECTIVENESS);
        }


        $block_percent = round($fighter_attack->substitution_percent * 100);

        return $fighter_is_p1
            ? "[player]'s substitute took $block_percent% of [opponent]'s damage" . ($effect_reduced ? " and effects" : "") . "!"
            : "[opponent]'s substitute took $block_percent% of [player]'s damage" . ($effect_reduced ? " and effects" : "") . "!";
    }
    protected function applyCounter(BattleAttack $fighter_attack, BattleAttack $incoming_attack, bool $fighter_is_p1): string {
        // Apply piercing
        $fighter_attack->counter_percent *= (1 - $incoming_attack->piercing_percent);

        // Apply reduction
        $incoming_attack->countered_percent = $fighter_attack->counter_percent;
        $incoming_attack->countered_raw_damage = $incoming_attack->damage * $fighter_attack->counter_percent;
        $incoming_attack->countered_jutsu_type = $fighter_attack->jutsu_type;
        $incoming_attack->damage *= (1 - $fighter_attack->counter_percent);

        // Set display
        $block_percent = (int)round($fighter_attack->counter_percent * 100);

        return $fighter_is_p1
            ? "[player] countered $block_percent% of [opponent]'s damage!"
            : "[opponent] countered $block_percent% of [player]'s damage!";
    }

    protected function applyReflect(BattleAttack $fighter_attack, BattleAttack $incoming_attack, bool $fighter_is_p1): string {
        // Apply piercing
        $fighter_attack->reflect_percent *= (1 - $incoming_attack->piercing_percent);

        // Apply reduction
        $incoming_attack->reflected_percent = $fighter_attack->reflect_percent;
        $incoming_attack->reflected_raw_damage = $incoming_attack->damage * $fighter_attack->reflect_percent;
        $incoming_attack->reflected_jutsu_type = $fighter_attack->jutsu_type;
        $incoming_attack->damage *= (1 - $fighter_attack->reflect_percent);

        if ($fighter_attack->reflect_percent > 0) {
            $fighter_attack->effects[] = new Effect(
                effect: 'reflect_damage',
                effect_amount: $incoming_attack->reflected_raw_damage / $fighter_attack->reflect_duration,
                effect_length: $fighter_attack->reflect_duration,
                damage_type: $fighter_attack->jutsu_type,
            );
        }

        $block_percent = (int)round($fighter_attack->reflect_percent * 100);

        return $fighter_is_p1
            ? "[player] reflected $block_percent% of [opponent]'s damage!"
            : "[opponent] reflected $block_percent% of [player]'s damage!";
    }

    public static function diminishingReturns($val, $scale) {
        if($val < 0) {
            return -self::diminishingReturns(-$val, $scale);
        }
        else if($val < $scale) {
            return $val;
        }

        $mult = $val / $scale;
        $trinum = (sqrt(8.0 * $mult + 1.0) - 1.0) / 2.0;

        return $trinum * $scale;
    }

    public static function formatNumber(int|float $number, int $precision = 0): string {
        return number_format(num: $number, decimals: $precision, thousands_separator: $number >= 10000 ? ',' : '');
    }

    public function getTotalLoot(): int {
        return $this->battle->total_loot;
    }
}