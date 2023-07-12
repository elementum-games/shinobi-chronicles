<?php
/**
 * @var System $system;
 * @var PremiumShopManager $premiumShopManager
 * @var User $player
 */
?>
<style>
    .kunaiPack {
        display: inline-block;
        box-sizing: border-box;

        margin: 20px;
        padding: 5px 5px 10px;
        width: 185px;
        min-height: 60px;

        background: var(--theme-content-darker-bg-color);
        border: 1px solid var(--theme-content-darker2-bg-color);
    }
    b {
        display: inline-block;
        margin-bottom: 2px;
    }
    form {
        margin-top: 6px;
    }
</style>
<table class='table'>
    <tr>
        <th>Buy Ancient Kunai</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            <p style='width:80%;margin:auto auto 4px;'>All payments are securely processed through Paypal. You do not need a
                Paypal account to
                pay with a credit card.</p>

            <?php foreach(System::getKunaiPacks() as $pack): ?>
                <div class='kunaiPack'>
                    <b>$<?= $pack['cost'] ?> USD</b><br />
                    <?= $pack['kunai'] ?> AK + <?= $pack['bonus'] ?> bonus<br />
                    Total: <?= ($pack['kunai'] + $pack['bonus']) ?> Ancient Kunai<br />
                    <form action='<?= $premiumShopManager->getPaypalUrl() ?>' method='post'>
                        <input type='hidden' name='cmd' value='_xclick' />
                        <input type='hidden' name='business' value='<?= $premiumShopManager->getPaypalBusinessId() ?>' />
                        <input type='hidden' name='cancel_return' value='<?= $system->router->base_url ?>' />
                        <input type='hidden' name='return' value='<?= $system->router->base_url ?>' />
                        <input type='hidden' name='amount' value='<?= $pack['cost'] ?>' />
                        <input type='hidden' name='quantity' value='1' />
                        <input type='hidden' name='cn' value='Spirit Shards' />
                        <input type='hidden' name='no_note' value='1' />
                        <input type='hidden' name='no_shipping' value='1' />
                        <input type='hidden' name='currency_code' value='USD' />
                        <input type='hidden'
                               name='item_name'
                               value='<?= ($pack['kunai'] + $pack['bonus']) ?> Ancient Kunai - <?= $player->user_name ?>'
                        />
                        <input type='hidden' name='custom' value='<?= $player->user_id ?>' />
                        <input type='hidden' name='notify_url' value='<?= $premiumShopManager->getPaypalListenerUrl() ?>' />
                        <input type='image' style='background:none;cursor:pointer;'
                               src='https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-small.png'
                               name='submit' alt='Buy Ancient Kunai'>
                        <img src='https://www.paypal.com/en_US/i/scr/pixel.gif' alt=''
                             style='border:0;width:1px;height:1px;position:absolute;' />
                    </form>
                </div>
            <?php endforeach; ?>
        </td>
    </tr>
</table>
