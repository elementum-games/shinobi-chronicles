<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var array $staff_members
 * @var array $payment_rates
 */
?>
<style>
    label.payment_label {
        display: inline-block;
        width: 100px;
        height: 25px;
    }
</style>
<table class="table">
    <tr><th>Staff Payments</th></tr>
    <tr>
        <td>
            <form action="<?=$self_link?>" method="post">
                <?php foreach($staff_members as $member): ?>
                    <label class="payment_label"><?=$member['user_name']?>:</label>
                    <input type="text" name="<?=$member['user_id']?>_payment" value="<?=$payment_rates[$member['staff_level']]?>"/><br />
                <?php endforeach ?>
                <input type="submit" name="process_payments" value="Send Payments" />
            </form>
        </td>
    </tr>
</table>
