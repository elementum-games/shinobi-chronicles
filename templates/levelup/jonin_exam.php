<table class="table">
    <tr><th><?=$exam_name?><?=($player->exam_stage == $STAGE_PASS ? ' Graduation' : '')?></th></tr>
        <tr>
            <td style="text-align: center;">
                <?php if($player->exam_stage != $STAGE_PASS): ?>
                    Welcome to the Jonin Exam. In order to pass, you must complete a special mission
                    assigned by the Kage.
                <?php endif ?>
                <?php if($player->staff_manager->isHeadAdmin() && $player->exam_stage == 0): ?>
                    <hr />
                        As a <?=$player->staff_manager->getStaffLevelName(null, 'long')?> you can skip this exam.
                        <form action="<?=$self_link?>" method="post">
                            <input type="submit" name="skip_exam" value="Skip" />
                        </form>
                    <hr />
                <?php endif ?>
                <?php if($player->exam_stage == 0): ?>
                    <form action='<?=$self_link?>' method='post'>
                        <button type='submit' name='start_exam'>Start mission</button>
                    </form>
                <?php elseif(!$element): ?>
                    Congratulations, you have passed the Jonin Exam!<br />
                    <br />
                    After passing the exam, you have been recognized as an expert ninja of the <?=$player->village?> Village. You
                    can now train students, challenge your clan leader for their position, and access more powerful jutsu.

                    <!--With your new uniform you can carry
                    an extra weapon and wear another piece of armor, and as your skills with jutsu progress you are able to
                    keep an extra jutsu ready to use in combat.<br />-->

                    <br />
                    With the experience you have gained with your first element, your chakra is now refined enough to discover
                    your second elemental chakra nature. For this exam you are taken to a temple deep within the village
                    and sat in the center of a large jutsu seal. Surrounding you are 5 pedestals that the jutsu seal runs to:
                    The elders pull one away, disconnecting it from the jutsu. You are instructed to shut off your elemental
                    chakra and focus beyond it within yourself while infusing chakra into the seal, a difficult task but one you are
                    now capable of.<br />
                    <br />
                    <form action='<?=$self_link?>' method='post'>
                        <p style='text-align:center;'>
                            <b>Choose an element to focus on</b><br />
                            <i>(Note: Choose carefully, this will determine your secondary chakra nature, which cannot be
                                changed without AK)</i><br />
                            <select name='element'>
                                <?php foreach($elements as $elem): ?>
                                    <option value="<?=$elem?>"><?=$elem?></option>
                                <?php endforeach ?>
                            </select><br />
                            <input type='submit' value='Infuse Chakra' />
                        </p>
                    </form>
                <?php else: ?>
                    <?=$display?>
                <?php endif ?>
            </td>
        </tr>
</table>