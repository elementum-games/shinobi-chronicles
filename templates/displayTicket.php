<?php
/**
 * @var array $support
 * @var array $responses
 */
?>
<script type="text/javascript" src="/scripts/supportScripts.js"></script>
<script type='text/javascript'>
    var shiftPressed = false;
    $(document).ready(function(){
        $('#responseMessage').keypress(function( event ) {
            if (event.which == 13 && !event.shiftKey && $('#quickReply').prop('checked')) {
                $('#responseSubmit').trigger('click');
            }
        });
    });
</script>

<table class="table">
    <tr><th><?=$support['subject']?></th></tr>
    <tr><td>
        <label style="width:8em; font-weight:bold;">Support Type:</label><?=$support['support_type']?><br />
        <label style="width:8em; font-weight:bold;">Submitted:</label><?= strftime(SupportManager::$strfString, $support['time']) ?><br />
        <label style="width:8em; font-weight:bold;">Status:</label><?= ($support['open'] ? 'Open' : 'Closed') ?><br />
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
                <div style="white-space: pre-wrap; margin-left: 5px;"><p><?=$response['message']?></p></div>
            </td></tr>
        <?php endforeach ?>
    <?php endif ?>
</table>

<?php if($support['open']): ?>
<table class="table">
    <tr><th>Add Response</th></tr>
    <tr><td style="text-align: center;">
        <form action="<?=$self_link?>" method="post">
            <textarea id="responseMessage" name="message" style="display:block; width:500px;height:200px; margin:auto;"></textarea><br />
            <input type="checkbox" id="quickReply" checked="checked" />Quick Reply<br />
            <input type="submit" id="responseSubmit" name="add_response" value="Add Response" />
            <input type="submit" name="close_ticket" value="Cancel Request" />
        </form>
    </td></tr>
</table>
<?php endif ?>