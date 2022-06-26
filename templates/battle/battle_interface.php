
<?php

/**
 * @var System $system;
 * @var BattleManager $battleManager
 * @var Battle $battle
 * @var Fighter $player
 * @var Fighter $opponent
 *
 * @var string $self_link
 * @var string $refresh_link
 */

    $battle_text = null;
    if($battle->battle_text) {
        $battle_text = $system->html_parse(stripslashes($battle->battle_text));
        $battle_text = str_replace(array('[br]', '[hr]'), array('<br />', '<hr />'), $battle_text);
    }

    require 'templates/battle/resource_bar.php';
    require 'templates/battle/fighter_avatar.php';
?>

<style>
    .fighterDisplay {
        display: flex;
        flex-direction: row;
        gap: 8px;
    }
    .fighterDisplay.opponent {
        flex-direction: row-reverse;
    }
</style>
<div class='submenuMargin'></div>

<?php $system->printMessage(); ?>

<!-- PLAYER DISPLAYS -->
<table class='table'>
    <tr>
        <th style='width:50%;'>
            <a href='<?= $system->links['members'] ?>&user=<?= $player->getName() ?>'
               style='text-decoration:none'
            >
                <?= $player->getName() ?>
            </a>
        </th>
        <th style='width:50%;'>
            <?php if($opponent instanceof NPC): ?>
                <?= $opponent->getName() ?>
            <?php else: ?>
                <a href='<?= $system->links['members'] ?>&user=<?= $opponent->getName() ?>'
                   style='text-decoration:none'
                >
                    <?= $opponent->getName() ?>
                </a>
            <?php endif; ?>
        </th>
    </tr>
    <tr><td>
        <div class='fighterDisplay'>
            <?php renderAvatar($player) ?>
            <div class='resourceBars'>
                <?php resourceBar($player->health, $player->max_health, 'health') ?>
                <?php if(!$battleManager->spectate): ?>
                    <?php resourceBar($player->chakra, $player->max_chakra, 'chakra') ?>
                <?php endif; ?>
            </div>
        </div>
    </td>
    <td>
        <div class='fighterDisplay opponent'>
            <?php renderAvatar($opponent) ?>
            <div class='resourceBars'>
                <?php resourceBar($opponent->health, $opponent->max_health,'health') ?>
            </div>
        </div>
    </td></tr>
    <!-- Battle field -->
    <tr><td colspan='2'>
        <?php require 'templates/battle/battle_field.php'; ?>
    </td></tr>
</table>

<!-- Trigger win action or display action prompt-->
<?php if(!$battle->isComplete() && !$battleManager->spectate): ?>
    <?php require 'templates/battle/action_prompt.php'; ?>
<?php elseif($battleManager->spectate): ?>
    <table class='table' style='margin-top:2px;'>
        <tr><td style='text-align:center;'>
            <?php if($battle->winner == Battle::TEAM1): ?>
               <?=  $battle->player1->getName() ?> won!
            <?php elseif($battle->winner == Battle::TEAM2): ?>
                <?= $battle->player2->getName() ?> won!
            <?php elseif($battle->winner == Battle::DRAW): ?>
                Fight ended in a draw.
            <?php else: ?>
                <b><?= $battle->timeRemaining() ?></b> seconds remaining<br />
                <a href='<?= $refresh_link ?>'>Refresh</a>
            <?php endif; ?>
        </td></tr>
    </table>
<?php endif; ?>

<!--// Battle text display-->
<?php if($battle_text): ?>
    <table class='table'>
        <tr><th colspan='2'>Last turn</th></tr>
        <tr><td style='text-align:center;' colspan='2'><?= $battle_text ?></td></tr>
    </table>
<?php endif; ?>