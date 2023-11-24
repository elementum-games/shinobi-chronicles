
<?php

/**
 * @var System $system;
 * @var BattleManager $battleManager
 * @var Battle $battle
 * @var Fighter $player
 * @var Fighter $opponent
 *
 * @var string $self_link
 * @var string $refresh_link
 */

$health_percent = round(($player->health / $player->max_health) * 100);
$chakra_percent = round(($player->chakra / $player->max_chakra) * 100);
$stamina_percent = round(($player->stamina / $player->max_stamina) * 100);
$player_avatar_size = $player->getAvatarSize() . 'px';

$opponent_health_percent = round(($opponent->health / $opponent->max_health) * 100);
$opponent_avatar_size = $opponent->getAvatarSize() . 'px';

$battle_text = null;
if($battle->battle_text) {
    $battle_text = $system->html_parse(stripslashes($battle->battle_text));
    $battle_text = str_replace(array('[br]', '[hr]'), array('<br />', '<hr />'), $battle_text);
}
?>

<script type='text/javascript'>
    let battle_id = <?= $battle->battle_id ?>;
    var apiUrl = `api/battle.php?check_turn=` + battle_id;
    let turn_count = <?= $battle->turn_count ?>;
    let prep_time_remaining = <?= $battle->prepTimeRemaining() ?>;
    let player1_submitted = <?= (int)isset($battle->fighter_actions[$battle->player1->combat_id]) ?>;
    let player2_submitted = <?= (int)isset($battle->fighter_actions[$battle->player2->combat_id]) ?>;
    let player1_time = <?= $battle->timeRemaining($battle->player1_id) ?>;
    let player2_time = <?= $battle->timeRemaining($battle->player2_id) ?>;

    function checkTurn() {
        if (prep_time_remaining > 0 && turn_count == 0) {
            prep_time_remaining--;
            $("#prep_time_remaining").text(prep_time_remaining);
            if (prep_time_remaining == 0) {
                updateContent();
                return;
            }
        }
        player1_time--;
        $("#player1_time").text(player1_time);
        player2_time--;
        $("#player2_time").text(player2_time);
        if ((player1_time <= 0 || player1_submitted) && (player2_time <= 0 || player2_submitted)) {
            updateContent();
            return;
        }
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.data != undefined && data.data.turn_count > turn_count) {
                    updateContent();
                } else {
                    setTimeout(checkTurn, 1000);
                }
            })
            .catch(err => {
                console.error(err);
                setTimeout(checkTurn, 1000);
            });
    }

    function updateContent() {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newContent = doc.querySelector("#content").innerHTML;
                $("#content").html(newContent);
            })
            .catch(err => {
                console.error('Failed to fetch new content', err);
            });
    }

    checkTurn();
</script>

<style type='text/css'>
    .playerAvatar {
        display:block;
        margin: auto;
        max-width:<?= $player_avatar_size ?> !important;
        max-height:<?= $player_avatar_size ?> !important;
    }
    .opponentAvatar {
        display:block;
        margin:auto;
        max-width:<?= $opponent_avatar_size ?> !important;
        max-height:<?= $opponent_avatar_size ?> !important;
    }

    .resourceBarOuter {
        position: relative;
        height: 15px;
        width: 240px;
        border: 1px solid black;
        border-radius: 17px;

        background-color: rgba(0, 0, 0, 0.6);
    }

    /* Parent must be Position: relative */
    .innerResourceBarLabel{
        display: block;
        position: absolute;
        left: 0;
        right: 0;

        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.2px;
        line-height:15px;

        color: #ffffff;
        text-shadow:
                -1px 0 0 rgba(0,0,0,0.7),
                -1px -1px 0 rgba(0,0,0,0.7),
                0 -1px 0 rgba(0,0,0,0.7),
                1px -1px 0 rgba(0,0,0,0.7),
                1px 0 0 rgba(0,0,0,0.7),
                1px 1px 0 rgba(0,0,0,0.7),
                0 1px 0 rgba(0,0,0,0.7),
                -1px 1px 0 rgba(0,0,0,0.7);

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

    #forfeitButton, #retreatButton {
        cursor: pointer;
    }
    #forfeitDialog button, #retreatDialog button {
        cursor: pointer;
    }

    .forfeitFormButtons, .retreatFormButtons {
        display: flex;
        justify-content: space-evenly;
        margin-top: 12px;
        padding: 0 25px;
    }

    #forfeitDialog, #retreatDialog {
        display: none;
    }
