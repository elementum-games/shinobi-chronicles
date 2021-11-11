

<?php if (!$player->special_mission): ?>

<div class="contentDiv">
    <h2 class="contentDivHeader">
        Special Missions
    </h2>

    <p>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam nec est ultricies, consectetur lectus sit amet, 
    elementum nisl. Ut euismod est imperdiet nisi finibus egestas. Fusce et turpis vitae ipsum facilisis faucibus 
    eget in dolor. Morbi vehicula pretium nisl ut elementum. Proin elementum erat et sodales tincidunt. Etiam nec 
    tortor pretium, pharetra nibh sit amet, semper dui. Aenean in quam in quam sagittis aliquam.
    </p>
    <p>
    Vivamus ut viverra nisl. Quisque at hendrerit orci. Suspendisse volutpat augue eget velit dapibus, nec 
    consectetur nisi suscipit. Vivamus a odio quis purus blandit luctus eget ac felis. Morbi rhoncus dapibus arcu 
    in efficitur. Nunc luctus rhoncus nisl et vulputate. Donec ornare a libero eget venenatis. Quisque efficitur 
    auctor tempus. Suspendisse vel consequat diam, sit amet fermentum tellus. Etiam eu nisi feugiat, sollicitudin
    mi sed, mattis tellus. Aliquam interdum turpis vitae felis posuere pretium. Curabitur lobortis elit vel 
    accumsan vulputate. 
    </p>
    <a href="<?= $self_link ?>&start=easy"><button>Start EASY Mission!</button></a>
    <a href="<?= $self_link ?>&start=normal"><button>Start Normal Mission!</button></a>
    <a href="<?= $self_link ?>&start=hard"><button>Start Hard Mission!</button></a>
    <a href="<?= $self_link ?>&start=nightmare"><button>Start Nightmare Mission!</button></a>
</div>

<?php endif; ?>

<?php if ($player->special_mission): ?>

<div id="spec_miss_wrapper">
    <div id="spec_miss_cancel_wrapper">
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

<script type="text/javascript" src="<?= $system->link ?>/scripts/specialmissions.js"></script>

<?php endif; ?>