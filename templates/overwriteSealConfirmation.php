<?php
/**
 * @var User $player
 * @var System $system
 * @var string $self_link
 * @var int $seal_level
 * @var int $seal_length
 */
?>

<table class="table">
    <tr><th>Confirm Change of Seal</th></tr>
    <tr>
        <td style="text-align: center;">
            Are you sure you would like to change from your <?=$player->forbidden_seal->name?>? You will lose
            <?=$system->time_remaining($player->forbidden_seal->seal_time_remaining)?> of premium time.
            <b>This can not be undone!</b><br />
            <br />
            <form action="<?=$self_link?>&view=forbidden_seal" method="post">
                <input type="hidden" name="confirm_seal_overwrite" value="1" />
                <input type="hidden" name="seal_level" value="<?=$seal_level?>" />
                <input type="hidden" name="seal_length" value="<?=$seal_length?>" />
                <input type="submit" name="forbidden_seal" value="Confirm" />
            </form>
        </td>
    </tr>
</table>