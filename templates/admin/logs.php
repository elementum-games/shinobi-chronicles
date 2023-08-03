<?php
/**
 * @var string $log_type
 * @var System $system
 * @var string $self_link
 * @var int $previous
 * @var int $next
 * @var int $offset
 * @var int $max
 *
 * @var int|null $character_id
 * @var string|null $currency_type
 */

$colspan = 3;
if($log_type == 'currency_logs') {
    $colspan = 7;
}
?>

<div class='submenu'>
    <ul class='submenu'>
        <li style='width:32.9%;'><a href="<?=$system->router->links['admin']?>&page=logs&log_type=staff_logs">Staff Logs</a></li>
        <li style='width:32.9%;'><a href="<?=$system->router->links['admin']?>&page=logs&log_type=currency_logs">Currency Logs</a></li>
    </ul>
</div>

<?php if($log_type == 'staff_logs'): ?>
    <table class="table" style="table-layout: auto;">
        <tr><th colspan="<?=$colspan?>"><?=System::unSlug($log_type)?></th></tr>
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
<?php elseif($log_type === 'currency_logs'): ?>
    <?php require 'templates/admin/currency_logs.php'; ?>
<?php endif; ?>

