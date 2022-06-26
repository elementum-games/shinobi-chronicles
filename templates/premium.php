<?php
/**
 * @var System $system
 * @var User   $player
 * @var string $self_link
 * @var string $view
 * @var array  $costs
 * @var array  $available_clans
 * @var array $name_colors
 * @var int $kunai_per_dollar
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
            <b>Your Ancient Kunai:</b> <?= $player->premium_credits ?>
        </td>
    </tr>
</table>

<?php if($view == 'character_changes'): ?>
    <!--// Character reset-->
    <table class='table'>
        <tr>
            <th>Character Reset</th>
            <th>Individual Stat Resets</th>
        </tr>
        <tr>
            <td style='text-align:center;'>You can reset your character and start over as a level 1 Akademi-sei. This is <b>free.</b><br/>
                <form action='<?= $self_link ?>' method='post'>
                    <input type='submit' name='user_reset' value='Reset Character' style='margin-top:8px;'/>
                </form>
            </td>
            <td style='text-align:center;'>
                You can reset an individual stat, freeing up space in your total stat cap to train something else higher.
                This is <b>free.</b><br/>
                <form action='<?= $self_link ?>&view=character_changes' method='post'>
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <select id='statResetSelect' name='stat' style='margin:6px 0 8px;'>
                        <?php foreach($player->stats as $stat): ?>
                            <option value='<?= $stat ?>'><?= ucwords(str_replace('_', ' ', $stat)) ?></option>';
                        <?php endforeach; ?>
                    </select>
                    <br/>
                    <input type='submit' name='stat_reset' value='Reset stat'/>
                </form>
            </td>
        </tr>

        <tr>
            <th colspan='2'>Username Change</th>
        </tr>
        <tr>
            <td colspan='2' style='text-align:center;'>You can change your username free once per account or
                for <?= $costs['name_change'] ?> AK afterward.
                Any changes to the case of your name do not cost.<br/>
                <p>Free Changes left: <?= $player->username_changes ?></p>
                <form action='<?= $self_link ?>' method='post'>
                    <input type='text' name='new_name'/>
                    <input type='submit' name='name_change' value='Change'/>
                </form>
            </td>
        </tr>

        <tr>
            <th colspan='2'>Gender Change</th>
        </tr>
        <tr>
            <td style='text-align:center;' colspan='2'>You can change your gender for <?= $costs['gender_change'] ?> Ancient
                Kunai.
                <br/>('<?= User::GENDER_NONE ?>' gender will not be displayed on view profile)
                <form action='<?= $self_link ?>' method='post'>
                    <select name='new_gender'>
                        <?php foreach(User::$genders as $new_gender): ?>
                            <?php if($player->gender == $new_gender): ?>
                                continue;
                            <?php else: ?>
                                <option value='<?= $new_gender ?>'><?= $new_gender ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select><br/>
                    <input type='submit' name='change_gender' value='Change Gender'/>
                </form>
            </td>
        </tr>
    </table>

    <!--// Stat reallocation-->
    <table class='table'>
        <tr>
            <th>Stat Transfers</th>
        </tr>
        <tr>
            <td style='text-align:center;'>
                <!--suppress JSUnresolvedFunction -->
                <script type='text/javascript'>
                    let stats = {};

                    function statSelectChange() {
                        $('#transferAmount').val(stats[ $('#statAllocateSelect').val() ]);
                        statAllocateCostDisplay();
                    }

                    function statAllocateCostDisplay() {
                        let cost;
                        const transferAmount = parseInt($('#transferAmount').val());
                        if (transferAmount <= 10) {
                            cost = 0;
                        }
                        else {
                            cost = 1 + Math.floor(transferAmount / 300);
                        }
                        const time = transferAmount * 0.2;
                        const display = cost + ' AK / ' + time + ' minutes';
                        $('#statAllocateCost').html(display);
                    }
                </script>
                You can transfer points from one stat to another. This costs Ancient Kunai and takes time to complete, both
                cost and time increase
                the higher your stat amount is.<br/>
                Stat transfers under 10 are free but have a <b>24 hour cool down</b>.<br/>
                <form action='<?= $self_link ?>&view=character_changes' method='post'>
                    <br/>
                    Transfer<br/>
                    <select id='statAllocateSelect' name='original_stat' onchange='statSelectChange();'>
                        <?php foreach($player->stats as $stat): ?>
                            <option value='<?= $stat ?>'><?= ucwords(str_replace('_', ' ', $stat)) ?></option>
                        <?php endforeach; ?>

                    </select><br/>
                    to<br/>
                    <select name='target_stat'>
                        <?php foreach($player->stats as $stat): ?>
                            <option value='<?= $stat ?>'><?= ucwords(str_replace('_', ' ', $stat)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <script type='text/javascript'>
                        <?php foreach($player->stats as $stat): ?>
                        <?php if(strpos($stat, 'skill') !== false): ?>
                        stats.<?= $stat ?> = <?= ($player->{$stat} - 10) ?>;
                        <?php else: ?>
                        stats.<?= $stat ?> = <?= ($player->{$stat} - 5) ?>;
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </script>
                    <?php
                    if($player->bloodline_id) {
                        $init_cost = (1 + floor(($player->bloodline_skill - 10) / 300));
                        $init_transfer_amount = $player->bloodline_skill - 10;
                        $init_length = ($player->bloodline_skill - 10) * 0.25;
                    }
                    else {
                        $init_cost = (1 + floor(($player->ninjutsu_skill - 10) / 300));
                        $init_transfer_amount = $player->ninjutsu_skill - 10;
                        $init_length = ($player->ninjutsu_skill - 10) * 0.25;
                    }
                    ?>
                    <br/>
                    <br/>
                    Transfer amount:<br/>
                    <input type='text' id='transferAmount' name='transfer_amount' value='<?= $init_transfer_amount ?>'
                           onkeyup='statAllocateCostDisplay()'/><br/>
                    <span id='statAllocateCost'><?= $init_cost ?> AK / <?= $init_length ?> minutes</span><br/>
                    <input type='submit' name='stat_allocate' value='Transfer Stat Points'/>
                </form>
            </td>
        </tr>
    </table>

    <!-- // Change Element-->
    <?php if($player->elements): ?>
        <table class='table'>
            <tr>
                <th>Chakra Element Change</th>
            </tr>
            <tr>
                <td style='text-align:center;'>
                    You can undergo harsh training by the village elders to reattune your chakra nature.
                    <br/>
                    A gift offering of <?= $costs['element_change'] ?> Ancient Kunai is required.
                    <br/>
                    <br/>
                    <b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original element you
                        will have to pay another fee. You will forget any elemental jutsu you currently have of this
                        nature.)</b>

                    <br/>Choose your element to reattune:
                    <br/>
                    <form action='<?= $self_link ?>' method='post'>
                        <select name='current_element'>
                            <?php foreach($player->elements as $slot => $element): ?>
                                <option value='<?= $slot ?>'><?= $element ?></option>
                            <?php endforeach; ?>
                        </select>
                        <br/>
                        Choose your element to attune to:<br/>
                        <select name='new_element'>
                            <?php foreach(Jutsu::$elements as $new_element): ?>
                                <?php
                                if($player->elements['first'] == $new_element) {
                                    continue;
                                }
                                else if($player->elements['second'] == $new_element) {
                                    continue;
                                }
                                ?>
                                <option value='<?= $new_element ?>'><?= $new_element ?></option>
                            <?php endforeach; ?>
                        </select><br/>
                        <input type='submit' name='change_element' value='Change Element'/>
                    </form>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!--// Village change-->
    <?php if($player->rank >= 2): ?>
        <form method='POST'>
            <table class='table'>
                <tr>
                    <th>Change Clan</th>
                </tr>
                <tr>
                    <td style='text-align:center;'>
                        <p>If you choose to abandon your clan now, you must gain the respect of other leaders in order to be
                            accepted into their family. A gift offering of <?= $costs['clan_change'] ?> Ancient Kunai is
                            required.
                        </p>
                        <p>
                            <b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original
                                village you
                                will have to pay a higher transfer fee. Furthermore, you'll be removed from any clan
                                office.)</b>
                            <br/>Select the clan below:
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style='text-align:center;'>
                        <select name='clan_change_id'>
                            <?php foreach($available_clans as $clan_id => $clan_name): ?>
                                <option value='<?= $clan_id ?>'><?= $clan_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style='text-align:center;'><input type='submit' name='change_clan' value='Change'></td>
                </tr>
            </table>
        </form>

        <table class='table'>
            <tr>
                <th>Change Village</th>
            </tr>
            <tr>
                <td style='text-align:center;'>
                    You can betray your own village and go to another village if you no longer wish to be a ninja in your
                    own village.
                    However to get the other village to accept you, you must offer them <?= $costs['village_change'] ?>
                    Ancient Kunai.<br/>
                    <br/>
                    <b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original village you
                        will have to pay
                        a higher transfer fee)</b><br/>
                    <form action='<?= $self_link ?>' method='post'>
                        <select name='new_village'>
                            <?php foreach(System::$villages as $village): ?>
                                <?php if($player->village == $village) continue; ?>
                                <option value='<?= $village ?>'><?= $village ?></option>
                            <?php endforeach; ?>
                        </select><br/>
                        <input type='submit' name='change_village' value='Change Village'/>
                    </form>
                </td>
            </tr>
        </table>
    <?php endif; ?>
<?php elseif($view == 'bloodlines'): ?>
    <?php if($player->rank >= 2): ?>
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
                        <?= $rank ?> Bloodlines (<?= $costs['bloodline'][$rank_id] ?> Ancient Kunai)<br/>
                        <form action='<?= $self_link ?>' method='post'>
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
                <?php if(isset($player->forbidden_seal['level'])): ?>
                    <?= System::$forbidden_seals[$player->forbidden_seal['level']] ?><br/>
                    <?= $system->time_remaining($player->forbidden_seal['time'] - time()) ?>
                    <br />
                <?php else: ?>
                    None<br />
                <?php endif; ?>

                <?php if($player->canChangeChatColor()): ?>
                    <br /><b>Change Name Color:</b><br />
                    <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                        <?php foreach($name_colors as $name_color=>$class): ?>
                            <input type='radio' name='name_color' value='<?= $name_color ?>'
                                <?= ($player->chat_color == $name_color ? "checked='checked'" : '') ?> />
                            <span class='<?= $class ?>' style='font-weight:bold;'><?= ucwords($name_color) ?></span>
                        <?php endforeach; ?>
                        <br />
                        <input type='submit' name='change_color' value='Change Name Color'/>
                    </form>
                <?php else: ?>
                    None
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Twin Sparrow Seal</th>
            <th>Four Dragon Seal</th>
        </tr>
        <tr>
            <td style='width:50%;vertical-align:top;'>
                <p style='font-weight:bold;text-align:center;'>
                    <?= $costs['forbidden_seal'][1] ?> Ancient Kunai / 30 days</p>
                <br/>
                +10% regen rate<br/>
                Blue/Pink username color in chat<br/>
                Larger avatar (125x125 -> 175x175)<br/>
                Longer logout timer (60 -> 90 minutes)<br/>
                Larger inbox (50 -> 75 messages)<br/>
                Longer journal (1000 -> 2000 characters)<br/>
                Larger journal images (300x200 -> 500x500)<br/>
                Longer chat posts (350 -> 450 characters)<br/>
                Longer PMs (1000 -> 1500 characters)<br/>
                <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                    <p style='width:100%;text-align:center;margin: 1em 0 0;'>
                        <input type='hidden' name='seal_level' value='1'/>
                        <select name='seal_length'>
                            <option value='30'>30 days (<?= ($costs['forbidden_seal'][1] * 1) ?> AK)</option>
                            <option value='60'>60 days (<?= ($costs['forbidden_seal'][1] * 2) ?> AK)</option>
                            <option value='90'>90 days (<?= ($costs['forbidden_seal'][1] * 3) ?> AK)</option>
                        </select><br/>
                        <input type='submit' name='forbidden_seal' value='<?= ($player->forbidden_seal &&
                            $player->forbidden_seal['level'] == 1 ? 'Extend' : 'Purchase') ?>' />
                    </p>
                </form>
            </td>
            <td style='width:50%;vertical-align:top;'>
                <p style='font-weight:bold;text-align:center;'>
                    <?= $costs['forbidden_seal'][2] ?> Ancient Kunai / 30 days</p>
                <br/>
                All benefits of Twin Sparrow Seal<br/>
                +20% regen rate<br/>
                +1 jutsu equip slots<br/>
                +1 weapon equip slots<br/>
                +1 armor equip slots<br/>
                Enhanced long trainings (1.5x length, 2x gains)<br/>
                Enhanced extended trainings (1.5x length, 2x gains)<br/>
                <form action='<?= $self_link ?>&view=forbidden_seal' method='post'>
                    <p style='width:100%;text-align:center;margin: 2.2em 0 0;'>
                        <input type='hidden' name='seal_level' value='2'/>
                        <select name='seal_length'>
                            <option value='30'>30 days (<?= ($costs['forbidden_seal'][2] * 1) ?> AK)</option>
                            <option value='60'>60 days (<?= ($costs['forbidden_seal'][2] * 2) ?> AK)</option>
                            <option value='90'>90 days (<?= ($costs['forbidden_seal'][2] * 3) ?> AK)</option>
                        </select><br/>
                        <input type='submit' name='forbidden_seal' value='<?= ($player->forbidden_seal &&
                            $player->forbidden_seal['level'] == 2 ? 'Extend' : 'Purchase') ?>' />
                    </p>
                </form>
            </td>
        </tr>
    </table>
<!-- END CHAR CHANGES -->
<?php elseif($view == 'buy_kunai'): ?>
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

                <?php foreach($system->getKunaiPacks() as $pack): ?>
                    <div class='kunaiPack'>
                        <b>$<?= $pack['cost'] ?> USD</b><br />
                        <?= $pack['kunai'] ?> AK + <?= $pack['bonus'] ?> bonus<br />
                        Total: <?= ($pack['kunai'] + $pack['bonus']) ?> Ancient Kunai<br />
                        <form action='<?= $paypal_url ?>' method='post'>
                            <input type='hidden' name='cmd' value='_xclick' />
                            <input type='hidden' name='business' value='<?= $paypal_business_id ?>' />
                            <input type='hidden' name='cancel_return' value='<?= $system->link ?>' />
                            <input type='hidden' name='return' value='<?= $system->link ?>' />
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
                            <input type='hidden' name='notify_url' value='<?= $paypal_listener_url ?>' />
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
    <?php premiumCreditExchange() ?>
<?php endif; ?>