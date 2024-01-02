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

<!--// Character reset-->
<table class='table'>
    <tr>
        <th id='premium_characterReset_header' >Character Reset</th>
        <th id='premium_individualStatReset_header'>Individual Stat Resets</th>
    </tr>
    <tr>
        <td id='premium_characterReset_data' style='text-align:center;'>You can reset your character and start over as a level 1 Akademi-sei. This is <b>free.</b><br/>
            <form action='<?= $self_link ?>' method='post'>
                <input type='submit' name='user_reset' value='Reset Character' style='margin-top:8px;'/>
            </form>
        </td>
        <td id='premium_individualStatReset_data' style='text-align:center;'>
            You can reset an individual stat, freeing up space in your total stat cap to train something else higher.
            This is <b>free.</b><br/>
            <form action='<?= $self_link ?>&view=character_changes' method='post'>
                <!--suppress HtmlFormInputWithoutLabel -->
                <select id='statResetSelect' name='stat' style='margin:5px 0 5px;'>
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
        <th id='premium_characterReset_header'>Reset AI Battle Counts</th>
        <th id='premium_individualStatReset_header'>Reset PvP Battle Counts</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            This will reset your AI Wins and AI Losses to 0.<br />
            <br />
            Cost: <?= $premiumShopManager->costs['reset_ai_battles'] ?> AK
            <form action='<?= $self_link ?>' method='post'>
                <input type='submit' name='reset_ai_battles' value='Reset AI Battles' style='margin-top:8px;'/>
            </form>
        </td>
        <td style='text-align:center;'>
            This will reset your PvP Wins and PvP Losses to 0.<br />
            <br />
            Cost: <?= $premiumShopManager->costs['reset_pvp_battles'] ?> AK
            <form action='<?= $self_link ?>' method='post'>
                <input type='submit' name='reset_pvp_battles' value='Reset PvP Battles' style='margin-top:8px;'/>
            </form>
        </td>
    </tr>

    <tr>
        <th colspan='2'>Username Change</th>
    </tr>
    <tr>
        <td colspan='2' style='text-align:center;'>You can change your username free once per account or
            for <?= $premiumShopManager->costs['name_change'] ?> AK afterward.
            Any changes to the case of your name do not cost.<br/>
            <p>Free Changes left: <?= $player->free_username_changes ?></p>
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
        <td style='text-align:center;' colspan='2'>You can change your gender for <?= $premiumShopManager->costs['gender_change'] ?> Ancient
            Kunai.
            <br/>('<?= User::GENDER_NONE ?>' gender will not be displayed on view profile)
            <form action='<?= $self_link ?>' method='post'>
                <select name='new_gender'>
                    <?php foreach(User::$genders as $new_gender): ?>
                        <?php if($player->gender != $new_gender): ?>
                            <option value='<?= $new_gender ?>'><?= $new_gender ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select><br/>
                <input type='submit' style='margin-top: 5px' name='change_gender' value='Change Gender'/>
            </form>
        </td>
    </tr>
</table>

<!--// Stat reallocation-->
<style>
    .stat_transfer_form {
        width: 470px;
        max-width: 75%;

        display: flex;
        gap: 10px;
        justify-content: space-evenly;

        margin: 10px auto 5px;
        padding: 15px 5px;

        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 5px;
    }
    #transferAmount {
        width: 100px;
    }
    #statAllocateCost {
        margin-bottom: 12px;
    }
    .stat_transfer_form b {
        display: inline-block;
        margin-bottom: 2px;
    }
