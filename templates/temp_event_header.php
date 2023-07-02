<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 */
?>

<script type='text/javascript'>countdownTimer(<?= $system->event->end_time->getTimestamp() - time() ?>, 'eventEnd');</script>

<table class="table">
    <tr><th><?= $system->event->name ?></th></tr>
    <tr>
        <td style="text-align: center;">
            <?= $system->event->name ?> is active! <a href="<?=$system->router->getUrl('event')?>">Event Detail</a>
            <div id="eventEnd">
                <?= $system->time_remaining($system->event->end_time->getTimestamp() - time()) ?>
            </div>
        </td>
    </tr>
</table>