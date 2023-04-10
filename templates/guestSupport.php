<?php
/**
 * @var System $system
 * @var SupportManager $supportManager
 * @var array $request_types
 * @var array $responses
 * @var bool $supportCreated
 * @var string $support_key
 */
?>

<?php if($supportCreated): ?>
    <table class="table">
        <tr><th>Support Created</th></tr>
        <tr><td>
            Your support has been created. You will receive an email shortly from our system confirming, be sure to
                check your spam folder. Your support key is <?=$support_key?>. Keep this key safe as it will act as
                a password to access your support.
        </td></tr>
    </table>
<?php endif ?>
<?php if(!isset($_GET['support_key']) && !$supportCreated): ?>
<table class="table">
    <tr><th>Shinobi Chronicles Support System</th></tr>
    <tr><td>
        Welcome to Shinobi Chronicles Support. We are happy to help you, whether you are looking to have your questions
            answered before joining or are having issues accessing the site after signing up. Please note that you must
            provide a valid email to submit a support as a guest. We will notify you of updates to your support through
            email.<br />
        <br />
        We are currently unable to service the following email domains: <?= implode(', ', System::UNSERVICEABLE_EMAIL_DOMAINS) ?>
        </td></tr>
        <tr><th>Access an Ongoing Support</th></tr>
        <tr><td>
        If you already have submitted a support, you may enter the support key below to respond to staff replies or provide
            updated information if a staff member has yet to respond.<br />
            <br />
        <form action="<?=$system->router->base_url?>support.php" method="get">
            <label style="width:8em; font-weight:bold;">Support Key:</label><input type="text" name="support_key" /><br />
            <label style="width:8em; font-weight:bold;">Email:</label><input type="text" name="email" /><br />
            <input type="submit" value="Search"/>
        </form>
        </td></tr>
        <tr><th>Submit a new Support</th></tr>
        <tr><td>
        <form action="<?=$system->router->base_url?>support.php" method="post">
            <label style="width:8em; font-weight:bold;">Name:</label><input type="text" name="name" /><br />
            <label style="width:8em; font-weight:bold;">Subject:</label><input type="text" name="subject" /><br />
            <label style="width:8em; font-weight:bold;">Email:</label><input type="text" name="email" /><br />
            <label style="width:8em; font-weight:bold;">Support Type:</label><select name="support_type">
                <?php foreach($request_types as $type): ?>
                    <option value="<?=$type?>"><?=$type?></option>
                <?php endforeach ?>
            </select><br />
            <label style="width:8em; font-weight: bold;">Details:</label>
            <textarea style='display:block; margin: 0 auto 0 auto; width:500px; height:150px;' name="message"></textarea><br />
            <input type="submit" name="add_support" name="Submit" />
        </form>
    </td></tr>
</table>
<?php elseif(isset($_GET['support_key']) && (isset($_GET['email']) && $_GET['email'] != '')): ?>
<script type="text/javascript" src="/scripts/supportScripts.js"></script>
<table class="table">
    <?php if(!$supportData): ?>
        <tr><td>Support not found!</td></tr>
    <?php else:?>
        <tr><th><?= $supportData['subject']?></th></tr>
        <tr><td>
            <label style="width: 8em; font-weight:bold;">Submitted By:</label><?=$supportData['user_name']?><br />
            <label style="width: 8em; font-weight:bold;">Assigned To:</label><?=($supportData['admin_name'] ? $supportData['admin_name'] : 'Not Assigned')?><br />
            <label style="width: 8em; font-weight:bold;">Support Key:</label><?= $supportData['support_key']?><br />
            <label style="width: 8em; font-weight:bold;">Date:</label><?= strftime(SupportManager::$strfString, $supportData['time']) ?><br />
            <label style="width: 8em; font-weight:bold;">Last Updated:</label><?= strftime(SupportManager::$strfString, $supportData['updated'])?><br />
            <label style="width: 8em; font-weight:bold;">Details:</label><div style="white-space:pre-wrap; display:inline-block"><?=$supportData['message']?></div>
        </td></tr>
    <?php endif ?>
    <?php if($responses && !empty($responses)): ?>
        <tr><th>Responses - <a onclick="showAll()" style="cursor: pointer;"><em>Show All</em></a></th></tr>
        <?php foreach($responses as $pos=>$response): ?>
            <tr onclick="toggleDetails(<?=$response['response_id']?>)" style="cursor: pointer;"><th><?=$response['user_name']?> - <?= strftime(SupportManager::$strfString, $response['time'])?></th></tr>
            <tr id="<?=$response['response_id']?>" class="response" style="display:<?=($pos==0 ? 'table-row' : 'none')?>;"><td><?=$response['message']?></td></tr>
        <?php endforeach ?>
    <?php endif ?>
</table>
<?php if($supportData['open']): ?>
    <table class="table">
        <tr><th>Add Response</th></tr>
        <tr><td>
            <form action="<?=$system->router->base_url?>support.php?support_key=<?=$supportData['support_key']?>&email=<?=$supportData['email']?>" method="post">
                <textarea name="message" style="display:block;width:500px;height:200px;margin:5px auto 5px auto;"></textarea><br />
                <input type="submit" name="add_response" value="Add Response" />
            </form>
        </td></tr>
    </table>
<?php endif ?>
<?php endif ?>