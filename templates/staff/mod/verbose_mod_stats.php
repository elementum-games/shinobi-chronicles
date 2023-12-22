<?php
/**
 * @var System $system
 * @var array $record_user
 * @var array $staff_data
 */
?>
<script type="text/javascript">
    function toggleAll(type) {
        $('.'+type).toggle();

        let link = $('#'+type+'_HIDE');
        let showHide = (link.text() === 'Hide All' ? 'Show All' : 'Hide All');
        link.text(showHide);
    }
</script>


<table class="table">
    <tr><th>Staff Logs - <?=$record_user['user_name']?>(<?=$record_user['user_id']?>)</th></tr>
    <tr>
        <td>
            <table class="table" style="table-layout: auto; margin-top: 0;">
                <tr><th colspan="2">Chat Data (<?=count($staff_data['chat_posts'])?>) - <a id="CHAT_HIDE" onclick="toggleAll('CHAT')" style="cursor: pointer;">Show All</a></th></tr>
                <tr>
                    <th style="width: 25%;">Date</th>
                    <th>Content</th>
                </tr>
                <?php foreach($staff_data['chat_posts'] as $chat_data): ?>
                    <tr class='CHAT' style="text-align: center; display: none;">
                        <td>
                            <?= strftime('%b/%e/%Y  %H:%I:%S', $chat_data['time']) ?>
                        </td>
                        <td>
                            <?= $chat_data['message'] ?>
                            <?php if($chat_data['deleted']): ?>
                                <br /><em>(DELETED)</em>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </table>

            <table class="table" style="table-layout: auto;">
                <tr><th colspan="3">Mod Actions (<?=count($staff_data['action_log'])?>) - <a id="M_ACTION_HIDE" onclick="toggleAll('M_ACTION')" style="cursor: pointer;">Show All</a></th></tr>
                <tr>
                    <th style="width: 25%;">Date</th>
                    <th>Action</th>
                    <th>Content</th>
                </tr>
                <?php foreach($staff_data['action_log'] as $mod_action): ?>
                    <tr class='M_ACTION' style="text-align: center; display: none;">
                        <td><?= strftime('%b/%e/%Y  %H:%I:%S', $mod_action['time']) ?></td>
                        <td><?= System::unSlug($mod_action['type']) ?></td>
                        <td><?= $mod_action['content'] ?></td>
                    </tr>
                <?php endforeach ?>
            </table>

            <table class="table" style="table-layout: auto; margin-bottom: 10px;">
                <tr><th colspan="4">Reports Handled (<?=count($staff_data['reports_handled'])?>) - <a id="REPORTS_HIDE" onclick="toggleAll('REPORTS')" style="cursor: pointer;">Show All</a></th></tr>
                <tr>
                    <th style="width: 25%;">Date</th>
                    <th>Report Type</th>
                    <th>Verdict</th>
                    <th></th>
                </tr>
                <?php foreach($staff_data['reports_handled'] as $report): ?>
                    <tr class='REPORTS' style="text-align: center; display: none;">
                        <td><?= strftime('%b/%e/%Y  %H:%I:%S', $report['time']) ?></td>
                        <td><?= ReportManager::$report_types[$report['report_type']] ?></td>
                        <td><?= ReportManager::$report_verdicts[$report['status']] ?></td>
                        <td><a href="<?=$system->router->getUrl('report', ['page'=>'view_report', 'report_id'=>$report['report_id']])?>" target="_blank">View</a></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </td>
    </tr>
</table>