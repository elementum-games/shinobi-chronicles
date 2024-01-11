<?php
/** @var array $user_battles */
/** @var array $ai_battles */
/** @var array $battle_logs */
/** @var string $battle_background_link */
/** @var int $p1_max_health */
/** @var int $p2_max_health */
/** @var string $display */

/** @var System $system */

?>

<script>
    function toggleLog(turn) {
        $(".turn_" + turn).toggle();
    }
    function copyUrl() {
        navigator.clipboard.writeText(window.location.href)
            .then(() => {
                console.log('URL copied to clipboard');
            })
            .catch(err => {
                console.error('Error in copying URL: ', err);
            });
        $("#share_btn").text("Link Copied!");
    }
    function showLogs() {
        $(".turn").show();
    }
    function hideLogs() {
        $(".turn").hide();
    }
    function updateDisplay(display) {
        let url = new URL(window.location.href);
        let params = url.searchParams;
        params.set('display', display);
        url.search = params.toString();
        window.location.href = url.toString();
    }
</script>

<style type='text/css'>
        .playerAvatar {
        display: block;
        margin-top: auto;
        margin-left: auto;
        margin-right: auto;
        max-width: <?= $p1_avatar_size ?>px !important;
        max-height: <?= $p1_avatar_size ?>px !important;
    }

    .opponentAvatar {
        display: block;
        margin-top: auto;
        margin-left: auto;
        margin-right: auto;
        max-width: <?= $p2_avatar_size ?>px !important;
        max-height: <?= $p2_avatar_size ?>px !important;
    }

    .resourceBarOuter {
        position: relative;
        height: 15px;
        width: 240px;
        border: 1px solid black;
        border-radius: 17px;
        background-color: rgba(0, 0, 0, 0.6);
        margin-left: auto;
        margin-right: auto;
    }

    /* Parent must be Position: relative */
    .innerResourceBarLabel {
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.2px;
        line-height: 15px;
        color: #ffffff;
        text-shadow: -1px 0 0 rgba(0,0,0,0.7), -1px -1px 0 rgba(0,0,0,0.7), 0 -1px 0 rgba(0,0,0,0.7), 1px -1px 0 rgba(0,0,0,0.7), 1px 0 0 rgba(0,0,0,0.7), 1px 1px 0 rgba(0,0,0,0.7), 0 1px 0 rgba(0,0,0,0.7), -1px 1px 0 rgba(0,0,0,0.7);
        z-index: 100;
    }

    .healthFill {
        background: linear-gradient(to right, rgb(200, 30, 20), rgb(240, 50, 50));
        height: 100%;
        border-radius: 12px;
    }

    .chakraFill {
        background: #1060ff linear-gradient(to right, #1060ff, #2080ff);
        height: 100%;
        border-radius: 12px;
    }

    .staminaFill {
        background: linear-gradient(to right, rgb(10, 180, 10), rgb(40, 220, 40));
        height: 100%;
        border-radius: 12px;
    }

        .active_effects_container {
        display: flex;
        justify-content: center;
        column-gap: 14px;
        row-gap: 7px;
        flex-wrap: wrap;
        margin-bottom: 5px;
        margin-top: 5px;
    }

    .active_effect {
        display: inline-flex;
        gap: 10px;
        color: white;
        font-family: var(--font-secondary);
        font-weight: bold;
        height: 26px;
        align-items: center;
        justify-content: center;
    }

        .active_effect.buff .effect_name {
            background-color: #2a5e3c;
        }

        .active_effect.nerf .effect_name {
            background-color: #7c2d2d;
        }

        .active_effect .effect_duration {
            margin-left: -27px;
            height: 21px;
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
            margin-top: 2px;
        }

        .active_effect .effect_duration_decoration {
            margin-left: -22px;
            margin-top: 1px;
        }

        .active_effect .effect_name {
            line-height: 20px;
            font-size: 12px;
            height: 21px;
            padding-left: 10px;
            padding-right: 18px;
            border-radius: 4px;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.2);
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
        }
</style>

