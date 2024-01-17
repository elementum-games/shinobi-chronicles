<?php
/**
 * @var string $exam_name
 * @var string $self_link
 */
?>
<table class="table">
    <tr><th><?=$exam_name?></th></tr>
    <tr>
        <td style="text-align: center;">
            <form action="<?=$self_link?>" method="post">
                <?php if($player->rank_num == 1): ?>
                    The academy instructor calls you to the front of the class. They explain that you have made vast improvements
                    since starting your instruction.<br />
                    You are handed a thing, blood-red envelope and instructed not to open it until you have reached the exam area.<br />
                <?php elseif($player->rank_num == 2): ?>
                    Having trained tirelessly for what seems like years, you approach the gates of the <?=$exam_name?>.<br />
                    The <?=$player->village->kage_name?> announces that the time for the written exam has begun.<br />
                <?php elseif($player->rank_num == 3): ?>
                    The <?=$player->village->kage_name?> summons you to their office. All of the <?=$player->village->name?>
                    Village Jounin are currently on assigned duties, and there is a priority mission that needs attention.
                    Completing this mission will earn you the rank of Jounin.<br />
                <?php endif ?>
                <br />
                <input type="submit" name="begin_exam" value="<?=($player->rank_num < 3) ? "Start Exam" : "Accept Mission"?>" />
            </form>
        </td>
    </tr>
</table>