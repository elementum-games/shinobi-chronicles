<?php
/**
 * @var System $system
 * @var array $clans
 */
$col_span = 3;
?>
<table class="table">
    <tr><th colspan="<?=$col_span?>">Edit Clan Select</th></tr>
    <?php if(empty($clans)): ?>
        <tr><td colspan="<?=$col_span?>" style="text-align: center;">No Clans</td></tr>
    <?php else: ?>
        <tr>
            <th>Name</th>
            <th>Village</th>
            <th>Bloodline Only</th>
        </tr>
        <?php foreach($clans as $clan): ?>
            <tr style="text-align: center;">
                <td>
                    <a href="<?=$system->router->getUrl('admin', ['page' => 'edit_clan', 'clan_id' => $clan['clan_id']])?>">
                        <?=$clan['name']?>
                    </a>
                </td>
                <td><?=$clan['village']?></td>
                <td><?=($clan['bloodline_only'] ? 'Yes' : 'No')?></td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
</table>
