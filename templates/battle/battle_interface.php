
<?php

/**
 * @var System $system;
 * @var Battle $battle
 * @var Fighter $player
 * @var Fighter $opponent
 *
 * @var string $self_link
 * @var string $refresh_link
 */

    $health_percent = round(($player->health / $player->max_health) * 100);
    $chakra_percent = round(($player->chakra / $player->max_chakra) * 100);
    $stamina_percent = round(($player->stamina / $player->max_stamina) * 100);
    $player_avatar_size = $player->getAvatarSize() . 'px';

    $opponent_health_percent = round(($opponent->health / $opponent->max_health) * 100);
    $opponent_avatar_size = $opponent->getAvatarSize() . 'px';

    $battle_text = null;
    if($battle->battle_text) {
        $battle_text = $system->html_parse(stripslashes($battle->battle_text));
        $battle_text = str_replace(array('[br]', '[hr]'), array('<br />', '<hr />'), $battle_text);
    }
?>

<style type='text/css'>
    .playerAvatar {
        display:block;
        margin: auto;
        max-width:<?= $player_avatar_size ?>;
        max-height:<?= $player_avatar_size ?>;
    }
    .opponentAvatar {
        display:block;
        margin:auto;
        max-width:<?= $opponent_avatar_size ?>;
        max-height:<?= $opponent_avatar_size ?>;
    }

    .resourceBarOuter {
        height:6px;
        width:250px;
        border-style:solid;
        border-width:1px;
    }
    .healthFill {
        background-color:#C00000;
        height:6px;
    }
    .chakraFill {
        background-color:#0000B0;
        height:6px;
    }
    .staminaFill {
        background-color:#00B000;
        height:6px;
    }
</style>

<div class='submenu'>
    <ul class='submenu'>
        <li style='width:100%;'><a href='<?= $refresh_link ?>'>Refresh Battle</a></li>
    </ul>
</div>
<div class='submenuMargin'></div>

<?php $system->printMessage(); ?>
<table class='table'>
    <tr>
        <th style='width:50%;'>
            <a href='<?= $system->links['members'] ?>&user=<?= $player->getName() ?>' style='text-decoration:none'><?= $player->getName() ?></a>
        </th>
        <th style='width:50%;'>
            <?php if($opponent instanceof AI): ?>
                <?= $opponent->getName() ?>
            <?php else: ?>
                <a href='<?= $system->links['members'] ?>&user=<?= $opponent->getName() ?>' style='text-decoration:none'><?= $opponent->getName() ?></a>
            <?php endif; ?>
        </th>
    </tr>
    <tr><td>
        <img src='<?= $player->avatar_link ?>' class='playerAvatar' />
        <label style='width:80px;'>Health:</label>
            <?= sprintf("%.2f", $player->health) ?> / <?= sprintf("%.2f", $player->max_health) ?><br />
        <div class='resourceBarOuter'><div class='healthFill' style='width:<?= $health_percent ?>%;'></div></div>

        <?php if(!$battle->spectate): ?>
            <label style='width:80px;'>Chakra:</label>
            <?= sprintf("%.2f", $player->chakra) ?> / <?= sprintf("%.2f", $player->max_chakra) ?><br />
            <div class='resourceBarOuter'><div class='chakraFill' style='width:<?= $chakra_percent ?>%;'></div></div>
            <label style='width:80px;'>Stamina:</label>
            <?= sprintf("%.2f", $player->stamina) ?> / <?= sprintf("%.2f", $player->max_stamina) ?><br />
            <div class='resourceBarOuter'><div class='staminaFill' style='width:<?= $stamina_percent ?>%;'></div></div>
        <?php endif; ?>
    </td>
    <td>
        <img src='<?= $opponent->avatar_link ?>' class='opponentAvatar' />
        <label style='width:80px;'>Health:</label>
        <?= sprintf("%.2f", $opponent->health) ?> / <?= sprintf("%.2f", $opponent->max_health) ?><br />
        <div class='resourceBarOuter'><div class='healthFill' style='width:<?= $opponent_health_percent ?>%;'></div></div>
    </td></tr>
</table>

<table class='table'>
    <!--// Battle text display-->
    <?php if($battle_text): ?>
        <tr><th colspan='2'>Last turn</th></tr>
        <tr><td style='text-align:center;' colspan='2'><?= $battle_text ?></td></tr>
    <?php endif; ?>

    <!--// Trigger win action or display action prompt-->
    <?php if(!$battle->isComplete() && !$battle->spectate): ?>
        <tr><th colspan='2'>Select Action</th></tr>

        <?php if(!$battle->playerActionSubmitted()): ?>
            <?php require 'templates/battle/action_prompt.php'; ?>
        <?php elseif(!$battle->opponentActionSubmitted()): ?>
            <tr><td colspan='2'>Please wait for <?= $opponent->getName() ?> to select an action.</td></tr>
        <?php endif; ?>

        <!--// Turn timer-->
        <tr><td style='text-align:center;' colspan='2'>
            <?= ($battle->isPreparationPhase() ? "Prep-" : "") ?>Time remaining:
                <?= $battle->isPreparationPhase() ? $battle->prepTimeRemaining() : $battle->timeRemaining() ?> seconds
        </td></tr>
    <?php endif; ?>

    <?php if($battle->spectate): ?>
        <tr><td style='text-align:center;' colspan='2'>
            <?php if($battle->winner == Battle::TEAM1): ?>
               <?=  $battle->player1->getName() ?> won!
            <?php elseif($battle->winner == Battle::TEAM2): ?>
                <?= $battle->player2->getName() ?> won!
            <?php elseif($battle->winner == Battle::DRAW): ?>
                Fight ended in a draw.
            <?php else: ?>
                Time remaining: <?= $battle->timeRemaining() ?> seconds
            <?php endif; ?>
        </td></tr>
    <?php endif; ?>
</table>