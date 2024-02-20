<style>
    .versusFighterInput {
        width:325px;
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
        width: 125px;
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
    const statCap = 250000;

    function prefillStats(fighterKey) {
        let statsPresetSelect = document.getElementById(`${fighterKey}_stats_preset`);
        if(statsPresetSelect == null) {
            console.error("Invalid stats select element");
            return;
        }

        let selectedStatsPreset = statsPresetSelect.selectedOptions[0];
        if(selectedStatsPreset.value.length < 1) {
            console.warn("No stats preset selected!");
            return;
        }

        let [offenseType, offSkill, blSkill, speed] = selectedStatsPreset.value.split('_');
        offSkill = parseInt(offSkill) / 100;
        blSkill = parseInt(blSkill) / 100;
        speed = parseInt(speed) / 100;

        switch(offenseType) {
            case 'nin':
                document.getElementById(`${fighterKey}_ninjutsu_skill`).value = offSkill * statCap;
                document.getElementById(`${fighterKey}_taijutsu_skill`).value = 0;
                document.getElementById(`${fighterKey}_genjutsu_skill`).value = 0;
                document.getElementById(`${fighterKey}_bloodline_skill`).value = blSkill * statCap;
                document.getElementById(`${fighterKey}_speed`).value = 0;
                document.getElementById(`${fighterKey}_cast_speed`).value = speed * statCap;

                document.getElementById(`${fighterKey}_jutsu1_type`).value = 'ninjutsu';
                break;
            case 'tai':
                document.getElementById(`${fighterKey}_ninjutsu_skill`).value = 0;
                document.getElementById(`${fighterKey}_taijutsu_skill`).value = offSkill * statCap;
                document.getElementById(`${fighterKey}_genjutsu_skill`).value = 0;
                document.getElementById(`${fighterKey}_bloodline_skill`).value = blSkill * statCap;
                document.getElementById(`${fighterKey}_speed`).value = speed * statCap;
                document.getElementById(`${fighterKey}_cast_speed`).value = 0;

                document.getElementById(`${fighterKey}_jutsu1_type`).value = 'taijutsu';
                break;
            case 'gen':
                document.getElementById(`${fighterKey}_ninjutsu_skill`).value = 0;
                document.getElementById(`${fighterKey}_genjutsu_skill`).value = offSkill * statCap;
                document.getElementById(`${fighterKey}_taijutsu_skill`).value = 0;
                document.getElementById(`${fighterKey}_bloodline_skill`).value = blSkill * statCap;
                document.getElementById(`${fighterKey}_speed`).value = 0;
                document.getElementById(`${fighterKey}_cast_speed`).value = speed * statCap;

                document.getElementById(`${fighterKey}_jutsu1_type`).value = 'genjutsu';
                break;
            default:
                console.warn('invalid offense type!');
        }
    }

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

            boostEl.value = boost.effect;
            boostAmountEl.value = boost.power;
        });
    }

    function prefillJutsu(fighterKey, jutsuKey) {
        let jutsuSelect = document.getElementById(`${fighterKey}_${jutsuKey}_prefill`);
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
        
        let typeEl = document.getElementById(`${fighterKey}_${jutsuKey}_type`);
        let powerEl = document.getElementById(`${fighterKey}_${jutsuKey}_power`);
        let elementEl = document.getElementById(`${fighterKey}_${jutsuKey}_element`);

        let effectEl = document.getElementById(`${fighterKey}_${jutsuKey}_effect`);
        let effectAmountEl = document.getElementById(`${fighterKey}_${jutsuKey}_effect_amount`);
        let effectLengthEl = document.getElementById(`${fighterKey}_${jutsuKey}_effect_length`);

        let effect2El = document.getElementById(`${fighterKey}_${jutsuKey}_effect2`);
        let effect2AmountEl = document.getElementById(`${fighterKey}_${jutsuKey}_effect2_amount`);
        let effect2LengthEl = document.getElementById(`${fighterKey}_${jutsuKey}_effect2_length`);

        typeEl.value = selectedJutsu.jutsu_type;
        powerEl.value = selectedJutsu.base_power;
        elementEl.value = selectedJutsu.element;

        effectEl.value = selectedJutsu.effects[0].effect;
        effectAmountEl.value = selectedJutsu.effects[0].effect_amount;
        effectLengthEl.value = selectedJutsu.effects[0].effect_length;

        effect2El.value = selectedJutsu.effects[1].effect;
        effect2AmountEl.value = selectedJutsu.effects[1].effect_amount;
        effect2LengthEl.value = selectedJutsu.effects[1].effect_length;
    }
