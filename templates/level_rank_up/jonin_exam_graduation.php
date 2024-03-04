<?php
/**
 * @var System $system
 * @var User $player
 * @var string[] $available_element_names
 * @var Element|null $new_element
 */
?>

<table class="table">
    <tr><th>Jonin Exam Graduation</th></tr>
    <tr>
        <td>
            <?php if($new_element != null): ?>
                <div style="text-align: center;">
                    <?php if($new_element == Element::FIRE): ?>
                        With the image of blazing fires in your mind, you flow chakra from your stomach,
                        down through your legs and into the seal on the floor. Suddenly one of the pedestals bursts into
                        fire, breaking your focus. The elders smile and say:
                        <p>
                            <i>"Congratulations, you now have the Fire element. Fire is the embodiment of
                            consuming destruction, that devours anything in its path. Your Fire jutsu will be strong against Wind jutsu, as
                            they will only fan the flames and strengthen your jutsu. However, you must be careful against Water jutsu, as they
                            can extinguish your fires."</i>
                        </p>
                    <?php elseif($new_element == Element::WIND): ?>
                        Picturing a tempestuous tornado, you flow chakra from your stomach,
                        down through your legs and into the seal on the floor. You feel a disturbance in the room and
                        suddenly realize that a small whirlwind has formed around one of the pedestals. The elders smile and
                        say:
                        <p>
                            <i>"Congratulations, you have the Wind element. Wind is the sharpest out of all chakra natures,
                            and can slice through anything when used properly. Your Wind chakra will be strong against
                            Lightning jutsu, as you can cut and dissipate it, but you will be weak against Fire jutsu,
                            because your wind only serves to fan their flames and make them stronger."</i>
                        </p>
                    <?php elseif($new_element == Element::LIGHTNING): ?>
                        Imagining the feel of electricity coursing through your veins, you flow chakra from your stomach,
                        down through your legs and into the seal on the floor. Suddenly you feel a charge in the air and
                        one of the pedestals begins to spark with crackling electricity. The elders smile and say:
                        <p>
                            <i>"Congratulations, you have the Lightning element. Lightning embodies pure speed, and skilled users of
                            this element physically augment themselves to swiftly strike through almost anything. Your Lightning
                            jutsu will be strong against Earth as your speed can slice straight through the slower techniques of Earth,
                            but you must be careful against Wind jutsu as they will dissipate your Lightning."</i>
                        </p>
                    <?php elseif($new_element == Element::EARTH): ?>
                        Envisioning stone as hard as the temple you are sitting in, you flow chakra from your stomach,
                        down through your legs and into the seal on the floor. Suddenly dirt from nowhere begins to fall off one of the
                        pedestals, and the elders smile and say:
                        <p>
                            <i>"Congratulations, you have the Earth element. Earth
                                is the hardiest of elements and can protect you or your teammates from enemy attacks. Your Earth jutsu will be
                                strong against Water jutsu, as you can turn the water to mud and render it useless, but you will be weak to
                                Lightning jutsu, as they are one of the few types that can swiftly evade and strike through your techniques."</i>
                        </p>
                    <?php elseif($new_element == Element::WATER): ?>
                        With thoughts of splashing rivers flowing through your mind, you flow chakra from your stomach,
                        down through your legs and into the seal on the floor. Suddenly a small geyser erupts from one of
                        the pedestals, and the elders smile and say:
                        <p>
                            <i>"Congratulations, you have the Water element. Water is a versatile element that can control the flow
                            of the battle, trapping enemies or launching large-scale attacks at them. Your Water jutsu will be strong against
                            Fire jutsu because you can extinguish them, but Earth jutsu can turn your water into mud and render it useless."</i>
                        </p>
                    <?php endif; ?>
                    <br />
                    <a href='<?= $system->router->links['profile'] ?>'>Continue</a>
                </div>
            <?php else: ?>
                Congratulations, you have passed the Jonin Exam!<br />
                <br />
                After passing the Jonin Exam, you have been recognized as an expert ninja of the <?=$player->village->name?>
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
                <form action='<?= $system->router->getUrl('rankup'); ?>' method='post'>
                    <p style='text-align:center;'>
                        <b>Choose an element to focus on</b><br />
                        <i>(Note: Choose carefully, this will determine your secondary chakra nature, which cannot be
                            changed without AK)</i><br />
                        <select name='element'>
                            <?php foreach($available_element_names as $elem): ?>
                                <option value="<?=$elem?>"><?=$elem?></option>
                            <?php endforeach ?>
                        </select><br />
                        <input type='submit' name='select_chakra' value='Infuse Chakra' />
                    </p>
                </form>
            <?php endif; ?>
        </td>
    </tr>
</table>
