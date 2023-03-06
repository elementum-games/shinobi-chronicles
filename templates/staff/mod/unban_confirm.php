<?php
/**
 * @var string $self_link
 * @var string $unban_type
 * @var array $user_data
 */
?>

<table class='table'>
    <tr><th>Confirm Ban Removal</th></tr>
    <tr>
        <td style='text-align:center;'>
            Remove <?=$user_data['user_name']?>'s <?=ucwords($unban_type)?> ban?<br />
            <form action='<?=$self_link?>' method='post'>
                <input type='hidden' name='user_name' value='<?=$user_data['user_name']?>' />
                <input type="hidden" name="ban_type" value="<?=$unban_type?>" />
                <input type='hidden' name='confirm' value='1' />
                <input type='submit' name='unban' value='Confirm' />
            </form>
        </td>
    </tr>
</table>
