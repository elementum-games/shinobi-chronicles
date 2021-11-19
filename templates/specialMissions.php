<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 */
?>

<?php if (!$player->special_mission): ?>
    <div class="contentDiv">
        <h2 class="contentDivHeader">
            Special Missions
        </h2>

        <p>
            As each nation vies for control of the world around them, strategic use of the ninja in their control is key to
            any gains made or losses prevented. Most missions undertaken serve as a means to generate income from loyal
            clients, who fund the shinobi way of life in exchange for valuable services few others can provide. But
            occasionally, a task of such great importance arises to a village that it warrants special designation. These
            missions serve the nation as a whole by providing valuable intelligence, resources and staging points for future
            military efforts.
        </p>
        <p>
            Undertaking such integral efforts requires a ninja of unparalleled dedication to the village, for enemies abound
            and not just any warrior would risk their lives for the betterment of all. But if you are here, then your
            efforts have been noticed. Are you willing to put everything on the line, leave your home behind, and undertake
            a series of carefully coordinated efforts that will bring power and glory to your homeland?
        </p>
        <p>
            Special missions take about 1-5 minutes to complete. Your character will automatically perform
            the steps without you having to manually move, scout, etc. You can be attacked by other players while your
            character is moving around the map.

            Special missions reward money and jutsu exp/levels at random for your equipped and Bloodline jutsu.
        </p>
        <a href="<?= $self_link ?>&start=easy"><button>Start Easy Mission!</button></a>
        <a href="<?= $self_link ?>&start=normal"><button>Start Normal Mission!</button></a>
        <a href="<?= $self_link ?>&start=hard"><button>Start Hard Mission!</button></a>
        <a href="<?= $self_link ?>&start=nightmare"><button>Start Nightmare Mission!</button></a>
    </div>
<?php endif; ?>

<?php if ($player->special_mission): ?>
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
    <script type="text/javascript" src="<?= $system->link ?>/scripts/specialmissions.js"></script>
<?php endif; ?>