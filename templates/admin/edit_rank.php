<?php
/**
 * @var System $system
 */
?>
<style>
    label {
        display:inline-block;
        width:120px;
    }
</style>

<table class="table">
    <tr><th>Edit <?=$content_name?> (<?=stripslashes($content_data['name'])?>)</th></tr>
    <tr>
        <td>
            <form action="<?=$system->router->getUrl('admin', ['page' => 'edit_rank', 'rank_id' => $rank_id])?>" method="post">
                <input type="hidden" name="rank_id" value="<?=$rank_id?>" />
                <?php displayFormFields($variables, $content_data); ?>
                <input type="submit" name="<?=$content_name?>_data" value="Edit" />
            </form>
        </td>
    </tr>
</table>
