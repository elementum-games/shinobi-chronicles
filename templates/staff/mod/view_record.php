<?php
/**
 * @var User $player
 * @var System $system
 * @var string $self_link
 * @var string $user_name
 * @var array $reports
 * @var array $record
 * @var array $users
 */
?>

<table class='table'>
    <tr><th colspan='5'>Reports for <b><?=$user_name?></b></th></tr>
    <tr>
        <th>Reason</th>
        <th>Moderator</th>
        <th>Report Type</th>
        <th>Verdict</th>
        <th></th>
    </tr>
    <?php if(empty($reports)): ?>
        <tr>
            <td colspan="3" style="text-align: center;">No reports</td>
        </tr>
    <?php else:?>
        <?php foreach($reports as $id => $report): ?>
            <tr>
                <td><?=$report['reason']?></td>
                <td><?=($users[$report['moderator_id']] ?? 'N/A')?></td>
                <td><?=ReportManager::$report_types[$report['report_type']]?></td>
                <td><?=ReportManager::$report_verdicts[$report['status']]?></td>
                <td><a href='<?=$system->links['report']?>&page=view_report&report_id=<?=$id?>'>View</a></td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
</table>
<script type='text/javascript'>
    $(document).ready(function(){
        $('#commentBox').keypress(function( event ) {
            if (event.which == 13 && event.shiftKey) {
                $('#commentSubmit').trigger('click');
            }
        });
    });
</script>

<table id='commentTable' class="table" style="width:75%">
    <tr><th>Add Record Note</th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>#commentTable" method="post">
                <textarea id='commentBox' name='content' style="width:450px;height:250px;margin-bottom:5px;"></textarea><br />
                <em>(Shift + Enter to quick submit)</em><br />
                <input id='commentSubmit' type="submit" name="add_note" value="Add" />
            </form>
        </td>
    </tr>
</table>

<table class="table" style="table-layout:auto;">
    <tr><th colspan="5">Record Notes</th></tr>
    <?php if(empty($record)): ?>
        <tr><td colspan="5" style="text-align: center;">No record</td></tr>
    <?php else: ?>
        <tr>
            <th style="width:15%;">Moderator</th>
            <th style="width:8%;">Record Type</th>
            <th>Content</th>
            <th style="width:10%;">Time</th>
            <th style="width:8%;"></th>
        </tr>
        <?php foreach($record as $recordDisplay): ?>
            <?php if($recordDisplay['deleted'] && ($player->staff_manager->isUserAdmin() || $player->staff_manager->isHeadModerator())): ?>
                <tr>
                    <td style="text-align: center;"><?=$recordDisplay['staff_name']?> (<?=$recordDisplay['staff_id']?>)</td>
                    <td style="text-align: center;"><?=$system->unSlug($recordDisplay['record_type'])?></td>
                    <td>
                        <?=$recordDisplay['data']?><br />
                        <div style="width:100%;text-align: center;"><em>(Deleted)</em></div>
                    </td>
                    <td style="text-align: center;"><?=date("F j, Y", $recordDisplay['time'])?></td>
                    <td style="text-align: center;">
                        <?php if($player->staff_manager->isUserAdmin()): ?>
                            <form action="<?=$self_link?>" method="post">
                                <input type="hidden" name="record_id" value="<?=$recordDisplay['record_id']?>" />
                                <input type="hidden" name="user_id" value="<?=$recordDisplay['user_id']?>" />
                                <input type="submit" name="recover_record" value="Recover" />
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php elseif(!$recordDisplay['deleted']): ?>
                <tr>
                    <td style="text-align: center;"><?=$recordDisplay['staff_name']?> (<?=$recordDisplay['staff_id']?>)</td>
                    <td style="text-align: center;"><?=$system->unSlug($recordDisplay['record_type'])?></td>
                    <td><?=nl2br($recordDisplay['data'])?></td>
                    <td style="text-align: center;"><?=date("F j, Y", $recordDisplay['time'])?></td>
                    <td style="text-align: center;">
                        <?php if($player->staff_manager->isHeadModerator()): ?>
                            <form action="<?=$self_link?>" method="post">
                                <input type="hidden" name="record_id" value="<?=$recordDisplay['record_id']?>" />
                                <input type="hidden" name="user_id" value="<?=$recordDisplay['user_id']?>" />
                                <input type="submit" name="delete_record_note" value="Delete" />
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>
</table>