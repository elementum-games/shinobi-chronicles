<?php
/**
 * @var array $supports
 * @var string $self_link
 */
?>

<table class="table" style="table-layout:auto;">
    <tr><th colspan="4">My Support Requests</th></tr>
    <tr>
        <th style="width:60%;">Subject</th>
        <th style="width:20%;">Date</th>
        <th style="width:10%;">Type</th>
        <th style="width:8%;"></th>
    </tr>
    <?php foreach($supports as $support): ?>
        <tr style="text-align: center;">
            <td><?=$support['subject']?></td>
            <td><?=strftime('%m/%d/%y @ %I:%M', $support['time'])?></td>
            <td><?=$support['support_type']?></td>
            <td><a href="<?=$self_link?>?support_id=<?=$support['support_id']?>">View</a></td>
        </tr>
    <?php endforeach ?>
</table>
