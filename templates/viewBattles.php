<?php
/** @var array $battles */
/** @var string $self_link */

?>
<table class='table'>
    <tr><th colspan='3'>View Battles</th></tr>
    <tr>
        <th>Player 1</th>
        <th>Player 2</th>
        <th>Winner</th>
    </tr>
    <?php foreach($battles as $battle): ?>
        <tr>
            <td><?= $battle['player1'] ?></td>
            <td><?= $battle['player2'] ?></td>
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