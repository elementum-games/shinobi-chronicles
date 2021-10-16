<?php
/**
 * @var array $support
 * @var array $responses
 */
?>

<script type="text/javascript">
    function toggleDetails(id) {
        var rowID='#'+id;
        $(rowID).toggle();
    }
    function showAll() {
        $('.response').show();
    }
</script>

<style type="text/css">
    table label {
        widtH: 8em;
        font-weight:bold;
    }

    table .support_detail {
        width: 75%;
        margin: 0 auto 0 auto;
        padding: 3px;
        white-space:pre-wrap;
    }
    table .support_detail p {
        margin: 2px 0 2px 5px;
    }

    form textarea {
        width: 475px;
        height: 200px;
    }
    form input {
        margin-top: 3px;
        margin-bottom: 3px;
    }
</style>

<table class="table">
    <tr><th><?=$support['subject']?></th></tr>
    <tr><td>
        <label>Support Type:</label><?=$support['support_type']?><br />
        <label>Submitted:</label><?= strftime("%m/%d/%y @ %I:%M", $support['time']) ?><br />
        <?php if($support['time'] != $support['updated']): ?>
            <label>Last Updated:</label><?= strftime("%m/%d/%y @ %I:%M", $support['updated']) ?><br />
        <?php endif ?>
        <label>Details:</label><br />
        <div class="support_detail"><p><?=$support['message']?></p></div>
    </td></tr>
    <tr><th>Responses - <em><a onclick="showAll()" style="cursor: pointer;">Show All</a></em></th></tr>
    <?php if(!$responses): ?>
        <tr style="text-align:center;"><td>No responses!</td></tr>
    <?php else: ?>
        <?php foreach($responses as $response): ?>
            <tr onclick="toggleDetails(<?=$response['response_id']?>)" style="cursor: pointer;">
                <th><?=$response['user_name']?> - <?=strftime("%m/%d/%y @ %I:%M", $response['time'])?></th>
            </tr>
            <tr id="<?=$response['response_id']?>" class="response" style="display:none;"><td>
                <div class="support_detail"><p><?=$response['message']?></p></div>
            </td></tr>
        <?php endforeach ?>
    <?php endif ?>
</table>

<table class="table">
    <tr><th>Add Response</th></tr>
    <tr><td style="text-align: center;">
        <form action="<?=$self_link?>" method="post">
            <textarea name="message"></textarea><br />
            <input type="submit" name="add_response" value="Add Response" />
        </form>
    </td></tr>
</table>
