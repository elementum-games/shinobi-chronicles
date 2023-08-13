<?php
/**
 * @var User $player
 * @var System $system
 * @var string $self_link
 */
?>
<style>
    .premium_credit_exchange label {
        width: 11em;
        display: inline-block;
        font-weight: bold;
        text-align: left;
    }

    .currency_amount {
        display: inline-block;
        width: 8.5em;
        text-align: left;
    }

    .create_offer {
        margin: 5px auto;
        text-align: center;
    }
    .create_offer input, .create_offer select {
        width: 9em;
        box-sizing: border-box;
    }
</style>
<table class="table premium_credit_exchange">
    <tr><th colspan="4">Ancient Kunai Exchange</th></tr>
    <tr>
        <td colspan="4">
            <div style="text-align: center;">
                <label>Your money:</label>
                <span class='currency_amount'>&yen;<?=number_format($player->money->getAmount())?></span><br />
                <label>Your Ancient Kunai:</label>
                <span class='currency_amount'><?=number_format($player->getPremiumCredits())?></span>
            </div>
        </td>
    </tr>
    <?php if(empty($offers)): ?>
        <tr>
            <td colspan="4" style="text-align: center;"><b>No Offers!</b></td>
        </tr>
    <?php else: ?>
        <tr>
            <th>Seller</th>
            <th>Ancient Kunai</th>
            <th>Cost</th>
            <th></th>
        </tr>
        <?php foreach($offers as $offer): ?>
            <?php $seller_name = $credit_users[$offer['seller']]; ?>
            <tr class="fourColGrid" style="text-align: center;">
                <td><a href='<?=$system->router->links['members']?>&user=<?=$offer['seller_name']?>'><?=$offer['seller_name']?></a></td>
                <td>
                    <?=number_format($offer['premium_credits'])?> Ancient Kunai
                </td>
                <td>
                    &yen;<?=number_format($offer['money'])?><br />
                    (&yen;<?=number_format($offer['money']/$offer['premium_credits'])?>/AK)
                </td>
                <td>
                    <?php if($offer['seller'] == $player->user_id): ?>
                        <a href='<?=$self_link?>&cancel=<?=$offer['id']?>'>Cancel</a>
                    <?php else: ?>
                        <a href='<?=$self_link?>&purchase=<?=$offer['id']?>'>Purchase</a>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    <?php endif; ?>
    <tr><th colspan="4">Create Offer</th></tr>
    <td colspan="4">
        <script type='text/javascript'>
            function calcPreview() {
                var total_credits = parseInt(<?=$player->getPremiumCredits()?>);
                var premium_credits = parseInt($('#premium_credits').val());
                var money = parseFloat($('#money option:selected').val());
                var total_money = premium_credits * (money * 1000);

                if(isNaN(total_money)) {
                    $('#offerPreview').html("");
                    return false;
                }
                else if(total_credits < premium_credits) {
                    $('#offerPreview').html("<b style='color:red;'>You do not have that much Ancient Kunai!</b>");
                }
                else {
                    $('#offerPreview').html('You are offering <b>' + premium_credits.toLocaleString('en-US') + '</b> Ancient Kunai for &yen;<b>'
                        + total_money.toLocaleString('en-US') + '</b>.');
                    return true;
                }
            }
        </script>
        <form action="<?=$self_link?>" method="post">
            <div class="create_offer">
                <label class="currency_label">Ancient Kunai:</label>
                <input
                    type='number'
                    name='premium_credits'
                    id='premium_credits'
                    min='1'
                    max='<?= $player->getPremiumCredits() ?>'
                    onKeyUp='calcPreview()'
                    onchange='calcPreview()'
                /><br />
                <label class="currency_label">Yen Each:</label>
                <select name='money' id='money' onchange='calcPreview();'>
                    <?php for($i = PremiumShopManager::EXCHANGE_MIN_YEN_PER_AK; $i <= PremiumShopManager::EXCHANGE_MAX_YEN_PER_AK; $i += 1): ?>
                        <option value='<?=sprintf("%.1f", $i)?>'>&yen;<?=number_format(sprintf("%.0f", $i*1000))?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div style="text-align: center;">
                <span id='offerPreview'>&nbsp;</span><br />
                <input type='submit' name='new_offer' value='Submit' />
            </div>
        </form>
    </td>
</table>