</style>

<div class='submenu'>
    <ul class='submenu'>
        <li style='width:100%;'><a href='<?= $refresh_link ?>'>Refresh Battle</a></li>
    </ul>
</div>
<div class='submenuMargin'></div>

<?php $system->printMessage(); ?>
<table class='table'>
    <tr><th colspan="2">Turn <?= $battle->winner ? $battle->turn_count : $battle->turn_count + 1 ?></th></tr>
    <tr>
        <th id='bi_th_user' style='width:50%;'>
            <a href='<?= $system->router->links['members'] ?>&user=<?= $player->getName() ?>' style='text-decoration:none'><?= $player->getName() ?></a>
        </th>
        <th id='bi_th_opponent' style='width:50%;'>
            <?php if($opponent instanceof AI): ?>
                <?= $opponent->getName() ?>
            <?php else: ?>
                <a href='<?= $system->router->links['members'] ?>&user=<?= $opponent->getName() ?>' style='text-decoration:none'><?= $opponent->getName() ?></a>
            <?php endif; ?>
        </th>
    </tr>
    <tr>
        <td style='text-align: center;' id='bi_td_player'>
            <img src='<?= $player->avatar_link ?>' class='playerAvatar' alt='player_profile_img' />
            <div id='player_battle_stats_container' style='display: inline-block; text-align: center; margin-top: 10px;'>

                <!-- Health -->
                <div class='resourceBarOuter healthPreview'>
                    <label class='innerResourceBarLabel' ><?= sprintf("%.0f", $player->health) ?> / <?= sprintf("%.0f", $player->max_health) ?></label>
                    <div class='healthFill' style='width:<?= $health_percent ?>%;'></div>
                </div>

                <?php if(!$battleManager->spectate): ?>

                    <!-- Chakra -->
                <div class='resourceBarOuter chakraPreview' style='margin-top:6px;'>
                    <label class='innerResourceBarLabel'>
                        <?= sprintf("%.0f", $player->chakra) ?> / <?= sprintf("%.0f", $player->max_chakra) ?>
                    </label>
                    <div class='chakraFill' style='width:<?= $chakra_percent ?>%;'></div>
                </div>

                    <!-- Stamina -->
                <div class='resourceBarOuter staminaPreview' style='margin-top:6px;'>
                    <label class='innerResourceBarLabel'>
                        <?= sprintf("%.0f", $player->stamina) ?> / <?= sprintf("%.0f", $player->max_stamina) ?>
                    </label>
                    <div class='staminaFill' style='width:<?= $stamina_percent ?>%;'></div>
                </div>

                <?php endif; ?>
            </div>
        </td>
        <td style='text-align: center;' id='bi_td_opponent'>
            <img src='<?= $opponent->avatar_link ?>' class='opponentAvatar' />
            <div id='ai_battle_stats_container' style='display: inline-block; text-align: center; margin-top: 10px;'>
                <div class='resourceBarOuter healthPreview' style='margin-top:8px;'>
                    <div class='healthFill' style='width:<?= $opponent_health_percent ?>%;'>
                        <label class='innerResourceBarLabel'>
                            <?= sprintf("%.0f", $opponent->health) ?> / <?= sprintf("%.0f", $opponent->max_health) ?>
                        </label>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>

