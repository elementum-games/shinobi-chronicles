<?php
/**
 * @var array $support
 * @var array $responses
 */
?>
<script type="text/javascript" src="/scripts/supportScripts.js"></script>
<table class="table">
    <tr><th><?=$support['subject']?></th></tr>
    <tr><td>
        <label style="width:8em; font-weight:bold;">Support Type:</label><?=$support['support_type']?><br />
        <label style="width:8em; font-weight:bold;">Submitted:</label><?= strftime(SupportManager::$strfString, $support['time']) ?><br />
        <label style="width:8em; font-weight:bold;">Status:</label><?= ($support['open'] ? 'Open' : 'Closed') ?><br />
        <?php if($support['assigned_to'] != ''): ?>
            <label style="width:8em; font-weight:bold;">Assigned To:</label><?=$support['admin_name']?><br />
        <?php endif ?>
        <?php if($support['time'] != $support['updated']): ?>
            <label style="width:8em; font-weight:bold;">Last Updated:</label><?= strftime(SupportManager::$strfString, $support['updated']) ?><br />
        <?php endif ?>
        <label style="width:8em; font-weight:bold;">Details:</label><div style="white-space:pre-wrap; display:inline-block;"><?=$support['message']?></div>
    </td></tr>
    <tr><th>Responses - <em><a onclick="showAll()" style="cursor: pointer;">Show All</a></em></th></tr>
    <?php if(!$responses): ?>
        <tr style="text-align:center;"><td>No responses!</td></tr>
    <?php else: ?>
        <?php foreach($responses as $pos=>$response): ?>
            <tr onclick="toggleDetails(<?=$response['response_id']?>)" style="cursor: pointer;">
                <th><?=$response['user_name']?> - <?=strftime(SupportManager::$strfString, $response['time'])?></th>
            </tr>
            <tr id="<?=$response['response_id']?>" class="response" style="display:<?=($pos == 0 ? 'table-row' : 'none')?>"><td>
                <div class="support_detail"><p><?=$response['message']?></p></div>
            </td></tr>
        <?php endforeach ?>
    <?php endif ?>
</table>

<?php if($support['open']): ?>
<table class="table">
    <tr><th>Add Response</th></tr>
    <tr><td style="text-align: center;">
        <form action="<?=$self_link?>" method="post">
            <textarea name="message"></textarea><br />
            <input type="submit" name="add_response" value="Add Response" />
            <input type="submit" name="close_ticket" value="Cancel Request" />
        </form>
    </td></tr>
</table>
<?php endif ?>