</script>

<?php

function selected($condition): string {
    return $condition ? "selected='selected'" : '';
}

/**
 * @param string $fighter_form_key
 * @param int    $jutsu_index
 * @param array  $FORM_DATA
 * @param Jutsu[]  $jutsu_by_group
 * @return void
 */
function displayJutsuInput(string $fighter_form_key, int $jutsu_index, array $FORM_DATA, array $jutsu_by_group) {
    $jutsu_form_key = "jutsu{$jutsu_index}";
    $jutsu_form_data = $FORM_DATA[$fighter_form_key][$jutsu_form_key];

    ?>
    <div class='jutsu_input'>
        <select
            id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_prefill'
            name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][id]'
            onchange='prefillJutsu("<?= $fighter_form_key ?>", "<?= $jutsu_form_key ?>")'
            style='margin: 2px auto 6px'
        >
            <option value='0'>Select to auto-fill jutsu</option>
            <?php foreach($jutsu_by_group as $group => $jutsu_list): ?>
                <optgroup label="<?= $group ?>">
                    <?php foreach($jutsu_list as $jutsu): ?>
                        <option
                            value='<?= $jutsu->id ?>'
                            data-jutsu='<?= json_encode($jutsu) ?>'
                            <?= selected(($jutsu_form_data['id']) == $jutsu->id) ?>
                        >
                            <?= $jutsu->name ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select><br />

        <label>Offense:</label>
        <select
            id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_type'
            name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][type]'
        >
            <option value='ninjutsu' <?= selected(($jutsu_form_data['type']) == 'ninjutsu') ?>>
                Ninjutsu
            </option>
            <option value='taijutsu' <?= selected(($jutsu_form_data['type']) == 'taijutsu') ?>>
                Taijutsu
            </option>
            <option value='genjutsu' <?= selected(($jutsu_form_data['type']) == 'genjutsu') ?>>
                Genjutsu
            </option>
        </select><br />

        <label>Base Power:</label>
        <input
            type='number'
            step='0.05'
            id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_power'
            name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][power]'
            value='<?= $jutsu_form_data['power'] ?>'
            style='width:70px;'
        /><br />

        <label>Element:</label>
        <select
            id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_element'
            name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][element]'
        >
            <?php foreach(Jutsu::$elements as $element): ?>
                <option
                    value='<?= $element ?>'
                    <?= selected(($jutsu_form_data['element']) == $element) ?>
                >
                    <?= System::unSlug($element) ?>
                </option>
            <?php endforeach; ?>
        </select><br />

        <div class='effect_input'>
            <label>Effect</label>
            <select
                id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_effect'
                name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][effect]'
            >
                <optgroup label="Damage">
                    <?php foreach(BattleEffectsManager::DAMAGE_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Clash">
                    <?php foreach(BattleEffectsManager::CLASH_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Buff">
                    <?php foreach(BattleEffectsManager::BUFF_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Debuff">
                    <?php foreach(BattleEffectsManager::DEBUFF_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            </select>

            <label>Effect Amount:</label>
            <input
                type='number'
                id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_effect_amount'
                name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][effect_amount]'
                value='<?= $jutsu_form_data['effect_amount'] ?>'
            />

            <label>Effect Length:</label>
            <input
                type='number'
                id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_effect_length'
                name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][effect_length]'
                value='<?= $jutsu_form_data['effect_length'] ?>'
            />
        </div>
        <div class='effect_input'>
            <label>Effect 2</label>
            <select
                    id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_effect2'
                    name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][effect2]'
            >
                <optgroup label="Damage">
                    <?php foreach(BattleEffectsManager::DAMAGE_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect2'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Clash">
                    <?php foreach(BattleEffectsManager::CLASH_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect2'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Buff">
                    <?php foreach(BattleEffectsManager::BUFF_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect2'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Debuff">
                    <?php foreach(BattleEffectsManager::DEBUFF_EFFECTS as $effect): ?>
                        <option value='<?= $effect ?>' <?= selected($jutsu_form_data['effect2'] == $effect) ?>>
                            <?= System::unSlug($effect) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            </select>

            <label>Effect 2 Amount:</label>
            <input
                type='number'
                id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_effect2_amount'
                name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][effect2_amount]'
                value='<?= $jutsu_form_data['effect2_amount'] ?>'
            />

            <label>Effect 2 Length:</label>
            <input
                type='number'
                id='<?= $fighter_form_key ?>_<?= $jutsu_form_key ?>_effect2_length'
                name='<?= $fighter_form_key ?>[<?= $jutsu_form_key ?>][effect2_length]'
                value='<?= $jutsu_form_data['effect2_length'] ?>'
            />
        </div>
    </div>

    <?php
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

    $num_bloodline_boosts = 3;
    $num_active_effects = 3;

    $default_values = [
        'jutsu1' => [
            'id' => 0,
            'type' => 'ninjutsu',
            'power' => 4,
            'element' => Jutsu::ELEMENT_NONE,
            'effect' => 'none',
            'effect_amount' => 0,
            'effect_length' => 0,
            'effect2' => 'none',
            'effect2_amount' => 0,
            'effect2_length' => 0,
        ],
        'bloodline_id' => 0,
        'active_effects' => [],
    ];
    foreach($stats as $stat) {
        $default_values[$stat] = 0;
    }
    for($i = 1; $i <= $num_bloodline_boosts; $i++) {
        $default_values["bloodline_boost_{$i}"] = 'none';
        $default_values["bloodline_boost_{$i}_power"] = 0;
    }
    for($i = 1; $i <= $num_active_effects; $i++) {
        $default_values['active_effects'][$i] = [
            'effect' => 'none',
            'amount' => 0,
        ];
    }

    $FORM_DATA = $_POST;
    if(empty($FORM_DATA['fighter1'])) {
        $FORM_DATA['fighter1'] = $default_values;
        $FORM_DATA['fighter1_stats_preset'] = '';
    }
    if(empty($FORM_DATA['fighter2'])) {
        $FORM_DATA['fighter2'] = $default_values;
        $FORM_DATA['fighter2_stats_preset'] = '';
    }

    $stat_preset_options = [
        '20_20_60',
        '33_33_33',
        '40_40_20',
        '50_50_0',
        '0_85_15',
        '0_100_0',
    ];

    ?>
    <div class='versusFighterInput' style='margin-left: 20px;'>
        <b><?= ucwords($fighter_form_key) ?></b>
        <select
            id='<?= $fighter_form_key ?>_stats_preset'
            name='<?= $fighter_form_key ?>_stats_preset'
            style='display:inline-block;margin-left:21px;margin-bottom:12px;'
            onchange='prefillStats("<?= $fighter_form_key ?>")'
        >
            <option>Pre-fill stats (Off/BL/Speed)</option>
            <optgroup label="Jonin Ninjutsu">
                <?php foreach($stat_preset_options as $option): ?>
                    <option
                        value='nin_<?= $option ?>'
                        <?= selected($FORM_DATA["{$fighter_form_key}_stats_preset"] == "nin_{$option}") ?>
                    >
                        Nin <?= str_replace("_", "/", $option)?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Jonin Taijutsu">
                <?php foreach($stat_preset_options as $option): ?>
                    <option
                        value='tai_<?= $option ?>'
                        <?= selected($FORM_DATA["{$fighter_form_key}_stats_preset"] == "tai_{$option}") ?>
                    >
                        Tai <?= str_replace("_", "/", $option)?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Jonin Genjutsu">
                <?php foreach($stat_preset_options as $option): ?>
                    <option
                        value='gen_<?= $option ?>'
                        <?= selected($FORM_DATA["{$fighter_form_key}_stats_preset"] == "gen_{$option}") ?>
                    >
                        Gen <?= str_replace("_", "/", $option)?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        </select>
        <br />
        <?php foreach($stats as $stat): ?>
            <label><?= $stat ?>:</label>
            <input
                type='number'
                step='10'
                id='<?= $fighter_form_key ?>_<?= $stat ?>'
                name='<?= $fighter_form_key ?>[<?= $stat ?>]'
                value='<?= $FORM_DATA[$fighter_form_key][$stat] ?>'
            /><br />
        <?php endforeach; ?>
        <br />

        <b>Bloodline boosts</b><br />
        <select
            id='bloodline_prefill_<?= $fighter_form_key ?>'
            name='<?= $fighter_form_key ?>[bloodline_id]'
            onchange='prefillBloodline("<?= $fighter_form_key ?>")'
        >
            <option value='0'>Select to auto-fill boosts</option>
            <?php foreach($bloodlines_by_rank as $rank => $bloodlines): ?>
                <optgroup label="<?= Bloodline::$public_ranks[$rank] ?>">
                    <?php foreach($bloodlines as $bloodline): ?>
                        <option
                            value='<?= $bloodline->bloodline_id ?>'
                            data-boosts='<?= json_encode($bloodline->base_combat_boosts) ?>'
                            <?= selected(($FORM_DATA[$fighter_form_key]['bloodline_id']) == $bloodline->bloodline_id) ?>
                        >
                            <?= $bloodline->name ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <p class='bloodline_boosts' style='margin-top:8px;'>
            <?php for($i = 1; $i <= $num_bloodline_boosts; $i++): ?>
                <select
                    id='<?= $fighter_form_key?>_bloodline_boost_<?= $i ?>'
                    name='<?= $fighter_form_key ?>[bloodline_boost_<?= $i ?>]'
                >
                    <option value='none'>None</option>
                    <?php foreach($bloodline_combat_boosts as $boost): ?>
                        <option value='<?= $boost ?>'
                            <?= selected(($FORM_DATA[$fighter_form_key]["bloodline_boost_{$i}"]) == $boost) ?>
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
                    value='<?= $FORM_DATA[$fighter_form_key]["bloodline_boost_{$i}_power"] ?>'
                />
                <br />
            <?php endfor; ?>
        </p>

        <b>Active Effects</b><br />
        <div class='active_effects_input'>
            <?php for($i = 1; $i <= $num_active_effects; $i++): ?>
                <div style='margin:4px auto;'>
                    <label>Effect <?= $i ?></label>
                    <select
                        id='<?= $fighter_form_key ?>_active_effect_<?= $i ?>'
                        name='<?= $fighter_form_key ?>[active_effects][<?= $i ?>][effect]'
                    >
                        <optgroup label="Damage">
                            <?php foreach(BattleEffectsManager::DAMAGE_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($FORM_DATA[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Clash">
                            <?php foreach(BattleEffectsManager::CLASH_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($FORM_DATA[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Buff">
                            <?php foreach(BattleEffectsManager::BUFF_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($FORM_DATA[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
                                    <?= System::unSlug($effect) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Debuff">
                            <?php foreach(BattleEffectsManager::DEBUFF_EFFECTS as $effect): ?>
                                <option value='<?= $effect ?>' <?= selected($FORM_DATA[$fighter_form_key]['active_effects'][$i]['effect'] == $effect) ?>>
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
                        value='<?= $FORM_DATA[$fighter_form_key]['active_effects'][$i]['amount'] ?>'
                    />
                </div>
            <?php endfor; ?>
        </div>

        <b>Jutsu</b><br />
        <?php
            displayJutsuInput(
                fighter_form_key: $fighter_form_key,
                jutsu_index: 1,
                FORM_DATA: $FORM_DATA,
                jutsu_by_group: $jutsu_by_group
            );
        ?>
    </div>
    <?php
}
?>
