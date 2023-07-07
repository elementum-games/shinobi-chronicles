<?php
/**
 * @var System $system
 * @var string $self_link
 * @var string $confirmation_type
 * @var string $confirmation_string
 * @var string $submit_value
 * @var string $button_value
 * @var int|string $akCost
 */
?>

<table class="table">
    <tr><th><?=System::unSlug($confirmation_type)?></th></tr>
    <tr style="text-align: center;">
        <td>
            <?=$confirmation_string?><br />
            <?php if(isset($akCost) && $akCost > 0): ?>
                This will cost <?=$akCost?> Ancient Kunai.
            <?php endif ?>
            <form action="<?=$self_link?>" method="post">
                <input type="hidden" name="<?=$confirmation_type?>" value="1" />
                <?php if(isset($additional_form_data) && is_array($additional_form_data)): ?>
                    <?php foreach($additional_form_data as $name => $data): ?>
                        <input type="<?=$data['input_type']?>" name="<?=$name?>" value="<?=$data['value']?>" />

                    <?php endforeach ?>
                <?php endif ?>
                <input type="submit" name="<?=$submit_value?>" value="<?=$button_value?>" />
            </form>
        </td>
    </tr>
</table>
