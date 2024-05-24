<?php
/**
 * @var System $system
 * @var array $jutsu_constraints
 * @var Jutsu[] $ALL_JUTSU
 * @var Jutsu $jutsu
 */

 require_once __DIR__ . '/../../classes/RankManager.php';
 $RANK_NAMES = RankManager::fetchNames($system);
?>

<p style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
    <a href='<?= $system->routerV2->generateRoute("admin", ["action" => "edit_jutsu"]) ?>' style='font-size:14px;'>Back to jutsu list</a>
</p>
<table class='table'>
    <tr><th>Edit Jutsu (<?= stripslashes($jutsu->name) ?>)</th></tr>
    <tr><td>
        <form
            action='<?= $system->routerV2->current_route ?>'
            method='post'
        >
        <label>Jutsu ID:</label> <?= $jutsu->id ?><br />

        <?php require 'templates/admin/jutsu_form.php'; ?>

        <input type='submit' name='jutsu_data' value='Edit' />
        </form>
    </td></tr>
</table>
