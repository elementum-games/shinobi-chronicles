<?php
/**
 * @var System $system
 * @var array $missions
 */
$col_span = 5;
$current_rank = '';
?>
<p id='top' style='text-align:center;margin-top:20px;margin-bottom:-5px;'>
    <?php foreach(Mission::$rank_names as $id => $name): ?>
        | <a href='#rank_<?= $id ?>'><?= $name ?></a> |
    <?php endforeach; ?>
</p>
<table class="table">
    <tr><th colspan="<?=$col_span?>">Edit Mission Select</th></tr>
    <?php if(empty($missions)): ?>
        <tr><td colspan="<?=$col_span?>" style="text-align: center;">No Missions</td></tr>
    <?php else: ?>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Money</th>
            <th>Stages</th>
            <th>Rewards</th>
        </tr>
        <?php foreach($missions as $mission): ?>
            <?php if($current_rank != $mission['rank']): ?>
                <?php if($current_rank !== ''): ?>
                    <tr><td colspan="<?=$col_span?>" style="text-align: center;"><a href="#top">Return to Top</a></td></tr>
                <?php endif ?>
                <tr><th id='rank_<?=$mission['rank']?>' colspan="<?=$col_span?>"><?=Mission::$rank_names[$mission['rank']]?></th></tr>
                <?php $current_rank = $mission['rank']; ?>
            <?php endif ?>
            <tr style="text-align: center;">
                <td>
                    <a href="<?=$system->router->getUrl('admin', ['page' => 'edit_mission', 'mission_id' => $mission['mission_id']])?>">
                        <?=$mission['name']?>
                    </a>
                </td>
                <td><?=Mission::$type_names[$mission['mission_type']]?></td>
                <td>&yen;<?=$mission['money']?></td>
                <td><?=$mission['stages']?></td>
                <td><?=$mission['rewards']?></td>
            </tr>
        <?php endforeach ?>
        <tr><td colspan="<?=$col_span?>" style="text-align: center;"><a href="#top">Return to Top</a></td></tr>
    <?php endif ?>
</table>
