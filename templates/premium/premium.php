<?php
/**
 * @var System $system
 * @var User   $player
 * @var PremiumShopManager $premiumShopManager
 * @var ForbiddenSeal $twinSeal
 * @var ForbiddenSeal $fourDragonSeal
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
                <td style='text-align:center;'>A researcher from the village will implant another clan's DNA into
                    you in exchange for Ancient Kunai, allowing you to use a new bloodline
                    <?= ($player->bloodline_id ? ' instead of your own' : '') ?>.<br/><br/>
                    <?php if($player->bloodline_skill > 10): ?>
                    <b>Warning: Your bloodline skill will be reduced by <?= (Bloodline::SKILL_REDUCTION_ON_CHANGE * 100) ?>% as
                        you must
                        re-adjust to your new bloodline!</b><br/>
                    <?php endif; ?>
                    <br/>

                    <?php foreach(Bloodline::$public_ranks as $rank_id => $rank): ?>
                        <?php if(empty($bloodlines[$rank_id])) continue; ?>
                        <?= $rank ?> Bloodlines (<?= $premiumShopManager->costs['bloodline'][$rank_id] ?> Ancient Kunai)<br/>
                        <form action='<?= $self_link ?>&view=bloodlines' method='post'>
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
        <?php include('templates/bloodlineList.php') ?>
    <?php else: ?>
        <table class='table'><tr><td>
            You cannot access this section until you are a Genin!
        </td></tr></table>
    <?php endif; ?>
<!-- END CHAR CHANGES -->
<?php elseif($view == 'forbidden_seal'): ?>
    <table class='table'>
        <tr>
            <th colspan='2'>Forbidden Seals</th>
        </tr>
        <tr>
            <td style='text-align:center;' colspan='2'>
                Shinobi researchers can imbue you with a forbidden seal, providing you with various benefits, in exchange
                for Ancient Kunai. The
                specific benefits and their strengths depend on which seal the researchers give you. The seals will recede
                after 30 days
                naturally, although with extra chakra imbued they can last longer.<br/>
                <br/>

                <b>Your Forbidden Seal</b><br/>
                <?php if($player->forbidden_seal->level > 0): ?>
                    <?= $player->forbidden_seal->name ?><br/>
                    <?= $system->time_remaining($player->forbidden_seal->seal_time_remaining) ?>
                    <br />
                <?php else: ?>
                    None<br />
                <?php endif; ?>

                <?php if($player->canChangeChatColor()): ?>
                    <br /><b>Change Name Color:</b><br />
                    <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                        <?php foreach($available_name_colors as $name_color=>$class): ?>
                            <input type='radio' name='name_color' value='<?= $name_color ?>'
                                <?= ($player->chat_color == $name_color ? "checked='checked'" : '') ?> />
                            <span class='<?= $class ?>' style='font-weight:bold;'><?= ucwords($name_color) ?></span>
                        <?php endforeach; ?>
                        <br />
                        <?php if($player->premium_credits_purchased):?>
                            <b>Toggle Chat Effect (sparkles on name)</b><br />
                            <input type="radio" name="chat_effect" value="" <?= ($player->chat_effect == "" ? "checked='checked'" : "") ?> />Off
                            <input type="radio" name="chat_effect" value="sparkles" <?= ($player->chat_effect == "sparkles" ? "checked='checked'" : "") ?> />On
                        <?php endif ?>
                        <br />
                        <input type='submit' name='change_color' value='Change Name Color'/>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th id='premium_twinSparrowSeal_header'><?=ForbiddenSeal::$forbidden_seal_names[1]?></th>
            <th id='premium_fourDragonSeal_header'><?=ForbiddenSeal::$forbidden_seal_names[2]?></th>
        </tr>
        <tr>
            <td id='premium_twinSparrowSeal_data' style='width:50%;vertical-align:top;'>
                <p style='font-weight:bold;text-align:center;'>
                    <?= $premiumShopManager->costs['forbidden_seal_monthly_cost'][1] ?> Ancient Kunai / 30 days</p>
                <br/>
                +<?=$twinSeal->regen_boost?>% regen rate<br/>
                <?=$twinSeal->name_color_display?> username color in chat<br/>
                Additional avatar styles (new layout)<br/>
                Larger avatar (<?=$baseDisplay['avatar_size_display']?> -> <?=$twinSeal->avatar_size_display?>)<br/>
                Larger inbox (<?=$baseDisplay['inbox_size']?> -> <?=$twinSeal->inbox_size?> messages)<br/>
                Longer journal (<?=$baseDisplay['journal_size']?> -> <?=$twinSeal->journal_size?> characters)<br/>
                Larger journal images (<?=$baseDisplay['journal_image_display']?> -> <?=$twinSeal->journal_image_display?>)<br/>
                Longer chat posts (<?=$baseDisplay['chat_post_size']?> -> <?=$twinSeal->chat_post_size?> characters)<br/>
                Longer PMs (<?=$baseDisplay['pm_size']?> -> <?=$twinSeal->pm_size?> characters)<br/>
                Cheaper stat transfers +<?= $twinSeal->extra_stat_transfer_points_per_ak ?> stat points per AK<br />
                View logs of your last <?= $twinSeal->max_battle_history_view ?> battles
                <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                    <p style='width:100%;text-align:center;margin: 1em 0 0;'>
                        <input type='hidden' name='seal_level' value='1'/>
                        <select name='seal_length'>
                            <?php foreach($premiumShopManager->costs['forbidden_seal'][1] as $pLength => $pCost): ?>
                                <option value="<?=$pLength?>"><?=$pLength?> days (<?=$pCost?> AK)</option>
                            <?php endforeach ?>
                        </select><br/>
                        <input type='submit' name='forbidden_seal' value='<?= ($player->forbidden_seal->level == 1 ? 'Extend' : 'Purchase') ?>' />
                    </p>
                </form>
            </td>
            <td id='premium_fourDragonSeal_data' style='width:50%;vertical-align:top;'>
                <p style='font-weight:bold;text-align:center;'>
                    <?= $premiumShopManager->costs['forbidden_seal_monthly_cost'][2] ?> Ancient Kunai / 30 days</p>
                <br/>
                All benefits of Twin Sparrow Seal<br/>
                +<?=$fourDragonSeal->regen_boost?>% regen rate<br/>
                +<?=$fourDragonSeal->extra_jutsu_equips?> jutsu equip slots<br/>
                +<?=$fourDragonSeal->extra_weapon_equips?> weapon equip slots<br/>
                +<?=$fourDragonSeal->extra_armor_equips?> armor equip slots<br/>
                Larger avatar filesize (<?= ($fourDragonSeal->avatar_filesize / 1024) ?> KB)<br />
                Longer journal (<?=$baseDisplay['journal_size']?> -> <?=$fourDragonSeal->journal_size?> characters)<br/>
                Enhanced long trainings (<?=$fourDragonSeal->long_training_time?>x length, <?=$fourDragonSeal->long_training_gains?>x gains)<br/>
                Enhanced extended trainings (<?=$fourDragonSeal->extended_training_time?>x length, <?=$fourDragonSeal->extended_training_gains?>x gains)<br/>
                Faster stat transfers (+<?=$fourDragonSeal->stat_transfer_boost?>/minute)<br />
                Cheaper stat transfers +<?= $fourDragonSeal->extra_stat_transfer_points_per_ak ?> stat points per AK<br />
                View logs of your last <?= $fourDragonSeal->max_battle_history_view ?> battles
                <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                    <p style='width:100%;text-align:center;margin: 2.2em 0 0;'>
                        <input type='hidden' name='seal_level' value='2'/>
                        <select name='seal_length'>
                            <?php foreach($premiumShopManager->costs['forbidden_seal'][2] as $pLength => $pCost): ?>
                                <option value="<?=$pLength?>"><?=$pLength?> days (<?=$pCost?> AK)</option>
                            <?php endforeach ?>
                        </select><br/>
                        <input type='submit' name='forbidden_seal' value='<?= ($player->forbidden_seal->level == 2 ? 'Extend' : 'Purchase') ?>' />
                    </p>
                </form>
            </td>
        </tr>
    </table>
<!-- END CHAR CHANGES -->
<?php elseif($view == 'buy_kunai'): ?>
    <?php require 'templates/premium/buy_premium_credits.php' ?>
    <?php premiumCreditExchange() ?>
<?php endif; ?>