<?php
/**
 * @var System $system
 * @var User   $player
 * @var PremiumShopManager $premiumShopManager
 *
 * @var ForbiddenSeal $baseDisplay;
 * @var ForbiddenSeal $twinSeal
 * @var ForbiddenSeal $fourDragonSeal
 * @var ForbiddenSeal $eightDeitiesSeal
 *
 * @var string $self_link
 * @var string $view
 * @var array $available_name_colors
 */
?>

<table class='table'>
    <tr>
        <th colspan='5'>Forbidden Seals</th>
    </tr>
    <tr>
        <td style='text-align:center;' colspan='5'>
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
                    <input type='submit' style='margin-top: 5px' name='change_color' value='Change Name Color'/>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th></th>
        <th>No Seal</th>
        <th id='premium_twinSparrowSeal_header'><?=ForbiddenSeal::$forbidden_seal_names[1]?></th>
        <th id='premium_fourDragonSeal_header'><?=ForbiddenSeal::$forbidden_seal_names[2]?></th>
        <th id='premium_eightDeitiesSeal_header'><?=ForbiddenSeal::$forbidden_seal_names[3]?></th>
    </tr>
    <tr>
        <th>Regen Boost</th>
        <td></td>
        <td><?=$twinSeal->regen_boost?>%</td>
        <td><?= $fourDragonSeal->regen_boost ?>%</td>
        <td><?= $eightDeitiesSeal->regen_boost ?>%</td>
    </tr>
    <tr>
        <th>Chat Name Colors</th>
        <td></td>
        <td><?= $twinSeal->name_color_display ?></td>
        <td><?= $fourDragonSeal->name_color_display ?></td>
        <td><?= $eightDeitiesSeal->name_color_display ?></td>
    </tr>
    <tr>
        <th>Avatar</th>
        <td><?= $baseDisplay->avatar_size ?>x<?= $baseDisplay->avatar_size ?> (<?= round($baseDisplay->avatar_filesize / ForbiddenSeal::ONE_MEGABYTE, 1) ?> MB)</td>
        <td>
            <?= $twinSeal->avatar_size ?>x<?= $twinSeal->avatar_size ?>&nbsp;
            (<?= round($twinSeal->avatar_filesize / ForbiddenSeal::ONE_MEGABYTE, 1) ?> MB)
            <br />
            Additional avatar styles
        </td>
        <td>
            <?= $fourDragonSeal->avatar_size ?>x<?= $fourDragonSeal->avatar_size ?>&nbsp;
            (<?= round($fourDragonSeal->avatar_filesize / ForbiddenSeal::ONE_MEGABYTE, 1) ?> MB)
            <br />
            Additional avatar styles
        </td>
        <td><?= $eightDeitiesSeal->avatar_size ?>x<?= $eightDeitiesSeal->avatar_size ?>&nbsp;
            (<?= round($eightDeitiesSeal->avatar_filesize / ForbiddenSeal::ONE_MEGABYTE, 1) ?> MB)
            <br />
            Additional avatar styles
        </td>
    </tr>
    <tr>
        <th>Inbox</th>
        <td><?= $baseDisplay->inbox_size ?> messages</td>
        <td><?= $twinSeal->inbox_size ?> messages</td>
        <td><?= $fourDragonSeal->inbox_size ?> messages</td>
        <td><?= $eightDeitiesSeal->inbox_size ?> messages</td>
    </tr>
    <tr>
        <th>Journal</th>
        <td>
            <?= $baseDisplay->journal_size ?> characters<br />
            <?= $baseDisplay->journal_image_display ?> images
        </td>
        <td>
            <?= $twinSeal->journal_size ?> characters<br />
            <?= $twinSeal->journal_image_display ?> images
        </td>
        <td>
            <?= $fourDragonSeal->journal_size ?> characters<br />
            <?= $fourDragonSeal->journal_image_display ?> images
        </td>
        <td>
            <?= $eightDeitiesSeal->journal_size ?> characters<br />
            <?= $eightDeitiesSeal->journal_image_display ?> images<br />
            YouTube video embeds
        </td>
    </tr>
    <tr>
        <th>Long Training</th>
        <td><?=$baseDisplay->long_training_time?>x length, <?=$baseDisplay->long_training_gains?>x gains</td>
        <td><?=$twinSeal->long_training_time?>x length, <?=$twinSeal->long_training_gains?>x gains</td>
        <td><?=$fourDragonSeal->long_training_time?>x length, <?=$fourDragonSeal->long_training_gains?>x gains</td>
        <td><?=$eightDeitiesSeal->long_training_time?>x length, <?=$eightDeitiesSeal->long_training_gains?>x gains</td>
    </tr>
    <tr>
        <th>Extended Training</th>
        <td><?=$baseDisplay->extended_training_time?>x length, <?=$baseDisplay->extended_training_gains?>x gains</td>
        <td><?=$twinSeal->extended_training_time?>x length, <?=$twinSeal->extended_training_gains?>x gains</td>
        <td><?=$fourDragonSeal->extended_training_time?>x length, <?=$fourDragonSeal->extended_training_gains?>x gains</td>
        <td><?=$eightDeitiesSeal->extended_training_time?>x length, <?=$eightDeitiesSeal->extended_training_gains?>x gains</td>
    </tr>
    <tr>
        <th>Stat Transfers</th>
        <td></td>
        <td>
            +<?= $twinSeal->extra_stat_transfer_points_per_ak ?> stat points per AK<br />
            +<?= $twinSeal->free_transfer_bonus ?>% free daily transfer<br />
            +<?= $twinSeal->stat_transfer_boost?> stats per minute<br />
        </td>
        <td>
            +<?= $fourDragonSeal->extra_stat_transfer_points_per_ak ?> stat points per AK<br />
            +<?= $fourDragonSeal->free_transfer_bonus ?>% free daily transfer<br />
            +<?= $fourDragonSeal->stat_transfer_boost?> stats per minute<br />
        </td>
        <td>
            +<?= $eightDeitiesSeal->extra_stat_transfer_points_per_ak ?> stat points per AK<br />
            +<?= $eightDeitiesSeal->free_transfer_bonus ?>% free daily transfer<br />
            +<?= $eightDeitiesSeal->stat_transfer_boost?> stats per minute<br />
        </td>
    </tr>
    <tr>
        <th>Battle History</th>
        <td></td>
        <td style='vertical-align: top;'>View previous <?= $twinSeal->max_battle_history_view ?> battle logs</td>
        <td style='vertical-align: top;'>View previous <?= $fourDragonSeal->max_battle_history_view ?> battle logs </td>
        <td style='vertical-align: top;'>View previous <?= $eightDeitiesSeal->max_battle_history_view ?> battle logs</td>
    </tr>
    <tr>
        <th>Equipment</th>
        <td></td>
        <td></td>
        <td>
            +<?=$fourDragonSeal->extra_jutsu_equips?> jutsu equip slots<br/>
            +<?=$fourDragonSeal->extra_weapon_equips?> weapon equip slots<br/>
            +<?=$fourDragonSeal->extra_armor_equips?> armor equip slots<br/>
        </td>
        <td>
            +<?=$eightDeitiesSeal->extra_jutsu_equips?> jutsu equip slots<br/>
            +<?=$eightDeitiesSeal->extra_weapon_equips?> weapon equip slots<br/>
            +<?=$eightDeitiesSeal->extra_armor_equips?> armor equip slots<br/>
        </td>
    </tr>
    <tr>
        <th>Reputation Gain</th>
        <td></td>
        <td style='vertical-align: top;'></td>
        <td style='vertical-align: top;'>
            +<?= $fourDragonSeal->bonus_pve_reputation ?> Reputation from PVE sources
        </td>
        <td style='vertical-align: top;'>
            +<?= $eightDeitiesSeal->bonus_pve_reputation ?> Reputation from PVE sources
        </td>
    </tr>

    <tr>
        <th>Purchase</th>
        <td></td>
        <td style='vertical-align:bottom;'>
            <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                <p style='width:100%;text-align:center;margin: 1em 0 0;'>
                    <input type='hidden' name='seal_level' value='1'/>
                    <select name='seal_length'>
                        <?php foreach($premiumShopManager->costs['forbidden_seal'][1] as $pLength => $pCost): ?>
                            <option value="<?=$pLength?>"><?=$pLength?> days (<?=$pCost?> AK)</option>
                        <?php endforeach ?>
                    </select><br/>
                    <input type='submit' style='margin-top: 5px' name='forbidden_seal' value='<?= ($player->forbidden_seal->level == 1 ? 'Extend' : 'Purchase') ?>' />
                </p>
            </form>
        </td>
        <td style='vertical-align:bottom;'>
            <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                <p style='width:100%;text-align:center;margin: 2.2em 0 0;'>
                    <input type='hidden' name='seal_level' value='2'/>
                    <select name='seal_length'>
                        <?php foreach($premiumShopManager->costs['forbidden_seal'][2] as $pLength => $pCost): ?>
                            <option value="<?=$pLength?>"><?=$pLength?> days (<?=$pCost?> AK)</option>
                        <?php endforeach ?>
                    </select><br/>
                    <input type='submit' style='margin-top: 5px' name='forbidden_seal' value='<?= ($player->forbidden_seal->level == 2 ? 'Extend' : 'Purchase') ?>' />
                </p>
            </form>
        </td>
        <td style='vertical-align:bottom;'>
            <!--TEMPORARY SALE LOGIC-->
            <?php if($premiumShopManager->tierThreeSaleActive()): ?>
                <?php if($player->forbidden_seal->level == 1 || $player->forbidden_seal->level == 2): ?>
                    <b>Upgrade Special</b>
                    <br />
                    Any seal credit above purchase price will be refunded at <?=PremiumShopManager::SALE_REFUND_RATE?>%
                <?php endif ?>
            <?php endif ?>

            <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                <p style='width:100%;text-align:center;margin: 2.2em 0 0;'>
                    <input type='hidden' name='seal_level' value='3'/>
                    <select name='seal_length' style='width:125px;'>
                        <?php foreach($premiumShopManager->costs['forbidden_seal'][3] as $pLength => $pCost): ?>
                            <option value="<?=$pLength?>">
                                <?=$pLength?> days (<?=$pCost?> AK)
                                <?php if($pLength === 90): ?>
                                    [15% off!]
                                <?php endif; ?>
                            </option>
                        <?php endforeach ?>
                    </select><br/>
                    <input type='submit' style='margin-top: 5px' name='forbidden_seal' value='<?= ($player->forbidden_seal->level == 3 ? 'Extend' : 'Purchase') ?>' />
                </p>
            </form>
        </td>
    </tr>

</table>