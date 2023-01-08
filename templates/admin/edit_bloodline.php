<?php
/**
 * @var string $admin_panel_url
 * @var array $bloodline_data
 * @var array $variables the allowed constraints for bloodline fields
 */
?>
<table class='table'>
    <tr><th>Edit Bloodline (<?= stripslashes($bloodline_data['name']) ?>)</th></tr>
    <tr><td>
        <form action='<?= $admin_panel_url ?>&page=edit_bloodline' method='post'>
            <?php displayFormFields($variables, $bloodline_data) ?>
            <br />
            <input type='hidden' name='bloodline_id' value='{$editing_bloodline_id}' />
            <input type='submit' name='bloodline_data' value='Edit' />
        </form>
    </td></tr>
</table>