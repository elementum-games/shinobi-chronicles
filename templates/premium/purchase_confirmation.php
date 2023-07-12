<?php

function renderPurchaseConfirmation(
    string $purchase_type,
    string $confirmation_type,
    string $confirmation_string,
    string $form_action_link,
    string $form_submit_prompt,
    array $additional_form_data = [],
    int $ak_cost = 0,
): void {
?>
    <table class="table">
        <tr><th><?=System::unSlug($confirmation_type)?></th></tr>
        <tr style="text-align: center;">
            <td>
                <?=$confirmation_string?><br />
                <?php if(isset($ak_cost) && $ak_cost > 0): ?>
                    This will cost <?= $ak_cost ?> Ancient Kunai.
                <?php endif ?>
                <form action="<?= $form_action_link ?>" method="post">
                    <input type="hidden" name="<?= $confirmation_type ?>" value="1" />
                    <?php foreach($additional_form_data as $name => $data): ?>
                        <input type="<?=$data['input_type']?>" name="<?=$name?>" value="<?=$data['value']?>" />
                    <?php endforeach; ?>
                    <input type="submit" name="<?= $purchase_type ?>" value="<?= $form_submit_prompt ?>" />
                </form>
            </td>
        </tr>
    </table>
<?php
}
