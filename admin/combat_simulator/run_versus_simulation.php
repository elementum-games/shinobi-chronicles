<?php

require __DIR__ . "/TestFighter.php";
require_once __DIR__ . '/calcDamage.php';

/**
 * @throws DatabaseDeadlockException
 */
function runVersusSimulation(System $system, User $player, array $player1_data, array $player2_data): array {
    $rankManager = new RankManager($system);
    $rankManager->loadRanks();

    $valid_jutsu_types = JutsuOffenseType::values();

    if(!in_array($player1_data['jutsu1']['type'], $valid_jutsu_types)) {
        throw new RuntimeException("Invalid jutsu type for player 1!");
    }
    if(!in_array($player2_data['jutsu1']['type'], $valid_jutsu_types)) {
        throw new RuntimeException("Invalid jutsu type for player 2!");
    }

    $player1 = TestFighter::fromFormData(
        system: $system,
        rankManager: $rankManager,
        fighter_data: $player1_data,
        name: "Player 1"
    );
    $player1->combat_id = Battle::combatId(Battle::TEAM1, $player1);
    $player1_jutsu = $player1->addJutsuFromFormData($player1_data['jutsu1']);

    $player2 = TestFighter::fromFormData(
        system: $system,
        rankManager: $rankManager,
        fighter_data: $player2_data,
        name: "Player 2"
    );
    $player2->combat_id = Battle::combatId(Battle::TEAM2, $player2);
    $player2_jutsu = $player2->addJutsuFromFormData($player2_data['jutsu1']);

    // Effects
    $player1_effects = $player1->activeEffectsFromFormData($player1_data['active_effects']);
    $player2_effects = $player2->activeEffectsFromFormData($player2_data['active_effects']);

    $results = calcDamage(
        system: $system,
        user: $player,
        player1: $player1,
        player2: $player2,
        player1_jutsu: $player1_jutsu,
        player2_jutsu: $player2_jutsu,
        player1_effects: $player1_effects,
        player2_effects: $player2_effects
    );

    $results['winning_fighter'] = null;
    $results['winning_percent'] = 0;
    $results['damage_difference'] = 0;

    if($results['player1']['damage_taken'] > $results['player2']['damage_taken']) {
        $results['winning_fighter'] = 'player2';
        $results['winning_percent'] = (
                ($results['player1']['damage_taken'] / max(1, $results['player2']['damage_taken'])) * 100
            ) - 100;
        $results['damage_difference'] = $results['player1']['damage_taken'] - $results['player2']['damage_taken'];
    }
    if($results['player2']['damage_taken'] > $results['player1']['damage_taken']) {
        $results['winning_fighter'] = 'player1';
        $results['winning_percent'] = (
                ($results['player2']['damage_taken'] / max(1, $results['player1']['damage_taken'])) * 100
            ) - 100;
        $results['damage_difference'] = $results['player2']['damage_taken'] - $results['player1']['damage_taken'];
    }

    return $results;
}