<table class='table'>
    <!--// Battle text display-->
    <?php if($battle_text): ?>
        <tr><th colspan='2'>Last turn</th></tr>
        <tr><td style='text-align:center;' colspan='2'><?= $battle_text ?></td></tr>
    <?php endif; ?>

    <!--// Trigger win action or display action prompt-->
    <?php if(!$battle->isComplete() && !$battleManager->spectate): ?>
        <tr><th colspan='2'>Select Action</th></tr>

        <?php if(!$battleManager->playerActionSubmitted()): ?>
            <?php require 'templates/battle/action_prompt.php'; ?>
        <?php elseif(!$battleManager->opponentActionSubmitted()): ?>
            <tr><td colspan='2' style="text-align: center">Please wait for <?= $opponent->getName() ?> to select an action.</td></tr>
        <?php endif; ?>

        <!--// Turn timer-->
        <tr><td style='text-align:center;' colspan='2'>
                <p>
                    <?php if ($battle->isPreparationPhase()): ?>
                        <?php echo "Prep-Time Remaining: <span id='prep_time_remaining'>" . $battle->prepTimeRemaining() . "</span>" ?>
                    <?php elseif (!$battleManager->opponent instanceof NPC): ?>
                        <?php echo "Time Remaining<br>"?>
                        <?php if (isset($battle->fighter_actions[$battle->player1->combat_id])): ?>
                            <?php echo "<b>" . $battle->player1->user_name . ":</b> ". "waiting" ?>
                        <?php else: ?>
                            <?php echo "<b>" . $battle->player1->user_name . ":</b> <span id='player1_time'>". $battle->timeRemaining($battle->player1_id) . "</span> seconds" ?>
                        <?php endif; ?>
                        <br />
                        <?php if (isset($battle->fighter_actions[$battle->player2->combat_id])): ?>
                            <?php echo "<b>" . $battle->player2->user_name . ":</b> ". "waiting" ?>
                        <?php else: ?>
                            <?php echo "<b>" . $battle->player2->user_name . ":</b> <span id='player2_time'>" . $battle->timeRemaining($battle->player2_id) . "</span> seconds" ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
                <?php if ($battle->isPreparationPhase()): ?>
                    <a id='retreatButton'>Retreat</a>
                    <div id="retreatDialog">
                        <form method="post">
                            <p>Are you sure you want to retreat from this battle? You will suffer half the normal Reputation loss.</p>
                            <div class='retreatFormButtons'>
                                <button id="cancelBtn" value="cancel">Cancel</button>
                                <button id="confirmBtn" name="retreat" value="1">Confirm</button>
                            </div>
                        </form>
                    </div>
                    <script type='text/javascript'>
                        const retreatButton = document.getElementById('retreatButton');
                        const retreatDialog = document.getElementById('retreatDialog');
                        const cancelButton = document.getElementById('cancelBtn');

                        retreatButton.addEventListener('click', () => {
                            $(retreatDialog).show();
                        });
                        cancelButton.addEventListener('click', (e) => {
                            e.preventDefault();
                            $(retreatDialog).hide();
                        });
                    </script>
                <?php else: ?>
                    <a id='forfeitButton'>Forfeit</a>
                    <div id="forfeitDialog">
                        <form method="post">
                            <p>Are you sure you want to forfeit this battle?</p>
                            <div class='forfeitFormButtons'>
                                <button id="cancelBtn" value="cancel">Cancel</button>
                                <button id="confirmBtn" name="forfeit" value="1">Confirm</button>
                            </div>
                        </form>
                    </div>
                    <script type='text/javascript'>
                        const forfeitButton = document.getElementById('forfeitButton');
                        const forfeitDialog = document.getElementById('forfeitDialog');
                        const cancelButton = document.getElementById('cancelBtn');

                        forfeitButton.addEventListener('click', () => {
                            $(forfeitDialog).show();
                        });
                        cancelButton.addEventListener('click', (e) => {
                            e.preventDefault();
                            $(forfeitDialog).hide();
                        });
                    </script>
                    <?php endif; ?>

</td></tr>
    <?php endif; ?>

    <?php if($battleManager->spectate): ?>
        <tr><td style='text-align:center;' colspan='2'>
                <?php if($battle->winner == Battle::TEAM1): ?>
                    <?=  $battle->player1->getName() ?> won!
                <?php elseif($battle->winner == Battle::TEAM2): ?>
                    <?= $battle->player2->getName() ?> won!
                <?php elseif($battle->winner == Battle::DRAW): ?>
                    Fight ended in a draw.
                <?php else: ?>
                    <?php echo "Time Remaining<br>"?>
                        <?php if (isset($battle->fighter_actions[$battle->player1->combat_id])): ?>
                            <?php echo "<b>" . $battle->player1->user_name . ":</b> ". "waiting" ?>
                        <?php else: ?>
                            <?php echo "<b>" . $battle->player1->user_name . ":</b> <span id='player1_time'>". $battle->timeRemaining($battle->player1_id) . "</span> seconds" ?>
                        <?php endif; ?>
                        <br />
                        <?php if (isset($battle->fighter_actions[$battle->player2->combat_id])): ?>
                            <?php echo "<b>" . $battle->player2->user_name . ":</b> ". "waiting" ?>
                        <?php else: ?>
                <?php echo "<b>" . $battle->player2->user_name . ":</b> <span id='player2_time'>" . $battle->timeRemaining($battle->player2_id) . "</span> seconds" ?>
                        <?php endif; ?>
                <?php endif; ?>
            </td></tr>
    <?php endif; ?>
</table>