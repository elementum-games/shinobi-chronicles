<?php

/**
 * @var System $system
 * @var User $user
 */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . '/run_versus_simulation.php';

$results = null;

if(isset($_POST['run_simulation'])) {
    $player1_data = $_POST['fighter1'];
    $player2_data = $_POST['fighter2'];

    try {
        $results = runVersusSimulation(
            system: $system,
            player: $user,
            player1_data: $player1_data,
            player2_data: $player2_data
        );
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

require 'vs_fighter_input.php';

?>
<html lang="en">
    <head>
        <title>SC Combat - VS mode</title>
    </head>
    <body>
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

            .results {
                width:550px;
                background-color:#EAEAEA;
                text-align:left;
                margin-left:auto;
                margin-right:auto;
                padding:8px;
                border:1px solid #000000;
                border-radius:10px;

                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 2%;
            }

            .player1, .player2 {
                width: 48%;
                box-sizing: border-box;
                padding: 5px;

                background-color:#fafafa;
            }
            .winner {
                background-color: #a0ffa0;
            }

            .collision {
                width: 100%;
                margin-top: 10px;
                background-color: #fafafa;
                text-align: center;
                padding: 5px;
            }
        </style>

        <?php if($results != null): ?>
            <div class='results'>
                <div class='player1 <?= ($results['winning_fighter'] == 'player1' ? 'winner' : '') ?>'>
                    <b>Player 1:</b><br />
                    <?= number_format($results['player1']['raw_damage']) ?> raw damage<br />
                    <?= number_format($results['player1']['collision_damage']) ?> post-collision damage<br />
                    <?= number_format($results['player1']['damage_before_resist']) ?> pre-resist damage<br />
                    <?= number_format($results['player1']['damage_dealt']) ?> final damage dealt<br />
                    <br />
                    <?= number_format($results['player1']['damage_taken'], 2) ?> damage taken<br />
                </div>
                <div class='player2 <?= ($results['winning_fighter'] == 'player2' ? 'winner' : '') ?>'>
                    <b>Player 2:</b><br />
                    <?= number_format($results['player2']['raw_damage']) ?> raw damage<br />
                    <?= number_format($results['player2']['collision_damage']) ?> post-collision damage<br />
                    <?= number_format($results['player2']['damage_before_resist']) ?> pre-resist damage<br />
                    <?= number_format($results['player2']['damage_dealt']) ?> final damage dealt<br />
                    <br />
                    <?= number_format($results['player2']['damage_taken'], 2) ?> damage taken<br />
                </div>
                <div class='collision'>
                    <?php if($results['collision_text']): ?>
                        <?= str_replace("[br]", "<br />", $results['collision_text']) ?><br />
                    <?php endif; ?>

                    <?php if($results['winning_fighter'] == 'player1'): ?>
                        <br />
                        <b>Player 1 won by <?= number_format($results['winning_percent'], 2) ?>%
                            (<?= number_format($results['damage_difference'], 2) ?> damage)</b><br />
                    <?php endif; ?>
                    <?php if($results['winning_fighter'] == 'player2'): ?>
                        <br />
                        <b>Player 2 won by <?= number_format($results['winning_percent'], 2) ?>%
                            (<?= number_format($results['damage_difference'], 2) ?> damage)</b><br />
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <form action='vs_old.php' method='post'>
            <div class='vs_container'>
                <?php displayFighterInput(system: $system, fighter_form_key: 'fighter1'); ?>
                <?php displayFighterInput(system: $system, fighter_form_key: 'fighter2'); ?>
                <input type='submit' name='run_simulation' value='Run Simulation' />
            </div>
        </form>
    </body>
</html>
