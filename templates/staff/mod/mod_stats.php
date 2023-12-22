<?php
/**
 * @var System $system
 * @var int $total_reports
 * @var int $total_mod_actions
 * @var int $total_chat_posts
 * @var array $mod_staff
 */

function calc_percent(int $amount, int $total): string {
    if($total == 0) {
        return "<em>(N/A)<em>";
    }

    $perc = round($amount/$total * 100, 1);
    return "<em>({$perc}%)";
}
?>


<table class="table">
    <tr><th colspan="4">Moderator Stats</th></tr>
    <tr>
        <th>Name</th>
        <th>Actions</th>
        <th>Reports Handled</th>
        <th>Chat Posts</th>
    </tr>
    <?php foreach($mod_staff as $UID => $stats): ?>
        <tr style="text-align: center;">
            <td><?=$stats['user_name']?></td>
            <td><?=$stats['mod_actions']?> <?= calc_percent($stats['mod_actions'], $total_mod_actions) ?></td>
            <td><?=$stats['reports_handled']?> <?= calc_percent($stats['reports_handled'], $total_reports) ?></td>
            <td><?=$stats['chat_posts']?> <?= calc_percent($stats['chat_posts'], $total_chat_posts) ?></td>
        </tr>
    <?php endforeach ?>
    <tr style="text-align: center;">
        <td><b><em>Totals</em></b></td>
        <td><?= $total_mod_actions ?></td>
        <td><?= $total_reports ?></td>
        <td><?= $total_chat_posts ?></td>
    </tr>
</table>
<div style="width:100%;text-align: center;"><a href="<?=$system->router->getUrl('mod', ['view'=>'mod_stats'])?>">Return to Search</a></div>