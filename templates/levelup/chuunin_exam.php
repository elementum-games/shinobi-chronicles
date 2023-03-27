<table class="table">
    <tr><th><?=$exam_name?></th></tr>
    <?php if($stage != $CHUUNIN_STAGE_PASS): ?>
        <tr>
            <td style="text-align: center;">
                Welcome to the Chuunin Exam. In order to pass, you must complete all three stages: The written
                exam, survival exam, and combat exam. See below for instructions on the current stage.
            </td>
        </tr>
    <?php endif ?>
    <?php if($player->staff_manager->isHeadAdmin() && $stage == $CHUUNIN_STAGE_WRITTEN): ?>
        <tr>
            <td style="text-align: center;">
                As a <?=$player->staff_manager->getStaffLevelName(null, 'long')?>, you are allowed to
                skip this exam.
                <form action="<?=$self_link?>" method="post">
                    <input type="submit" name="skip_exam" value="Skip" />
                </form>
            </td>
        </tr>
    <?php endif ?>
    <?php if($stage < $CHUUNIN_STAGE_PASS): ?>
        <?php if($stage == $CHUUNIN_STAGE_WRITTEN): ?>
            <tr>
                <td>
                    <p style='text-align:center;'>Answer the following questions correctly to proceed:</p>
                    <br />
                    <form style='padding-left:15px;' action='<?=$self_link?>' method='post'>

                        <b>What type of jutsu involves controlling the chakra in the opponent's mind to cause illusions, reducing their
                            combat effectiveness, causing sensations of pain, or even completely immobilizing them?</b><br />
                        <input type='radio' name='question1' value='ninjutsu' /> Ninjutsu<br />
                        <input type='radio' name='question1' value='taijutsu' /> Taijutsu<br />
                        <input type='radio' name='question1' value='genjutsu' /> Genjutsu<br />
                        <input type='radio' name='question1' value='all' /> All of the above<br />
                        <br />

                        <b>Which type of jutsu can Armor (harden) protect you from?</b><br />
                        <input type='radio' name='question2' value='ninjutsu' /> Ninjutsu<br />
                        <input type='radio' name='question2' value='taijutsu' /> Taijutsu<br />
                        <input type='radio' name='question2' value='genjutsu' /> Genjutsu<br />
                        <input type='radio' name='question2' value='all' /> All of the above<br />
                        <br />

                        <b>What Ninja Village has the most villagers?</b><br />
                        <input type='radio' name='question3' value='Leaf' /> Leaf<br />
                        <input type='radio' name='question3' value='Cloud' /> Cloud<br />
                        <input type='radio' name='question3' value='Stone' /> Stone<br />
                        <input type='radio' name='question3' value='Mist' /> Mist<br />
                        <input type='radio' name='question3' value='Sand' /> Sand<br />

                        <p style="text-align: center;"><input type='submit' value='Submit Answers' /></p>
                    </form>
                </td>
            </tr>
        <?php elseif($stage < $CHUUNIN_STAGE_DUEL): ?>
            <tr><th>Stage 2 - Survival Exam</th></tr>
            <tr>
                <td style="text-align: center;">
                    You must fight your way through the forest of death, defeating other ninjas to acquire the exam scroll. Being defeated
                    will result in failing the exam.
                </td>
            </tr>
        <?php else: ?>
            <tr><th>Stage 3 - Combat Exam</th></tr>
            <tr>
                <td style="text-align: center;">
                    For your final exam, you must fight against one of the strongest exam participants in a duel.
                </td>
            </tr>
        <?php endif ?>
    <?php endif ?>
</table>