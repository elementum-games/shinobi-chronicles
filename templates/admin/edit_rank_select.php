<?php
/**
 * @var System $system
 * @var string $content_name
 */
?>

<table class="table">
    <tr><th colspan="8">Select <?= $content_name ?></th></tr>
    <tr>
        <th>Name</th>
        <th>Base Lvl</th>
        <th>Lvl Cap</th>
        <th>Base Stats</th>
        <th>Stats / Lvl</th>
        <th>HP / Lvl</th>
        <th>Pool Gain</th>
        <th>Stat Cap</th>
    </tr>
    <?php foreach($ranks as $rank): ?>
        <tr style="text-align: center;">
            <td>
                <a href="<?=$system->router->getUrl('admin', ['page'=>'edit_rank', 'rank_id' => $rank['rank_id']])?>"><?=$rank['name']?></a>
            </td>
            <td><?=$rank['base_level']?></td>
            <td><?=$rank['max_level']?></td>
            <td><?=$rank['base_stats']?></td>
            <td><?=$rank['stats_per_level']?></td>
            <td><?=$rank['health_gain']?></td>
            <td><?=$rank['pool_gain']?></td>
            <td><?=$rank['stat_cap']?></td>
        </tr>
    <?php endforeach ?>
</table>