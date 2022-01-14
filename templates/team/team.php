<?php
/**
 * @var User $player
 * @var System $system
 *
 * @var string $self_link
 * @var int $self_id
 *
 * @var string $leader_name
 * @var string $leader_avatar
 *
 * @var string $boost_text
 * @var string $boost_time
 *
 * @var array $available_missions
 * @var array $team_mission_name
 * @var array $team_members
 *
 * @var array $RANK_NAMES
 */
?>

<table class='table'>
    <tr><th colspan='3'><?= $player->team->name ?></th></tr>
    <tr>
        <th style='width: 33%;'>Information</th>
        <th style='width: 33%;'>Boost</th>
        <th style='width: 33%;'>Leader</th>
    </tr>
    <tr>
        <td style='vertical-align: top;'>
            <b>Team Type:</b> Shinobi<br />
            <br>
            <b>Points:</b> <?= number_format($player->team->points) ?><br />
            <br>
            <a href='<?= $self_link ?>&leave_team=1'><p class='button'>Leave Team</p></a>
        </td>


        <td style='text-align: center;vertical-align: middle;'>
            <b>Current Boosts</b><br />
            <?php if ($player->team->boost != 'none'): ?>
                <?= $player->team->getBoostLabel() ?> (<?= $player->team->boost_amount ?>%)<br />
                <br />
                <b>Time Remaining:</b><br>
                <?= $system->timeRemaining($player->team->boost_time + (60*60*24*7) - time(), 'long') ?>
            <?php else: ?>
                None
            <?php endif; ?>
        </td>

        <td rowspan='2' style='text-align: center;vertical-align: middle;'>
            <p style='font-size:1.1rem;font-weight:bold;margin-bottom:5px;'><?= $leader_name ?></p>
            <img src='<?= $leader_avatar ?>' style='max-width:125px;max-height:125px;' />
        </td>
    </tr>
    <tr>
        <td colspan='2' style='text-align: center;'>
            <img src='<?= $player->team->logo ?>' style='width: 450px; height: 100px;'>
        </td>
    </tr>
</table>

<!--// Start mission-->
<table class='table'>
    <tr><th colspan='2'>Missions</th></tr>
    <tr><td style='text-align:center; width: 50%;'>
            <?php if($player->team->mission_id && $player->user_id == $player->team->leader): ?>
                <p style='margin: 5px 0 0;'>
                    <a href='<?= $self_link ?>&cancel_mission=1'><span class='button'>Cancel Mission</span></a>
                </p>
            <?php elseif($player->user_id == $player->team->leader): ?>

            <form action='<?= $self_link ?>' method='post'>
                <label>
                    <select name='mission_id'>
                        <?php foreach($available_missions as $mission): ?>
                            <option value='<?= $mission['mission_id'] ?>'><?= $mission['name'] ?></option>
                        <?php endforeach; ?>
                        </select>
                <input type='submit' name='start_mission' value='Start Mission' />
            </form>
            <?php endif; ?>

            </td>
        <!--// Mission display-->
        <td style='text-align: center;'>
            <div>
                <p style='font-size:1.1em;font-weight:bold;text-decoration:underline;margin-top:0;margin-bottom:5px;'>Current Mission</p>
                <?php if($team_mission_name == null): ?>
                    None
                <?php elseif($player->mission_id == $player->team->mission_id): ?>
                    <b><?= $team_mission_name ?></b><br />
                    <?= $player->mission_stage['description'] ?>

                    <?php if(is_array($player->team->mission_stage) && $player->team->mission_stage['count_needed']): ?>
                        (<?= $player->team->mission_stage['count'] ?> / <?= $player->team->mission_stage['count_needed'] ?> remaining)
                    <?php endif; ?>
                <?php else: ?>
                    <b><?= $team_mission_name ?></b><br />
                    <br />
                    <a href='<?= $self_link ?>&join_mission=1'><span class='button'>Join Mission</span></a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>



