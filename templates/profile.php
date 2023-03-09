<?php
/**
 * @var User $player
 * @var System $system
 *
 * @var int $exp_needed
 */

$exp_percent = ($player->rank->exp_per_level - ($exp_needed - $player->exp)) / $player->rank->exp_per_level * 100;
if($exp_percent < 0) {
    $exp_percent = 0;
}
else if($exp_percent > 100) {
    $exp_percent = 100;
}
$exp_width = round($exp_percent * 2);

$regen_cut = 0;
if($player->battle_id or isset($_SESSION['ai_id'])) {
    $regen_cut = round(($player->regen_rate + $player->regen_boost) * 0.7, 1);
}

$healthRegen = ($player->regen_rate + $player->regen_boost - $regen_cut) * 2;
$standardRegen = $player->regen_rate + $player->regen_boost - $regen_cut;

$health_width = round(($player->health / $player->max_health) * 100, 3);
$health_width = min($health_width, 100.0);

$chakra_width = round(($player->chakra / $player->max_chakra) * 100, 3);
$chakra_width = min($chakra_width, 100.0);

$stamina_width = round(($player->stamina / $player->max_stamina) * 100, 3);
$stamina_width = min($stamina_width, 100.0);

$health_regen_amount = min($healthRegen, $player->max_health - $player->health);
$chakra_regen_amount = min($standardRegen, $player->max_chakra - $player->chakra);
$stamina_regen_amount = min($standardRegen, $player->max_stamina - $player->stamina);

$health_regen_width = round($health_regen_amount / $player->max_health, 3) * 100;
$chakra_regen_width = round($chakra_regen_amount / $player->max_chakra, 3) * 100;
$stamina_regen_width = round($stamina_regen_amount / $player->max_stamina, 3) * 100;

$time_since_last_regen = time() - $player->last_update;

$exp_remaining = $exp_needed - $player->exp;
if($exp_remaining < 0) {
    $exp_remaining = 0;
}

$clan_positions = [
    1 => 'Leader',
    2 => 'Elder 1',
    3 => 'Elder 2',
];

?>

