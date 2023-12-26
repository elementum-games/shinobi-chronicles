<?php

/**
 * @var System $system;
 * @var array $all_npcs
 * @var string $self_link
 */

require_once __DIR__ . '/../../classes/RankManager.php';
$RANK_NAMES = RankManager::fetchNames($system);

?>

<table class='table' style="text-align: center">
    <tr><th colspan='11'>Arena AI</th></tr>
    <tr>
        <th colspan="2">Name</th>
        <th>Level</th>
        <th>HP</th>
        <th>Nin</th>
        <th>Gen</th>
        <th>Tai</th>
        <th>CSpd</th>
        <th>Spd</th>
        <th>Money</th>
        <th>Scaling</th>
    </tr>
    <?php $current_rank = 0; ?>
    <?php foreach($all_npcs as $id => $npc): ?>
    <?php if ($npc['arena_enabled']): ?>
    <?php
            if($npc['rank'] > $current_rank) {
                $current_rank = $npc['rank'];
                echo "<tr><th colspan='11'>$RANK_NAMES[$current_rank]</th></tr>";
            }
    ?>
    <tr>
        <td style="text-align: left" colspan="2">
            <a href="<?= $self_link ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
        </td>
        <td><?= $npc['level'] ?></td>
        <td><?= $npc['max_health'] ?></td>
        <td><?= $npc['ninjutsu_skill'] ?></td>
        <td><?= $npc['genjutsu_skill'] ?></td>
        <td><?= $npc['taijutsu_skill'] ?></td>
        <td><?= $npc['cast_speed'] ?></td>
        <td><?= $npc['speed'] ?></td>
        <td><?= $npc['money'] ?></td>
        <td><?= $npc['scaling'] ? "True" : "False" ?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</table>
<table class='table' style="text-align: center">
    <tr><th colspan='11'>Patrol AI</th></tr>
    <tr>
        <th colspan="2">Name</th>
        <th>Level</th>
        <th>HP</th>
        <th>Nin</th>
        <th>Gen</th>
        <th>Tai</th>
        <th>CSpd</th>
        <th>Spd</th>
        <th>Money</th>
        <th>Scaling</th>
    </tr>
    <?php $current_rank = 0; ?>
    <?php foreach ($all_npcs as $id => $npc): ?>
        <?php if ($npc['is_patrol']): ?>
            <?php
            if ($npc['rank'] > $current_rank) {
                $current_rank = $npc['rank'];
                echo "<tr><th colspan='11'>$RANK_NAMES[$current_rank]</th></tr>";
            }
            ?>
            <tr>
                <td style="text-align: left" colspan="2">
                    <a href="<?= $self_link ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
                </td>
                <td><?= $npc['level'] ?></td>
                <td><?= $npc['max_health'] ?></td>
                <td><?= $npc['ninjutsu_skill'] ?></td>
                <td><?= $npc['genjutsu_skill'] ?></td>
                <td><?= $npc['taijutsu_skill'] ?></td>
                <td><?= $npc['cast_speed'] ?></td>
                <td><?= $npc['speed'] ?></td>
                <td><?= $npc['money'] ?></td>
                <td><?= $npc['scaling'] ? "True" : "False" ?></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>
<table class='table' style="text-align: center">
    <tr><th colspan='11'>Other AI</th></tr>
    <tr>
        <th colspan="2">Name</th>
        <th>Level</th>
        <th>HP</th>
        <th>Nin</th>
        <th>Gen</th>
        <th>Tai</th>
        <th>CSpd</th>
        <th>Spd</th>
        <th>Money</th>
        <th>Scaling</th>
    </tr>
    <?php $current_rank = 0; ?>
    <?php foreach ($all_npcs as $id => $npc): ?>
    <?php if (!$npc['arena_enabled'] && !$npc['is_patrol']): ?>
    <?php
            if ($npc['rank'] > $current_rank) {
                $current_rank = $npc['rank'];
                echo "<tr><th colspan='11'>$RANK_NAMES[$current_rank]</th></tr>";
            }
    ?>
    <tr>
        <td style="text-align: left" colspan="2">
            <a href="<?= $self_link ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
        </td>
        <td><?= $npc['level'] ?></td>
        <td><?= $npc['max_health'] ?></td>
        <td><?= $npc['ninjutsu_skill'] ?></td>
        <td><?= $npc['genjutsu_skill'] ?></td>
        <td><?= $npc['taijutsu_skill'] ?></td>
        <td><?= $npc['cast_speed'] ?></td>
        <td><?= $npc['speed'] ?></td>
        <td><?= $npc['money'] ?></td>
        <td><?= $npc['scaling'] ? "True" : "False" ?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</table>