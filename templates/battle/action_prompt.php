<?php
/**
 * @var BattleManager $battleManager
 * @var Battle        $battle
 * @var User          $player
 * @var Fighter       $opponent
 * @var string        $self_link
 * @var string        $refresh_link
 */

?>

<table class='table'>
    <tr><th>
        Select <?= $battleManager->getPhaseLabel() ?> Action
    </th></tr>

    <?php if(!$battleManager->playerActionSubmitted()): ?>
        <?php if($battle->isPreparationPhase()): ?>
            <?php require 'templates/battle/prep_phase_action_prompt.php'; ?>
        <?php elseif($battle->isMovementPhase()): ?>
            <tr><td style='text-align:center;'>
                <em>Select a tile above</em>
            </td></tr>
        <?php elseif($battle->isAttackPhase()): ?>
            <?php require 'templates/battle/attack_action_prompt.php' ?>
        <?php else: ?>
            <tr><td>
                invalid phase
            </td></tr>
        <?php endif; ?>
    <?php elseif(!$battleManager->opponentActionSubmitted()): ?>
        <tr><td>Please wait for <?= $opponent->getName() ?> to select an action.</td></tr>
    <?php endif; ?>

    <!-- Turn timer-->
    <tr><td style='text-align:center;'>
        <b><?= $battle->timeRemaining() ?></b> seconds remaining
        <br/><a href='<?= $refresh_link ?>'>Refresh</a>
    </td></tr>
</table>
