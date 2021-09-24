<?php
/** @var array $battles */
/** @var string $self_link */

?>
<table class='table'>
    <tr><th colspan='4'>View Battles</th></tr>
    <?php foreach($battles as $battle): ?>
        <tr>
            <td><?= $battle['player1'] ?></td>
            <td><?= $battle['player2'] ?></td>
            <td><?= $battle['winner'] ?></td>
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