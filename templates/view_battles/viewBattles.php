<?php

/**
 * @var System $system
 * @var User $player
 * @var array  $battles
 * @var array  $scheduled_battles
 * @var string $self_link
*/

?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const timeElements = document.querySelectorAll('.utc-time');

    timeElements.forEach(element => {
        const utcTime = new Date(element.getAttribute('data-utc-time'));
        element.textContent = utcTime.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    });
});
</script>

<?php if (!empty($scheduled_battles)): ?>
    <table class='table' style="text-align:center;">
        <tr>
            <th colspan='3'>View Challenges</th>
        </tr>
        <tr id='viewBattles_headers'>
            <th>Challenger</th>
            <th>Defender</th>
            <th>Time</th>
        </tr>
        <?php foreach($scheduled_battles as $battle): ?>
        <tr id='viewBattles_data'>
            <td>
                <a href="<?= $system->router->links['members']?>&user=<?= $battle['challenger_name'] ?>">
                    <?= $battle['challenger_name'] ?>
                </a>
            </td>
            <td>
                <a href="<?= $system->router->links['members']?>&user=<?= $battle['seat_holder_name'] ?>">
                    <?= $battle['seat_holder_name'] ?>
                </a>
            </td>
            <td>
                <?php if(isset($battle['battle_id'])): ?>
                <a href="<?= $self_link ?>&battle_id=<?= $battle['battle_id'] ?>">Watch</a>
                <?php else: ?>
                <span class="utc-time" data-utc-time="<?= gmdate('c', $battle['time']) ?>"><?= Date('M jS, h:i A', $battle['time']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<table class='table' style="text-align:center;">
    <tr><th colspan='3'>View Battles</th></tr>
    <tr id='viewBattles_headers'>
        <th>Player 1</th>
        <th>Player 2</th>
        <th>Winner</th>
    </tr>
    <?php foreach($battles as $battle): ?>
        <tr id='viewBattles_data'>
            <td><a href="<?= $system->router->links['members']?>&user=<?= $battle['player1'] ?>"><?= $battle['player1'] ?></a></td>
            <td><a href="<?= $system->router->links['members']?>&user=<?= $battle['player2'] ?>"><?= $battle['player2'] ?></a></td>
            <td>
            <?php if($battle['winner']): ?>
                    <?= $battle['winner'] ?>
                    <?php if($player->isHeadAdmin()): ?>
                        <a href='<?= $system->router->getUrl('view_battles', ['view_log' => $battle['id']]) ?>'>(Log)</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= $self_link ?>&battle_id=<?= $battle['id'] ?>">Watch</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
