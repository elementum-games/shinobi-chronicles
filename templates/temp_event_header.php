<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 */
?>

<script type='text/javascript'>countdownTimer(<?= System::SC_EVENT_END - time() ?>, 'eventEnd');</script>

<table class="table">
    <tr><th><?=System::SC_EVENT_NAME?></th></tr>
    <tr>
        <td style="text-align: center;">
            <?= System::SC_EVENT_NAME ?> is active! <a href="<?=$system->router->getUrl('event')?>">Event Detail</a>
            <div id="eventEnd">
                <?= $system->time_remaining(System::SC_EVENT_END - time()) ?>
            </div>
        </td>
    </tr>
</table>