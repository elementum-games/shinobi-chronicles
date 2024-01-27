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
        $player1_jutsu = $player1->addJutsu(
            jutsu_type: $player1_data['jutsu_type'],
            base_power: (int)$player1_data['jutsu_power'],
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

$bloodlines_by_rank = [];
$result = $system->db->query("SELECT * FROM `bloodlines` WHERE `rank` < 5 ORDER BY `rank` ASC");
if ($system->db->last_num_rows > 0) {
    while ($row = $system->db->fetch($result)) {
        $bloodlines_by_rank[$row['rank']][$row['bloodline_id']] = Bloodline::fromArray($row);
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

        background-color: #f2f2f2;
    }

    .jutsu_input {
        margin: 5px auto;
    }

    input[type='submit'] {
        display: block;
        margin: 10px auto auto;
    }
</style>

<script>
    function prefillBloodline(fighterKey) {
        let bloodlineSelect = document.getElementById(`bloodline_prefill_${fighterKey}`);
        if(bloodlineSelect == null) {
            console.error("Invalid bloodline select element");
            return;
        }

        let selectedBloodline = bloodlineSelect.selectedOptions[0];
        if(parseInt(selectedBloodline.value) === 0) {
            console.warn("No bloodline selected!");
            return;
        }

        let selectedBloodlineBoosts = JSON.parse(
            bloodlineSelect.selectedOptions[0].getAttribute("data-boosts")
        );

        selectedBloodlineBoosts.forEach((boost, i) => {
            let boostEl = document.getElementById(`${fighterKey}_bloodline_boost_${i + 1}`);
            let boostAmountEl = document.getElementById(`${fighterKey}_bloodline_boost_${i + 1}_amount`);

            console.log(`${fighterKey}_bloodline_boost_${i + 1}`, boostEl);
            console.log(`${fighterKey}_bloodline_boost_${i + 1}_amount`, boostAmountEl);

            boostEl.value = boost.effect;
            boostAmountEl.value = boost.power;
        });
    }
</script>

<?php
    /**
     * @param string $fighter_form_key
     * @param array  $bloodline_combat_boosts
     * @param Bloodline[][]  $bloodlines_by_rank
     * @return void
     */
    function displayFighterInput(string $fighter_form_key, array $bloodline_combat_boosts, array $bloodlines_by_rank): void {
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
            <?= ucwords($fighter_form_key) ?><br />
            <?php foreach($stats as $stat): ?>
                <label style='width:110px;'><?= $stat ?>:</label>
                <input type='number' step='10' name='<?= $fighter_form_key ?>[<?= $stat ?>]' value='<?= $_POST[$fighter_form_key][$stat] ?? 0 ?>' /><br />
            <?php endforeach; ?>
            <br />

            Bloodline boosts<br />
            <select
                id='bloodline_prefill_<?= $fighter_form_key ?>'
                name='<?= $fighter_form_key ?>_bloodline_id'
                onchange='prefillBloodline("<?= $fighter_form_key ?>")'
            >
                <?php foreach($bloodlines_by_rank as $rank => $bloodlines): ?>
                    <option value='0'>Select to auto-fill boosts</option>
                    <optgroup label="<?= Bloodline::$public_ranks[$rank] ?>">
                        <?php foreach($bloodlines as $bloodline): ?>
                            <option
                                value='<?= $bloodline->bloodline_id ?>'
                                data-boosts='<?= json_encode($bloodline->base_combat_boosts) ?>'
                                <?= (
                                    ($_POST["{$fighter_form_key}_bloodline_id"] ?? '') == $bloodline->bloodline_id
                                        ? "selected='selected'"
                                        : ''
                                ) ?>
                            >
                                <?= $bloodline->name ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select><br />
            <br />
            <?php for($i = 1; $i <= 3; $i++): ?>
                <select
                    id='<?= $fighter_form_key?>_bloodline_boost_<?= $i ?>'
                    name='<?= $fighter_form_key ?>[bloodline_boost_<?= $i ?>]'
                >
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
                    id='<?= $fighter_form_key?>_bloodline_boost_<?= $i ?>_amount'
                    name='<?= $fighter_form_key ?>[bloodline_boost_<?= $i ?>_power]'
                    style='width:60px'
                    value='<?= $_POST[$fighter_form_key]["bloodline_boost_{$i}_power"] ?? 0 ?>'
                />
                <br />
            <?php endfor; ?>
            <br />
            <br />

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
        </div>
        <?php
    }
?>

<form action='vs.php' method='post'>
    <div class='vs_container'>
        <?php displayFighterInput(
            fighter_form_key: 'fighter1',
            bloodline_combat_boosts: $bloodline_combat_boosts,
            bloodlines_by_rank: $bloodlines_by_rank
        ); ?>
        <?php displayFighterInput(
            fighter_form_key: 'fighter2',
            bloodline_combat_boosts: $bloodline_combat_boosts,
            bloodlines_by_rank: $bloodlines_by_rank
        ); ?>
        <input type='submit' name='run_simulation' value='Run Simulation' />
    </div>
</form>

