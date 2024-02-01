<?php

class TestBattleManager extends BattleManager {
    /**
     * @param array $player1_effects
     * @param array $player2_effects
     * @return void
     */
    public function setupPassiveEffects(array $player1_effects, array $player2_effects): void {
        $this->effects->active_effects = array_merge(
            $this->effects->active_effects,
            $player1_effects,
            $player2_effects
        );

        $this->effects->applyPassiveEffects($this->battle->player1, $this->battle->player2);
    }

    public function setFighters(Fighter $player1, Fighter $player2) {
        $this->battle->player1 = $player1;
        $this->battle->player2 = $player2;
    }

    public static function init(
        System $system, User $player, int $battle_id, bool $spectate = false, bool $load_fighters = true
    ): TestBattleManager {
        return new TestBattleManager($system, $player, $battle_id, $spectate, $load_fighters);
    }
}

/**
 * @param Fighter $player1
 * @param Fighter $player2
 * @param Jutsu   $player1_jutsu
 * @param Jutsu   $player2_jutsu
 * @param BattleEffect[]   $player1_effects
 * @param BattleEffect[]   $player2_effects
 * @return array
 * @throws DatabaseDeadlockException
 */
function calcDamage(
    Fighter $player1, Fighter $player2,
    Jutsu $player1_jutsu, Jutsu $player2_jutsu,
    array $player1_effects, array $player2_effects
): array {
    global $system;
    global $user;

    // AI battle = disabled randomness
    $battle_id = Battle::start($system, $player1, $player2, Battle::TYPE_AI_ARENA);
    $battle = TestBattleManager::init(
        system: $system,
        player: $user,
        battle_id: $battle_id,
        spectate: true,
        load_fighters: false
    );
    $battle->setFighters($player1, $player2);
    $battle->setupPassiveEffects($player1_effects, $player2_effects);

    $player1_attack = $battle->setupFighterAttack(
        fighter: $player1,
        target: $player2,
        action: new LegacyFighterAction(
            jutsu_id: $player1_jutsu->id,
            jutsu_purchase_type: $player1_jutsu->purchase_type,
            weapon_id: null,
            weapon_element: null
        )
    );
    $player2_attack = $battle->setupFighterAttack(
        fighter: $player2,
        target: $player1,
        action: new LegacyFighterAction(
            jutsu_id: $player2_jutsu->id,
            jutsu_purchase_type: $player2_jutsu->purchase_type,
            weapon_id: null,
            weapon_element: null
        )
    );

    $player1_raw_damage = $player1_attack->raw_damage;
    $player2_raw_damage = $player2_attack->raw_damage;

    $collision_text = $battle->jutsuCollision(
        player1: $player1,
        player2: $player2,
        player1_attack: $player1_attack,
        player2_attack: $player2_attack
    );

    $system->db->query("DELETE FROM `battles` WHERE `battle_id`={$battle_id}");
    $system->db->query("DELETE FROM `battle_logs` WHERE `battle_id`={$battle_id}");

    $player1_collision_damage = $player1_raw_damage;
    $player2_collision_damage = $player2_raw_damage;

    $player1_starting_health = $player1->health;
    $player2_starting_health = $player2->health;

    $player1_damage = $player2->calcDamageTaken($player1_collision_damage, $player1_jutsu->jutsu_type);
    $player2_damage = $player1->calcDamageTaken($player2_collision_damage, $player2_jutsu->jutsu_type);

    $battle->applyAttack($player1_attack, $player1, $player2);
    $battle->applyAttack($player2_attack, $player2, $player1);

    // Display
    return [
        'player1' => [
            'raw_damage' => $player1_raw_damage,
            'collision_damage' => $player1_collision_damage,
            'damage_dealt' => $player1_damage,
            'damage_taken' => $player1_starting_health - $player1->health,
        ],
        'player2' => [
            'raw_damage' => $player2_raw_damage,
            'collision_damage' => $player2_collision_damage,
            'damage_dealt' => $player2_damage,
            'damage_taken' => $player2_starting_health - $player2->health,
        ],
        'collision_text' => $collision_text,
    ];
}