<?php
/**
 * @var System $system
 * @var SupportManager $supportManager
 * @var User $player
 * @var string $self_link
 * @var array $supports
 * @var array $supportData
 * @var array $supportResposnes
 * @var int $support_id
 * @var int|null $next
 * @var int|null $previous
 * @var int|null $offset
 * @var string $category
 */
?>

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
    <tr><th colspan="2">Support Filters</th></tr>
    <tr>
        <td colspan="2">
            <div class="submenu">
                <ul class="submenu">
                    <li style="width:32.75%;"><a href="<?=$self_link?>&category=awaiting_staff">Awaiting Staff</a></li>
                    <li style="width:32.75%;"><a href="<?=$self_link?>&category=awaiting_user">Awaiting User</a></li>
                    <li style="width:32.75%;"><a href="<?=$self_link?>&category=closed">Closed</a></li>
                </ul>
            </div>
        </td>
    </tr>
    <tr style="text-align: center;">
        <td>
            <form action="<?=$self_link?>" method="get">
                <input type="hidden" name="id" value="30" />
                <input type="hidden" name="support_search" value="true" />
                <label style="width:6.5em; font-weight:bold;">Username:</label><input type="text" name="user_name" />
                <input type="submit" value="Search" />
            </form>
        </td>
        <td>
            <form action="<?=$self_link?>" method="get">
                <input type="hidden" name="id" value="30" />
                <input type="hidden" name="support_search" value="true" />
                <label style="width:6.5em; font-weight:bold;">Supp. Type:</label>
                <select name="support_type">
                    <?php foreach($supportTypes as $type): ?>
                        <option value="<?=$type?>"><?=$type?></option>
                    <?php endforeach ?>
                </select>
                <input type="submit" value="Search" />
            </form>
        </td>
    </tr>
    <tr style="text-align: center;">
        <td>
            <form action="<?=$self_link?>" method="get">
                <input type="hidden" name="id" value="30" />
                <input type="hidden" name="support_search" value="true" />
                <label style="width:6.5em; font-weight:bold;">IP Address:</label><input type="text" name="ip_address" />
                <input type="submit" value="Search" />
            </form>
        </td>
        <td>
            <form action="<?=$self_link?>" method="get">
                <input type="hidden" name="id" value="30" />
                <input type="hidden" name="support_search" value="true" />
                <label style="width:6.5em; font-weight:bold;">Supp. Key:</label><input type="text" name="support_key" />
                <input type="submit" value="Search" />
            </form>
        </td>
    </tr>
</table>

<?php if(!$support_id): ?>
    <table class="table">
        <tr>
            <th style="width: 20%;">Support Type</th>
            <th style="width: 40%;">Subject</th>
            <th style="width: 15%;">Submitted By</th>
            <th style="width: 10%;">Date</th>
            <th style="width:  8%;"></th>
        </tr>
        <?php if(!empty($supports)): ?>
        <?php foreach($supports as $support): ?>
                <tr style="text-align:center;">
                    <td><?= $support['support_type'] ?></td>
                    <td><?= $support['subject'] ?></td>
                    <td><?= $support['user_name'] ?></td>
                    <td><?= strftime(SupportManager::$strfString, $support['time']) ?></td>
                    <td><a href="<?=$self_link?>&support_id=<?=$support['support_id']?>">View</a></td>
                </tr>
            <?php endforeach ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No supports.</td>
            </tr>
        <?php endif ?>
    </table>
    <div style="width:50%; margin:auto; text-align: center;">
        <?php if(!is_null($previous) && ($offset != $previous)): ?>
        <a href="<?=$self_link?>&offset=<?=$previous?>">Previous</a>
        <?php endif ?>
        <?php if((!is_null($previous) && $previous != $offset) && (!is_null($next) && $next != $offset)):?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <?php endif ?>
        <?php if(!is_null($next) && ($offset != $next)): ?>
        <a href="<?=$self_link?>&offset=<?=$next?>">Next</a>
        <?php endif ?>
    </div>
<?php else: ?>
    <?php if(!$supportData): ?>
        <?php
            $system->message("Invalid support!");
            $system->printMessage();
        ?>
    <?php elseif(!$supportManager->canProcess($supportData['support_type'])):
        $system->message("No authorized to view or process this support!");
        $system->printMessage();
        ?>
    <?php else: ?>
        <script type="text/javascript" src="/scripts/supportScripts.js"></script>
        <table class="table">
            <tr><th><?=$supportData['subject']?></th></tr>
            <tr>
                <td>
                    <label style="width:8em; font-weight:bold;">Submitted By:</label><?=$supportData['user_name']?><br />
                    <label style="width:8em; font-weight:bold;">Status:</label><?=($supportData['open']) ? 'Open' : 'Closed'?><br />
                    <label style="width:8em; font-weight:bold;">Premium:</label><?=($supportData['premium'] ? 'Yes' : 'No')?><br />
                    <?=($supportData['user_id'] == 0 && isset($supportData['support_key']))
                        ? "<label style='width:8em; font-weight:bold;'>Support Key:</label>{$supportData['support_key']}<br />"
                        : "" ?>
                    <label style="width:8em; font-weight:bold;">Type:</label><?=$supportData['support_type']?><br />
                    <label style="width:8em; font-weight:bold;">Submitted:</label>
                        <?=strftime(SupportManager::$strfString, $supportData['time'])?><br />
                    <label style="width:8em; font-weight:bold;">Last Updated:</label>
                        <?=strftime(SupportManager::$strfString, $supportData['updated'])?><br />
                    <label style="width:8em; font-weight:bold;">Details:</label><br />
                        <div style="margin-left:8em; white-space:pre-wrap;"><?=$supportData['message']?></div>
                </td>
            </tr>
            <?php if(!empty($supportResponses)): ?>
                <tr><th>Responses - <a onclick="showAll()" style="cursor: pointer;"><em>Show All</em></a></th></tr>
                <?php foreach($supportResponses as $pos=>$response): ?>
                    <tr onclick="toggleDetails(<?=$response['response_id']?>)" style="cursor: pointer;"><th><?=$response['user_name']?> - <?=strftime(SupportManager::$strfString, $response['time'])?></th></tr>
                    <tr id="<?=$response['response_id']?>" class="response" style="display:<?=($pos==0 ? 'table-row' : 'none')?>;"><td><div style="margin-left:8em; white-space:pre-wrap"><?=$response['message']?></div></td></tr>
                <?php endforeach ?>
            <?php endif ?>
            </table>
            <!-- Response Table -->
            <table class="table">
                <tr><th>Add Response</th></tr>
                <tr style="text-align:center;"><td>
                    <form action="<?=$self_link?>&support_id=<?=$supportData['support_id']?>" method="post">
                        <textarea id="responseMessage" name="message" style="width:500px;height:200px;"></textarea><br />
                        <br />
                        <input type="checkbox" id="quickReply" checked="checked" />Quick Reply<br />
                        <?php if ($supportData['open']): ?>
                            <input type="submit" id="responseSubmit" name="add_response" value="Reply" />
                            <input type="submit" name="close_ticket" value="Close" />
                        <?php else: ?>
                            <input type="submit" id="responseSubmit" name="open_ticket" value="Re-open" />
                        <?php endif ?>
                    </form>
                </td></tr>
            </table>
    <?php endif ?>
<?php endif ?>
