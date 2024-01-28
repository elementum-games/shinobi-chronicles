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
        width: 110px;
        margin: 3px auto;
    }
    .active_effects_input label {
        width: 130px;
    }

    .active_effects_input {
        margin-bottom: 10px;
    }

    .jutsu_input {
        margin: 1px auto 5px;
        padding: 3px;
        background: rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .effect_input {
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
        let effectEl = document.getElementById(`${fighterKey}_jutsu_effect`);
        let effectAmountEl = document.getElementById(`${fighterKey}_jutsu_effect_amount`);
        let effectLengthEl = document.getElementById(`${fighterKey}_jutsu_effect_length`);

        typeEl.value = selectedJutsu.jutsu_type;
        powerEl.value = selectedJutsu.base_power;

        effectEl.value = selectedJutsu.effect;
        effectAmountEl.value = selectedJutsu.effect_amount;
        effectLengthEl.value = selectedJutsu.effect_length;
    }
</script>

<?php

function selected($condition): string {
    return $condition ? "selected='selected'" : '';
}

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

        <b>Bloodline boosts</b><br />
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
                            <?= selected(($_POST["{$fighter_form_key}_bloodline_id"] ?? '') == $bloodline->bloodline_id) ?>
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
                            <?= selected(($_POST[$fighter_form_key]["bloodline_boost_{$i}"] ?? '') == $boost) ?>
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

        <b>Active Effects</b><br />
        <div class='active_effects_input'>
            <?php for($i = 1; $i <= 3; $i++): ?>
                <div style='margin:4px auto;'>
                    <label>Effect <?= $i ?></label>
                    <select id='<?= $fighter_form_key ?>_active_effect_<?= $i ?>' name='<?= $fighter_form_key ?>[active_effects][<?= $i ?>][effect]'>
                        <optgroup label="Damage">
                            <?php foreach(BattleEffectsManager::DAMAGE_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Clash">
                            <?php foreach(BattleEffectsManager::CLASH_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Buff">
                            <?php foreach(BattleEffectsManager::BUFF_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Debuff">
                            <?php foreach(BattleEffectsManager::DEBUFF_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select><br />

                    <label>Effect <?= $i ?> Amount:</label>
                    <input
                        type='number'
                        id='<?= $fighter_form_key ?>_active_effect_<?= $i ?>_amount'
                        name='<?= $fighter_form_key ?>[active_effects][<?= $i ?>][amount]'
                        value='<?= $_POST[$fighter_form_key]['active_effects'][$i]['amount'] ?? 0 ?>'
                    />
                </div>
            <?php endfor; ?>
        </div>

        <b>Jutsu</b><br />
        <div class='jutsu_input'>
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
                                <?= selected(($_POST["{$fighter_form_key}_jutsu_id"] ?? '') == $jutsu->id) ?>
                            >
                                <?= $jutsu->name ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select><br />

            <label>Offense:</label>
            <select id='<?= $fighter_form_key ?>_jutsu_type' name='<?= $fighter_form_key ?>[jutsu_type]'>
                <option value='ninjutsu' <?= selected(($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'ninjutsu') ?>>
                    Ninjutsu
                </option>
                <option value='taijutsu' <?= selected(($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'taijutsu') ?>>
                    Taijutsu
                </option>
                <option value='genjutsu' <?= selected(($_POST[$fighter_form_key]['jutsu_type'] ?? '') == 'genjutsu') ?>>
                    Genjutsu
                </option>
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

            <div class='effect_input'>
                <label>Effect</label>
                <select id='<?= $fighter_form_key ?>_jutsu_effect' name='<?= $fighter_form_key ?>[jutsu_effect]'>
                    <optgroup label="Damage">
                        <?php foreach(BattleEffectsManager::DAMAGE_EFFECTS as $effect): ?>
                            <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['jutsu_effect'] == $effect) ?>>
                                <?= System::unSlug($effect) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Clash">
                        <?php foreach(BattleEffectsManager::CLASH_EFFECTS as $effect): ?>
                            <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['jutsu_effect'] == $effect) ?>>
                                <?= System::unSlug($effect) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Buff">
                        <?php foreach(BattleEffectsManager::BUFF_EFFECTS as $effect): ?>
                            <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['jutsu_effect'] == $effect) ?>>
                                <?= System::unSlug($effect) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Debuff">
                        <?php foreach(BattleEffectsManager::DEBUFF_EFFECTS as $effect): ?>
                            <option value='<?= $effect ?>' <?= selected($_POST[$fighter_form_key]['jutsu_effect'] == $effect) ?>>
                                <?= System::unSlug($effect) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>

                <label>Effect Amount:</label>
                <input
                    type='number'
                    id='<?= $fighter_form_key ?>_jutsu_effect_amount'
                    name='<?= $fighter_form_key ?>[jutsu_effect_amount]'
                    value='<?= $_POST[$fighter_form_key]['jutsu_effect_amount'] ?? 0 ?>'
                />

                <label>Effect Length:</label>
                <input
                    type='number'
                    id='<?= $fighter_form_key ?>_jutsu_effect_length'
                    name='<?= $fighter_form_key ?>[jutsu_effect_length]'
                    value='<?= $_POST[$fighter_form_key]['jutsu_effect_length'] ?? 0 ?>'
                />
            </div>
        </div>
    </div>
    <?php
}
?>
