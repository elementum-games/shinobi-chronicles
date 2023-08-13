<?php
session_start();

require "classes/System.php";
$system = new System();
$system->db->startTransaction();
$layout = $system->setLayoutByName("shadow_ribbon");

if(isset($_SESSION['user_id'])) {
    require_once 'classes.php';
    $player = User::loadFromId($system, $_SESSION['user_id'], read_only: true);
    $player->loadData();
    $layout = $system->setLayoutByName($player->layout);
}
else {
    require_once 'classes/Bloodline.php';
}

$layout->renderBeforeContentHTML($system, $player ?? null, 'Manual');

?>

<!--
  Probably move the styles somewhere, but since all the
  themes has their own style IDK where to keep this,
  maybe manual should have its own styles?
  But for now I'm keeping styles here
-->
<style>
  .grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 10px;
    padding: 10px;
  }

  .section-container {
    border-top: 1px solid #aaa;
    padding: 10px;
    margin-top: 2rem;
  }

  .bloodline-details {
    position: absolute;
    left: 10px;
    top: 10px;
  }

  .bloodline_accordion {
    position: relative;
  }

  .bloodline-details {
    position: absolute;
  }
</style>

<table class='table'>
  <tr>
    <th>Manual</th>
  </tr>
  <tr>
    <td>

      <div style="padding:7px 10px">
        <h1>Intro to Shinobi Chronicles</h1>
        <h4>Shinobi Chronicles is a browser based MMORPG inspired by the popular anime/manga series, Naruto. Train your character through the village ranking system, climb to new heights whilst becoming a world renowned Ninja!</h4>
        <h4>Learn various jutsu, unlock your bloodline and partake in combat with warring or neutral villages to earn points and unlock boosts and rewards for your entire village and personal squads.</h4>
        <h4>Work your way up through the ninja ranks and claim the highest possible position, the Kage, or become an elite level ninja, feared across all villages.</h4>
      </div>


      <div class="grid-container" style="border-top: 1px solid #aaa">
        <div class="item-1">
          <h2>Index</h2>
          <ol>
            <li><a href="#combat-section">Combat</a></li>
            <li><a href="#training-section">Training</a></li>
            <li><a href="#rank-section">Ranks & Stat caps</a></li>
            <li><a href="#bloodline-section">Bloodline</a></li>
            <li><a href="#currency-section">Currencies</a></li>
          </ol>
        </div>
        <div class="item-1">
          <h2>Contributions</h2>

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
            <li><a href="https://shinobichronicles.com/?id=6&user=Gojo">Gojo</a></li>
            <li><a href="https://shinobichronicles.com/?id=6&user=Stain">Stain</a></li>
            <li><a href="https://shinobichronicles.com/?id=6&user=Tetsuishi">Tetsuishi</a></li>
          </ul>
        </div>
      </div>


      <!--COMBAT SECTION-->
      <div id="combat-section" class="section-container">
        <a href="#combat-section">
          <h2>Combat</h2>
        </a>
        <p>
          Combat works as a turn based battle system, explore different uses of effects such as nerfs, boosts, drains, and many more to ensure and strategize the best possible outcome that may lead to your victory in battle
        </p>

        <table class="table">
          <tbody>
            <tr>
              <th colspan="2">Offenses</th>
            </tr>
            <tr>
              <th class="row1" style="width: 30%">Offense</th>
              <th class="row1" style="width: 70%">Description</th>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Ninjutsu</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Focuses on the use of hand signs and chakra based/element attacks</td>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Taijutsu</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Focuses on the use of hand to hand combat and various weapon effects</td>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Genjutsu</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Focuses on the use of illusions and high residual damage</td>
            </tr>
            <tr>
              <th colspan="2">Skills</th>
            </tr>
            <tr>
              <th class="row1" style="width: 30%">Skill</th>
              <th class="row1" style="width: 70%">Description</th>
            </tr>
            <tr class="table-multicolumns">
              <td class="row1" style="text-align: center;font-weight: bold">Cast Speed</td>
              <td class="row1" style="padding: 7px 10px;">Primarily used by Ninjutsu and Genjutsu skills to determine damage done or damage reduced when it is higher than your Opponents Speed/Cast Speed</td>
            </tr>
            <tr class="table-multicolumns">
              <td class="row1" style="text-align: center;font-weight: bold">Speed</td>
              <td class="row1" style="padding: 7px 10px;">Primarily used by Taijutsu skills to determine damage done or damage reduced when it is higher than your Opponents Speed/Cast Speed</td>
            </tr>
            
          </tbody>
        </table>

        <br><br>
        Apart from the offenses and the skills, there's also Gears and Weapons which can be equipped to get certain effects.

        <table class="table">
          <tbody>
            <tr>
              <th colspan="2">Gear and Weapon effects</th>
            </tr>
            <tr>
              <th class="row1" style="width: 30%">Effect</th>
              <th class="row1" style="width: 70%">Description</th>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Harden</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Resists damage from Taijutsu</td>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Lighten</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Increases speed (does not affect cast speed)</td>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Daze</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Lowers target's intelligence</td>
            </tr>
            <tr class="table_multicolumns">
              <td class="row1" style="width: 30%; text-align: center;font-weight: bold">Cripple</td>
              <td class="row1" style="width: 70%; padding: 7px 10px;">Lowers target speed</td>
            </tr>
          </tbody>
        </table>
      </div>


      <!--TRAINING SECTION-->
      <div id="training-section" class="section-container">
        <a href="#training-section">
          <h2>Training</h2>
        </a>
        <p>
          Your main source of gaining stats will be through a timed training system.
          You can train your stats through short, long, and extended trainings
        </p>
        <p><em>The current training rates are as followed</em></p>
        <div>
          <p>Academy Student - 8</p>
          <p>Genin - 12</p>
          <p>Chuunin - 16</p>
            <p>Jonin - 20</p>
        </div>
        <p>Note that a portion of stats gained from long and extended trainings are cut</p>

        <p>You can also make use of the Arena to fight AI to gain stat points while earning <?=Currency::MONEY_NAME?></p>
        <p><em>The current drop rate of stat points is 100%, Stat dropped is determined by the current stat training ongoin or the last the stat training done.</em></p>
      </div>


      <!--RANKS AND STAT CAPS-->
      <div id="rank-section" class="section-container">
        <a href="#rank-section">
          <h2>Ranks &amp; Stat caps</h2>
        </a>
        <table class="table">
          <tbody>
            <tr class="table-multicolumns">
              <th class="row1" style="width: 60%">Rank</th>
              <th class="row1">Max Level</th>
              <th class="row1">Stat Cap</th>
            </tr>
            <tr>
              <td class="row1" style="text-align: center; padding: 7px 20px">Academi-sei</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">10</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">1,000</td>
            </tr>
            <tr>
              <td class="row1" style="text-align: center; padding: 7px 20px">Genin</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">25</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">10,000</td>
            </tr>
            <tr>
              <td class="row1" style="text-align: center; padding: 7px 20px">Chunin</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">50</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">50,000</td>
            </tr>
            <tr>
              <td class="row1" style="text-align: center; padding: 7px 20px">Jounin</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">100</td>
              <td class="row1" style="text-align: center; padding: 7px 20px">250,000</td>
            </tr>
          </tbody>
        </table>
      </div>


      <!--BLOODLINE SECTION-->
      <div id="bloodline-section" class="section-container">
        <a href="#bloodline-section">
          <h2>Bloodline</h2>
        </a>
        <div>Bloodlines are items that you receive or buy with <b><?=Currency::PREMIUM_NAME?></b> which has specific boosts/resists and jutsus.</div>
        <div><b>Legendary</b> - 5% chance</div>
        <div><b>Elite</b> - 10% chance</div>
        <div><b>Common</b> - 15% chance</div>
        <div><b>Lesser</b> - 20% chance</div>
        <div><b>None</b> - 50% chance</div>

        <p>Use <b><?=Currency::PREMIUM_NAME?></b> to purchase or reroll to a new bloodline of your choosing. Note, 10% of bloodline skill will be lost when acquiring a new bloodline.</p>
        <div><b>Legendary</b> - 80 <?=Currency::PREMIUM_SYMBOL?></div>
        <div><b>Elite</b> - 60 <?=Currency::PREMIUM_SYMBOL?></div>
        <div><b>Common</b> - 40 <?=Currency::PREMIUM_SYMBOL?></div>
        <div><b>Lesser</b> - 20 <?=Currency::PREMIUM_SYMBOL?></div>

        <br />

        <?php include('templates/bloodlineList.php') ?>
      </div>


      <!--CURRENCIES-->
      <div id="currency-section" class="section-container">
        <a href="#curreny-section">
          <h2>Currencies</h2>
        </a>
        <p>Yen is the primary currency for Shinobi Chronicles, use Yen to purchase the jutsus of your offense type in the shop, gear to help you in battle, and healing items for after combat.</p>
        <p>You can earn Yen by fighting enemies in Arena, completing Missions and Special Missions or ask your friend to transfer you some!</p>
        <br />
        <h3><?=Currency::PREMIUM_NAME?></h3>
        <p>It is a premium currency used for various changes toward your character</p>
        <p>Use <b><?=Currency::PREMIUM_NAME?></b> to reset your character, change your username, transfer skill points into other stats, reset an individual stat, or to change your current clan</p>
        <br />
        <h3>Auras</h3>
        <p>With the use of <?=Currency::PREMIUM_NAME?>, you can imbue your character with a forbidden seal for enhanced benefits toward your character.</p>

      </div>



      <div>
        <h3>
          Twin Sparrow Seal
        </h3>
        <h4>
          5 <?=Currency::PREMIUM_NAME?> / 30 days
        </h4>
        <div>+10% regen rate</div>
        <div>Blue/Pink username color in chat</div>
        <div>Larger avatar (125x125 -> 175x175)</div>
        <div>Longer logout timer (60 -> 90 minutes)</div>
        <div>Larger inbox (50 -> 75 messages)</div>
        <div>Longer journal (1000 -> 2000 characters)</div>
        <div>Larger journal images (300x200 -> 500x500)</div>
        <div>Longer chat posts (350 -> 450 characters)</div>
        Longer PMs (1000 -> 1500 characters)rs)
      </div>

      <br>
      <!--Unedited-->

      <h3>
        Four Dragon Seal
      </h3>
      <h4>
        15 <?=Currency::PREMIUM_NAME?> / 30 days
      </h4>
      <ul>
        <li>All benefits of Twin Sparrow Seal</li>
        <li>+20% regen rate</li>
        <li>+1 jutsu equip slot</li>
        <li>+1 weapon equip slot</li>
        <li>+1 armor equip slot</li>
        <li>Enhanced long trainings (1.5x length, 2x gains)</li>
        <li>Enhanced extended trainings (1.5x length, 2.25x gains)</li>
        <li>Faster stat transfers (+5/minute)</li>
        <li>Cheaper stat transfers +100 stat points per <?=Currency::PREMIUM_SYMBOL?></li>
      </ul>

      <!--GOJO END MANUAL-->

      <!-- Content -->
      <div>

      </div>
    </td>
  </tr>
</table>

<script type="text/javascript">
  function toggleBloodlineDetails(bloodline_id) {
    console.log(bloodline_id)
    var details_panel = document.querySelector(`#bloodline-details-${bloodline_id}`);
    if (details_panel.style.display === "block") {
      details_panel.style.display = "none";
    } else {
      details_panel.style.display = "block";
    }
  }
</script>

<?php
$layout->renderAfterContentHTML($system, $player ?? null);

$system->db->commitTransaction();
