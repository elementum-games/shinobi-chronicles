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
$heading = coreHeading('./style/classic_blue/style.css') . <<<HTML
<body>
	<div id='container'>
		
		<div id='menu'>
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
		<div class='header contentHeader'>
		[HEADER_TITLE]
		</div>
HTML;

$top_menu = <<<HTML
<ul class='topMenu'>
	<li><a href='{$system->link}'>News</a></li>
	<li><a href='{$system->links['discord']}' target='_blank'>Discord</a></li>
	<li><a href='{$system->link}manual.php'>Manual</a></li>
	<li><a href='{$system->links['github']}'>GitHub</a></li>
	<li><a href='{$system->link}rules.php'>Rules</a></li>
	<li><a href='{$system->link}terms.php'>Terms of Service</a></li>
	<li><a href='{$system->link}support.php'>Support</a></li>
</ul>
HTML;

$side_menu_start = <<<HTML
	</div>
</div>
<div id='sidebar'>
	<div class='header'>
	Menu
	</div>
	
	<ul class='menu'>
HTML;

$village_menu_start = <<<HTML
	<hr />
HTML;

$action_menu_header = <<<HTML
	<hr />
HTML;

$staff_menu_header = <<<HTML
<hr />
HTML;

$side_menu_end = <<<HTML
		<br style='margin:0;' />
		</ul>
		<br style='margin:0;' />
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
HTML;
	
$footer = <<<HTML
	</div>
	<div id='footer'>
		Developed by LM Visions - Layout design by Fenrir
	</div>
</body>
</html>
HTML;


