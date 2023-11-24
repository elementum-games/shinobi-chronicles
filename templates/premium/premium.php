<?php
/**
 * @var System $system
 * @var User   $player
 * @var PremiumShopManager $premiumShopManager
 * @var ForbiddenSeal $twinSeal
 * @var ForbiddenSeal $fourDragonSeal
 * @var ForbiddenSeal $eightDeitiesSeal
 * @var string $self_link
 * @var string $view
 * @var array  $available_clans
 * @var array $available_name_colors
 * @var array $baseDisplay;
 *
 * @var string $paypal_url
 * @var string $paypal_business_id
 * @var string $paypal_listener_url
 */
?>

<!-- Sub-menu-->
<div class='submenu'>
    <ul class='submenu'>
        <li style='width:30.5%;'><a href='<?= $self_link ?>&view=character_changes'>Character Changes</a></li>
        <li style='width:23%;'><a href='<?= $self_link ?>&view=bloodlines'>Bloodlines</a></li>
        <li style='width:25.5%;'><a href='<?= $self_link ?>&view=forbidden_seal'>Forbidden Seal</a></li>
        <li style='width:18.5%;'><a href='<?= $self_link ?>&view=buy_kunai'>Buy AK</a></li>
    </ul>
</div>
<div class='submenuMargin'></div>

<?php $system->printMessage(); ?>

<!-- Summary-->
<table class='table'>
    <tr>
        <th>Premium</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            Here you can purchase and spend Ancient Kunai on a variety of boosts and in-game items.<br/>
            <br/>
            <b>Your Ancient Kunai:</b> <?= number_format($player->getPremiumCredits()) ?>
        </td>
    </tr>
</table>

<?php if($view == 'character_changes'): ?>
    <?php require 'templates/premium/character_changes.php'; ?>
<?php elseif($view == 'bloodlines'): ?>
    <?php if($player->rank_num >= 2): ?>
        <table class='table'>
            <tr>
                <th>Purchase New Bloodline</th>
            </tr>
            <tr>
                <td style='text-align:center;'>Researchers from the village will implant another clan's genetic material into
                    your body in exchange for Ancient Kunai.
                    <?= ($player->bloodline_id ? '<br/>This will replace your existing bloodline.' : '') ?><br/><br/>
                    <?php if($player->bloodline_skill > 10 && Bloodline::SKILL_REDUCTION_ON_CHANGE > 0): ?>
                    <b>Warning: Your bloodline skill will be reduced by <?= (Bloodline::SKILL_REDUCTION_ON_CHANGE * 100) ?>% as
                        you must
                        re-adjust to your new bloodline!</b><br/>
                    <?php endif; ?>
                    <?php foreach(Bloodline::$public_ranks as $rank_id => $rank): ?>
                        <?php if(empty($bloodlines[$rank_id]) || $rank_id > 2) continue; ?>
                        <?= $rank ?> Bloodlines (<?= $premiumShopManager->costs['bloodline'][$rank_id] ?> Ancient Kunai)<br/>
                        <form style='margin-bottom: 7px' action='<?= $self_link ?>&view=bloodlines' method='post'>
                            <select name='bloodline_id'>
                                <?php foreach($bloodlines[$rank_id] as $bloodline_id => $bloodline): ?>
                                    <!-- Need to keep bloodline in the bloodlines array for bloodline list-->
                                    <?php if($bloodline_id == $player->bloodline_id) continue; ?>
                                    <option value='<?= $bloodline_id ?>'><?= $bloodline['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type='submit' name='purchase_bloodline' value='Implant'/>
                        </form>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <table class='table'>
            <tr>
                <th>Awaken Bloodline (Random)</th>
            </tr>
            <tr>
                <td style='text-align:center;'>Researchers from the village will awaken a dormant bloodline within your lineage 
                    in exchange for Ancient Kunai.
                    <?= ($player->bloodline_id ? '<br/>This will replace your existing bloodline.' : '') ?><br/><br/>
                    <?php if($player->bloodline_skill > 10 && Bloodline::SKILL_REDUCTION_ON_CHANGE > 0): ?>
                    <b>Warning: Your bloodline skill will be reduced by <?= (Bloodline::SKILL_REDUCTION_ON_CHANGE * 100) ?>% as
                        you must
                        re-adjust to your new bloodline!</b><br/>
                    <?php endif; ?>
                    <?php foreach(Bloodline::$public_ranks as $rank_id => $rank): ?>
                        <?php if(empty($bloodlines[$rank_id]) || $rank_id > 2) continue; ?>
                        <?= $rank ?> Bloodline (<?= $premiumShopManager->costs['bloodline_random'][$rank_id] ?> Ancient Kunai)<br/>
                        <form style='margin-bottom: 7px' action='<?= $self_link ?>&view=bloodlines' method='post'>
                            <input type='submit' name='purchase_bloodline_random' value='Awaken <?= $rank ?> Bloodline'/>
                            <input type='hidden' name='bloodline_rank' value=<?=$rank_id?>/>
                        </form>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php include('templates/bloodlineList.php') ?>
    <?php else: ?>
        <table class='table'><tr><td>
            You cannot access this section until you are a Genin!
        </td></tr></table>
    <?php endif; ?>
<!-- END CHAR CHANGES -->
<?php elseif($view == 'forbidden_seal'): ?>
    <?php require 'templates/premium/forbidden_seal.php'; ?>
<!-- END CHAR CHANGES -->
<?php elseif($view == 'buy_kunai'): ?>
    <?php require 'templates/premium/buy_premium_credits.php' ?>
    <?php premiumCreditExchange() ?>
<?php endif; ?>