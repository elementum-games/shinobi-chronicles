<?php

/**
 * @param Fighter $player1
 * @param Fighter $player2
 * @param Jutsu   $player1_jutsu
 * @param Jutsu   $player2_jutsu
 * @return array
 * @throws RuntimeException|DatabaseDeadlockException
 */
function calcDamage(Fighter $player1, Fighter $player2, Jutsu $player1_jutsu, Jutsu $player2_jutsu): array {
    global $system;
    global $user;

    $player1_raw_damage = $player1->calcDamage($player1_jutsu, true);
    $player2_raw_damage = $player2->calcDamage($player2_jutsu, true);

    // Collision
    $battle_id = Battle::start($system, $player1, $player2, Battle::TYPE_SPAR);
    $battle = BattleManager::init($system, $user, $battle_id, true, false);

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

    $collision_text = $battle->jutsuCollision(
        player1: $player1,
        player2: $player2,
        player1_damage: $player1_raw_damage,
        player2_damage: $player2_raw_damage,
        player1_attack: $player1_attack,
        player2_attack: $player2_attack
    );

    $system->db->query("DELETE FROM battles WHERE `battle_id`={$battle_id}");

    $player1_collision_damage = $player1_raw_damage;
    $player2_collision_damage = $player2_raw_damage;

    $player1_damage = $player2->calcDamageTaken($player1_collision_damage, $player1_jutsu->jutsu_type);
    $player2_damage = $player1->calcDamageTaken($player2_collision_damage, $player2_jutsu->jutsu_type);

    // Display
    return [
        'player1' => [
            'raw_damage' => $player1_raw_damage,
            'collision_damage' => $player1_collision_damage,
            'damage' => $player1_damage,
        ],
        'player2' => [
            'raw_damage' => $player2_raw_damage,
            'collision_damage' => $player2_collision_damage,
            'damage' => $player2_damage,
        ],
        'collision_text' => $collision_text,
    ];
}