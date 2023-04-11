<?php
/**
 * @var string $self_link
 * @var System $system
 * @var array $RANK_NAMES
 *
 * @var User $player
 * @var Clan $clan
 * @var array $missions
 * @var ?Mission $active_mission
 * @var string $page
 * @var ClanMemberDto[] $members
 * @var ClanMemberDto[] $officers
 * @var int $max_mission_rank
 * @var array $positions
 *
 * @var array $can_challenge
 * @var array $can_claim
 *
 * @var int $min
 * @var int $prev
 * @var int $next
 */

?>
<style>
    label {
        display: inline-block;
    }

    .clanLogo {
        float:right;
        width:100px;
        height:100px;
        margin:10px;
        margin-right:12px;
    }
    .clanInfo {
        float:left;
        margin-top:8px;
        max-width:500px;
    }
</style>

<div class='submenu'>
    <ul class='submenu'>
        <?php if($player->clan_office): ?>
            <li style='width:24%;'><a href='<?= $self_link ?>'>Clan HQ</a></li>
            <li style='width:24%;'><a href='<?= $self_link ?>&page=members'>Members</a></li>
            <li style='width:25%;'><a href='<?= $self_link ?>&page=missions'>Missions</a></li>
            <li style='width:24%;'><a href='<?= $self_link ?>&page=controls'>Controls</a></li>
        <?php else: ?>
            <li style='width:32.5%;'><a href='<?= $self_link ?>'>Clan HQ</a></li>
            <li style='width:33%;'><a href='<?= $self_link ?>&page=members'>Members</a></li>
            <li style='width:33%;'><a href='<?= $self_link ?>&page=missions'>Missions</a></li>
        <?php endif; ?>
    </ul>
</div>
<div class='submenuMargin'></div>
<table class='table'><tr><th><?= $player->clan->name ?> Clan</th></tr>
    <tr><td>
        <!--Clan Symbol-->
        <div class='clanLogo'>
            <img src='<?= $player->clan->logo_url ?>' style='max-width:100px;max-height:100px;' />
        </div>
        <div class='clanInfo'>
            <label style='width:7.2em;'>Village:</label>
                <?= $player->clan->village ?><br />
            <?php if($clan->boost_type == 'training'): ?>
                <label style='width:7.2em;'>Boost:</label>
                    <?= (int)$clan->boost_amount ?>% faster <?= System::unSlug($clan->boost_effect) ?> training<br />
            <?php endif; ?>
            <label style='width:7.2em;'>Reputation:</label>
                <?= $player->clan->points ?><br />
            <p style='font-style:italic;text-align:center;width:75%;'><?= $player->clan->motto ?></p>
        </div>
        <br style='clear:both;margin:0;' />
    </td></tr>
</table>

<!-- Members -->
<?php if($page == 'members'): ?>
    <table class='table'><tr><th colspan='4'>Clan Members</th></tr>
        <tr>
            <th style='width:30%;'>Username</th>
            <th style='width:20%;'>Rank</th>
            <th style='width:20%;'>Level</th>
            <th style='width:30%;'>Experience</th>
        </tr>

        <?php $count = 0; ?>
        <?php foreach($members as $member): ?>
            <?php $class = '';
                if(is_int($count++ / 2)) {
                    $class = 'row1';
                }
                else {
                    $class = 'row2';
                }
            ?>
            <tr>
                <td style='width:29%;' class='<?= $class ?>'>
                    <a href='<?= $system->router->links['members'] ?>&user=<?= $member->name ?>'><?= $member->name ?></a>
                </td>
                <td style='width:20%;text-align:center;' class='<?= $class ?>'><?= $RANK_NAMES[$member->rank_num] ?></td>
                <td style='width:20%;text-align:center;' class='<?= $class ?>'><?= $member->level ?></td>
                <td style='width:30%;text-align:center;' class='<?= $class ?>'><?= $member->exp ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p style='text-align:center;'>
       <?php if($min > 0): ?>
            <a href='<?= $self_link ?>&page=members&min=<?= $prev ?>'>Previous</a>
       <?php endif; ?>

       <?php if($next > 0): ?>
            <?php if($min > 0): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
            <?php endif; ?>
            <a href='<?= $self_link ?>&page=members&min=<?= $next ?>'>Next</a>
       <?php endif; ?>
    </p>
<?php elseif($page == 'missions'): ?>
    <?php
        $system->printMessage();
        $view = $max_mission_rank;
        if(isset($_GET['view_rank'])) {
            $view = (int)$_GET['view_rank'];
            if($view < 1 or $view > $max_mission_rank) {
                $view = $max_mission_rank;
            }
        }
    ?>
    <table class='table'><tr><th>Clan Missions</th></tr>
        <?php if($active_mission): ?>
            <?php if($active_mission->mission_type == Mission::TYPE_CLAN): ?>
                <tr><td style='text-align: center;'>
                    <p style='font-weight:bold;margin:2px 0;'>Active Mission: <?= $active_mission->name ?></p>
                    <p style='margin:0 0 4px;'><?= $player->mission_stage['description'] ?></p>
                    <a href='<?= $system->router->links['mission'] ?>&cancel_mission=1'>Abandon Mission</a>
                </td></tr>
            <?php else: ?>
                <tr><td style='text-align:center;'>
                    You are already on a mission!
                    type: <?= $active_mission->mission_type ?> / <?= Mission::TYPE_CLAN ?>
                </td></tr>
            <?php endif; ?>
        <?php else: ?>
            <tr><td style='text-align:center;'>
                <?php foreach($missions as $id => $mission): ?>
                    <a href='<?= $self_link ?>&page=missions&start_mission=<?= $id ?>'>
                        <p class='button' style='margin:5px;'><?= $mission['name'] ?></p>
                    </a><br />
                <?php endforeach; ?>
            </td></tr>
        <?php endif; ?>
    </table>
