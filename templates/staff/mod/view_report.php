<?php
/**
 * @var System $system
 * @var User $player
 * @var ReportManager $reportManager
 * @var string $self_link
 * @var array $user_names
 * @var array $report
 */
?>

<style type='text/css'>
    label {
        display:inline-block;
        width:10em;
        font-weight:bold;
    }
</style>

<table class="table">
    <tr><th>View Report</th></tr>
    <tr>
        <td>
            <label>Reported user:</label><a target="_blank" href="<?=$system->links['members']?>&user=<?=$user_names[$report['user_id']]?>"><?=$user_names[$report['user_id']]?></a>
            <a target="_blank" href="<?=$system->links['mod']?>&view_record=<?=$user_names[$report['user_id']]?>">(View Record)</a>
            <br />
            <label>Reported by:</label><?=$user_names[$report['reporter_id']]?><br />
            <label>Report type:</label><?=ReportManager::$report_types[$report['report_type']]?>
            <?php if($report['report_type'] == ReportManager::REPORT_TYPE_CHAT): ?>
                &nbsp;<a href="<?=$system->links['chat_log']?>&report_id=<?=$report['report_id']?>">(View Chat Log)</a>
            <?php endif ?>
            <br />
            <?php if($report['status'] != ReportManager::VERDICT_UNHANDLED): ?>
                <label>Report verdict:</label><?=ReportManager::$report_verdicts[$report['status']]?><br />
            <?php endif ?>
            <br />
            <?php if($report['report_type'] != ReportManager::REPORT_TYPE_PROFILE): ?>
                <label>Reported content:</label><br />
                <p style='width:500px;margin-top:3px;border:1px solid #000;margin-left:25px;padding:4px;'>
                    <?=wordwrap($system->html_parse(html_entity_decode(stripslashes($report['content']))), 70)?>
                </p>
            <?php endif ?>
            <label for='reason'>Reason:</label><br />
            <p style='margin-top:2px;margin-left:25px;'>
                <?=$report['reason']?>
            </p>
            <?php if($report['notes']):?>
                <label for='notes'>Notes:</label><br />
                <p style='margin-left:25px;margin-top:5px;'>
                    <?=wordwrap($system->html_parse(stripslashes($report['notes'])), 70)?>
                </p>
            <?php endif ?>
            <?php if($report['status'] == $reportManager::VERDICT_UNHANDLED): ?>
                <label for='verdict'>Verdict:</label>
                <p style='margin-left:25px;margin-top:5px;'>
                    <form action='<?=$self_link?>&page=view_report&report_id=<?=$report['report_id']?>' method='post'>
                        <div style="margin-left:65px;display:inline;"></div>
                        <input type='submit' name='handle_report' value='<?=ReportManager::$report_verdicts[ReportManager::VERDICT_GUILTY]?>' />
                        <input type='submit' name='handle_report' value='<?=ReportManager::$report_verdicts[ReportManager::VERDICT_NOT_GUILTY]?>' />
                    </form>
                </p>
                <div style="height:5px;"></div>
            <?php elseif($player->staff_manager->isHeadModerator()): ?>
                <label for='verdict'>Alter Verdict:</label>
                <p style='margin-left:25px;margin-top:5px;'>
                    <form action='<?=$self_link?>&page=view_report&report_id=<?=$report['report_id']?>' method='post'>
                        <div style="margin-left:95px;display:inline;"></div>
                        <input type='submit' name='alter_report' value='<?=(ReportManager::$report_verdicts[$report['status'] == ReportManager::VERDICT_GUILTY ? ReportManager::VERDICT_NOT_GUILTY : ReportManager::VERDICT_GUILTY])?>' />
                    </form>
                </p>
                <div style="height:5px;"></div>
            <?php endif ?>
        </td>
    </tr>
</table>