<table class='table'>
    <tr>
        <th colspan='4'>
            Team Members
        </th>
    </tr>
    <tr>
        <th style='width:30%;'>Username</th>
        <th style='width:20%;'>Rank</th>
        <th style='width:20%;'>Level</th>
        <th style='width:30%;'>PvP this month</th>
    </tr>

    <?php foreach($team_members as $row): ?>
        <tr class='table_multicolumns'>
            <td style='width:29%;'>
                <a href='<?= $system->links['members'] ?>&user=<?= $row['user_name'] ?>'><?= $row['user_name'] ?></a>
            </td>
            <td style='width:20%;text-align:center;'><?= $RANK_NAMES[$row['rank']] ?></td>
            <td style='width:20%;text-align:center;'><?= $row['level'] ?></td>
            <td style='width:30%;text-align:center;'><?= $row['monthly_pvp'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!--// Leader tools-->
<?php if($player->user_id == $player->team->leader): ?>
    <table class='table'>
        <!--// Team members (invite/kick)-->
        <tr><th colspan='3'>Team Controls</th></tr>
        <tr>
            <th style='width: 33%;'>Member Actions</th>
            <th style='width: 33%;'>Logo</th>
            <th style='width: 33%;'>Boost</th>
        </tr>
        <tr><td style='text-align:center;'>
                <br />
                <b>Invite Player</b><br />
                <form action='<?= $self_link ?>' method='get'>
                    <input type='hidden' name='id' value='<?= System::PAGE_IDS['team'] ?>'>
                    <input type='text' name='user_name' /><br />
                    <input type='submit' name='invite' value='Send' />
                </form>
                <br />

                <?php if(count($team_members) > 1): ?>
                    <b>Kick Member</b><br />
                    <!--// Kick-->
                    <form action='<?= $self_link ?>' method='post'>
                        <select name='user_id'>
                            <?php foreach($team_members as $user_id => $member): ?>
                                <?php if($member['user_id'] == $player->user_id) continue; ?>
                                <option value='<?= $member['user_id'] ?>'><?= $member['user_name'] ?></option>
                            <?php endforeach; ?>
                        </select><br />
                        <input type='submit' name='kick' value='Remove' />
                    </form><br />

                    <b>Transfer Leadership</b><br />
                    <!--// Transfer Leadership-->
                    <form action='<?= $self_link ?>' method='post'>
                        <select name='user_id'>
                            <?php foreach($team_members as $user_id => $member): ?>
                                <?php if($member['user_id'] == $player->user_id) continue; ?>
                                <option value='<?= $member['user_id'] ?>'><?= $member['user_name'] ?></option>
                            <?php endforeach; ?>
                        </select><br />
                        <input type='submit' name='transfer_leader' value='Transfer' />
                    </form><br />
                <?php endif; ?>
                </td>
            <td style='text-align: center;'>
                <br>
                <form action='<?= $self_link ?>' method='post'>
                    <input type='text' name='logo_link' value='<?= $player->team->logo ?>'><br>
                    Dimensions: 450x100<br>
                    <button type='submit'>Change Logo</button><br>
                </form>
            </td>
            <td style='text-align:center;'>
                <form action='<?= $self_link ?>' method='post'>
                    <div style='margin-top:2px;'>
                        <script type='text/javascript'>
                            let boosts = <?= json_encode(Team::$allowed_boosts) ?>;
                            let boostType;

                            function updateType() {
                                boostType = document.getElementById('boost_type').value;
                                document.querySelector("#boost_size option[value='small']")
                                    .innerText = boosts[boostType].small.amount + '%';
                                document.querySelector("#boost_size option[value='medium']")
                                    .innerText = boosts[boostType].medium.amount + '%';
                                document.querySelector("#boost_size option[value='large']")
                                    .innerText = boosts[boostType].large.amount + '%';
                            }

                            function displayCost() {
                                let display_value;
                                let boostSize = document.getElementById('boost_size').value;

                                let cost = boosts[boostType][boostSize].points_cost;

                                let display = document.getElementById('display_cost').innerHTML = cost + ' points';
                            }
                        </script>
                        <label for='boost_type'>Boost Type</label><br />
                        <select id='boost_type' name='boost_type' style='width:110px;' onchange='updateType()'>
                            <option value='<?= Team::BOOST_TRAINING ?>'>Training</option>
                            <option value='<?= Team::BOOST_AI_MONEY ?>'>AI Money</option>
                        </select>
                        <br />

                        <label for='boost_size' style='margin-top:8px;'>Amount</label><br />
                        <select name='boost_size' id='boost_size' onchange='displayCost()' style='width:110px;'>
                            <option selected value='small'>10%</option>
                            <option value='medium'>20%</option>
                            <option value='large'>30%</option>
                        </select><br>
                        <p style='margin:8px 0;font-weight: bold;'>
                            Cost: <span id='display_cost'>N/A</span>
                        </p>
                    </div>
                    <input type='submit' name="set_boost" value="Set Boost" /><br>
                    <label style='font-size: 0.8rem; font-style: italic;margin:5px 0;'>
                        Training boost is a chance to get extra gains, not a boost to all gains.
                    </label>

                    <script type='text/javascript'>
                        updateType();
                        displayCost();
                    </script>
                </form>
        </td></tr>
    </table>
<?php endif; ?>