<?php
/**
 * @var string $self_link
 * @var string $user_name
 * @var int $user_to_kick
 */
?>

<table class='table'>
    <tr><th>Remove Team Member</th></tr>
    <tr>
        <td style='text-align:center;'>
            <form action='<?=$self_link?>' method='post'>
                Are you sure you want to kick <b><?=$user_name?></b> from the team?<br />
                <input type="hidden" name="user_id" value="<?=$user_to_kick?>" />
                <input type="hidden" name="kick_confirm" value="1" />
                <input type="submit" name='kick' value="Confirm" />
            </form>
        </td>
    </tr>
</table>