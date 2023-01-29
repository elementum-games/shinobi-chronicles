<?php
/**
 * @var User $player
 * @var string $self_link
 * @var array $locked_out_users
 */
?>

<table class='table'>
    <tr><th colspan='3'>Locked Out Users</th></tr>
    <?php if(empty($locked_out_users)): ?>
        <tr>
            <td colspan="3" style="text-align: center;">No locked out users</td>
        </tr>
    <?php else: ?>
        <tr>
            <th style='width:60%;'>Username</th>
            <th style='width:20%;'>Type</th>
            <th style='width:20%;'>&nbsp;</th>
        </tr>
        <?php foreach($locked_out_users as $user): ?>
            <tr>
                <td><?=$user['user_name']?></td>
                <td><?=($user['failed_logins'] >= User::FULL_LOCK ? 'Full' : 'Partial')?></td>
                <td>
                    <?php if($player->staff_manager->isHeadModerator()):?>
                        <a href='<?=$self_link?>&view=locked_out_users&unlock_account=<?=$user['user_id']?>'>Unlock</a>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif ?>
</table>
