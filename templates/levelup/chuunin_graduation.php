<table class="table">
    <tr><th><?=$exam_name?> Graduation</th></tr>
    <?php if(!$element): ?>
        <tr>
            <td>
                Congratulations, you have passed the Chuunin Exam!<br />
                <br />
                After passing all three exams, you have been recognized as an official ninja of the <?=$player->village?> Village. You now
                have more rights, and more responsibilities. You can create or join a team with up to 3 other ninja, as well as
                take on C-rank and B-rank missions. You can no longer normally train or do arena fights inside the village, as your skills are too destructive to be
                safe around the civilians. With your new uniform you can carry an extra weapon and wear another piece of armor, and as
                your skills with jutsu progress you are able to keep an extra jutsu ready to use in combat.<br />
                <br />
                Your chakra has also reached the point where you are able to discover your first elemental chakra nature. The elders
                hand you a piece of chakra paper and instruct you to focus hard and infuse your chakra into the paper. The reaction
                will determine which your primary chakra nature type is.<br />
                <br />
                <form action='<?=$self_link?>' method='post'>
                    <p style='text-align:center;'>
                        <b>Choose an element to focus on</b><br />
                        <i>(Note: Choose carefully, this will determine your primary chakra nature, which cannot be
                            changed without AK)</i><br />
                        <select name='element'>
                            <option value='Fire'>Fire</option>
                            <option value='Wind'>Wind</option>
                            <option value='Lightning'>Lightning</option>
                            <option value='Earth'>Earth</option>
                            <option value='Water'>Water</option>
                        </select><br />
                        <input type='submit' value='Infuse Chakra' />
                    </p>
                </form>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <td style="text-align: center;">
                <?=$display?>
            </td>
        </tr>
    <?php endif ?>
</table>