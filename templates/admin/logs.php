<?php
/**
 *
 */

$colspan = 3;
if($view == 'currency_logs') {
    $colspan = 7;
}
?>

<div class='submenu'>
    <ul class='submenu'>
        <li style='width:32.9%;'><a href="<?=$system->links['admin']?>&page=logs&view=staff_logs">Staff Logs</a></li>
        <li style='width:32.9%;'><a href="<?=$system->links['admin']?>&page=logs&view=currency_logs">Currency Logs</a></li>
    </ul>
</div>

<table class="table" style="table-layout: auto;">
    <tr><th colspan="<?=$colspan?>"><?=System::unSlug($view)?></th></tr>
    <?php if($view == 'staff_logs'): ?>
        <?php if(empty($logs)): ?>
            <tr><td colspan="<?=$colspan?>" style="text-align: center;">No logs!</td> </tr>
        <?php else: ?>
            <tr>
                <th style="width:15%">Date</th>
                <th style="width:18%">Log Type</th>
                <th>Content</th>
            </tr>
            <?php foreach($logs as $log): ?>
                <tr style="text-align: center;">
                    <td><?=Date(StaffManager::DATE_FORMAT, $log['time'])?></td>
                    <td><?=System::unSlug($log['type'])?></td>
                    <td><?=nl2br($log['content'])?></td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    <?php elseif($view == 'currency_logs'): ?>
        <?php if(empty($logs)): ?>
            <tr><td colspan="<?=$colspan?>" style="text-align: center;">No logs!</td> </tr>
        <?php else: ?>
            <tr>
                <th style="width:15%;">Date</th>
                <th style="width:5%;">User ID</th>
                <th style="width:9%;">Currency Type</th>
                <th>Previous Balance</th>
                <th>New Balance</th>
                <th>Trans. Amount</th>
                <th>Description</th>
            </tr>
            <?php foreach($logs as $log): ?>
                <tr style="text-align: center;">
                    <td><?=Date(StaffManager::DATE_FORMAT, $log['transaction_time'])?></td>
                    <td><?=$log['character_id']?></td>
                    <td><?=System::unSlug($log['currency_type'])?></td>
                    <td><?=number_format($log['previous_balance'])?></td>
                    <td><?=number_format($log['new_balance'])?></td>
                    <td><?=number_format($log['transaction_amount'])?></td>
                    <td><?=$log['transaction_description']?></td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    <?php elseif($view == 'player_logs'): ?>
        <?php if(empty($logs)): ?>
            <tr><td colspan="<?=$colspan?>" style="text-align: center;">No logs!</td> </tr>
        <?php else: ?>
            <tr>
                <th style="width:15%;">Date</th>
                <th style="width:12%;">User Name</th>
                <th style="width:8%;">User ID</th>
                <th style="width:10%;">Log Type</th>
                <th>Contents</th>
            </tr>
            <?php foreach($logs as $log): ?>
                <tr style="text-align: center;">
                    <td><?=$log['log_time']?></td>
                    <td><?=$log['user_name']?></td>
                    <td><?=$log['user_id']?></td>
                    <td><?=System::unSlug($log['log_type'])?></td>
                    <td style="word-wrap: break-word; overflow-wrap: break-word"><?=nl2br($log['log_contents'])?></td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    <?php endif ?>
</table>

<div style="text-align: center">
    <?php if($offset != 0): ?>
        <a href="<?=$self_link?>&offset=<?=$previous?>">Previous</a>
    <?php endif ?>
    <?php if($max != 0 && $max != $offset): ?>
        <?php if($offset != 0): ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        <?php endif ?>
        <a href="<?=$self_link?>&offset=<?=$next?>">Next</a>
    <?php endif ?>
</div>