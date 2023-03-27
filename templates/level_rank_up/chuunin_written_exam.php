

<table class="table">
    <?php if($player->exam_stage == $CHUUNIN_STAGE_WRITTEN): ?>
        <tr><th><?=$exam_name?> - Stage 1 - Written</th></tr>
        <tr>
            <td>
                <p style='text-align:center;'>Answer the following questions correctly to proceed:</p>
                <br />
                <form action='<?=$self_link?>' method='post'>

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

                    <p style="text-align: center;"><input type='submit' name='written_exam' value='Submit Answers' /></p>
                </form>
            </td>
        </tr>
    <?php endif ?>
</table>
