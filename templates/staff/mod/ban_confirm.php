<?php
/**
 * @var int $ban_length
 * @var string $ban_length_key
 * @var string $ban_type
 * @var string $self_link
 * @var array $user_data
 */
?>

<table class="table">
    <tr><th>Confirm Ban</th></tr>
    <tr>
        <td style="text-align: center;">
            <?php if($ban_length == StaffManager::PERM_BAN_VALUE): ?>
                Issue permanent <?=ucwords($ban_type)?> Ban?
            <?php elseif($ban_length >= StaffManager::MINUTES_PER_DAY * 30): ?>
				<?=ucwords($ban_type)?> Ban <?=$user_data['user_name']?> for <?=($ban_length / (30 * StaffManager::MINUTES_PER_DAY))?> month(s)?<br />
            <?php elseif($ban_length >= StaffManager::MINUTES_PER_DAY): ?>
                <?=ucwords($ban_type)?> Ban <?=$user_data['user_name']?> for <?=$ban_length / StaffManager::MINUTES_PER_DAY ?> day(s)?<br />
            <?php else: ?>
                <?=ucwords($ban_type)?> Ban <?=$user_data['user_name']?> for <?=$ban_length?> minutes?<br />
            <?php endif ?>
            <form action="<?=$self_link?>" method="post">
                <input type='hidden' name='user_name' value='<?=$user_data['user_name']?>' />
                <input type='hidden' name='ban_type' value='<?=$ban_type?>' />
                <input type='hidden' name='ban_length_key' value='<?=$ban_length_key?>' />
                <input type='hidden' name='confirm' value='1' />
                <input type='submit' name='ban' value='Confirm' />
            </form>
        </td>
    </tr>
</table>