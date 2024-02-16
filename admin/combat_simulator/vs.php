<?php

/** @var System $system */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . "/TestFighter.php";
require __DIR__ . "/calcDamage.php";

$rankManager = new RankManager($system);
$rankManager->loadRanks();

$results = null;

if(isset($_POST['run_simulation'])) {
    $player1_data = $_POST['fighter1'];
    $player2_data = $_POST['fighter2'];

    $valid_jutsu_types = [
        Jutsu::TYPE_NINJUTSU,
        Jutsu::TYPE_TAIJUTSU,
        Jutsu::TYPE_GENJUTSU,
    ];
    try {
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
<form action='vs.php' method='post'>
    <div class='vs_container'>
        <?php displayFighterInput(system: $system, fighter_form_key: 'fighter1'); ?>
        <?php displayFighterInput(system: $system, fighter_form_key: 'fighter2'); ?>
        <input type='submit' name='run_simulation' value='Run Simulation' />
    </div>
</form>

