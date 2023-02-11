<?php
/**
 * @var User $player
 * @var System $system
 * @var string $self_link
 */
?>
<style>
    label.currency_label {
        width: 11em;
        display: inline-block;
        font-weight: bold;
    }
</style>
<table class="table">
    <tr><th colspan="4">Ancient Kunai Exchange</th></tr>
    <tr>
        <td colspan="4">
            <div style="margin-left:15px;">
                <label class="currency_label">Your money:</label>
                    &yen;<?=number_format($player->getMoney())?><br />
                <label class="currency_label">Your Ancient Kunai:</label>
                    <?=number_format($player->getPremiumCredits())?>
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
            <th>Offering</th>
            <th>Requesting</th>
            <th></th>
        </tr>
        <?php foreach($offers as $offer): ?>
            <?php $seller_name = $credit_users[$offer['seller']]; ?>
            <tr class="fourColGrid" style="text-align: center;">
                <td><a href='<?=$system->links['members']?>&user=<?=$offer['seller_name']?>'><?=$offer['seller_name']?></a></td>
                <td>
                    <?php if($offer['offer_type'] == $TRADE_TYPE_ANCIENT_KUNAI): ?>
                        <?=number_format($offer['premium_credits'])?> Ancient Kunai
                    <?php elseif($offer['offer_type'] == $TRADE_TYPE_YEN): ?>
                        &yen;<?=number_format($offer['money'])?><br />
                        <em>(&yen;<?=number_format($offer['money']/$offer['premium_credits'])?>/AK)</em>
                    <?php endif ?>
                </td>
                <td>
                    <?php if($offer['offer_type'] == $TRADE_TYPE_ANCIENT_KUNAI): ?>
                        &yen;<?=number_format($offer['money'])?><br />
                        <em>(&yen;<?=number_format($offer['money']/$offer['premium_credits'])?>/AK)</em>
                    <?php elseif($offer['offer_type'] == $TRADE_TYPE_YEN): ?>
                        <?=number_format($offer['premium_credits'])?> Ancient Kunai
                    <?php endif ?>
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
    <?php endif ?>
</table>

<script type='text/javascript'>
    function calcPreview() {
        var offer_type = $('#offer_type').val();
        var premium_credits = parseInt($('#premium_credits').val());
        var money = parseFloat($('#money option:selected').val());
        var total_money = premium_credits * (money * 1000);
        if(isNaN(total_money)) {
            return false;
        }
        else {
            if(offer_type == <?=$TRADE_TYPE_ANCIENT_KUNAI?>) {
                $('#offerPreview').html('You are offering <b>' + premium_credits.toLocaleString('en-US') + '</b> Ancient Kunai for &yen;<b>'
                    + total_money.toLocaleString('en-US') + '</b>.');
            }
            else if(offer_type == <?=$TRADE_TYPE_YEN?>) {
                $('#offerPreview').html('You are offering &yen;<b>' + total_money.toLocaleString('en-US') + '</b> for <b>'
                    + premium_credits.toLocaleString('en-US') + '</b> Anicent Kunai.');
            }
            return true;
        }
    }
</script>
<table class="table">
    <tr><th>Create Offer</th></tr>
    <td>
        <form action="<?=$self_link?>" method="post">
            <div style="margin-left: 50px;margin-bottom:5px;">
                <label class="currency_label">Ancient Kunai:</label>
                <input style="width:115px;" type='text' name='premium_credits' id='premium_credits' style='width:80px;margin-left:2px;' onKeyUp='calcPreview()' /><br />
                <label class="currency_label">Money:</label>
                <select style="width:115px;" onchange='calcPreview();' name='money' id='money'>
                    <?php for($i = $price_min; $i <= $price_max; $i += 0.5): ?>
                        <option value='<?=sprintf("%.1f", $i)?>'>&yen;<?=number_format(sprintf("%.0f", $i*1000))?></option>
                    <?php endfor ?>
                </select><br />
                <label class="currency_label">Offering:</label>
                <select style="width:115px;" onchange="calcPreview()" name="offer_type" id="offer_type">
                    <option value="<?=$TRADE_TYPE_ANCIENT_KUNAI?>"><?=$trade_types[$TRADE_TYPE_ANCIENT_KUNAI]?></option>
                    <option value="<?=$TRADE_TYPE_YEN?>"><?=$trade_types[$TRADE_TYPE_YEN]?></option>
                </select>
            </div>
            <div style="text-align: center;">
                <span id='offerPreview'>&nbsp;</span><br />
                <input type='submit' name='new_offer' value='Submit' />
            </div>
        </form>
    </td>
</table>