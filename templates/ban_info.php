<?php
/**
 * @var System $system
 * @var string $ban_type
 * @var int $ban_expire
 */
?>

<table class="table">
    <tr><th><?=ucwords($ban_type)?> Ban</th></tr>
    <tr>
        <td style="text-align: center;">
            <?php if($ban_type == StaffManager::BAN_TYPE_IP): ?>
                Your site access is currently restricted.
            <?php elseif($ban_expire == StaffManager::PERM_BAN_VALUE): ?>
                You are currently <b>PERMANENTLY</b> banned.
            <?php else: ?>
                You are currently banned.<br />
                <b>Time Remaining:</b> <?=$ban_expire?>
            <?php endif ?>
            <br />
            Visit the <a href="<?=$system->router->base_url?>/support.php">Support Center</a> to appeal this
            <?=($ban_type == StaffManager::BAN_TYPE_IP ? 'restriction' : 'ban')?>.
        </td>
    </tr>
</table>