<!-- Battle Log Display -->
<?php if (isset($battle_logs) && count($battle_logs) > 1): ?>
    <table class="table" style="width: 90%; margin-left: auto; margin-right: auto; margin-bottom: -5px; margin-top: -5px;">
    <tbody>
        <tr>
            <td style="text-align: center">
                <a href="#" onclick="updateDisplay('full')">Full View</a>
            </td>
            <td style="text-align: center">
                <a href="#" onclick="updateDisplay('simple')">Simple View</a>
            </td>
            <td style="text-align: center">
                <a href="#" onclick="hideLogs()">Collapse All</a>
            </td>
            <td style="text-align: center">
                <a href="#" onclick="showLogs()">Expand All</a>
            </td>
            <td style="text-align: center">
                <a id="share_btn" href="#" onclick="copyUrl()">Share</a>
            </td>
        </tr>
    </tbody>
</table>
    <?php if ($display == "simple"): ?>
        <table class='table' style="text-align:center;">
            <tr>
                <th colspan="2">Battle Log</th>
            </tr>
            <?php foreach ($battle_logs as $log): ?>
            <?php if ($log['turn'] == 0): ?>
            <?php continue; ?>
            <?php endif; ?>
            <tr>
                <th style="cursor: pointer" onclick="toggleLog(<?= $log['turn'] ?>)" colspan="2">Turn <?= $log['turn'] ?></th>
            </tr>
            <tr class="turn turn_<?= $log['turn'] ?>">
                <td colspan="2">
                    <?= $system->html_parse($log['content']) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <table class='table' style="text-align:center;">
            <tr>
                <th colspan="2">Battle Log</th>
            </tr>
            <?php foreach ($battle_logs as $log): ?>
                    <?php if ($log['turn'] == 0): ?>
                            <?php continue; ?>
                    <?php endif; ?>
                    <tr>
                        <th style="cursor: pointer" onclick="toggleLog(<?= $log['turn'] ?>)" colspan="2">Turn <?= $log['turn'] ?></th>
                    </tr>
                    <?php if (isset($p1_max_health)): ?>
                            <tr class="turn turn_<?= $log['turn'] ?>">
                                <th><?= $p1_name ?></th>
                                <th><?= $p2_name ?></th>
                            </tr>
                            <tr class="turn turn_<?= $log['turn'] ?>" style="background: linear-gradient(to right, var(--main-background-color) 0%, transparent 10%, transparent 90%, var(--main-background-color) 100%), url('<?= $battle_background_link ?>'); background-repeat: no-repeat; background-position: center; background-size: cover;">
                    <td id='bi_td_player' style="border-right: none">
                        <div style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; min-height: 273px">
                            <img src='<?= $p1_avatar ?>' class='playerAvatar' alt='player_profile_img' />
                            <div id='player_battle_stats_container' style='display: inline-block; text-align: center; margin-top: 8px;'>
                                <div class='resourceBarOuter healthPreview'>
                                    <label class='innerResourceBarLabel'><?= sprintf("%.0f", $log['player1_health']) ?> / <?= sprintf("%.0f", $p1_max_health) ?></label>
                                    <div class='healthFill' style='width:<?= round(($log['player1_health'] / $p1_max_health) * 100) ?>%;'></div>
                                </div>
                                <div class='resourceBarOuter chakraPreview' style='margin-top:6px;'>
                                    <label style="opacity: 75%" class='innerResourceBarLabel'>??? / ???</label>
                                    <div class='chakraFill' style='width:100%'></div>
                                </div>
                                <div class='resourceBarOuter staminaPreview' style='margin-top:6px;'>
                                    <label style="opacity: 75%" class='innerResourceBarLabel'>??? / ???</label>
                                    <div class='staminaFill' style='width:100%;'></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style='text-align: center; border-left: none' id='bi_td_opponent'>
                        <div style="display: flex; flex-direction: column; justify-content: flex-start; align-items: center; min-height: 273px">
                            <img src='<?= $p2_avatar ?>' class='opponentAvatar' />
                            <div id='ai_battle_stats_container' style='display: inline-block; text-align: center'>
                                <div class='resourceBarOuter healthPreview' style='margin-top: 8px';>
                                    <label class='innerResourceBarLabel'><?= sprintf("%.0f", $log['player2_health']) ?> / <?= sprintf("%.0f", $p2_max_health) ?></label>
                                    <div class='healthFill' style='width:<?= round(($log['player2_health'] / $p2_max_health) * 100) ?>%;'></div>
                                </div>
                                <div class='resourceBarOuter chakraPreview' style='margin-top:6px;'>
                                    <label style="opacity: 75%" class='innerResourceBarLabel'>??? / ???</label>
                                    <div class='chakraFill' style='width:100%'></div>
                                </div>
                                <div class='resourceBarOuter staminaPreview' style='margin-top:6px;'>
                                    <label style="opacity: 75%" class='innerResourceBarLabel'>??? / ???</label>
                                    <div class='staminaFill' style='width:100%;'></div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if (count($log['active_effects']) > 0): ?>
                <tr class="turn turn_<?= $log['turn'] ?>">
                    <td style="border-top:none">
                        <div class="active_effects_container">
                            <?php foreach ($log['active_effects'] as $effect): ?>
                            <?php if ($effect->target == $p1_key && $effect->turns > 0): ?>
                            <div class="<?php echo in_array($effect->effect, BattleEffect::$buff_effects) ? "active_effect buff" : "active_effect nerf" ?>">
                                <div class="effect_name">
                                    <?= System::unSlug($effect->effect) ?>
                                </div>
                                <svg class="effect_duration_decoration" width="26" height="26">
                                    <polygon points="0,13 13,0 26,13 13,26" fill="#ad9357" stroke="black" stroke-width="1"></polygon>
                                </svg>
                                <div class="effect_duration">
                                    <?= System::unSlug($effect->turns) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td style="border-top:none">
                        <div class="active_effects_container">
                            <?php foreach ($log['active_effects'] as $effect): ?>
                            <?php if ($effect->target == $p2_key && $effect->turns > 0): ?>
                            <div class="<?php echo in_array($effect->effect, BattleEffect::$buff_effects) ? "active_effect buff" : "active_effect nerf" ?>">
                                <div class="effect_name">
                                    <?= System::unSlug($effect->effect) ?>
                                </div>
                                <svg class="effect_duration_decoration" width="26" height="26">
                                    <polygon points="0,13 13,0 26,13 13,26" fill="#ad9357" stroke="black" stroke-width="1"></polygon>
                                </svg>
                                <div class="effect_duration">
                                    <?= System::unSlug($effect->turns) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if (isset($p1_max_health)): ?>
                <tr class="turn turn_<?= $log['turn'] ?>">
                    <th colspan="2">
                        Battle Log
                    </th>
                </tr>
            <?php endif; ?>
            <tr class="turn turn_<?= $log['turn'] ?>">
                <td colspan="2">
                    <?= $system->html_parse($log['content']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
<?php endif; ?>

<!-- PvP Battle Listing -->
<?php if (isset($user_battles)): ?>
<table class='table' style="text-align:center;">
    <tr>
        <th colspan='4'>PvP Battle History</th>
    </tr>
    <tr>
        <th>Player 1</th>
        <th>Player 2</th>
        <th>Winner</th>
        <th>Log</th>
    </tr>
    <?php foreach($user_battles as $battle): ?>
    <tr>
        <td>
            <a href="<?= $system->router->links['members']?>&user=<?= $battle['player1'] ?>" style='text-decoration:none'>
                <?= $battle['player1'] ?>
            </a>
        </td>
        <td>
            <a href="<?= $system->router->links['members']?>&user=<?= $battle['player2'] ?>" style='text-decoration:none'>
                <?= $battle['player2'] ?>
            </a>
        </td>
        <td>
            <?= $battle['winner'] ?>
        </td>
        <td style="text-align:center">
            <a href="<?= $system->router->links['view_battles'] ?>&view_log=<?= $battle['id'] ?>">View</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<!-- AI Battle Listing -->
<?php if (isset($ai_battles)): ?>
<table class='table' style="text-align:center;">
    <tr>
        <th colspan='4'>PvE Battle History</th>
    </tr>
    <tr>
        <th>Player</th>
        <th>Opponent</th>
        <th>Winner</th>
        <th>Log</th>
    </tr>
    <?php foreach ($ai_battles as $battle): ?>
        <tr>
            <td>
                <a href="<?= $system->router->links['members'] ?>&user=<?= $battle['player1'] ?>" style='text-decoration:none'>
                    <?= $battle['player1'] ?>
                </a>
            </td>
            <td>
                <a href="<?= $system->router->links['members'] ?>&user=<?= $battle['player2'] ?>" style='text-decoration:none'>
                    <?= $battle['player2'] ?>
                </a>
            </td>
            <td>
                <?= $battle['winner'] ?>
            </td>
            <td style="text-align:center">
                <a href="<?= $system->router->links['view_battles'] ?>&view_log=<?= $battle['id'] ?>">View</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>