</style>
<table class='table'>
    <tr>
        <th>Stat Transfers</th>
    </tr>
    <tr>
        <td style='text-align:center;'>
            You can transfer points from one stat to another. This costs Ancient Kunai and takes time to complete, both
            cost and time increase
            the higher your stat amount is. Stat transfers under <?= $premiumShopManager->max_free_stat_change_amount ?>
            are free but have a <b><?= PremiumShopManager::$free_stat_change_cooldown_hours ?> hour cool down</b>.<br/>

            <?php if($player->stat_transfer_completion_time > 0): ?>
                <br />
                <b>Transfer in Progress</b><br />
                You are currently transferring <?= $player->stat_transfer_amount ?> points to <?= System::unSlug($player->stat_transfer_target_stat) ?>.<br />
                <?= $system->time_remaining($player->stat_transfer_completion_time - time()) ?> remaining
            <?php else: ?>
                <?php if($premiumShopManager->free_stat_change_cooldown_left > 0): ?>
                    <br /><b>Free stat change cooldown remaining</b><br />
                    <?= $system->time_remaining($premiumShopManager->free_stat_change_cooldown_left) ?>
                <?php endif; ?>

                <form action='<?= $self_link ?>&view=character_changes' method='post'>
                    <div class='stat_transfer_form'>
                        <div>
                            <b>Transfer Speed</b><br />
                            <select id='transferSpeed' name='transfer_speed' onchange='statAllocateCostDisplay()'>
                                <option value='<?= PremiumShopManager::STAT_TRANSFER_STANDARD ?>'>Standard</option>
                                <option value='<?= PremiumShopManager::STAT_TRANSFER_EXPEDITED ?>'>Expedited</option>
                                <option value='<?= PremiumShopManager::STAT_TRANSFER_SUPER_EXPEDITED ?>'>Super Expedited</option>
                            </select>
                        </div>
                        <div>
                            <b>Transfer</b><br/>
                            <select id='statAllocateSelect' name='original_stat' onchange='statSelectChange();'>
                                <?php foreach($player->stats as $stat): ?>
                                    <option value='<?= $stat ?>'><?= System::unSlug($stat) ?></option>
                                <?php endforeach; ?>
                            </select><br/>
                            to<br/>
                            <select name='target_stat'>
                                <?php foreach($player->stats as $stat): ?>
                                    <?php if($stat === 'intelligence' || $stat === 'willpower') continue; ?>
                                    <option value='<?= $stat ?>'><?= System::unSlug($stat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <?php
                            if($player->bloodline_id) {
                                $init_transfer_amount = $player->bloodline_skill;
                            }
                            else {
                                $init_transfer_amount = $player->ninjutsu_skill;
                            }
                            ?>
                            <b>Transfer amount:</b><br/>
                            <input
                                type='number'
                                id='transferAmount'
                                name='transfer_amount'
                                value='<?= $init_transfer_amount ?>'
                                max='<?= $init_transfer_amount ?>'
                                onchange='statAllocateCostDisplay()'
                                onkeyup='statAllocateCostDisplay()'
                            /><br/>
                        </div>
                    </div>
                    <span id='statAllocateCost'></span><br/>
                    <input type='submit' style='margin-top: 5px; margin-bottom: 2px' name='stat_allocate' value='Transfer Stat Points'/>
                </form>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!--suppress JSUnresolvedFunction -->
<script type='text/javascript'>
    let stats = {};
    <?php foreach($player->stats as $stat): ?>
        <?php if(str_contains($stat, 'skill')): ?>
            stats.<?= $stat ?> = <?= ($player->{$stat}) ?>;
        <?php else: ?>
            stats.<?= $stat ?> = <?= ($player->{$stat}) ?>;
        <?php endif; ?>
    <?php endforeach; ?>

    let pointsPerMin = <?= $premiumShopManager->stat_transfer_points_per_min ?>;
    let pointsPerAk = <?= $premiumShopManager->stat_transfer_points_per_ak ?>;
    let expeditedPointsPerYen = <?= $premiumShopManager->expedited_stat_transfer_points_per_yen ?>;

    const expeditedSpeedMultiplier = <?= PremiumShopManager::EXPEDITED_STAT_TRANSFER_SPEED_MULTIPLIER ?>;
    const superExpeditedSpeedMultiplier = <?= PremiumShopManager::SUPER_EXPEDITED_STAT_TRANSFER_SPEED_MULTIPLIER ?>;

    const superExpeditedAkCostMultiplier = <?= PremiumShopManager::SUPER_EXPEDITED_AK_COST_MULTIPLIER ?>;
    const superExpeditedYenCostMultiplier = <?= PremiumShopManager::SUPER_EXPEDITED_YEN_COST_MULTIPLIER ?>;

    let maxFreeStatChangeAmount = <?= $premiumShopManager->max_free_stat_change_amount ?>;
    let freeStatChangeActive = Boolean(<?= ($premiumShopManager->free_stat_change_cooldown_left <= 0) ?>);
    let statBeingTransferred = 'ninjutsu_skill';

    const transferAmountEl = document.getElementById('transferAmount');
    const transferSpeedEl = document.getElementById('transferSpeed');
    const statSelectEl = document.getElementById('statAllocateSelect');
    const statCostEl = document.getElementById('statAllocateCost');

    statBeingTransferred = document.getElementById('statAllocateSelect').value;

    function statSelectChange() {
        statBeingTransferred = statSelectEl.value;
        transferAmountEl.value = stats[statBeingTransferred];
        transferAmountEl.setAttribute('max', stats[statBeingTransferred]);
        statAllocateCostDisplay();
    }

    function statAllocateCostDisplay() {
        let ak_cost = 0, yen_cost = 0;
        let is_dev = <?= $system->isDevEnvironment() ? '1' : '0'; ?>;

        const transferAmount = parseInt(transferAmountEl.value);
        const transferSpeed = transferSpeedEl.value;

        let time = transferAmount / pointsPerMin;

        if (transferAmount <= maxFreeStatChangeAmount && freeStatChangeActive) {
            ak_cost = 0;
        }
        else {
            ak_cost = 1 + Math.floor(transferAmount / pointsPerAk);
        }

        if(transferSpeed === 'expedited') {
            yen_cost = transferAmount / expeditedPointsPerYen;
            time = transferAmount / (pointsPerMin * expeditedSpeedMultiplier);
        }
        else if(transferSpeed === 'super_expedited') {
            ak_cost = 1 + Math.floor(
                (transferAmount / pointsPerAk) * superExpeditedAkCostMultiplier
            );
            yen_cost = (transferAmount / expeditedPointsPerYen) * superExpeditedYenCostMultiplier;
            time = transferAmount / (pointsPerMin * superExpeditedSpeedMultiplier);
        }

        if(yen_cost > 0) {
            yen_cost = Math.round(yen_cost / 100) * 100;
        }
        time = Math.floor(time);

        if (is_dev) {
            ak_cost = 0;
            yen_cost = 0;
            time = 0;
        }

        statCostEl.innerHTML = `${ak_cost} AK / ${yen_cost} yen / ${time} minutes`;
    }

    statAllocateCostDisplay();
</script>

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
                A gift offering of <?= $premiumShopManager->costs['element_change'] ?> Ancient Kunai is required.
                <br/>
                <br/>
                <b>(IMPORTANT: This is non-reversible once completed<br />If you want to return to your original element you
                    will have to pay another fee.)</b><br />

                <br/>Choose your element to reattune:
                <br/>
                <form action='<?= $self_link ?>' method='post'>
                    <select name='editing_element_index'>
                        <?php foreach($player->elements as $slot => $element): ?>
                            <option value='<?= $slot ?>'><?= $element ?></option>
                        <?php endforeach; ?>
                    </select>
                    <br/>
                    Choose your element to attune to:<br/>
                    <select name='new_element'>
                        <?php foreach(Jutsu::$elements as $new_element): ?>
                            <?php
                            if(in_array($new_element, $player->elements)) {
                                continue;
                            }
                            ?>
                            <option value='<?= $new_element ?>'><?= $new_element ?></option>
                        <?php endforeach; ?>
                    </select><br/>
                    <input type='submit' style='margin-top: 5px' name='change_element' value='Change Element'/>
                </form>
            </td>
        </tr>
    </table>
<?php endif; ?>

<!--// Village change-->
<?php if($player->rank_num >= 2): ?>
    <form method='POST'>
        <table class='table'>
            <tr>
                <th>Change Clan</th>
            </tr>
            <tr>
                <td style='text-align:center;'>
                    <p>If you choose to abandon your clan now, you must gain the respect of other leaders in order to be
                        accepted into their family. A gift offering of <?= $premiumShopManager->costs['clan_change'] ?> Ancient Kunai is
                        required.
                    </p>
                    <p>
                        <b>(IMPORTANT: This is non-reversable once completed<br />If you want to return to your original
                            village you will have to pay a higher transfer fee.<br />Furthermore, you'll be removed from any clanoffice.)</b><br />
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
                However to get the other village to accept you, you must offer them <?= $premiumShopManager->costs['village_change'] ?>
                Ancient Kunai.<br/>
                <p>You will lose 20% of your Reputation for all village changes after the first (you can not fall below Shinobi).</p>
                <p>Villages with "From the Ashes" policy reduce the Reputation and Ancient Kunai cost to transfer by 50%.</p>
                <br/>
                <b>(IMPORTANT: This is non-reversable once completed<br />If you want to return to your original village you
                    will have to pay
                    a higher transfer fee)</b><br/><br />
                <form action='<?= $self_link ?>' method='post'>
                    <select name='new_village'>
                        <?php foreach(System::$villages as $village): ?>
                            <?php if($player->village->name == $village) continue; ?>
                            <option value='<?= $village ?>'><?= $village ?></option>
                        <?php endforeach; ?>
                    </select><br/>
                    <input type='submit' style='margin-top: 5px' name='change_village' value='Change Village'/>
                </form>
            </td>
        </tr>
    </table>
<?php endif; ?>