<!-- Officer controls-->
<?php elseif($player->clan_office && $page == 'controls'): ?>
    <table class='table' style='margin-bottom:0;border-bottom-left-radius:0;border-bottom-right-radius:0;'>
        <tr><th>Controls</th></tr>
        <tr><td style='text-align:center;'>
                <form action='<?= $self_link ?>&page=controls' method='post'>
                    <input type='hidden' name='resign' value='1' />
                    <button type='submit'>Resign</button>
                </form>
            </td></tr>
    </table>
    <table class='table' style='margin-top:0;border-top-left-radius:0;border-top-right-radius:0;'>
        <?php if($player->clan_office == 1): ?>
            <tr>
                <th style='width:60%;border-radius:0;'>Motto</th>
                <th style='width:40%;border-radius:0;'>Logo</th>
            <tr>
                <td>
                    <!--Motto-->
                    <div style='text-align:center;'>
                        <form action='<?= $self_link ?>&page=controls' method='post'>
                            <textarea name='motto' style='width:350px;'><?= $player->clan->motto ?></textarea><br />
                            <button type='submit'>Edit</button>
                        </form>
                    </div>
                </td>
                <td>
                    <!--Logo-->
                    <div style='text-align:center;'>
                        <form action='<?= $self_link ?>&page=controls' method='post'>
                            <input type='text' name='logo' style='width:200px;' value='<?= $player->clan->logo_url ?>' /><br />
                            <button type='submit'>Edit</button>
                        </form>
                    </div>
                </td>
            </tr>
            <tr><th colspan='2'>Change Boost (<?= Clan::$BOOST_COST ?> Reputation)</th></tr>
            <tr><td colspan='2' style='text-align:center;'>
                <div style='text-align:center;'>
                    <form action='<?= $self_link ?>&page=controls' method='post'>
                        <select name='boost'>
                            <?php foreach($player->clan->getTrainingBoostOptions() as $boost): ?>
                                <option value='<?= $boost ?>'><?= System::unSlug($boost) ?> training</option>
                            <?php endforeach; ?>
                            </select><br />
                        <button type='submit'>Change Boost</button>
                    </form>
                </div>
            </td></tr>
        <?php endif; ?>

        <tr><th colspan='2'>Clan Info</th></tr>
        <tr><td colspan='2' style='text-align:center;'>
                <!--Info-->
                <form action='<?= $self_link ?>&page=controls' method='post'>
                    <textarea name='info' style='width:550px;height:250px;'><?= $player->clan->info ?></textarea><br />
                    <button type='submit'>Edit</button>
                </form>
            </td></tr>
    </table>
<?php elseif($page == 'HQ'): ?>
    <table class='table'><tr><th>Clan Leaders</th></tr>
        <tr><td style='text-align:center;'>
            <?php foreach(Clan::$offices as $office): ?>
                <?php $avatar_size = $office == 1 ? 125 : 100; ?>

                <div style='display:inline-block;height:<?= $avatar_size ?>px;width:<?= $avatar_size ?>px;margin-right:20px;'>
                    <?php if(isset($officers[$office])): ?>
                        <img src='<?= $officers[$office]->avatar_link ?>' /><br />
                        <span style='font-weight:bold;'>
                        <a href='<?= $system->router->links['members'] ?>&user=<?= $officers[$office]->name ?>'>
                            <?= $officers[$office]->name ?>
                        </a></span><br />

                        <?php if($can_claim[$office]): ?>
                            <a href='<?= $self_link ?>&page=challenge&challenge=<?= $office ?>'>(Claim)</a>
                        <?php elseif($can_challenge[$office]): ?>
                            <!--<a href='<?= $self_link ?>&page=challenge&challenge=<?= $office ?>'>(Challenge)</a>-->
                        <?php endif; ?>
                        <br />
                    <?php else: ?>
                        <img src='../images/default_avatar.png' style='max-width:100px;max-height:100px;' /><br />
                        <span style='font-weight:bold;'>None</span><br />
                        <?php if($can_claim[$office]): ?>
                            <a style='text-decoration:none;' href='<?= $self_link ?>&page=challenge&challenge=<?= $office ?>'>(Claim)</a>
                        <?php endif; ?>
                        <br />
                    <?php endif; ?>

                    <?= Clan::$office_labels[$office] ?>
                </div>
            <?php endforeach; ?>
        </td></tr>
    </table>
    <table class='table'><tr><th>Clan Hall</th></tr>
        <tr><td style='text-align:center;'>
            <?= $player->clan->info ?>
        </td></tr>
    </table>
<?php endif; ?>
