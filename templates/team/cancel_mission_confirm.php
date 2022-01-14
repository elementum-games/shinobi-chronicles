<?php
/**
 * @var string $self_link
 */
?>

<table class="table">
    <tr><th>Cancel Mission</th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="get">
                Are you sure you want to cancel your teams mission?<br />
                <input type="hidden" name="id" value="<?=System::PAGE_IDS['team']?>" />
                <input type="hidden" name="cancel_mission" value="1" />
                <input type="hidden" name="confirm" value="1" />
                <input type="submit" value="Confirm" />
            </form>
        </td>
    </tr>
</table>
