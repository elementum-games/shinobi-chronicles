<?php
/**
 * @var System $system
 * @var array $jutsu_constraints
 * @var array $RANK_NAMES
 * @var Jutsu[] $ALL_JUTSU
 * @var Jutsu $jutsu
 */
?>

<p style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
    <a href='<?= $system->router->getUrl('admin', ['page' => 'edit_jutsu'])?>' style='font-size:14px;'>Back to jutsu list</a>
</p>
<table class='table'>
    <tr><th>Edit Jutsu (<?= stripslashes($jutsu->name) ?>)</th></tr>
    <tr><td>
        <form
            action='<?= $system->router->getUrl(
                page_name: 'admin',
                url_params: ['page' => 'edit_jutsu', 'jutsu_id' => $jutsu->id, 'jutsu_type' => $jutsu->jutsu_type,]
            ) ?>'
            method='post'
        >
        <label>Jutsu ID:</label> <?= $jutsu->id ?><br />

        <?php require 'templates/admin/jutsu_form.php'; ?>

        <input type='submit' name='jutsu_data' value='Edit' />
        </form>
    </td></tr>
</table>
