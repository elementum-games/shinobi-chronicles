<?php
/**
 * @var string $ban_type
 * @var int $ban_expire
 */
?>

<table class="table">
    <tr><th><?=ucwords($ban_type)?> Ban</th></tr>
    <tr>
        <td style="text-align: center;">
            <?php if($ban_expire == StaffManager::PERM_BAN_VALUE): ?>
                You are currently <b>PERMANENTLY</b> <?=ucwords($ban_type)?> banned.
            <?php else: ?>
                You are currently <?=ucwords($ban_type)?> banned.<br />
                <b>Time Remaining:</b> <?=$ban_expire?>
            <?php endif ?>
        </td>
    </tr>
</table>