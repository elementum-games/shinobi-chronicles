<?php
/*
File:		layout_vars.php
Coder:		Levi Meahan
Created:	02/21/2012
Revised:	08/24/2013 by Levi Meahan
Purpose:	Contains variables for layout HTML sections: Header, menus, footer
*/

/** @var System $system */

require_once 'layout/_common.php';
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
		<li><a href='{$system->router->base_url}'>News</a></li>
		<li><a href='{$system->router->base_url['discord']}' target='_blank'>Discord</a></li>
		<li><a href='{$system->router->base_url}manual.php'>Manual</a></li>
		<li><a href='{$system->router->base_url}rules.php'>Rules</a></li>
		<li><a href='{$system->router->base_url}terms.php'>Terms of Service</a></li>
	</ul>


			</div>
		</div>

HTML;

$side_menu_start = <<<HTML

	<div id='sidebar'>
  <ul  id='menu'>

						<div>
						<div id="character_header" class='header'>
						Character
						</div>
				  	<div id="player_menu" class='buttonList'>
HTML;
$village_menu_start = <<<HTML
			      </div>
						</div>

				<div>
				<div id="travel_header" class='header'>
			  Village
				</div>
		  	<div id="travel_menu" class='buttonList'>
HTML;
$action_menu_header = <<<HTML
	  		</div>
				</div>

		<div>
		<div id="action_header" class='header'>
				Travel
		</div>
		<div id="action_menu" class='buttonList'>
HTML;
$staff_menu_header = <<<HTML
		</div>
		</div>

				<div>
				<div id="staff_header" class='header'>
					Staff
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
		<form action='{$system->router->base_url}' method='post'>
		<span>Username</span><br />
		<input type='text' name='user_name' /><br />
		<span>Password</span><br />
		<input type='password' name='password' /><br />
		<input type='submit' name='login' value='Login' />
		</form>
		<p>
			<a class='link' href='{$system->router->base_url}register.php'>Create an account</a>
		</p>
	</div>
</div>
HTML;

$footer = <<<HTML
</div>

<script type='text/javascript' src="./style/cextralite/responsive_script.js"></script>

<div id='footer'>
	Developed by LM Visions - Layout design by Cextra
</div>

</body>
</html>
HTML;

return new Layout(
    key: 'cextralite',
    heading: $heading,
    header: $header,
    body_start: $body_start,
    top_menu: $top_menu,
    side_menu_start: $side_menu_start,
    village_menu_start: $village_menu_start,
    action_menu_header: $action_menu_header,
    staff_menu_header: $staff_menu_header,
    side_menu_end: $side_menu_end,
    login_menu: $login_menu,
    footer: $footer,
);
