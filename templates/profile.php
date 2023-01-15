<?php
/**
 * @var User $player
 * @var System $system
 *
 * @var int $exp_needed
 */

$exp_percent = ($player->exp_per_level - ($exp_needed - $player->exp)) / $player->exp_per_level * 100;
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

$health_after_regen = min($player->health + $healthRegen, $player->max_health);
$chakra_after_regen = min($player->chakra + $standardRegen, $player->max_chakra);
$stamina_after_regen = min($player->stamina + $standardRegen, $player->max_stamina);

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

<table class='profile_table table'>
<tr>
    <td style='width:50%;text-align:center;'>
        <span style='font-size:1.3em;font-family:\"tempus sans itc\",serif;font-weight:bold;'><?= $player->user_name ?></span><br />
        <?= $system->imageCheck($player->avatar_link, $player->getAvatarSize()) ?>
        <br />
    </td>
    <td style='width:50%;'>
		<label style='width:6.7em;' for='healthbar'>Health:</label>
        <span id='health'>
            <?= sprintf("%.2f", $player->health) ?> / <?= sprintf("%.2f", $player->max_health) ?>
            <?php if($player->health != $player->max_health): ?>
                -> <b style='color: green;'><?= sprintf("%.2f",$health_after_regen) ?></b>
            <?php endif; ?>
        </span><br />

        <div style='height:6px;width:250px;border-style:solid;border-width:1px;border-radius: 4px;'>
            <progress
                id='healthbar'
                style='accent-color:#C00000;height:6px;width: 100%;'
                value='<?= $player->health ?>'
                max='<?= $player->max_health ?>'></progress>
        </div>
        <label style='width:6.7em;' for='chakrabar'>Chakra:</label>
        <span id='chakra'>
            <?= sprintf("%.2f", $player->chakra) ?> / <?= sprintf("%.2f", $player->max_chakra) ?>
            <?php if($player->chakra != $player->max_chakra): ?>
                -> <b style='color: green;'><?= sprintf("%.2f",$chakra_after_regen) ?></b>
            <?php endif; ?>
        </span><br />

        <div style='height:6px;width:250px;border-style:solid;border-width:1px;border-radius: 4px;'>
            <progress
                id='chakrabar'
                style='accent-color:#0000B0;height:6px;width:100%;'
                value='<?= $player->chakra ?>'
                max='<?= $player->max_chakra ?>'></progress>
        </div>
        <label style='width:6.7em;' for='staminabar'>Stamina:</label>
        <span id='stamina'>
            <?= sprintf("%.2f", $player->stamina) ?> / <?= sprintf("%.2f", $player->max_stamina) ?>
            <?php if($player->stamina != $player->max_stamina): ?>
                -> <b style='color: green;'><?= sprintf("%.2f",$stamina_after_regen) ?></b>
            <?php endif; ?>
        </span><br />

        <div style='height:6px;width:250px;border-style:solid;border-width:1px;border-radius: 4px;'>
        <progress
            id='staminabar'
            style='accent-color:#00B000;height:6px;width:100%;'
            value='<?= $player->stamina ?>'
            max='<?= $player->max_stamina ?>'></progress>
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
            //regen timer script - can be moved to its own script.js file
            var remainingtime = <?= (59 - $time_since_last_regen) ?>;
            var statusBars = {
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

            var regen = <?= $player->regen_rate + $player->regen_boost ?>; // no regen cut

            setInterval(() => {
                document.getElementById('regentimer').innerHTML = remainingtime; //minus 1 to compensate for lag

                if(remainingtime <= 0){
                    remainingtime = 60;

                    //Check each bar to see if regen will exceed max.
                    let healthRegen = regen * 2;

                    statusBars.health.current = Math.min(statusBars.health.current + healthRegen, statusBars.health.max);
                    statusBars.health.next_regen = Math.min(statusBars.health.current + healthRegen, statusBars.health.max);

                    //Check Chakra Bar
                    statusBars.chakra.current = Math.min(statusBars.chakra.current + regen, statusBars.chakra.max);
                    statusBars.chakra.next_regen = Math.min(statusBars.chakra.current + regen, statusBars.chakra.max);

                    //Check Stamina Bar
                    statusBars.stamina.current = Math.min(statusBars.stamina.current + regen, statusBars.stamina.max);
                    statusBars.stamina.next_regen = Math.min(statusBars.stamina.current + regen, statusBars.stamina.max);


                    //Update Health Bar
                    const currentAndMaxHealth = statusBars.health.current.toFixed(2) + '/' + statusBars.health.max.toFixed(2);
                    $('#health').html(currentAndMaxHealth.concat((statusBars.health.current !== statusBars.health.max) ? ('-> <b style=\'color: green\'>' + statusBars.health.next_regen.toFixed(2) + '</b>') : ''));
                    $('#healthbar').val(statusBars.health.current);

                    //Update Chakra Bar
                     const currentAndMaxChakra = statusBars.chakra.current.toFixed(2) + '/' + statusBars.chakra.max.toFixed(2);
                    $('#chakra').html(currentAndMaxChakra.concat((statusBars.chakra.current !== statusBars.chakra.max)? ('-> <b style=\'color: green\'>' + statusBars.chakra.next_regen.toFixed(2) + '</b>') : ''));
                    $('#chakrabar').val(statusBars.chakra.current);

                    //Update Stamina Bar
                    const currentAndMaxStamina = statusBars.stamina.current.toFixed(2) + '/' + statusBars.stamina.max.toFixed(2);
                    $('#stamina').html(currentAndMaxStamina.concat((statusBars.stamina.current !== statusBars.stamina.max)? ('-> <b style=\'color: green\'>' + statusBars.stamina.next_regen.toFixed(2) + '</b>') : ''));
                    $('#staminabar').val(statusBars.stamina.current);
                }

                remainingtime--;

            }, 1000);

            //for some reason every other tick the javascript regen is ahead of the actual regen?
            //can't figure out why? its like the Regen changes every other minute in intervals or it doubles
            //can't find the error with my script
            //can't seem to find out where the error is, need help.

            function updateBarValue(bar)
            {

            }
        </script>

        <label style='width:9.2em;'>Regen Timer:</label>
        <span id='regentimer'><?= (60 - $time_since_last_regen) ?></span>
    </td>
</tr>
<?php $label_width = '7.1em'; ?>
<tr>
    <td style='width:50%;'>
		<label style='width:<?= $label_width ?>;'>Level:</label> <?= $player->level ?><br />
		<label style='width:<?= $label_width ?>;'>Rank:</label> <?= $player->rank_name ?><br />
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
        <?= sprintf("%.2f", $player->total_stats) ?> / <?= sprintf("%.2f", $player->stat_cap) ?><br />
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