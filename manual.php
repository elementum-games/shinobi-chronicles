<?php
session_start();

require "classes/System.php";
$system = new System();
$layout = System::DEFAULT_LAYOUT;

if(isset($_SESSION['user_id'])) {
    require_once "classes/User.php";
    require_once "classes/Bloodline.php";
    $player = new User($_SESSION['user_id']);
    $player->loadData();
    $layout = $player->layout;
}

$system->renderStaticPageHeader($layout);

?>

<table class='table'><tr><th>Manual</th></tr>
    <tr><td>
        <b>Contributing</b>
        To submit changes to this manual (you may need a GitHub account):
        <ol>
            <li>Go to <a href='https://github.com/levimeahan/shinobi-chronicles/blob/main/manual.php'>manual.php on GitHub</a></li>
            <li>Click the pencil icon on top right</li>
            <li>Make your changes</li>
            <li>Scroll down and click "Propose Changes"</li>
            <li>Click "Create Pull Request"</li>
        </ol>

        <h3>Authors</h3>
        <ul>
          <li>Gojo</li>
        </ul>

        <div style='margin: 4em 0em; border: 1px solid black; width: 100%;'></div><!--This is only a for an example, do not style inline unless absolutely necessary-->

          <div id='gojo-manual'>

          <h1>Intro to Shinobi Chronicles</h1>
          <h2>Shinobi Chronicles is a browser based MMORPG inspired by the popular anime/manga series, Naruto. Train your character through the village ranking system, climb to new heights whilst becoming a world renowned Ninja!</h2>
          <p>
          Learn various jutsu, unlock your bloodline and partake in combat with warring or neutral villages to earn points and unlock boosts and rewards for your entire village and personal squads.
          </p>
          <p>
          Work your way up through the ninja ranks and claim the highest possible position, the Kage, or become an elite level ninja, feared across all villages.
          </p>

          <div style='margin: 4em 0em; border: 1px solid black; width: 100%;'></div><!--This is only a for an example, do not style inline unless absolutely necessary-->

          <h2>Combat</h2>
          <p>
            Combat works as a turn based battle system, explore different uses of effects such as nerfs, boosts, drains, and many more to ensure and strategize the best possible outcome that may lead to your victory in battle
          </p>
          <p>In Shinobi Chronicles, there are 3 types of offense</p>
          <div>
            <p><b>Ninjutsu</b> - Focuses on the use of hand signs and chakra based/element attacks</p>
            <p><b>Taijutsu</b> - Focuses on the use of hand to hand combat and various weapon effects</p>
            <p><b>Genjutsu</b> - Focuses on the use of illusions and high residual damage</p>
          </div>

          <p>Aside from offense types, there are several sub stats to train in tandem</p>
          <div>
            <p><b>Cast Speed</b> - Allows you to cast jutsu faster than other Ninjutsu users and avoid damage from Taijutsu users</p>
            <p><b>Speed</b> - Allows you to move faster than Ninjutsu and Taijutsu users to avoid damage</p>
            <p><b>Intelligence</b> - Allows Genjutsu users to keep people locked in their Genjutsu, and also allows other offense users to break out of them</p>
            <p><b>Willpower</b> - Allows you to resist a percentage of effects from all offenses (Nerfs/Drains/Residuals ect)</p>
          </div>

          <br>

          <h2>Training</h2>
          <p>
            Your main source of gaining stats will be through a timed training system.
            You can train your stats through short, long, and extended trainings
          </p>
          <p><em>The current training rates are as followed</em></p>
          <div>
            <p>Academy Student - 4</p>
            <p>Genin - 6</p>
            <p>Chuunin - 8</p>
          </div>
          <p>Note that a portion of stats gained from long and extended trainings are cut</p>

          <p>You can also make use of the Arena to fight AI to gain stat points while earning yen</p>
          <p><em>The current drop rate for stats are as followed</em></p>
          <p>A base of a 25% drop per fight</p>
          <p>Increased by 15% if you are under or same level as AI</p>
          <p>ncreased by 10% if the fight lasts longer than 4 turns</p>

              <br/>

          <h2>Character Rank, Stat caps and Requirements</h2>
          <p><b>Academy student</b>- Starting rank up to level 10. Stat cap of 1000</p>
          <p><b>Genin</b> - Must be a lvl 10 AS plus required exp to take the exam. Stat cap of 5000</p>

          <p>
            In order to pass the exam, you must buy and learn <b>Basic Replacement</b>, <b>Clone Combo</b>, and <b>Transformation Trickery</b> from the shop and perform the hand signs.<br>
          </p>
          <p>
            <b>Chuunin</b> - Must be a lvl 20 Genin plus required exp to take the exam. Stat cap of 25000
            In order to pass the exam, the user must pass a basic test along with undergoing a series of battles.
          </p>

              <br/>

          <h2>Shinobi Chronicle Currencies</h2>
          <p>Yen is the primary currency for Shinobi Chronicles, use Yen to purchase the jutsus of your offense type in the shop, gear to help you in battle, and healing items for after combat</p>
          <p>Note, to use parent jutsu the child jutsu must be bought and leveled to 50 in order to learn the move</p>
          <p>You are only able to learn jutsu’s of your element</p>

          <br>

          <p>List of gear and weapon effects</p>
          <p><b>Harden</b> - Resists damage from Taijutsu</p>
          <p><b>Lighten</b> - Increases speed (does not affect cast speed)</p>
          <p><b>Daze</b> - Lowers target's intelligence</p>
          <p><b>Cripple</b> - Lowers target speed</p>

          <br>

          <p><b>Ancient Kunai</b> is a premium currency used for various changes toward your character</p>
          <h2>Character changes</h2>
          <p>Use Ancient Kunai to reset your character, change your username, transfer skill points into other stats, reset an individual stat, or to change your current clan</p>

              <br/>

          <h2>Bloodline</h2>
          <p>Bloodline rates for your first roll upon reaching Genin are as followed</p>
          <p><b>None</b> - 50% chance</p>
          <p><b>Lesser</b> - 20% chance</p>
          <p><b>Common</b> - 15% chance</p>
          <p><b>Elite</b> - 10% chance</p>
          <p><b>Legendary</b> - 5% chance</p>

          <p>Use Ancient Kunai to purchase or reroll to a new bloodline of your choosing. Note, 10% of bloodline skill will be lost when acquiring a new bloodline.</p>
          <p><b>Legendary</b> - 80 AK</p>
          <p><b>Elite</b> - 60 AK</p>
          <p><b>Common</b> - 40 AK</p>
          <p><b>Lesser</b> - 20 AK</p>

              <br/>

          Bloodline section with info TBA

              <br/>

          <h2>Auras</h2>
          <p>With the use of Ancient Kunai, you can imbue your character with a forbidden seal for enhanced benefits toward your character.</p>

          <div>
            <h3>
              Twin Sparrow Seal
            </h3>
            <h4>
              5 Ancient Kunai / 30 days
            </h4>
            <div>+10% regen rate</div>
            <div>Blue/Pink username color in chat</div>
            <div>Larger avatar (125x125 -> 175x175)</div>
            <div>Longer logout timer (60 -> 90 minutes)</div>
            <div>Larger inbox (50 -> 75 messages)</div>
            <div>Longer journal (1000 -> 2000 characters)</div>
            <div>Larger journal images (300x200 -> 500x500)</div>
            <div>Longer chat posts (350 -> 450 characters)</div>
            Longer PMs (1000 -> 1500 characters)rs)</div>
          </div>

          <br>
          <!--Unedited-->

          Twin Sparrow Seal                                                                                   Four Dragon Seal
          5 Ancient Kunai / 30 days			                                                                 15 Ancient Kunai / 30 days
          +10% regen rate                                                                               All benefits of Twin Sparrow
          Blue/Pink username color in chat                                                                    +20% regen rate
          Larger avatar (125x125 -> 175x175)                                                                 +1 jutsu equip slot
          Longer logout timer (60 -> 90 minutes)                                                            +1 weapon equip slot
          Larger inbox (50 -> 75 messages)                                                                   +1 armor equip slot
          Longer journal (1000 -> 2000 characters)                                                Enhanced long trainings (1.5x length, 2x gains)
          Larger journal images (300x200 -> 500x500)                                           Enhanced extended trainings (1.5x length, 2.25x gains)
          Longer chat posts (350 -> 450 characters)
          Longer PMs (1000 -> 1500 characters)

          <!--GOJO END MANUAL-->

        </div>

        <!-- Content -->
        <div>

        </div>
    </td></tr>
</table>

<?php
$system->renderStaticPageFooter($layout);
