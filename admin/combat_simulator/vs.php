<?php

/** @var System $system */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . "/TestFighter.php";
require __DIR__ . "/calcDamage.php";

$rankManager = new RankManager($system);
$rankManager->loadRanks();

if(isset($_POST['run_simulation'])) {
    $player1_data = $_POST['fighter1'];
    $player2_data = $_POST['fighter2'];

    $valid_jutsu_types = [
        Jutsu::TYPE_NINJUTSU,
        Jutsu::TYPE_TAIJUTSU,
        Jutsu::TYPE_GENJUTSU,
    ];
    try {
        if(!in_array($player1_data['jutsu_type'], $valid_jutsu_types)) {
            throw new RuntimeException("Invalid jutsu type for player 1!");
        }
        if(!in_array($player2_data['jutsu_type'], $valid_jutsu_types)) {
            throw new RuntimeException("Invalid jutsu type for player 2!");
        }

        $player1 = TestFighter::fromFormData(
            system: $system,
            rankManager: $rankManager,
            fighter_data: $player1_data,
            name: "Player 1"
        );
        $player1_jutsu = $player1->addJutsu(
            jutsu_type: $player1_data['jutsu_type'],
            base_power: (int)$player1_data['jutsu_power'],
            effect: $player1_data['jutsu_effect'],
            effect_amount: (int)$player1_data['jutsu_effect_amount'],
            effect_length: (int)$player1_data['jutsu_effect_length'],
        );

        $player2 = TestFighter::fromFormData(
            system: $system,
            rankManager: $rankManager,
            fighter_data: $player2_data,
            name: "Player 2"
        );
        $player2_jutsu = $player2->addJutsu(
            jutsu_type: $player2_data['jutsu_type'],
            base_power: (int)$player2_data['jutsu_power'],
            effect: $player2_data['jutsu_effect'],
            effect_amount: (int)$player2_data['jutsu_effect_amount'],
            effect_length: (int)$player2_data['jutsu_effect_length'],
        );

        $damages = calcDamage(
            player1: $player1,
            player2: $player2,
            player1_jutsu: $player1_jutsu,
            player2_jutsu: $player2_jutsu
        );

        echo "<div style='width:500px;background-color:#EAEAEA;text-align:center;margin-left:auto;margin-right:auto;
            padding:8px;border:1px solid #000000;border-radius:10px;'>
        Player 1:<br />
        {$damages['player1']['raw_damage']} raw damage<br />
        {$damages['player1']['collision_damage']} post-collision damage<br />
        {$damages['player1']['damage']} final damage<br />";

        if($damages['collision_text']) {
            echo "<hr />" . $damages['collision_text'] . "<hr />";
        }
        else {
            echo "<hr />";
        }

        echo "Player 2:<br />
        {$damages['player2']['raw_damage']} raw damage<br />
        {$damages['player2']['collision_damage']} post-collision damage<br />
        {$damages['player2']['damage']} final damage<br />
        </div>";
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

require 'vs_fighter_input.php';

?>

<style>
    .vs_container {
        width: 800px;
        margin: 5px auto;
        text-align: center;
    }

    input[type='submit'] {
        display: block;
        margin: 10px auto auto;
    }
</style>

<form action='vs.php' method='post'>
    <div class='vs_container'>
        <?php displayFighterInput(system: $system, fighter_form_key: 'fighter1'); ?>
        <?php displayFighterInput(system: $system, fighter_form_key: 'fighter2'); ?>
        <input type='submit' name='run_simulation' value='Run Simulation' />
    </div>
</form>

