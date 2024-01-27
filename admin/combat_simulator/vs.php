<?php

/** @var System $system */
require __DIR__ . "/../_authenticate_admin.php";

require __DIR__ . "/TestFighter.php";
require __DIR__ . "/calcDamage.php";

/** @var string[] $bloodline_combat_boosts */
require_once __DIR__ . '/../constraints/bloodline.php';

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
        $player1_jutsu = new Jutsu(
            id: 1,
            name: 'p1j',
            rank: $player1->rank,
            jutsu_type: $player1_data['jutsu_type'],
            base_power: (int)$player1_data['jutsu_power'],
            range: 1,
            effect_1: 'none',
            base_effect_amount_1: 0,
            effect_length_1: 0,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: 'nope',
            battle_text: 'no',
            cooldown: 0,
            use_type: Jutsu::USE_TYPE_PROJECTILE,
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 0,
            purchase_cost: 0,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Jutsu::ELEMENT_NONE,
            hand_seals: 1
        );
        $player1_jutsu->setLevel(50, 0);
        $player1->jutsu[$player1_jutsu->id] = $player1_jutsu;

        $player2 = TestFighter::fromFormData(
            system: $system,
            rankManager: $rankManager,
            fighter_data: $player2_data,
            name: "Player 2"
        );
        $player2_jutsu = new Jutsu(
            id: 1,
            name: 'p1j',
            rank: $player2->rank,
            jutsu_type: $player2_data['jutsu_type'],
            base_power: (int)$player2_data['jutsu_power'],
            range: 1,
            effect_1: 'none',
            base_effect_amount_1: 0,
            effect_length_1: 0,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: 'no',
            battle_text: 'nope',
            cooldown: 0,
            use_type: Jutsu::USE_TYPE_PROJECTILE,
            target_type: Jutsu::TARGET_TYPE_TILE,
            use_cost: 0,
            purchase_cost: 0,
            purchase_type: Jutsu::PURCHASE_TYPE_PURCHASABLE,
            parent_jutsu: 0,
            element: Jutsu::ELEMENT_NONE,
            hand_seals: 0
        );
        $player2_jutsu->setLevel(50, 0);
        $player2->jutsu[$player2_jutsu->id] = $player2_jutsu;

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

?>

<style>
    label {
        display: inline-block;
    }

    .vs_container {
        width: 800px;
        margin: 5px auto;
        text-align: center;
    }

    .versusFighterInput {
        width:300px;
        display:inline-block;
        border:1px solid #000000;
        border-radius:10px;

        text-align: left;

        padding: 8px;
    }

    .jutsu_input {
        margin: 5px auto;
    }

    input[type='submit'] {
        display: block;
        margin: 5px auto auto;
    }
</style>

<?php
    function displayFighterInput(string $fighter_form_key, array $bloodline_combat_boosts): void {
        $stats = [
            'ninjutsu_skill',
            'taijutsu_skill',
            'genjutsu_skill',
            'bloodline_skill',
            'speed',
            'cast_speed',
        ];

        ?>
        <div class='versusFighterInput' style='margin-left: 20px;'>
            Player 2<br />
            <?php foreach($stats as $stat): ?>
                <label style='width:110px;'><?= $stat ?>:</label>
                <input type='number' step='10' name='<?= $fighter_form_key ?>[<?= $stat ?>]' value='<?= $_POST[$fighter_form_key][$stat] ?? 0 ?>' /><br />
            <?php endforeach; ?>

            <div class='jutsu_input'>
                <label style='width:110px;'>Jutsu power:</label>
                <input type='text' name='<?= $fighter_form_key ?>[jutsu_power]' value='<?= $_POST[$fighter_form_key]['jutsu_power'] ?? 1 ?>' /><br />
                <label>
                    <input type='radio' name='<?= $fighter_form_key ?>[jutsu_type]' value='ninjutsu'
                        <?= (($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'ninjutsu' ? "checked='checked'" : '') ?>
                    />
                    Ninjutsu
                </label><br />
                <label>
                    <input type='radio' name='<?= $fighter_form_key ?>[jutsu_type]' value='taijutsu'
                        <?= (($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'taijutsu' ? "checked='checked'" : '') ?>
                    />
                    Taijutsu
                </label><br />
                <label>
                    <input type='radio' name='<?= $fighter_form_key ?>[jutsu_type]' value='genjutsu'
                        <?= (($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'genjutsu' ? "checked='checked'" : '') ?>
                    />
                    Genjutsu
                </label><br />
            </div>

            Bloodline boosts<br />
            <?php for($i = 1; $i <= 3; $i++): ?>
                <select name='<?= $fighter_form_key ?>[bloodline_boost_<?= $i ?>]'>
                    <option value='none'>None</option>
                    <?php foreach($bloodline_combat_boosts as $boost): ?>
                        <option value='<?= $boost ?>'
                            <?= (($_POST[$fighter_form_key]["bloodline_boost_{$i}"] ?? '') == $boost ? "selected='selected'" : '') ?>
                        >
                            <?= $boost ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input
                        type='number'
                        name='<?= $fighter_form_key ?>[bloodline_boost_<?= $i ?>_power]'
                        style='width:60px'
                        value='<?= $_POST[$fighter_form_key]["bloodline_boost_{$i}_power"] ?? 0 ?>'
                />
                <br />
            <?php endfor; ?>
        </div>
        <?php
    }
?>

<form action='vs.php' method='post'>
    <div class='vs_container'>
        <?php displayFighterInput('fighter1', $bloodline_combat_boosts); ?>
        <?php displayFighterInput('fighter2', $bloodline_combat_boosts); ?>
        <input type='submit' name='run_simulation' value='Run Simulation' />
    </div>
</form>

