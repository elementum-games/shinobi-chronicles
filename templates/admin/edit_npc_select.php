<?php

/**
 * @var System $system;
 * @var array $all_npcs
 */

require_once __DIR__ . '/../../classes/RankManager.php';
$RANK_NAMES = RankManager::fetchNames($system);
$difficulty = [NPC::DIFFICULTY_NONE => 0, NPC::DIFFICULTY_EASY => 1, NPC::DIFFICULTY_NORMAL => 2, NPC::DIFFICULTY_HARD => 3];
usort($all_npcs, function ($a, $b) use ($difficulty) {
    if ($a['rank'] !== $b['rank']) {
        return $a['rank'] < $b['rank'] ? -1 : 1;
    }
    if ($a['level'] !== $b['level']) {
        return $a['level'] < $b['level'] ? -1 : 1;
    }
    if ($difficulty[$a['difficulty_level']] !== $difficulty[$b['difficulty_level']]) {
        return $difficulty[$a['difficulty_level']] < $difficulty[$b['difficulty_level']] ? -1 : 1;
    }
    if ($a['scaling'] !== $b['scaling']) {
        return $a['scaling'] > $b['scaling'] ? -1 : 1;
    }
    return 0;
});
?>

<table class='table' style="text-align: center">
    <tr><th colspan='12'>Arena AI</th></tr>
    <tr>
        <th colspan="2">Name</th>
        <th>Level</th>
        <th>HP</th>
        <th>Nin</th>
        <th>Gen</th>
        <th>Tai</th>
        <th>CSpd</th>
        <th>Spd</th>
        <th>Yen</th>
        <th>Scales</th>
        <th>Diff</th>
    </tr>
    <?php $current_rank = 0; ?>
    <?php foreach($all_npcs as $id => $npc): ?>
    <?php if ($npc['arena_enabled']): ?>
    <?php
            if($npc['rank'] > $current_rank) {
                $current_rank = $npc['rank'];
                echo "<tr><th colspan='12'>$RANK_NAMES[$current_rank]</th></tr>";
            }
    ?>
    <tr>
        <td style="text-align: left" colspan="2">
            <a href="<?= $system->routerV2->current_route ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
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
        <td><?= System::unSlug($npc['difficulty_level']) ?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</table>
<table class='table' style="text-align: center">
    <tr><th colspan='12'>Patrol AI</th></tr>
    <tr>
        <th colspan="2">Name</th>
        <th>Level</th>
        <th>HP</th>
        <th>Nin</th>
        <th>Gen</th>
        <th>Tai</th>
        <th>CSpd</th>
        <th>Spd</th>
        <th>Yen</th>
        <th>Scales</th>
        <th>Diff</th>
    </tr>
    <?php $current_rank = 0; ?>
    <?php foreach ($all_npcs as $id => $npc): ?>
        <?php if ($npc['is_patrol']): ?>
            <?php
            if ($npc['rank'] > $current_rank) {
                $current_rank = $npc['rank'];
                echo "<tr><th colspan='12'>$RANK_NAMES[$current_rank]</th></tr>";
            }
            ?>
            <tr>
                <td style="text-align: left" colspan="2">
                    <a href="<?= $system->routerV2->current_route ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
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
                <td><?= System::unSlug($npc['difficulty_level']) ?></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>
<table class='table' style="text-align: center">
    <tr><th colspan='12'>Other AI</th></tr>
    <tr>
        <th colspan="2">Name</th>
        <th>Level</th>
        <th>HP</th>
        <th>Nin</th>
        <th>Gen</th>
        <th>Tai</th>
        <th>CSpd</th>
        <th>Spd</th>
        <th>Yen</th>
        <th>Scales</th>
        <th>Diff</th>
    </tr>
    <?php $current_rank = 0; ?>
    <?php foreach ($all_npcs as $id => $npc): ?>
    <?php if (!$npc['arena_enabled'] && !$npc['is_patrol']): ?>
    <?php
            if ($npc['rank'] > $current_rank) {
                $current_rank = $npc['rank'];
                echo "<tr><th colspan='12'>$RANK_NAMES[$current_rank]</th></tr>";
            }
    ?>
    <tr>
        <td style="text-align: left" colspan="2">
            <a href="<?= $system->routerV2->current_route ?>&npc_id=<?= $npc['ai_id'] ?>"><?= $npc['name'] ?></a>
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
        <td><?= System::unSlug($npc['difficulty_level']) ?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</table>