<?php
/**
 * @var SupportManager $supportSystem
 * @var string $self_link
 * @var string $request_type
 * @var string $subject
 * @var string $message
 */
?>

<table class="table" style="text-align: center;">
    <tr><th>Confirm Premium Cost</th></tr>
    <tr><td>
        <form action="<?=$self_link?>" method="post">
            <input type="hidden" name="support_type" value="<?=$request_type?>" />
            <input type="hidden" name="subject" value="<?=$subject?>" />
            <input type="hidden" name="message" value="<?=$message?>" />
            Submitting as a premium support will cost <?=$supportSystem->requestPremiumCosts[$request_type]?> <?=Currency::PREMIUM_SYMBOL?>.<br />
            Please confirm how you would like to process this support.<br />
            <input type="submit" name="confirm_prem_support" value="Submit as Premium" />
            <input type="submit" name="add_support" value="Submit as Regular" />
        </form>
    </td></tr>
</table>