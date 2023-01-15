<?php
/**
 * @var string $admin_panel_url
 * @var array $bloodline_data
 * @var array $variables the allowed constraints for bloodline fields
 * @var int $editing_bloodline_id
 */
?>
<table class='table'>
    <tr><th>Edit Bloodline (<?= stripslashes($bloodline_data['name']) ?>)</th></tr>
    <tr><td>
        <form action='<?= $admin_panel_url ?>&page=edit_bloodline&bloodline_id=<?= $editing_bloodline_id ?>' method='post'>
            <?php displayFormFields($variables, $bloodline_data) ?>
            <br />
            <input type='submit' name='bloodline_data' value='Edit' />
        </form>
    </td></tr>
</table>