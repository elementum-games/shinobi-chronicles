<?php
/*
File:		layout_vars.php
Coder:		Levi Meahan
Created:	02/21/2012
Revised:	08/24/2013 by Levi Meahan
Purpose:	Contains variables for layout HTML sections: Header, menus, footer
*/

/** @var System $system */

require 'layout/_common.php';
$heading = coreHeading('./style/cextralite/style.css') . <<<HTML
<body>
	<div id='container'>
		<div>
HTML;

$header = <<<HTML
	</div>

		<div id='header'>
			<img src='./style/classic_blue/images/banner.png' />
		</div>

		<div id='contentContainer'>

HTML;

$body_start = <<<HTML

	  <div id='content'>
		<div class='contentHeader'>
		[HEADER_TITLE]
		</div>

HTML;

$top_menu = <<<HTML

	<div style="height: 70px; width: 70px; margin: auto; padding: 0;" class="menuTouchItem">
		<img style='width: 100%;' src="" />
	</div>

	<ul class='topMenu'>
		<li><a href='{$system->link}'>News</a></li>
		<li><a href='{$system->link['discord']}' target='_blank'>Discord</a></li>
		<li><a href='{$system->link}manual.php'>Manual</a></li>
		<li><a href='{$system->link}rules.php'>Rules</a></li>
		<li><a href='{$system->link}terms.php'>Terms of Service</a></li>
	</ul>


			</div>
		</div>

HTML;

$side_menu_start = <<<HTML

	<div id='sidebar'>
  <ul  id='menu'>

						<div>
						<div id="character_header" class='header'>
						Character Menu
						</div>
				  	<div id="player_menu" class='buttonList'>
HTML;
$village_menu_start = <<<HTML
			      </div>
						</div>

				<div>
				<div id="travel_header" class='header'>
				Travel Menu
				</div>
		  	<div id="travel_menu" class='buttonList'>
HTML;
$action_menu_header = <<<HTML
	  		</div>
				</div>

		<div>
		<div id="action_header" class='header'>
				Action Menu
		</div>
		<div id="action_menu" class='buttonList'>
HTML;
$staff_menu_header = <<<HTML
		</div>
		</div>

				<div>
				<div id="staff_header" class='header'>
					Staff Menu
				</div>
				<div id="staff_menu" class='buttonList'>
HTML;
$side_menu_end = <<<HTML
				</div>
				</div>

</div>
		<br style='margin:0px;' />
		</ul>
		<br style='margin:0px;' />
		<div class='logout'>
			<a href='?logout=1'>Logout</a>
			<p id='logoutTimer' style='margin-top:5px;'><!--LOGOUT_TIMER--></p>
		</div>
	</div>
HTML;

$login_menu = <<<HTML
	</div>
</div>
<div id='sidebar' style='min-height:475px;'>
	<div class='header'>
	Login
	</div>
	<div id='login'>
		<form action='{$system->link}' method='post'>
		<span>Username</span><br />
		<input type='text' name='user_name' /><br />
		<span>Password</span><br />
		<input type='password' name='password' /><br />
		<input type='submit' name='login' value='Login' />
		</form>
		<p>
			<a class='link' href='{$system->link}register.php'>Create an account</a>
		</p>
	</div>
</div>
HTML;$footer = <<<HTML
</div>

<script type='text/javascript' src="./style/cextralite/responsive_script.js"></script>

<div id='footer'>
	Developed by LM Visions - Layout design by Cextra
</div>

</body>
</html>
HTML;
