

<table class="table">
    <tr><th><?=$exam_name?> Gradution</th></tr>
    <tr>
        <td>
            <?php if(!$element): ?>
                Congratulations, you have passed the <?=$exam_name?>!<br />
                <br />
                After passing the <?=$exam_name?>, you have been recognized as an expert ninja of the <?=$player->village->name?>
                Village. You can now train students, challenge your clan leader for their position, and access more powerful jutsu.
                <br />
                <!--With your new uniform you can carry
                an extra weapon and wear another piece of armor, and as your skills with jutsu progress you are able to
                keep an extra jutsu ready to use in combat.<br />-->

                <br />
                With the experience you have gained with your first element, your chakra is now refined enough to discover
                your second elemental chakra nature. For this exam you are taken to a temple deep within the village
                and sat in the center of a large jutsu seal. Surrounding you are 5 pedestals that the jutsu seal runs to:<br />
                The elders pull one away, disconnecting it from the jutsu. You are instructed to shut off your elemental
                chakra and focus beyond it within yourself while infusing chakra into the seal, a difficult task but one you are
                now capable of.<br />
                <br />
                <form action='<?=$self_link?>' method='post'>
                    <p style='text-align:center;'>
                        <b>Choose an element to focus on</b><br />
                        <i>(Note: Choose carefully, this will determine your secondary chakra nature, which cannot be
                            changed without <?=Currency::PREMIUM_NAME?>)</i><br />
                        <select name='element'>
                            <?php foreach($elements as $elem): ?>
                                <option value="<?=$elem?>"><?=$elem?></option>
                            <?php endforeach ?>
                        </select><br />
                        <input type='submit' name='select_chakra' value='Infuse Chakra' />
                    </p>
                </form>
            <?php else: ?>
                <p style="text-align: center;">
                    <?=$element_display?>
                </p>
            <?php endif ?>
        </td>
    </tr>
</table>
