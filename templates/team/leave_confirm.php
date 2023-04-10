<?php
/**
 * @var User $player
 * @var string $self_link
 */
?>

<table class="table">
    <tr><th>Leave Team</th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="get">
                <input type="hidden" name="id" value="<?=Router::PAGE_IDS['team']?>" />
                <input type="hidden" name="leave_team" value="1" />
                <input type="hidden" name="leave_confirm" value="1" />
                Are you sure you want to leave <b><?=$player->team->name?></b>?<br />
                <input type="submit" value="Leave" />
            </form>
        </td>
    </tr>
</table>
