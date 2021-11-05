<?php
/**
 * @var array $supports
 * @var string $self_link
 */
?>

<table class="table" style="table-layout:auto;">
    <tr><th colspan="5">My Support Requests</th></tr>
    <tr style="text-align:center;"><td colspan="5">
        <form action="<?=$self_link?>" method="post">
            Add Guest Support to my account<br />
            Support Key:<input type="text" name="support_key" />
            <input type="submit" name="add_guest_support" value="Add" />
        </form>
    </td></tr>
    <tr>
        <th style="width:45%;">Subject</th>
        <th style="width:20%;">Date</th>
        <th style="width:10%;">Type</th>
        <th style="width:15%;">Status</th>
        <th style="width:8%;"></th>
    </tr>
    <?php foreach($supports as $support): ?>
        <tr style="text-align: center;">
            <td><?=$support['subject']?></td>
            <td><?=strftime(SupportManager::$strfString, $support['time'])?></td>
            <td><?=$support['support_type']?></td>
            <td><?=($support['open']) ? 'Open' : 'Closed'?></td>
            <td><a href="<?=$self_link?>?support_id=<?=$support['support_id']?>">View</a></td>
        </tr>
    <?php endforeach ?>
</table>
