<table class="table">
    <tr><th><?=$exam_name?><?=($stage == 4 ? " Graduation" : "")?></th></tr>
    <?php if($stage < 4): ?>
    <tr>
        <td style="text-align: center;">
            Welcome to the <?=$exam_name?>. In order to pass, you must demonstrate the following three jutsu:<br />
            <?=$jutsu_data[1]['name']?>, <?=$jutsu_data[2]['name']?> and <?=$jutsu_data[3]['name']?>.<br />
            See below for instructions on your current task.
        </td>
    </tr>
    <?php endif ?>
    <?php if($player->staff_manager->isHeadAdmin() && $stage < 4): ?>
        <tr>
            <td style="text-align: center;">
                As a <?=$player->staff_manager->getStaffLevelName(null, 'long')?>, you may skip this exam.<br />
                <form action="<?=$self_link?>" method="post">
                    <input type="hidden" name="skip_exam" value="1" />
                    <input type="submit" value="Skip Exam" />
                </form>
                <br />
                Additionally, you are provided the answer:<br />
                <?php if($stage == 1): ?>
                    Seals: 1-2
                <?php elseif($stage == 2): ?>
                    Seals: 3-2-12
                <?php elseif($stage == 3): ?>
                    Seals: 2-7
                <?php endif ?>
            </td>
        </tr>
    <?php endif ?>
    <tr>
        <td style="text-align: center;">
            <?php if($stage < 4): ?>
                <?=$prompt?>
                <?php include 'templates/levelup/handSeals.php'; ?>
                <form action="<?=$self_link?>" method="post">
                    <input type="hidden" id="hand_seal_input" name="hand_seals" value="<?=$submitted_hand_seals?>" />
                    <input type="submit" name="attack" value="Submit" />
                </form>
            <?php else: ?>
                <?=$rank_display?>
            <?php endif ?>
        </td>
    </tr>
</table>