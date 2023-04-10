<?php
/**
 * @var User $player
 * @var System $system
 * @var string $self_link
 * @var array $banned_users
 */
?>

<table class='table' style="table-layout:auto;"><tr><th colspan='2'>Banned Users</th></tr>
    <tr>
        <th style="width:20%;">Username</th>
        <th>Ban type(s)</th>
    </tr>
    <?php if(empty($banned_users)): ?>
        <tr><td colspan="2" style="text-align: center;">No banned users</td></tr>
    <?php else: ?>
        <?php foreach($banned_users as $user): ?>
            <tr>
                <td style="text-align: center;">
                    <a href='<?=$system->router->links['members']?>&user=<?=$user['user_name']?>'><?=$user['user_name']?></a>
                </td>
                <td>
                    <?=$user['ban_string']?>
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif?>
</table>