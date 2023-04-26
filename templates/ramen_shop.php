<?php
/**
 * @var User $player
 * @var array $ramen_choices
 * @var string $self_link
 */

    require_once __DIR__ . '/battle/resource_bar.php';
?>
<style>
    .healthBarContainer {
        display: flex;
        justify-content: center;

        width: 240px;
        margin: 2px auto 6px;
    }

    .choicesContainer {
        display: flex;
        justify-content: space-evenly;
        align-items: center;

        margin-top: 8px;
    }
    .ramenImage {
        width: 100px;
    }

    .choiceLabel {
        display: inline-block;
        width: 175px;
        text-align: left;
    }
</style>
<table class='table'><tr><th>Ichikawa Ramen</th></tr>
    <tr><td style='text-align:center;'>
        Welcome to Ichikawa Ramen. Our nutritious ramen is just the thing your body needs to recover after a long day
        of training or fighting!<br />
        <br />
        <label style='width:9em;font-weight:bold;'>Your Money</label><br />
        &yen;<?= number_format(num: $player->getMoney()) ?><br />
        <label style='width:9em;font-weight:bold;margin-top:8px;'>Your Health</label>
        <div class='healthBarContainer'>
            <?php resourceBar($player->health, $player->max_health, 'health'); ?>
        </div>
    </td></tr>
    <tr><td style='text-align:center;'>
        <div class='choicesContainer'>
            <img src="images/pages/ramen_300px.png" class='ramenImage' />
            <div class='choices'>
                <?php foreach($ramen_choices as $key => $ramen): ?>
                    <a href='<?= $self_link ?>&heal=<?= $key ?>'><span class='button' style='width:10em;'><?= $ramen['label'] ?> ramen</span></a>
                    <span class='choiceLabel'>&nbsp;&nbsp;&nbsp;(<?= $ramen['health_amount'] ?> health, -&yen;<?= $ramen['cost'] ?>)</span><br />
                <?php endforeach; ?>
            </div>
        </div>

    </td></tr>
</table>
