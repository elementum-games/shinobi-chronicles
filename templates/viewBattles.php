<?php
/** @var array $battles */
/** @var string $self_link */

?>
<table class='table' style="text-align:center;">
    <tr><th colspan='3'>View Battles</th></tr>
    <tr id='viewBattles_headers'>
        <th>Player 1</th>
        <th>Player 2</th>
        <th>Winner</th>
    </tr>
    <?php foreach($battles as $battle): ?>
        <tr id='viewBattles_data'>
            <td><a href="<?= $system->router->links['members']?>&user=<?= $battle['player1'] ?>" style='text-decoration:none'><?= $battle['player1'] ?></a></td>
            <td><a href="<?= $system->router->links['members']?>&user=<?= $battle['player2'] ?>" style='text-decoration:none'><?= $battle['player2'] ?></a></td>
            <td>
            <?php if($battle['winner']): ?>
                    <?= $battle['winner'] ?>
                <?php else: ?>
                    <a href="<?= $self_link ?>&battle_id=<?= $battle['id'] ?>">Watch</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