<style>
    .resourceContainer {
        width: 100%;
        margin: 4px auto;

        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .resourceBarOuter {
        display: flex;
        position: relative;
        height: 17px;
        width: 260px;
        border: 1px solid black;
        border-radius: 17px;

        background: rgba(0,0,0,0.7);
        overflow: hidden;
    }

    /* Parent must be Position: relative */
    .innerResourceBarLabel{
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        align-self: center;

        font-size: 12px;
        font-weight: bold;
        letter-spacing: 0.2px;
        line-height:15px;
        text-align: center;

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

    .fill {
        position: absolute;
        top: 0;
        z-index: 2;
        height: 100%;
    }
    .preview {
        position: absolute;
        top: 0;
        z-index: 2;
        height: 100%;

        opacity: 0.5;
        overflow: hidden;
    }

    .health {
        background: linear-gradient(to right, rgb(200, 30, 20), rgb(240, 50, 50));
    }
    .health.preview {
        background: rgb(240, 50, 50);
    }

    .chakra {
        background: #1060ff linear-gradient(to right, #1060ff, #2080ff);
    }
    .chakra.preview {
        background: #2080ff;
    }

    .stamina {
        background: linear-gradient(to right, rgb(10, 180, 10), rgb(40, 220, 40));
    }
    .stamina.preview {
        background:  rgb(40, 220, 40);
    }


    .preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 400%;
        height: 100%;
        transform: translate3d(-100%, 0, 0);

        background: linear-gradient(
            to right,
            transparent,
            rgba(255,255,255,0.1),
            rgba(255,255,255,0.3),
            rgba(255,255,255,0.4),
            rgba(255,255,255,0.3),
            rgba(255,255,255,0.1),
            transparent
        );

        animation-duration: 2.4s;
        animation-name: gleam;
        animation-iteration-count: infinite;
        animation-timing-function: linear;
    }
    @keyframes gleam {
        0% {
            transform: translate3d(-100%, 0, 0);
        }
        40% {
            transform: translate3d(25%, 0, 0);
        }
        100% {
            transform: translate3d(25%, 0, 0);
        }
    }
</style>

<table class='profile_table table'>
<tr>
    <td style='width:50%;text-align:center;'>
        <span style='font-size:1.3em;font-family:\"tempus sans itc\",serif;font-weight:bold;'><?= $player->user_name ?></span><br />
        <?= $system->imageCheck($player->avatar_link, $player->getAvatarSize()) ?>
        <br />
    </td>
    <td style='width:50%;'>

        <!--Health Bar-->
        <div class='resourceContainer'>
            <span>Health:</span>
            <div id='health' class='resourceBarOuter'>
                <label class='innerResourceBarLabel'>
                    <?= sprintf("%.2f", $player->health) ?> / <?= sprintf("%.2f", $player->max_health) ?>
                </label>
                <div class='health fill' style='width:<?= $health_width ?>%;'></div>
                <div class='health preview' style='
                    left:<?= $health_width ?>%;
                    width:<?= $health_regen_width ?>%;'
                ></div>
            </div>
        </div>
        
        <!--Chakra Bar-->
        <div class='resourceContainer'>
            <span>Chakra:</span>
            <div id='chakra' class='resourceBarOuter'>
                <label class='innerResourceBarLabel'>
                    <?= sprintf("%.2f", $player->chakra) ?> / <?= sprintf("%.2f", $player->max_chakra) ?>
                </label>
                <div class='chakra fill' style='width:<?= $chakra_width ?>%;'></div>
                <div class='chakra preview' style='
                    left:<?= $chakra_width ?>%;
                    width:<?= $chakra_regen_width ?>%;'
                ></div>
            </div>
        </div>


        <!--stamina Bar-->
        <div class='resourceContainer'>
            <span>Stamina:</span>
            <div id='stamina' class='resourceBarOuter'>
                <label class='innerResourceBarLabel'>
                    <?= sprintf("%.2f", $player->stamina) ?> / <?= sprintf("%.2f", $player->max_stamina) ?>
                </label>
                <div class='stamina fill' style='width:<?= $stamina_width ?>%;'></div>
                <div class='stamina preview' style='
                        left:<?= $stamina_width ?>%;
                        width:<?= $stamina_regen_width ?>%;'
                ></div>
            </div>
        </div>

        <br />
        Regeneration Rate: <?= $player->regen_rate ?>

        <?php if($player->regen_boost): ?>
             (+<?= $player->regen_boost ?>)
        <?php endif; ?>
        <?php if($regen_cut): ?>
            <span style='color:#8A0000;'>(-<?= $regen_cut ?>)</span>
        <?php endif; ?>
           -> <span style='color:#00C000;'><?= ($standardRegen) ?></span>

        <br />

        <script>
            let remainingtime = <?= (59 - $time_since_last_regen) ?>;
            const statusBars = {
                health: {
                    current: <?= $player->health ?>,
                    max: <?= $player->max_health ?>,
                    next_regen: 0
                },
                chakra: {
                    current: <?= $player->chakra ?>,
                    max: <?= $player->max_chakra ?>,
                    next_regen: 0
                },
                stamina: {
                    current: <?= $player->stamina ?>,
                    max: <?= $player->max_stamina ?>,
                    next_regen: 0
                }
            };

            function round(num, places) {
                return Math.round(num * Math.pow(10, places + 2)) / Math.pow(10, places);
            }

            let regen = <?= $player->regen_rate + $player->regen_boost ?>; // no regen cut

            setInterval(() => {
                document.getElementById('regentimer').innerHTML = remainingtime; //minus 1 to compensate for lag

                if(remainingtime <= 0){
                    remainingtime = 60;

                    //Check each bar to see if regen will exceed max.
                    let healthRegen = regen * 2;
                    
                    statusBars.health.current = Math.min(statusBars.health.current + healthRegen, statusBars.health.max);
                    statusBars.chakra.current = Math.min(statusBars.chakra.current + regen, statusBars.chakra.max);
                    statusBars.stamina.current = Math.min(statusBars.stamina.current + regen, statusBars.stamina.max);

                    // Round to 1 decimal place
                    const healthWidth = round(statusBars.health.current / statusBars.health.max, 2);
                    const chakraWidth = round(statusBars.chakra.current / statusBars.chakra.max, 2);
                    const staminaWidth = round(statusBars.stamina.current / statusBars.stamina.max, 2);

                    const healthRegenAmount = Math.min(healthRegen, statusBars.health.max - statusBars.health.current);
                    const chakraRegenAmount = Math.min(regen, statusBars.chakra.max - statusBars.chakra.current);
                    const staminaRegenAmount = Math.min(regen, statusBars.stamina.max - statusBars.stamina.current);

                    const healthRegenWidth = round(healthRegenAmount / statusBars.health.max, 2);
                    const chakraRegenWidth = round(chakraRegenAmount / statusBars.chakra.max, 2);
                    const staminaRegenWidth = round(staminaRegenAmount / statusBars.stamina.max, 2);
                    
                    document.querySelector('#health label').innerText =
                        statusBars.health.current.toFixed(2) + '/' + statusBars.health.max.toFixed(2);
                    document.querySelector('#health .fill').style.width = `${healthWidth}%`;
                    document.querySelector('#health .preview').style.left = `${healthWidth}%`;
                    document.querySelector('#health .preview').style.width = `${healthRegenWidth}%`;

                    document.querySelector('#chakra label').innerText =
                        statusBars.chakra.current.toFixed(2) + '/' + statusBars.chakra.max.toFixed(2);
                    document.querySelector('#chakra .fill').style.width = `${chakraWidth}%`;
                    document.querySelector('#chakra .preview').style.left = `${chakraWidth}%`;
                    document.querySelector('#chakra .preview').style.width = `${chakraRegenWidth}%`;

                    document.querySelector('#stamina label').innerText =
                        statusBars.stamina.current.toFixed(2) + '/' + statusBars.stamina.max.toFixed(2);
                    document.querySelector('#stamina .fill').style.width = `${staminaWidth}%`;
                    document.querySelector('#stamina .preview').style.left = `${staminaWidth}%`;
                    document.querySelector('#stamina .preview').style.width = `${staminaRegenWidth}%`;
                }

                remainingtime--;

            }, 1000);
        </script>

        <label style='width:9.2em;'>Regen Timer:</label>
        <span id='regentimer'><?= (60 - $time_since_last_regen) ?></span>
    </td>
</tr>
<?php $label_width = '7.1em'; ?>
<tr>
    <td style='width:50%;'>
		<label style='width:<?= $label_width ?>;'>Level:</label> <?= $player->level ?><br />
		<label style='width:<?= $label_width ?>;'>Rank:</label> <?= $player->rank->name ?><br />
        <?php if($player->clan): ?>
			<label style='width:<?= $label_width ?>;'>Clan:</label> <?= $player->clan['name'] ?>
			<br />
            <?php if($player->clan_office): ?>
                <label style='width:<?= $label_width ?>;'>Clan Rank:</label> <?= $clan_positions[$player->clan_office] ?>
                <br />
            <?php endif; ?>
		<?php endif; ?>
    <label style='width:<?= $label_width ?>;'>Exp:</label> <?= $player->exp ?><br />
		<label style='width:<?= $label_width ?>;'>Next level in:</label> <?= $exp_remaining ?> exp<br />
		<div style='height:6px;width:200px;border-style:solid;border-width:1px;'>
    <div style='background-color:#FFD700;height:6px;width:<?= $exp_width ?>px;'></div></div>
    <br />
		
    <label style='width:<?= $label_width ?>;'>Spouse:</label>
    <?php if($player->spouse > 0): ?>
        <a href='<?= $system->links['members'] ?>&user=<?= $player->spouse_name ?>'><?= $player->spouse_name ?></a><br />
        <label style='width:<?= $label_width ?>;'>Anniversary:</label> <?= Date('F j, Y', $player->marriage_time) ?><br />
    <?php else: ?>
        None<br />
    <?php endif; ?>

    <br />
    <label style='width:<?= $label_width ?>;'>Gender:</label> <?= $player->gender ?><br />
    <label style='width:<?= $label_width ?>;'>Village:</label> <?= $player->village ?><br />
    <label style='width:<?= $label_width ?>;'>Location:</label> <?= $player->location ?><br />
    <label style='width:<?= $label_width ?>;'>Money:</label> &yen;<?= $player->getMoney() ?><br />
    <label style='width:<?= $label_width ?>;'>Ancient Kunai:</label> <?= $player->getPremiumCredits() ?><br />
    <label style='width:<?= $label_width ?>;'>Ancient Kunai purchased:</label> <?= $player->premium_credits_purchased ?><br />

    <br />
    <label style='width:<?= $label_width ?>;'>PvP wins:</label>		<?= $player->pvp_wins ?><br />
    <label style='width:<?= $label_width ?>;'>PvP losses:</label> 	<?= $player->pvp_losses ?><br />
    <label style='width:<?= $label_width ?>;'>AI wins:</label>		<?= $player->ai_wins ?><br />
    <label style='width:<?= $label_width ?>;'>AI losses:</label>	<?= $player->ai_losses ?><br />
    </td>

    <td style='width:50%;'>
    <label style='width:9.2em;'>Total stats:</label>
        <?= sprintf("%.2f", $player->total_stats) ?> / <?= sprintf("%.2f", $player->rank->stat_cap) ?><br />
    <br />
    <label style='width:9.2em;'>Bloodline:</label>
    <?= ($player->bloodline_id ? $player->bloodline_name : 'None') ?><br />
    <?php if($player->bloodline_id): ?>
        <label style='width:9.2em;'>Bloodline skill:</label><?= $player->bloodline_skill ?><br />
    <?php endif; ?>

    <?php if($player->elements): ?>
        <br /><label style='width:9.2em;'>Element<?= (count($player->elements) > 1 ? 's' : '') ?>:</label>
        <?= implode(', ', $player->elements) ?><br />
    <?php endif; ?>

    <br />
    <label style='width:9.2em;'>Ninjutsu skill:</label><?= $player->ninjutsu_skill ?><br />
    <label style='width:9.2em;'>Genjutsu skill:</label><?= $player->genjutsu_skill ?><br />
    <label style='width:9.2em;'>Taijutsu skill:</label><?= $player->taijutsu_skill ?><br />
    <br />
    <label style='width:9.2em;'>Cast speed:</label><?= sprintf("%.2f", $player->cast_speed) ?><br />
    <label style='width:9.2em;'>Speed:</label><?= sprintf("%.2f", $player->speed) ?><br />
    <label style='width:9.2em;'>Intelligence:</label><?= sprintf("%.2f", $player->intelligence) ?><br />
    <label style='width:9.2em;'>Willpower:</label><?= sprintf("%.2f", $player->willpower) ?><br />
    <br />

    <b>Missions Completed:</b><br />
    &nbsp;&nbsp;<label style='width:5em;'><?= Mission::$rank_names[Mission::RANK_D] ?>:</label>
        <?= (($player->missions_completed[Mission::RANK_D]) ??'0') ?>
        <br />
    &nbsp;&nbsp;<label style='width:5em;'><?= Mission::$rank_names[Mission::RANK_C] ?>:</label>
        <?= (($player->missions_completed[Mission::RANK_C]) ?? '0') ?>
        <br />
    &nbsp;&nbsp;<label style='width:5em;'><?= Mission::$rank_names[Mission::RANK_B] ?>:</label>
        <?= (($player->missions_completed[Mission::RANK_B]) ?? '0') ?>
        <br />
    &nbsp;&nbsp;<label style='width:5em;'><?= Mission::$rank_names[Mission::RANK_A] ?>:</label>
        <?= (($player->missions_completed[Mission::RANK_A]) ?? '0') ?>
        <br />
    &nbsp;&nbsp;<label style='width:5em;'><?= Mission::$rank_names[Mission::RANK_S] ?>:</label>
        <?= (($player->missions_completed[Mission::RANK_S]) ?? '0') ?>
        <br />
    </td>
</tr>
</table>

<?php
    $dt_time_remaining = System::timeRemaining(
        time_remaining: $player->daily_tasks_reset + (60 * 60 * 24) - time(),
        format: 'short',
        include_days: false,
        include_seconds: true
    );
?>

<div class='contentDiv'>
    <h2 class='contentDivHeader'>Daily Tasks</h2>

    <div id='dailyTaskWrapper'>
        <?php foreach($player->daily_tasks as $daily_task): ?>
            <?php
                $dt_progress = 0;
                if($daily_task->progress != 0) {
                    $dt_progress = $daily_task->progress / $daily_task->amount * 100;
                }
                $dt_status_class_name = ($daily_task->complete ? 'Complete' : 'NotComplete');
            ?>

            <div class='dailyTask'>
                <div class='dailyTaskTitle'>
                    <?= $daily_task->name ?>
                </div>
                <div class='dailyTaskGoal'>
                    <span>Task:</span>
                    <span><?= $daily_task->getPrompt() ?></span>
                </div>
                <div class='dailyTaskDifficulty'>
                    <span>Difficulty:</span>
                    <span class='dailyTask<?= $daily_task->difficulty ?>'><?= $daily_task->difficulty ?></span>
                </div>
                <div class='dailyTaskReward'>
                    <span>Reward:</span>
                    <span>¥<?= $daily_task->reward ?></span>
                </div>
                <div class='dailyTaskProgress'>
                    <div class='dailyTaskProgressBar dailyTask<?= $dt_status_class_name ?>'>
                        <div style='width: <?= $dt_progress ?>%;'></div>
                    </div>
                </div>
                <div class='dailyTaskProgressCaption'>
                    <span><?= $daily_task->progress ?></span> / <span><?= $daily_task->amount ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
			
    <div class='contentDivCaption'>
        <span>Time Remaining:</span>
        <span id='dailyTaskTimer'><?= $dt_time_remaining ?> left
        </span>
    </div>

    <script type='text/javascript'>
        let stringValue = <?= ($player->daily_tasks_reset + (60 * 60 * 24) - time()) ?>;
        let targetSpan = document.getElementById('dailyTaskTimer');
        setInterval(() => {
            stringValue--;
            let stringTime = timeRemaining(stringValue, 'short', false, true);
            targetSpan.innerHTML = stringTime + ' left';
        }, 1000);
    </script>
</div>