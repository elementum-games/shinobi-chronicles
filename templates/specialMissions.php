<?php
/**
 * @var System $system
 * @var User   $player
 * @var string $self_link
 */
?>
<link rel='stylesheet' type='text/css' href='style/special_missions.css' />
<?php if(!$player->special_mission): ?>
    <div class="contentDiv">
        <h2 class="contentDivHeader">
            Special Missions
        </h2>

        <p>
            As nations struggle for control of the world around them, it falls upon ninja to undertake the missions that
            see these goals realised. Most missions serve as a means to generate income from loyal clients and fund the
            shinobi way of life. Occasionally, however, a task of such great importance arises to a village that it
            warrants special designation. These missions challenge even the strongest shinobi, but the potential rewards
            are endless. Enemies abound, but those dedicated few who take these missions secure valuable information for
            their village. This information in turn supports the greater war, helping the nation grow stronger than
            those around it.
        </p>
        <p>
            Special missions take about 1-5 minutes to complete. Your character will automatically perform
            the steps without you having to manually move, scout, etc. You can be attacked by other players while your
            character is moving around the map.

            Special missions reward money and jutsu exp/levels at random for your equipped and Bloodline jutsu.
        </p>
        <?php if($player->mission_rep_cd - time() > 0): ?>
            <?php $remaining = $player->mission_rep_cd - time(); ?>
            <p>
                You can gain village reputation in: <div id='rep_cd' style='display: inline-block'><?=System::timeRemaining($remaining)?></div>
                <script type='text/javascript'>countdownTimer(<?=$remaining?>, 'rep_cd', false);</script>";
            </p>
        <?php endif ?>
        <a href="<?= $self_link ?>&start=easy">
            <button>Start Easy Mission!</button>
        </a>
        <a href="<?= $self_link ?>&start=normal">
            <button>Start Normal Mission!</button>
        </a>
        <a href="<?= $self_link ?>&start=hard">
            <button>Start Hard Mission!</button>
        </a>
        <a href="<?= $self_link ?>&start=nightmare">
            <button>Start Nightmare Mission!</button>
        </a>
    </div>
<?php endif; ?>

<?php if($player->special_mission): ?>
    <div id="spec_miss_wrapper">
        <div id="spec_miss_cancel_wrapper">
            <span class="spec_miss_page_warning">Stay on this page to keep your mission progressing!</span>
            <a id="spec_miss_cancel" href="<?= $self_link ?>&cancelmission=true">Cancel Mission</a>
        </div>
        <div id="spec_miss_header">
            <div id="spec_miss_timer_wrapper">
                <div id="spec_miss_timer_title">
                    Duration
                </div>
                <div id="spec_miss_timer">
                    0:00<!-- Timer -->
                </div>
            </div>
            <div id="spec_miss_character_wrapper">
                <div id="spec_miss_status_wrapper">
                    <div id="spec_miss_status_title">
                        Status
                    </div>
                    <div id="spec_miss_status_text">
                        In Progress
                    </div>
                </div>
                <div id="spec_miss_health_wrapper">
                    <div id="spec_miss_health_icon"></div>
                    <div id="spec_miss_health_bar_wrapper">
                        <div id="spec_miss_health_bar_out">
                            <div id="spec_miss_health_bar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="spec_miss_progress_wrapper">
                <div id="spec_miss_progress_title">
                    Progress
                </div>
                <div id="spec_miss_progress_div">
                    <span id="spec_miss_progress">0<!-- Progress --></span>/100
                </div>
                <div id="spec_miss_reward_wrapper">
                    ¥<span id="spec_miss_reward">0</span>
                </div>
            </div>
        </div>
        <div id="spec_miss_log_wrapper">
            <!-- Log Entries -->
        </div>
    </div>


    <!-- TEMPLATE START -->
    <template id="log_entry_template">
        <div class="spec_miss_log_entry">
            <div class="spec_miss_log_entry_icon_wrapper">
                <div class="spec_miss_log_entry_icon">
                    <!-- Icon -->
                </div>
            </div>
            <div class="spec_miss_log_entry_text">
                <!-- Event Text -->
            </div>
            <div class="spec_miss_log_entry_timestamp_wrapper">
                <span id="spec_miss_log_entry_timestamp">
                    <!-- TimeStamp -->
                </span>
            </div>
        </div>
    </template>
    <!-- TEMPLATE END -->
    <script type="text/javascript">
        const missionEventDurationMs = <?= SpecialMission::EVENT_DURATION_MS ?>;
    </script>
    <script type="text/javascript" src="<?= $system->router->base_url ?>/scripts/specialmissions.js"></script>
<?php endif; ?>
