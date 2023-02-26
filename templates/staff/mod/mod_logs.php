<table class="table" style="table-layout: auto;">
    <tr><th colspan="3">Moderator Logs</th></tr>
    <tr>
        <th style="width:15%;">Time</th>
        <th style="width:10%;">Type</th>
        <th>Content</th>
    </tr>
    <?php if(empty($logs)): ?>
        <tr>
            <td colspan="3" style="text-align: center;">No mod logs!</td>
        </tr>
    <?php else: ?>
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
