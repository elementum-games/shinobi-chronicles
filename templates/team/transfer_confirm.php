<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var int $new_leader
 * @var string $new_leader_name
 */
?>

<table class="table">
    <tr><th>Confirm Leadership Transfer</th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="post">
                Are you sure you want to transfer leadership to <?=$new_leader_name?>?<br />
                <input type="hidden" name="confirm" value="1">
                <input type="hidden" name="user_id" value="<?=$new_leader?>" />
                <input type="submit" name="transfer_leader" value="Transfer" />
            </form>
        </td>
    </tr>
</table>
