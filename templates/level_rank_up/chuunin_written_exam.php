

<table class="table">
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

                    <b>What type of jutsu focuses on hand-to-hand combat and physical techniques, utilizing the body's natural energy and strength, often without the use of chakra or weapons?</b><br />
                    <input type='radio' name='question2' value='ninjutsu' /> Ninjutsu<br />
                    <input type='radio' name='question2' value='taijutsu' /> Taijutsu<br />
                    <input type='radio' name='question2' value='genjutsu' /> Genjutsu<br />
                    <input type='radio' name='question2' value='all' /> All of the above<br />
                    <br />

                    <b>Which type of jutsu primarily involves manipulating chakra to create physical effects or phenomena, such as breathing fire, creating water blasts, or manipulating earth?</b><br />
                    <input type='radio' name='question3' value='ninjutsu' /> Ninjutsu<br />
                    <input type='radio' name='question3' value='taijutsu' /> Taijutsu<br />
                    <input type='radio' name='question3' value='genjutsu' /> Genjutsu<br />
                    <input type='radio' name='question3' value='all' /> All of the above<br />
                    <br />

                    <b>What are the names of the 5 Great Ninja Villages?</b><br />
                    <input type='radio' name='question4' value='rain_sound_leaf_grass_waterfall' /> Rain, Sound, Leaf, Grass, Waterfall<br />
                    <input type='radio' name='question4' value='rock_wind_flame_ocean_sky' /> Rock, Cloud, Flame, Ocean, Sand<br />
                    <input type='radio' name='question4' value='stone_cloud_leaf_sand_mist' /> Stone, Cloud, Leaf, Sand, Mist<br />
                    <input type='radio' name='question4' value='river_mountain_forest_desert_fog' /> River, Stone, Forest, Desert, Fog<br />
                    <input type='radio' name='question4' value='earth_thunder_wood_iron_snow' /> Earth, Thunder, Wood, Iron, Mist<br />
                    <br />

                    <b>Which sequence correctly represents the cycle of elemental strengths and weaknesses?</b><br />
                    <input type='radio' name='elementQuestion' value='fire_wind_lightning_earth_water' /> Fire > Wind > Lightning > Earth > Water > Fire<br />
                    <input type='radio' name='elementQuestion' value='fire_water_earth_lightning_wind' /> Fire > Water > Earth > Lightning > Wind > Fire<br />
                    <input type='radio' name='elementQuestion' value='earth_fire_water_wind_lightning' /> Earth > Fire > Water > Wind > Lightning > Earth<br />
                    <input type='radio' name='elementQuestion' value='lightning_earth_fire_water_wind' /> Lightning > Earth > Fire > Water > Wind > Lightning<br />
                    <input type='radio' name='elementQuestion' value='wind_lightning_water_fire_earth' /> Wind > Lightning > Water > Fire > Earth > Wind<br />
                    <br />

                    <p style="text-align: center;"><input type='submit' name='written_exam' value='Submit Answers' /></p>
                </form>
            </td>
        </tr>
</table>
