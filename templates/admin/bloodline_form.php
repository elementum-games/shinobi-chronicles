<?php
/**
 * @var System      $system
 * @var ?Bloodline  $existing_bloodline
 * @var array       $bloodline_constraints the allowed constraints for bloodline fields
 * @var string      $form_action_url
 */

 if(!isset($existing_bloodline)) {
     $existing_bloodline = null;
 }
?>
<style>
    .bloodline-form label {
        display: inline-block;
        min-width: 130px;
    }

    .boost-input-container {
        margin-top: 2px;
        display: flex;
        flex-direction: row;
    }

    .boost-input {
        margin-left: 20px;
        background: rgba(0,0,0,0.1);
        padding: 5px;
        border-radius: 5px;
    }
    .boost-input label {
        min-width: 100px;
    }
    .boost-input input[type="number"] {
        width: 140px;
        box-sizing:border-box;
    }
    .boost-input select {
        width: 140px;
    }

    .jutsu-container {
        margin: 5px auto;
        padding: 5px;
        background: rgba(0,0,0,0.1);
        border-radius: 5px;
    }
</style>
<table class='table bloodline-form'>
    <?php if($existing_bloodline != null): ?>
        <tr><th>Edit Bloodline (<?= stripslashes($existing_bloodline->name) ?>)</th></tr>
    <?php else: ?>
        <tr><th>Create Bloodline</th></tr>
    <?php endif; ?>
    <tr><td>
        <form action='<?= $form_action_url ?>' method='post'>
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?= $existing_bloodline?->name ?? "" ?>"><br>

            <label for="clan_id">Clan Id:</label>
            <input type="text" name="clan_id" value="<?= $existing_bloodline?->clan_id ?? 0 ?>"><br>

            <label for="rank" style="margin-top:5px;">Rank:</label>
            <select name="rank">
                <?php foreach($bloodline_constraints['rank']['options'] as $value => $label): ?>
                    <option
                        name="rank"
                        value="<?= $value ?>"
                        <?= $existing_bloodline?->rank == $value ? "selected='selected'" : "" ?>
                    ><?= $label ?></option>
                <?php endforeach; ?>
            </select><br />

            <label for="village" style="margin-top:5px;">Village:</label>
            <select name="village">
                <?php foreach($bloodline_constraints['village']['options'] as $value): ?>
                    <option
                        name="village"
                        value="<?= $value ?>"
                        <?= $existing_bloodline?->village == $value ? "selected='selected'" : "" ?>
                    ><?= $value ?></option>
                <?php endforeach; ?>
            </select><br />

            <label for="passive_boosts" style='margin-top:5px;'>Passive Boosts:</label>
            <div class='boost-input-container'>
                <?php for($i = 0; $i < $bloodline_constraints['passive_boosts']['count']; $i++): ?>
                    <div id="passive_boosts_<?= $i ?>" class='boost-input'>
                        <label for="passive_boosts[<?= $i ?>][power]">Power:</label>
                        <input
                            type="number"
                            name="passive_boosts[<?= $i ?>][power]"
                            value="<?= $existing_bloodline?->passive_boosts[$i]->power ?? 0 ?>"
                        >
                        <br />

                        <label for="passive_boosts[<?= $i ?>][effect]" style="margin-top:5px;">Effect:</label>
                        <select name="passive_boosts[<?= $i ?>][effect]">
                            <option value="none">None</option>
                            <?php foreach($bloodline_constraints['passive_boosts']['variables']['effect']['options'] as $value): ?>
                                <option
                                    value="<?= $value ?>"
                                    <?= ($existing_bloodline?->passive_boosts[$i]->effect ?? "") == $value ? "selected='selected'" : "" ?>
                                ><?= System::unSlug($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>

            <label for="combat_boosts" style='margin-top:5px;'>Combat Boosts:</label>
            <div class='boost-input-container'>
                <?php for($i = 0; $i < $bloodline_constraints['combat_boosts']['count']; $i++): ?>
                    <div id="combat_boosts_<?= $i ?>" class='boost-input'>
                        <label for="combat_boosts[<?= $i ?>][power]">Power:</label>
                        <input
                            type="number"
                            step="0.1"
                            name="combat_boosts[<?= $i ?>][power]"
                            value="<?= $existing_bloodline?->combat_boosts[$i]->power ?? 0 ?>"
                        >
                        <br />

                        <label for="combat_boosts[<?= $i ?>][effect]" style="margin-top:5px;">Effect:</label>
                        <select name="combat_boosts[<?= $i ?>][effect]">
                            <option value="none">None</option>
                            <?php foreach($bloodline_constraints['combat_boosts']['variables']['effect']['options'] as $value): ?>
                                <option
                                    value="<?= $value ?>"
                                    <?= ($existing_bloodline?->combat_boosts[$i]->effect ?? "") == $value ? "selected='selected'" : "" ?>
                                ><?= System::unSlug($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>
            <label for="jutsu">Jutsu:</label><i>(1 required)</i>
            <div style="margin-left:15px;margin-top:0;">
                <?php for($i = 0; $i < $bloodline_constraints['jutsu']['count']; $i++): ?>
                    <span style="display:block;margin-top:10px;font-weight:bold;">#<?= ($i + 1) ?>:
                        <button onclick="$('#jutsu_<?= $i ?>').toggle();return false;">Show/Hide</button>
                    </span>

                    <div id="jutsu_<?= $i ?>" class='jutsu-container' style="margin-left: 5px;">
                        <?php
                        $parent_field_name = "jutsu[{$i}]";
                        $existing_jutsu = $existing_bloodline?->jutsu[$i] ?? null;
                        $disable_hand_seals = true;
                        require 'templates/admin/jutsu_form.php';
                        ?>
                    </div>
                <?php endfor; ?>
            </div>
            <input type="submit" name="bloodline_data" value="<?= $existing_bloodline != null ? "Edit" : "Create" ?>">
        </form>
    </td></tr>
</table>
