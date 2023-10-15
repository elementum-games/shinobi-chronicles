<?php
/**
 * @var string $exam_name
 * @var string $bloodline_display
 */
?>

<table class="table">
    <tr><th><?=$exam_name?> Graduation</th></tr>
    <tr>
        <td style="text-align: center">
            Congratulations, you have passed the <?=$exam_name?>!<br />
            <br />
            Having demonstrated your skill with Ninjutsu, the village elders examine you to determine if you have a special power: a
            Kekkei Genkai (Bloodline Limit ability). Closing <?=$gender?> eyes an elder places <?=$gender?> hand on your stomach - <?= $gender ?> chakra
            resonating with the core of your being.<br />
            <br />
            <?=$bloodline_display?>
        </td>
    </tr>
</table>
