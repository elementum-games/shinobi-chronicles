<?php
/**
 * @var ReportManager $reportManager
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var string $user_name
 * @var int $report_type
 * @var int $content_id
 * @var array $content_data
 */
?>

<style type='text/css'>
    label {
        display:inline-block;
        width:110px;
        font-weight:bold;
    }
</style>

<table class="table">
    <tr><th>Submit Report</th></tr>
    <tr>
        <td>
            <form action="<?=$self_link?>" method="post">
                <label>Reported User:</label><?=$user_name?><br />
                <label>Report Type:</label><?=ReportManager::$report_types[$report_type]?><br />
                <?php if($report_type != ReportManager::REPORT_TYPE_PROFILE): ?>
                    <label>Reported content:</label><br />
                    <p style='width:500px;margin-top:3px;border:1px solid #000;margin-left:25px;padding:4px;'>
                        <?=wordwrap($system->html_parse(stripslashes($content_data['message'])), 70)?>
                    </p>
                <?php endif ?>
                <label for='reason'>Reason:</label><br />
                <p style='margin-top:2px;margin-left:25px;'>
                    <select name='reason'>
                        <?php foreach(ReportManager::$report_reasons as $value => $name): ?>
                            <option value="<?=$value?>"><?=$name?></option>
                        <?php endforeach ?>
                    </select><br />
                </p>
                <label for='notes'>Notes (optional)</label><br />
                    <textarea name='notes' style='height:55px;width:300px;margin-left:25px;margin-top:5px;'></textarea><br />

                <input type='hidden' name='content_id' value='<?=$content_id?>' />
                <input type='hidden' name='report_type' value='<?=$report_type?>' />
                <p style='text-align:center;'>
                    <input type='submit' name='submit_report' value='Submit' />
                </p>
            </form>
        </td>
    </tr>
</table>
