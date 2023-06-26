<?php
/** @var array $user_battles */
/** @var array $battle_logs */
/** @var System $system */

?>
<table class='table' style="text-align:center;">
    <tr>
        <th colspan='4'>Battle History</th>
    </tr>
    <tr>
        <th>Player 1</th>
        <th>Player 2</th>
        <th>Winner</th>
        <th>Log</th>
    </tr>
    <?php foreach($user_battles as $battle): ?>
    <tr>
        <td>
            <a href="<?= $system->router->links['members']?>&user=<?= $battle['player1'] ?>" style='text-decoration:none'>
                <?= $battle['player1'] ?>
            </a>
        </td>
        <td>
            <a href="<?= $system->router->links['members']?>&user=<?= $battle['player2'] ?>" style='text-decoration:none'>
                <?= $battle['player2'] ?>
            </a>
        </td>
        <td>
            <?= $battle['winner'] ?>
        </td>
        <td style="text-align:center">
            <a href="<?= $system->router->links['view_battles'] ?>&view_log=<?= $battle['id'] ?>">View</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php if ($battle_logs): ?>
<table class='table' style="text-align:center;">
    <tr>
        <th>Battle Log</th>
    </tr>
    <?php foreach($battle_logs as $log): ?>
    <tr>
        <th>Turn <?= $log['turn'] ?></th>
    </tr>
    <tr>
        <td>
            <?= $system->html_parse($log['content']) ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>