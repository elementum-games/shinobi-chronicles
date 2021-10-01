<?php
session_start();

require "classes/System.php";
$system = new System();
$system->renderStaticPageHeader();

?>

<table class='table'><tr><th>Manual</th></tr>
    <tr><td>
        <!-- <b>Contributing</b>
        To submit changes to this manual (you may need a GitHub account):
        <ol>
            <li>Go to <a href='https://github.com/levimeahan/shinobi-chronicles/blob/main/manual.php'>manual.php on GitHub</a></li>
            <li>Click the pencil icon on top right</li>
            <li>Make your changes</li>
            <li>Scroll down and click "Propose Changes"</li>
            <li>Click "Create Pull Request"</li>
        </ol> -->

        <div id='manual_container'>

          <div id='welcome_header'>
            <h1>Welcome to Shinobi-Chronicles, an online browser-based rpg fighting game!</h1>
            <h3>
              This manual will be your guide to this world.
              You'll learn about the skills and techniques necessary to succeed as a player.
            </h3>
          </div>

          <div style='margin: 4em 0em; border: 1px solid black; width: 100%;'></div><!--This is only a for an example, do not style inline unless absolutely necessary-->


          <!--Start Skill Wraper-->
          <div id='skills_wrapper'>
            <h1>Skills</h1>
            <div id='manual_special_skills'>
              <h2>Special Skills</h2>
              <table>
                <th colspan="2">Special Skills</th>
                  <tr>
                    <td>Ninjutsu</td><td>Increases Ninjutsu based attacks</td>
                  </tr>
                  <tr>
                    <td>Taijutsu</td><td>Increases Taijutsu based attacks</td>
                  </tr>
                  <tr>
                    <td>Genjutsu</td><td>Increases Genjutsu based attacks</td>
                  </tr>
              </table>
            </div>

            <br>

            <!-- <div id='manual_general_skills'>
              <h2>General Skills</h2>
              <table>
                <th>Cast Speed</th>
                <th>Speed</th>
                <th>Willpower</th>
                <th>Intelligence</th>
                  <tr>
                    <td>Defends against ninjutsu and taijutsu</td>
                    <td>Defends against nin and tai as tai user</td>
                    <td>Reduce effects of debuffs</td>
                    <td>Keeps Genjutsu user keep their genjutsu applied</td>
                  </tr>
              </table> -->

              <br>

              <div id='manual_general_skills'>
                <h2>General Skills</h2>
                <table>
                  <th>Skill</th>
                  <th>Affects</th>
                    <tr>
                      <td>Cast Speed</td><td>Defends against ninjutsu and taijutsu</td>
                    </tr>
                    <tr>
                      <td>Speed</td><td>Defends against nin and tai as tai user</td>
                    </tr>
                    <tr>
                      <td>Willpower</td><td>Reduce effects of debuffs</td>
                    </tr>
                    <tr>
                      <td>Intelligence</td><td>Keeps Genjutsu user keep their genjutsu applied</td>
                    </tr>
                </table>
              </div>
            </div>
          </div>

          <br>

          <div>
            <h2>Helpful Skill Tips</h>

            <h3>Stat Capped</h>
            <table>
              <th colspan="2">Stat Caps</th>
              <tr>
                <td colspan='2'>Stats have a limit. You are able to train as much of a certain <b>skill stat</b> up until you reach your max stat limit.</td>
              </tr>
              <tr>
                <td>
                  <img src="./images/manual/stat_capped.png"/>
                </td>
                <td>
                  Once you reach the <b>maximum amount</b> of stats gained at your rank, you are unable to train for more stats.
                </td>
              </tr>

            </table>
          </div>
          <!--End Skill Wrapper-->

          <div style='margin: 4em 0em; border: 1px solid black; width: 100%;'></div><!--This is only a for an example, do not style inline unless absolutely necessary-->

          <h1>Navigation</h1>

          <!--User Profile-->
          <div id='manual_profile_wrapper'>
            <div id='manual_profile_header'>
              <h2 style='display: inline'>User Profile</h2> <!--This is only a for an example, do not style inline unless absolutely necessary-->
              <img width='40%' src="./images/manual/navigation_profile.png"/>
              <h3>Navigating the Profile Page</h3>
            </div>

            <div>
              <p>The profile summary is divided up into 4 sections.</p>
            </div>
          </div>
          <!--End User Profile-->

        </div>

        <!-- Content -->
        <div>

        </div>
    </td></tr>
</table>

<?php
$system->renderStaticPageFooter();
