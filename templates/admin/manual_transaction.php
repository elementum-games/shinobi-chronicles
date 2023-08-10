<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 * @var array $currency_types
 */
?>
<style>
    label.trans_label {
        display:inline-block;
        width: 75px;
    }
</style>
<table class="table">
    <tr><th>Manual Currenct Transaction</th></tr>
    <tr>
        <td>
            <form action="<?=$self_link?>" method="post">
                <label class="trans_label">Username:</label><input type="text" name="user_name" /><br />
                <label class="trans_label">Amount:</label><input type="text" name="amount"><br />
                <label class="trans_label">Currency:</label><select name="currency">
                    <?php foreach($currency_types as $c_type): ?>
                        <option value="<?=$c_type?>"><?=System::unSlug($c_type)?></option>
                    <?php endforeach ?>
                </select><br />
                <label class="trans_label">Type:</label><select name="trans_type">
                    <?php foreach($trans_types as $t_type): ?>
                        <option value="<?=$t_type?>"><?=System::unSlug($t_type)?></option>
                    <?php endforeach ?>
                </select><br />
                <input type="submit" name="process_transaction" value="Process" />
            </form>
        </td>
    </tr>
</table>
