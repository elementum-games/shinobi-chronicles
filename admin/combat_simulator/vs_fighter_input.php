<style>
    .versusFighterInput {
        width:300px;
        display:inline-block;
        border:1px solid #000000;
        border-radius:10px;

        text-align: left;

        padding: 10px;

        background-color: #f2f2f2;
    }
    .versusFighterInput label {
        width: 110px;
        margin: 2px auto;
        display: inline-block;
    }
    .jutsu_input label {
        width: 90px;
        margin: 3px auto;
    }

    .jutsu_input {
        margin: 5px auto;
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

    function prefillJutsu(fighterKey) {
        let jutsuSelect = document.getElementById(`${fighterKey}_jutsu_prefill`);
        if(jutsuSelect == null) {
            console.error("Invalid jutsu select element");
            return;
        }

        let selectedJutsuEl = jutsuSelect.selectedOptions[0];
        if(parseInt(selectedJutsuEl.value) === 0) {
            console.warn("No jutsu selected!");
            return;
        }

        let selectedJutsu = JSON.parse(selectedJutsuEl.getAttribute("data-jutsu"));

        console.log(selectedJutsu);
        
        let typeEl = document.getElementById(`${fighterKey}_jutsu_type`);
        let powerEl = document.getElementById(`${fighterKey}_jutsu_power`);

        typeEl.value = selectedJutsu.jutsu_type;
        powerEl.value = selectedJutsu.base_power;
    }
</script>

<?php

/**
 * @param System $system
 * @param string $fighter_form_key
 * @return void
 */
function displayFighterInput(System $system, string $fighter_form_key): void {
    /** @var string[] $bloodline_combat_boosts */
    require __DIR__ . '/../constraints/bloodline.php';

    $bloodlines_by_rank = [];
    /** @var Jutsu[][] $jutsu_by_group */
    $jutsu_by_group = [];

    $rank_names = RankManager::fetchNames($system);

    try {
        $result = $system->db->query("SELECT * FROM `bloodlines` WHERE `rank` < 5 ORDER BY `rank` ASC");
        while ($row = $system->db->fetch($result)) {
            $bloodlines_by_rank[$row['rank']][$row['bloodline_id']] = Bloodline::fromArray($row);
        }

        $result = $system->db->query("SELECT * FROM `jutsu` 
             WHERE `purchase_type` != " . Jutsu::PURCHASE_TYPE_NON_PURCHASABLE . " 
             ORDER BY `rank` DESC, `purchase_cost` DESC"
        );
        while($row = $system->db->fetch($result)) {
            $jutsu = Jutsu::fromArray($row['jutsu_id'], $row);
            $group = $rank_names[$jutsu->rank] . " " . ucwords($jutsu->jutsu_type);

            if(!isset($jutsu_by_group[$group])) {
                $jutsu_by_group[$group] = [];
            }

            $jutsu_by_group[$group][] = $jutsu;
        }
    } catch(RuntimeException|DatabaseDeadlockException $e) {
        echo $e->getMessage();
        return;
    }

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
        <b><?= ucwords($fighter_form_key) ?></b><br />
        <?php foreach($stats as $stat): ?>
            <label><?= $stat ?>:</label>
            <input type='number' step='10' name='<?= $fighter_form_key ?>[<?= $stat ?>]' value='<?= $_POST[$fighter_form_key][$stat] ?? 0 ?>' /><br />
        <?php endforeach; ?>
        <br />

        Bloodline boosts<br />
        <select
            id='bloodline_prefill_<?= $fighter_form_key ?>'
            name='<?= $fighter_form_key ?>_bloodline_id'
            onchange='prefillBloodline("<?= $fighter_form_key ?>")'
        >
            <option value='0'>Select to auto-fill boosts</option>
            <?php foreach($bloodlines_by_rank as $rank => $bloodlines): ?>
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
        </select>
        <p class='bloodline_boosts' style='margin-top:8px;'>
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
        </p>

        <div class='jutsu_input'>
            <b>Jutsu</b><br />
            <select
                id='<?= $fighter_form_key ?>_jutsu_prefill'
                name='<?= $fighter_form_key ?>_jutsu_id'
                onchange='prefillJutsu("<?= $fighter_form_key ?>")'
                style='margin: 2px auto 6px'
            >
                <option value='0'>Select to auto-fill jutsu</option>
                <?php foreach($jutsu_by_group as $group => $jutsu_list): ?>
                    <optgroup label="<?= $group ?>">
                        <?php foreach($jutsu_list as $jutsu): ?>
                            <option
                                value='<?= $jutsu->id ?>'
                                data-jutsu='<?= json_encode($jutsu) ?>'
                                <?= (($_POST["{$fighter_form_key}_jutsu_id"] ?? '') == $jutsu->id ? "selected='selected'" : '') ?>
                            >
                                <?= $jutsu->name ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select><br />

            <label>Base Power:</label>
            <input
                type='number'
                step='0.1'
                id='<?= $fighter_form_key ?>_jutsu_power'
                name='<?= $fighter_form_key ?>[jutsu_power]'
                value='<?= $_POST[$fighter_form_key]['jutsu_power'] ?? 4.0 ?>'
                style='width:70px;'
            /><br />

            <label>Offense:</label>
            <select id='<?= $fighter_form_key ?>_jutsu_type' name='<?= $fighter_form_key ?>[jutsu_type]'>
                <option value='ninjutsu' <?= (($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'ninjutsu' ? "selected='selected'" : '') ?>>
                    Ninjutsu
                </option>
                <option value='taijutsu' <?= (($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'taijutsu' ? "selected='selected'" : '') ?>>
                    Taijutsu
                </option>
                <option value='genjutsu' <?= (($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'genjutsu' ? "selected='selected'" : '') ?>>
                    Genjutsu
                </option>
            </select><br />

        </div>
    </div>
    <?php
}
?>
