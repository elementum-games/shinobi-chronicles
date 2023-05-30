<?php
/**
 * @var User $player
 * @var System $system
 */
?>

<style>
    .exam_header {
        text-align: center;
    }
    .specialization_select {
        width: 100px;
    }
    .submit_container {
        text-align: center;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .submit_wrapper {
        margin-top: 20px;
    }
    .question_label {
        margin-bottom: 5px;
        font-weight: bold;
    }
</style>

<table class="table">
    <tr>
        <th>
            Sensei Exam - Written
        </th>
    </tr>
    <tr>
        <td>
            <p class="exam_header">Answer the following questions correctly to proceed:</p>
            <form action="<?= $system->router->links['villageHQ'] ?>&view=sensei" method="post">
                <p class="question_label">Which of the following helps with completing Special Missions?</p>
                <div><input type="radio" name="question1" value="1a" checked/> <label>Stat Total</label></div>
                <div><input type="radio" name="question1" value="1b" /> <label>Max Health</label></div>
                <div><input type="radio" name="question1" value="1c" /> <label>Jutsu Levels</label></div>
                <div><input type="radio" name="question1" value="1d" /> <label>All of the above</label></div>
                <div><input type="radio" name="question1" value="1e" /> <label>None of the above (pure chance)</label></div>
                <p class="question_label">Which of these Forbidden Seal benefits is exclusive to Four Dragon Seal?</p>
                <div><input type="radio" name="question2" value="2a" checked/> <label>Increased Regen</label></div>
                <div><input type="radio" name="question2" value="2b" /> <label>Larger Avatar</label></div>
                <div><input type="radio" name="question2" value="2c" /> <label>Enhanced Training</label></div>
                <div><input type="radio" name="question2" value="2d" /> <label>Battle History</label></div>
                <div><input type="radio" name="question2" value="2e" /> <label>None of the above</label></div>
                <p class="question_label">Which of the following is considered a "Balance" build?</p>
                <div><input type="radio" name="question3" value="3a" checked/> <label>Equal Bloodline/Offense Stats</label></div>
                <div><input type="radio" name="question3" value="3b" /> <label>Equal Offense/Speed Stats</label></div>
                <div><input type="radio" name="question3" value="3c" /> <label>Equal Bloodline/Offense/Speed Stats</label></div>
                <div><input type="radio" name="question3" value="3d" /> <label>None of the above</label></div>
                <p class="question_label">How much speed is required to reach maximum evasion (35%)?</p>
                <div><input type="radio" name="question4" value="4a" checked/> <label>+50%</label></div>
                <div><input type="radio" name="question4" value="4b" /> <label>+66.6%</label></div>
                <div><input type="radio" name="question4" value="4c" /> <label>+87.5%</label></div>
                <div><input type="radio" name="question4" value="4d" /> <label>+100%</label></div>
                <p class="question_label">Which of the following jutsu has the greatest total power?</p>
                <div><input type="radio" name="question5" value="5a" checked/> <label>3.5 Base Power, lv100 - Shop Jutsu</label></div>
                <div><input type="radio" name="question5" value="5b" /> <label>3.5 Base Power, lv100 - Bloodline Jutsu</label></div>
                <div><input type="radio" name="question5" value="5c" /> <label>4.5 Base Power, lv1 - Shop Jutsu</label></div>
                <div><input type="radio" name="question5" value="5d" /> <label>All of the above (equal)</label></div>
                <p class="question_label">Which of the following is true about debuffs?</p>
                <div><input type="radio" name="question6" value="6a" checked/> <label>Offense debuffs are based on the target's stats</label></div>
                <div><input type="radio" name="question6" value="6b" /> <label>Offense debuffs are based on the Jutsu's damage</label></div>
                <div><input type="radio" name="question6" value="6c" /> <label>Speed debuffs are based on the Jutsu's damage</label></div>
                <div><input type="radio" name="question6" value="6d" /> <label>Speed debuffs are based on the user's stats</label></div>
                <div><input type="radio" name="question6" value="6e" /> <label>None of the above</label></div>
                <div class="submit_container">
                    <b>
                        Choose your Specialization
                    </b>
                    <select class="specialization_select" name='specialization'>
                        <option value="taijutsu">Taijutsu</option>
                        <option value="ninjutsu">Ninjutsu</option>
                        <option value="genjutsu">Genjutsu</option>
                    </select>
                    <p class="submit_wrapper">
                        <input type="submit" name="submit_exam" value="Submit Answers" />
                    </p>
                </div>
            </form>
        </td>
    </tr>
</table>