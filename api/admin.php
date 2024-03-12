<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = API::init(row_lock: true);

require __DIR__ . '/../admin/combat_simulator/run_versus_simulation.php';

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);

    if($_POST['action'] == 'run_versus_simulation') {
        $player1_data = $_POST['fighter1'];
        $player2_data = $_POST['fighter2'];

        $results = runVersusSimulation(
            system: $system,
            player: $player,
            player1_data: $player1_data,
            player2_data: $player2_data
        );
        API::exitWithData(
            data: [
                'results' => $results
            ],
            errors: [],
            debug_messages: [],
            system: $system
        );
    }
} catch(Exception $e) {
    API::exitWithException($e, system: $system);
}
