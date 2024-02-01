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
        $player1->combat_id = Battle::combatId(Battle::TEAM1, $player1);
        $player1_jutsu = $player1->addJutsu(
            jutsu_type: $player1_data['jutsu_type'],
            base_power: $player1_data['jutsu_power'],
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
        $player2->combat_id = Battle::combatId(Battle::TEAM2, $player2);
        $player2_jutsu = $player2->addJutsu(
            jutsu_type: $player2_data['jutsu_type'],
            base_power: $player2_data['jutsu_power'],
            effect: $player2_data['jutsu_effect'],
            effect_amount: (int)$player2_data['jutsu_effect_amount'],
            effect_length: (int)$player2_data['jutsu_effect_length'],
        );

        // Effects
        $player1_effects = [];
        $player2_effects = [];
        foreach($player1_data['active_effects'] as $active_effect) {
            if($active_effect['effect'] == 'none') continue;

            $player1_effects[] = new BattleEffect(
                user: $player1->combat_id,
                target: $player1->combat_id,
                turns: 1,
                effect: $active_effect['effect'],
                effect_amount: $active_effect['amount']
            );
        }
        foreach($player2_data['active_effects'] as $active_effect) {
            if($active_effect['effect'] == 'none') continue;

            $player2_effects[] = new BattleEffect(
                user: $player2->combat_id,
                target: $player2->combat_id,
                turns: 1,
                effect: $active_effect['effect'],
                effect_amount: $active_effect['amount']
            );
        }

        $results = calcDamage(
            player1: $player1,
            player2: $player2,
            player1_jutsu: $player1_jutsu,
            player2_jutsu: $player2_jutsu,
            player1_effects: $player1_effects,
            player2_effects: $player2_effects
        );
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
    .collision {
        width: 100%;
        margin-top: 10px;
        background-color:#fafafa;
        text-align: center;
        padding: 5px;
    }
</style>


<?php if($results != null): ?>
    <div class='results'>
        <div class='player1'>
            <b>Player 1:</b><br />
            <?= number_format($results['player1']['raw_damage']) ?> raw damage<br />
            <?= number_format($results['player1']['collision_damage']) ?> post-collision damage<br />
            <?= number_format($results['player1']['damage_dealt']) ?> final damage dealt<br />
            <br />
            <?= number_format($results['player1']['damage_taken'], 2) ?> damage taken<br />
        </div>
        <div class='player2'>
            <b>Player 2:</b><br />
            <?= number_format($results['player2']['raw_damage']) ?> raw damage<br />
            <?= number_format($results['player2']['collision_damage']) ?> post-collision damage<br />
            <?= number_format($results['player2']['damage_dealt']) ?> final damage dealt<br />
            <br />
            <?= number_format($results['player2']['damage_taken'], 2) ?> damage taken<br />
        </div>
        <?php if($results['collision_text']): ?>
            <div class='collision'>
                <?= $results['collision_text'] ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<form action='vs.php' method='post'>
    <div class='vs_container'>
        <?php displayFighterInput(system: $system, fighter_form_key: 'fighter1'); ?>
        <?php displayFighterInput(system: $system, fighter_form_key: 'fighter2'); ?>
        <input type='submit' name='run_simulation' value='Run Simulation' />
    </div>
</form>

