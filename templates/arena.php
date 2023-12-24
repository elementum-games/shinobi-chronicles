<?php
/**
 * @var System $system
 * @var User $player
 * @var array $difficulty_levels
 */
?>

<style>
/* arena Button */
.arena_button {
    cursor: pointer;
}
    .arena_button:hover text.arena_button_text {
        fill: #ffffff;
        text-shadow: 2px 2px 5px rgba(212, 113, 148, 1), -2px -2px 5px rgba(206, 110, 144, 1);
        transition: text-shadow 0.1s;
    }
    .arena_button:active .arena_button_background {
        fill: url(#arena_fill_click);
    }
.arena_button_background {
    clip-path: polygon(20px 0px, calc(100% - 20px) 0px, 100% 50%, calc(100% - 20px) 100%, 20px 100%, 0px 50%);
    fill: url(#arena_fill_default);
}
.arena_button_text {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    font-variant: small-caps;
    fill: #fef2da;
    user-select: none;
}
.arena_button_shadow_text {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    font-variant: small-caps;
    fill: #1e1d25;
    user-select: none;
}
</style>

<?php $system->printMessage(); ?>

<table id='arena_select' class='table' style='text-align:center;'>
    <tr><th>Choose Opponent</th></tr>
    <tr>
        <td style='text-align: center;'>
            <span>Welcome to the Arena. </span>
            <br />
            <span>Here you can fight against various opponents for cash prizes.</span>
            <br /><span>Fighting more difficult opponents will yield greater rewards. Select your opponent below:</span>
            <svg style="height: 0px; width: 0px">
                <radialGradient id="arena_fill_default" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                    <stop offset="0%" style="stop-color: #84314e; stop-opacity: 1"></stop>
                    <stop offset="100%" style="stop-color: #68293f; stop-opacity: 1"></stop>
                </radialGradient>
                <radialGradient id="arena_fill_click" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                    <stop offset="0%" style="stop-color: #68293f; stop-opacity: 1"></stop>
                    <stop offset="100%" style="stop-color: #84314e; stop-opacity: 1"></stop>
                </radialGradient>
            </svg>
            <div style="display: flex; gap: 45px; margin: 15px; justify-content: center">
                <div>
                    <a href="<?= $system->router->getUrl('arena') ?>&difficulty=easy" style="font-weight: normal">
                        <svg role="button" tabindex="0" name="arena_button" class="arena_button" width="160" height="24">
                            <rect class="arena_button_background" width="100%" height="100%" fill="url(#arena_fill_default)"></rect>
                            <text class="arena_button_shadow_text" x="50%" y="14" text-anchor="middle" dominant-baseline="middle">Easy</text>
                            <text class="arena_button_text" x="50%" y="12" text-anchor="middle" dominant-baseline="middle">Easy</text>
                        </svg>
                    </a>
                    <?php if (isset($player->ai_cooldowns[NPC::DIFFICULTY_EASY]) && time() < $player->ai_cooldowns[NPC::DIFFICULTY_EASY]): ?>
                        <br /><span id="easy_cooldown"><?= System::timeRemaining($player->ai_cooldowns[NPC::DIFFICULTY_EASY] - time()) ?></span>
                        <script type='text/javascript'>
                            countdownTimer(<?php echo ($player->ai_cooldowns[NPC::DIFFICULTY_EASY] - time()) ?>, 'easy_cooldown', false);
                        </script>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?= $system->router->getUrl('arena') ?>&difficulty=normal" style="font-weight: normal">
                        <svg role="button" tabindex="0" name="arena_button" class="arena_button" width="160" height="24">
                            <rect class="arena_button_background" width="100%" height="100%" fill="url(#arena_fill_default)"></rect>
                            <text class="arena_button_shadow_text" x="50%" y="14" text-anchor="middle" dominant-baseline="middle">Normal</text>
                            <text class="arena_button_text" x="50%" y="12" text-anchor="middle" dominant-baseline="middle">Normal</text>
                        </svg>
                    </a>
                    <?php if (isset($player->ai_cooldowns[NPC::DIFFICULTY_NORMAL]) && time() < $player->ai_cooldowns[NPC::DIFFICULTY_NORMAL]): ?>
                        <br /><span id="normal_cooldown"><?= System::timeRemaining($player->ai_cooldowns[NPC::DIFFICULTY_NORMAL] - time()) ?></span>
                        <script type='text/javascript'>
                            countdownTimer(<?php echo ($player->ai_cooldowns[NPC::DIFFICULTY_NORMAL] - time()) ?>, 'normal_cooldown', false);
                        </script>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="<?= $system->router->getUrl('arena') ?>&difficulty=hard" style="font-weight: normal">
                        <svg role="button" tabindex="0" name="arena_button" class="arena_button" width="160" height="24">
                            <rect class="arena_button_background" width="100%" height="100%" fill="url(#arena_fill_default)"></rect>
                            <text class="arena_button_shadow_text" x="50%" y="14" text-anchor="middle" dominant-baseline="middle">Hard</text>
                            <text class="arena_button_text" x="50%" y="12" text-anchor="middle" dominant-baseline="middle">Hard</text>
                        </svg>
                    </a>
                    <?php if (isset($player->ai_cooldowns[NPC::DIFFICULTY_HARD]) && time() < $player->ai_cooldowns[NPC::DIFFICULTY_HARD]): ?>
                        <br /><span id="hard_cooldown"><?= System::timeRemaining($player->ai_cooldowns[NPC::DIFFICULTY_HARD] - time()) ?></span>
                        <script type='text/javascript'>
                            countdownTimer(<?php echo ($player->ai_cooldowns[NPC::DIFFICULTY_HARD] - time()) ?>, 'hard_cooldown', false);
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </td>
    </tr>
</table>
