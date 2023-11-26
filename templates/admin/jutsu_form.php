<?php
    /**
     * @var System  $system
     * @var array   $jutsu_constraints
     * @var Jutsu[] $ALL_JUTSU
     * @var ?Jutsu  $existing_jutsu
     * @var ?string $parent_field_name
     */

    /* Bloodlines don't use hand seals */
    if(!isset($disable_hand_seals)) {
        $disable_hand_seals = false;
    }

    /* Parent field name is being used when this jutsu is one sub-component of a larger entity - such as a bloodline.
    In cases like that, this form is embedded multiple times, once for jutsu[0], jutsu[1], etc. So we need to change all
    field names here from "name" to "jutsu[1][name]" */
    if(!isset($parent_field_name)) {
        $parent_field_name = '';
    }

    $fieldName = function($name) use($parent_field_name) {
        if($parent_field_name) {
            return "{$parent_field_name}[{$name}]";
        }
        else {
            return $name;
        }
    };

    require_once __DIR__ . '/../../classes/RankManager.php';
    $RANK_NAMES = RankManager::fetchNames($system);
?>
<style>
    .jutsuForm label {
        display:inline-block;
        width:120px;
        margin: 2px 0;
    }
</style>

<div class='jutsuForm'>
    <label for="name">Name:</label>
    <input type="text" name="<?= $fieldName("name") ?>" value="<?= $existing_jutsu->name ?? "" ?>"><br />

    <label for="rank">Rank:</label>
    <select name="<?= $fieldName("rank") ?>">
        <?php foreach($RANK_NAMES as $rank_num => $name): ?>
            <option value="<?= $rank_num ?>" <?= ($rank_num == $existing_jutsu?->rank ? "selected" : "") ?>>
                <?= $name ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br />

    <label for="power">Power:</label>
    <input type="number" name="<?= $fieldName("power") ?>" step="0.1" value="<?= $existing_jutsu->base_power ?? 1.0 ?>" min="1.0"><br />

    <label for="range">Range:</label>
    <input type="number" name="<?= $fieldName("range") ?>" step="1" value="<?= $existing_jutsu->range ?? 1 ?>" min="1" max="10"><br />

    <label for="element">Element:</label>
    <select name="<?= $fieldName("element") ?>">
        <?php foreach($jutsu_constraints['element']['options'] as $option): ?>
            <option value="<?= $option ?>" <?= ($option == $existing_jutsu?->element ? "selected" : "") ?>>
                <?= System::unSlug($option) ?>
            </option>
        <?php endforeach; ?>
    </select><br />

    <label for="cooldown">Cooldown:</label>
    <input type="number" name="<?= $fieldName("cooldown") ?>" value="<?= $existing_jutsu->cooldown ?? 0 ?>" min="0"><br />

    <label for="parent_jutsu">Parent Jutsu:</label>
    <select name="<?= $fieldName("parent_jutsu") ?>">
        <option value="0">None</option>
        <?php foreach($ALL_JUTSU as $jutsu): ?>
            <?php if($jutsu->id === $existing_jutsu?->id) continue; ?>
            <?php if($jutsu->purchase_type === Jutsu::PURCHASE_TYPE_DEFAULT) continue; ?>
            <option value="<?= $jutsu->id ?>" <?= ($jutsu->id == $existing_jutsu?->parent_jutsu ? "selected" : "") ?>>
                <?= $jutsu->name ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br />

    <label for="purchase_cost">Purchase Cost:</label>
    <input type="number" name="<?= $fieldName("purchase_cost") ?>" value="<?= $existing_jutsu->purchase_cost ?? 0 ?>" min="0"><br />

    <label for="use_cost">Use Cost:</label>
    <input type="number" name="<?= $fieldName("use_cost") ?>" value="<?= $existing_jutsu->use_cost ?? 5 ?>" min="5"><br />

    <label for="use_type" style="margin-top:5px;">Use Type:</label>
    <select name="<?= $fieldName("use_type") ?>">
        <?php foreach($jutsu_constraints['use_type']['options'] as $option): ?>
            <option value="<?= $option ?>" <?= ($option == $existing_jutsu?->use_type ? "selected" : "") ?>>
                <?= System::unSlug($option) ?>
            </option>
        <?php endforeach; ?>
    </select><br />

    <label for="target_type" style="margin-top:5px;">Target Type:</label>
    <select name="<?= $fieldName("target_type") ?>">
        <?php foreach($jutsu_constraints['target_type']['options'] as $option): ?>
            <option value="<?= $option ?>" <?= ($option == $existing_jutsu?->target_type ? "selected" : "") ?>>
                <?= System::unSlug($option) ?>
            </option>
        <?php endforeach; ?>
    </select><br />

    <label for="jutsu_type" style="margin-top:5px;">Jutsu Type:</label>
    <select name="<?= $fieldName("jutsu_type") ?>">
        <?php foreach($jutsu_constraints['jutsu_type']['options'] as $option): ?>
            <option value="<?= $option ?>" <?= ($option == $existing_jutsu?->jutsu_type ? "selected" : "") ?>>
                <?= System::unSlug($option) ?>
            </option>
        <?php endforeach; ?>
    </select><br />

    <label for="purchase_type" style="margin-top:5px;">Purchase Type:</label>
    <select name="<?= $fieldName("purchase_type") ?>">
        <?php foreach($jutsu_constraints['purchase_type']['options'] as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($key == $existing_jutsu?->purchase_type ? "selected" : "") ?>>
                <?= System::unSlug($label) ?>
            </option>
        <?php endforeach; ?>
    </select><br />

    <label for="effect" style="margin-top:10px;">Effect 1:</label>
    <select name="<?= $fieldName("effect") ?>">
        <?php foreach($jutsu_constraints['effect']['options'] as $option): ?>
            <option value="<?= $option ?>" <?= ($option == $existing_jutsu->effects[0]?->effect ? "selected" : "") ?>>
                <?= System::unSlug($option) ?>
            </option>
        <?php endforeach; ?>
    </select><br />
    <label for="effect_amount">Effect Amount:</label>
    <input type="number" name="<?= $fieldName("effect_amount") ?>" value="<?= $existing_jutsu->effects[0]->effect_amount ?? 0 ?>" min="0" /><br />
    <label for="effect_length">Effect Length:</label>
    <input type="number" name="<?= $fieldName("effect_length") ?>" value="<?= $existing_jutsu->effects[0]->effect_length ?? 0 ?>" min="0" max="10" /><br />

    <label for="effect2" style="margin-top:10px;">Effect 2:</label>
    <select name="<?= $fieldName("effect2") ?>">
        <?php foreach ($jutsu_constraints['effect']['options'] as $option): ?>
            <option value="<?= $option ?>" <?= ($option == $existing_jutsu->effects[1]?->effect ? "selected" : "") ?>>
                <?= System::unSlug($option) ?>
            </option>
        <?php endforeach; ?>
    </select><br />
    <label for="effect2_amount">Effect Amount:</label>
    <input type="number" name="<?= $fieldName("effect2_amount") ?>" value="<?= $existing_jutsu->effects[1]->effect_amount ?? 0 ?>" min="0" /><br />
    <label for="effect2_length">Effect Length:</label>
    <input type="number" name="<?= $fieldName("effect2_length") ?>" value="<?= $existing_jutsu->effects[1]->effect_length ?? 0 ?>" min="0" max="10" /><br />

    <br /><label for="linked_jutsu_id">Linked Jutsu ID:</label>
    <input type="number" name="<?= $fieldName("linked_jutsu_id") ?>" value="<?= $existing_jutsu->linked_jutsu_id ?>" min="0" /><br />
    
    <?php if(!$disable_hand_seals): ?>
        <?php $hand_seal_options = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]; ?>
        <label style='margin-top: 10px;'>Hand Seals:</label>
        <input type='hidden' name="<?= $fieldName("hand_seals") ?>" value="" />
        <div id='hand_seals_container'></div><br />
        <script type='text/javascript'>
            <?php
                $hand_seals = [];
                if($existing_jutsu != null && $existing_jutsu->jutsu_type != Jutsu::TYPE_TAIJUTSU) {
                    $hand_seals = explode("-", $existing_jutsu->hand_seals);
                    $hand_seals = array_map('intval', $hand_seals);
                }
            ?>
            const initialHandSeals = <?= json_encode($hand_seals) ?>;
            const handSealsEl = document.getElementById('hand_seals');
            const handSealsContainer = document.getElementById('hand_seals_container');

            let handSealsSelected = [];

            function handleHandSealChange(event) {
                const inputEl = event.target;
                const inputIndex = parseInt(inputEl.getAttribute("id").split("_")[2]);
                const currentValue = handSealsSelected[inputIndex];
                const newValue = parseInt(event.target.value);

                console.log(inputIndex, currentValue, newValue);

                handSealsSelected[inputIndex] = newValue;

                // Check for adding input
                if(inputIndex === handSealsSelected.length - 1 && newValue !== 0) {
                    console.log('add new input');
                    handSealsContainer.appendChild(
                        createSelectElement(handSealsSelected.length)
                    );
                }

                // Check for removing input
                if(newValue === 0) {
                    handSealsSelected = [
                        ...handSealsSelected.slice(0, inputIndex),
                        ...handSealsSelected.slice(inputIndex + 1)
                    ];
                    handSealsContainer.removeChild(event.target);
                }
            }

            function createSelectElement(index, initialValue = 0) {
                // Options
                const optionEls = [
                    document.createElement('option')
                ];
                optionEls[0].value = "0";
                optionEls[0].innerText = "N/A";

                for(let i = 1; i <= 12; i++) {
                    let optionEl = document.createElement('option');
                    optionEl.value = i;
                    optionEl.innerText = i;
                    if(i === initialValue) {
                        optionEl.selected = true;
                    }

                    optionEls.push(optionEl);
                }

                // Select box
                const selectEl = document.createElement("select");
                selectEl.id = `hand_seals_${index}`;
                selectEl.name = 'hand_seals[]';
                selectEl.addEventListener('change', handleHandSealChange);
                optionEls.forEach(optionEl => {
                    selectEl.appendChild(optionEl)
                });

                return selectEl;
            }

            // Add existing handseals, if any
            initialHandSeals.forEach(handSeal => {
                const index = handSealsSelected.length;
                handSealsSelected[index] = handSeal;
                handSealsContainer.appendChild(
                    createSelectElement(index, handSeal)
                );
            });

            // Add empty input to select a handseal
            const lastIndex = handSealsSelected.length;
            handSealsSelected[lastIndex] = 0;
            handSealsContainer.appendChild(
                createSelectElement(lastIndex)
            );
        </script>
    <?php endif; ?>

    <label for="description">Description:</label><br />
    <textarea name="<?= $fieldName("description") ?>" rows="3" style="width:70%;max-width:500px;"><?= $existing_jutsu?->description ?></textarea><br />

    <label for="battle_text">Battle Text:</label><br />
    <textarea name="<?= $fieldName("battle_text") ?>" rows="3" style="width:70%;max-width:500px;"><?= $existing_jutsu?->battle_text ?></textarea><br />
</div>
