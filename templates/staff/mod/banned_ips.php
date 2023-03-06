<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var array $banned_ips
 */
?>

<table class="table" style="table-layout: auto;">
    <tr><th colspan="3">Banned IPs</th></tr>
    <?php if(empty($banned_ips)): ?>
        <tr>
            <td colspan="3" style="text-align: center;">
                No banned IPs.
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <th style="width:8%;">Ban Level</th>
            <th>IP Address</th>
            <th style="width:10%;"></th>
        </tr>
        <?php foreach($banned_ips as $ip): ?>
            <tr>
                <td style="text-align: center;">
                    <?=$ip['ban_level']?>
                </td>
                <td>
                    <?=$ip['ip_address']?>
                </td>
                <td>
                    <form action="<?=$self_link?>" method="post">
                        <input type="hidden" name="ip_address" value="<?=$ip['ip_address']?>" />
                        <input type="submit" name="unban_ip" value="Unban" />
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
</table>
