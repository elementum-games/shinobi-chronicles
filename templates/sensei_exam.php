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
            <form action="<?= $system->router->links['academy'] ?>" method="post">
                <p class="question_label">Which of the following helps with completing Special Missions?</p>
                <div><input type="radio" name="question1" value="1a" checked/> <label>Stat Total</label></div>
                <div><input type="radio" name="question1" value="1b" /> <label>Max Health</label></div>
                <div><input type="radio" name="question1" value="1c" /> <label>Jutsu Levels</label></div>
                <div><input type="radio" name="question1" value="1d" /> <label>All of the above</label></div>
                <div><input type="radio" name="question1" value="1e" /> <label>None of the above (pure chance)</label></div>
                <p class="question_label">Which of these Forbidden Seal benefits is not granted by <?= ForbiddenSeal::$forbidden_seal_names[1] ?>?</p>
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
                <p class="question_label">What are the soft and hard caps for Evasion, Resist, and Offense Nerf?</p>
                <div><input type="radio" name="question4" value="4a" checked/> <label>25% and 50%</label></div>
                <div><input type="radio" name="question4" value="4b" /> <label>35% and 65%</label></div>
                <div><input type="radio" name="question4" value="4c" /> <label>50% and 75%</label></div>
                <div><input type="radio" name="question4" value="4d" /> <label>65% and 100%</label></div>
                <p class="question_label">How much of an increase to a jutsu's power and effect does it gain by level 100?</p>
                <div><input type="radio" name="question5" value="5a" checked/> <label>30% Power, 20% Effect Strength</label></div>
                <div><input type="radio" name="question5" value="5b" /> <label>50% Power, 30% Effect Strength</label></div>
                <div><input type="radio" name="question5" value="5c" /> <label>50% Power, 50% Effect Strength</label></div>
                <p class="question_label">Which of the following effects is not reduced by a Piercing jutsu?</p>
                <div><input type="radio" name="question6" value="6a" checked/> <label>Counter</label></div>
                <div><input type="radio" name="question6" value="6b" /> <label>Reflect</label></div>
                <div><input type="radio" name="question6" value="6c" /> <label>Substitution</label></div>
                <div><input type="radio" name="question6" value="6d" /> <label>Barrier</label></div>
                <div><input type="radio" name="question6" value="6e" /> <label>Resists</label></div>
                <div><input type="radio" name="question6" value="6f" /> <label>None of the above</label></div>
                <div class="submit_container">
                    <b>
                        Choose your Specialization
                    </b>
                    <select class="specialization_select" name='specialization'>
                        <option value="taijutsu">Taijutsu</option>
                        <option value="ninjutsu">Ninjutsu</option>
                        <option value="genjutsu">Genjutsu</option>
                        <option value="speed">Speed</option>
                        <option value="cast_speed">Cast Speed</option>
                    </select>
                    <p class="submit_wrapper">
                        <input type="submit" name="submit_exam" value="Submit Answers" />
                    </p>
                </div>
            </form>
        </td>
    </tr>
</table>