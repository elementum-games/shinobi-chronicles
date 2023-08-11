<?php

/**
 * @var System $system;
 * @var array $all_npcs
 * @var string $self_link
 */

require_once __DIR__ . '/../../classes/RankManager.php';
$RANK_NAMES = RankManager::fetchNames($system);

?>

<table class='table'>
    <tr>
        <th>Name</th>
        <th>Level</th>
        <th>Max Health</th>
        <th>Nin Skill</th>
        <th>Gen Skill</th>
        <th>Tai Skill</th>
        <th>Cast Speed</th>
        <th>Speed</th>
        <th>Money</th>
    </tr>
    <tr><th colspan='9'><?= $RANK_NAMES[1] ?></th></tr>
    <?php $current_rank = 1; ?>
    <?php foreach($all_npcs as $id => $npc): ?>
        <?php
        if($npc['rank'] > $current_rank) {
            $current_rank = $npc['rank'];
            echo "<tr><th colspan='9'>$RANK_NAMES[$current_rank]</th></tr>";
        }
        ?>
        <tr>
            <td>
                <a href="<?= $self_link ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
            </td>
            <td><?= $npc['level'] ?></td>
            <td><?= $npc['max_health'] ?></td>
            <td><?= $npc['ninjutsu_skill'] ?></td>
            <td><?= $npc['genjutsu_skill'] ?></td>
            <td><?= $npc['taijutsu_skill'] ?></td>
            <td><?= $npc['cast_speed'] ?></td>
            <td><?= $npc['speed'] ?></td>
            <td>
                <?= $npc['money_multiplier'] ?>
                <?php if($npc['money_multiplier'] > 0): ?>
                    <em>(&yen;<?=User::calcMoneyGain($npc['rank'], $npc['money_multiplier'], NPC::MONEY_GAIN_MULTIPLE)?>)</em>
                <?php else: ?>
                    <b>(disabled)</b>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>