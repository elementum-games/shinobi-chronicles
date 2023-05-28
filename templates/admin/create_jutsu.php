<?php
/**
 * @var System $system
 * @var array $jutsu_constraints
 * @var array $RANK_NAMES
 * @var Jutsu[] $ALL_JUTSU
 */
?>

<style>
    label {
        display:inline-block;
        width:120px;
        margin: 2px 0;
    }
</style>

<table class='table'>
    <tr><th>Create Jutsu</th></tr>
    <tr><td>
        <form action="<?= $system->router->links['admin']?>&page=create_jutsu" method="post">
            <label for="name">Name:</label>
            <input type="text" name="name" value=""><br />

            <label for="rank">Rank:</label>
            <select name="rank">
                <?php foreach($RANK_NAMES as $rank_num => $name): ?>
                    <option value="<?= $rank_num ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            <br />

            <label for="power">Power:</label>
            <input type="number" name="power" step="0.1" value="1.0" min="1.0"><br />

            <label for="element">Element:</label>
            <select name="element">
                <?php foreach($jutsu_constraints['element']['options'] as $option): ?>
                    <option name="element" value="<?= $option ?>"><?= System::unSlug($option) ?></option>
                <?php endforeach; ?>
            </select><br />

            <label for="cooldown">Cooldown:</label>
            <input type="number" name="cooldown" value="0" min="0"><br />

            <label for="parent_jutsu">Parent Jutsu:</label>
            <select name="parent_jutsu">
                <option value="0">None</option>
                <?php foreach($ALL_JUTSU as $jutsu): ?>
                    <option value="<?= $jutsu->id ?>"><?= $jutsu->name ?></option>
                <?php endforeach; ?>
            </select>
            <br />

            <label for="purchase_cost">Purchase Cost:</label>
            <input type="number" name="purchase_cost" value="0" min="0"><br />

            <label for="use_cost">Use Cost:</label>
            <input type="number" name="use_cost" value="5" min="5"><br />

            <label for="use_type" style="margin-top:5px;">Use Type:</label>
            <select name="use_type">
                <?php foreach($jutsu_constraints['use_type']['options'] as $option): ?>
                    <option name="use_type" value="<?= $option ?>"><?= System::unSlug($option) ?></option>
                <?php endforeach; ?>
           </select><br />

            <label for="target_type" style="margin-top:5px;">Target Type:</label>
            <select name="target_type">
                <?php foreach($jutsu_constraints['target_type']['options'] as $option): ?>
                    <option name="target_type" value="<?= $option ?>"><?= System::unSlug($option) ?></option>
                <?php endforeach; ?>
            </select><br />

            <label for="jutsu_type" style="margin-top:5px;">Jutsu Type:</label>
            <select name="jutsu_type">
                <?php foreach($jutsu_constraints['jutsu_type']['options'] as $option): ?>
                    <option name="jutsu_type" value="<?= $option ?>"><?= System::unSlug($option) ?></option>
                <?php endforeach; ?>
            </select><br />

            <label for="purchase_type" style="margin-top:5px;">Purchase Type:</label>
            <select name="purchase_type">
                <?php foreach($jutsu_constraints['purchase_type']['options'] as $key => $label): ?>
                    <option name="jutsu_type" value="<?= $key ?>"><?= System::unSlug($label) ?></option>
                <?php endforeach; ?>
            </select><br />

            <label for="effect" style="margin-top:10px;">Effect:</label>
            <select name="effect">
                <?php foreach($jutsu_constraints['effect']['options'] as $option): ?>
                    <option name="effect" value="<?= $option ?>"><?= System::unSlug($option) ?></option>
                <?php endforeach; ?>
            </select><br />
            <label for="effect_amount">Effect Amount:</label>
            <input type="number" name="effect_amount" value="0" min="0"><br />
            <label for="effect_length">Effect Length:</label>
            <input type="number" name="effect_length" value="0" min="0" max="10"><br />

            <?php $hand_seal_options = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]; ?>
            <label style='margin-top: 10px;'>Hand Seals:</label>
            <input type='hidden' name="hand_seals" value="" />
            <div id='hand_seals_container'>
                <select name='hand_seals[]' id="hand_seals_0">
                    <option value='0'>N/A</option>
                    <?php foreach($hand_seal_options as $option): ?>
                        <option value='<?= $option ?>'><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </div><br />
            <script type='text/javascript'>
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
                }

                function createSelectElement(index) {
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

                document.getElementById('hand_seals_0').addEventListener('change', handleHandSealChange);
            </script>

            <label for="description">Description:</label><br />
            <textarea name="description" rows="3" style="width:70%;max-width:500px;"></textarea><br />

            <label for="battle_text">Battle Text:</label><br />
            <textarea name="battle_text" rows="3" style="width:70%;max-width:500px;"></textarea><br />

            <p style='text-align:center;'>
                <input type="submit" name="jutsu_data" value="Create">
            </p>
        </form>
    </td></tr>
</table>
