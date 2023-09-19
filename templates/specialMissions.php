<?php
/**
 * @var System $system
 * @var User   $player
 * @var string $self_link
 */
?>
<link rel='stylesheet' type='text/css' href='<?= $system->getCssFileLink("style/special_missions.css") ?>' />
<?php if(!$player->special_mission): ?>
    <div class="contentDiv">
        <h2 class="contentDivHeader">
            Special Missions
        </h2>

        <p style="width: inherit; text-align: center">
            As nations vie for power it falls upon shinobi to undertake missions that
            see these goals realized. Occasionally tasks of great importance warrant special designation. 
            These missions challenge even the strongest shinobi but come with great rewards. 
        </p>
        <ul style="list-style-type: none">
            <li>Special missions take 2-5 minutes to complete.</li>
            <li>Your character will automatically scout enemy territory while completing battles.</li>
            <li>You can be attacked by other players while your character is moving around the map.</li>
            <li>Special missions reward money, experience and village reputation increasing with mission difficulty.</li>
            <li>Completing special missions gradually drains chakra or stamina in exchange for jutsu experience.</li>
        </ul><br />
        <a href="<?= $self_link ?>&start=easy"><button>Start Easy Mission!</button></a>
        <a href="<?= $self_link ?>&start=normal"><button>Start Normal Mission!</button></a>
        <a href="<?= $self_link ?>&start=hard"><button>Start Hard Mission!</button></a>
        <a href="<?= $self_link ?>&start=nightmare"><button>Start Nightmare Mission!</button></a><br /><br />
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
