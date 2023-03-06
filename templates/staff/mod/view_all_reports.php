<?php
/**
 * @var System $system
 * @var array $reports;
 */
?>

<table class="table">
    <tr><th colspan="4">Reports</th></tr>
    <?php if(empty($reports['reports'])): ?>
        <tr><td colspan="4" style="text-align: center;">No reports</td></tr>
    <?php else: ?>
        <tr>
            <th>Reported User</th>
            <th>Reported By</th>
            <th>Reason</th>
            <th></th>
        </tr>
        <?php foreach($reports['reports'] as $report): ?>
            <tr>
                <td><?=$reports['users'][$report['user_id']]?></td>
                <td><?=$reports['users'][$report['reporter_id']]?></td>
                <td><?=$report['reason']?></td>
                <td><a href='<?=$system->links['report']?>&page=view_report&report_id=<?=$report['report_id']?>'>View</a></td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
</table>
