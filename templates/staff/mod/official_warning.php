<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 */
?>

<table class="table">
    <tr><th>Official Warning - <?=$user_data['user_name']?></th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>&official_warning=<?=$user_data['user_name']?>" method="post">
                <textarea name="content" style="width:450px;height:200px;margin-bottom:5px;"><?=nl2br($content)?></textarea><br />
                <input type="submit" name="send_official_warning" value="Send" />
            </form>
        </td>
    </tr>
</table>
