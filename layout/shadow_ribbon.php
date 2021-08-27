<?php
$heading = <<<HTML
<!doctype HTML public>
<html>
<head>
	<title>Shinobi Chronicles RPG</title>
	<link rel='stylesheet' type='text/css' href='./style/shadow_ribbon/layout.css' />
	<link rel="icon" href="images/icons/favicon.ico" type="image/x-icon" />
	<script type='text/javascript' src='http://code.jquery.com/jquery-2.1.0.min.js'></script>
	<script type='text/javascript' src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	<script type='text/javascript' src="./scripts/functions.js"></script>
	<script type='text/javascript' src="./scripts/timer.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="naruto, rpg, online, game, anime, manga, mmorpg" />
	<meta name="description" content="Shinobi Chronicles: An online browser-based RPG inspired by the anime/manga Naruto." />
	<script type='text/javascript'>
	$(document).ready(function(){
		if(typeof train_time !== 'undefined') {
			countdownTimer(train_time, 'trainingTimer');
		}
	});
	</script>
</head>
<body>
	<div id='header'>
		<img id='banner' src='./style/shadow_ribbon/images/new_banner.png' />	
		<div id='topMenu'>
HTML;
			
			
$header = <<<HTML
		</div>
	</div>
	
	<div id='container'>
HTML;

$body_start = <<<HTML
<div id='content'>
		<div class='contentHeader'>
		[HEADER_TITLE]
		</div>
HTML;


$top_menu = <<<HTML
<ul>
	<li><a href='$link'>News</a></li>
	<li><a href='http://shinobi-chronicles.com/forum/' target='_blank'>Forum</a></li>
	<li><a href='http://shinobi-chronicles.com/forum/viewtopic.php?f=4&t=360' target='_blank'>Manual</a></li>
	<li><a href='{$link}rules.php'>Rules</a></li>
	<li><a href='{$link}terms.php'>Terms of Service</a></li>
</ul>
HTML;

$side_menu_start = <<<HTML
	</div>
	<div id='sideMenu'>
		<div id='notifications'><!--[NOTIFICATIONS]--></div>
	
	<ul class='menu'>
	<h2><p>User Menu</p></h2>
	
	<li><a href='$link?id=1'>Profile</a></li>
	<li><a href='$link?id=2'>Inbox</a></li>
	<li><a href='$link?id=3'>Settings</a></li>
	<li><a href='$link?id=4'>Jutsu</a></li>
	<li><a href='$link?id=5'>Gear</a></li>
	<li><a href='$link?id=6'>Members</a></li>
	<li><a href='$link?id=7'>Chat</a></li>
HTML;

$village_menu = <<<HTML
	<h2><p>Village Menu</p></h2>
	<li><a href='$link?id=8'>Shop</a></li>
	<li><a href='$link?id=9'>Village HQ</a></li>
	<li><a href='$link?id=22'>Spar</a></li>
	<li><a href='$link?id=21'>Ancient Market</a></li>
HTML;

$action_menu_header = <<<HTML
	<h2><p>Activity Menu</p></h2>
HTML;

$staff_menu_header = <<<HTML
<h2><p>Staff Menu</p></h2>
HTML;

$side_menu_end = <<<HTML
		</ul>
		<div id='logout'>
			<a href='./?logout=1'>Logout</a>
			<p id='logoutTimer' style='margin-top:5px;'><!--LOGOUT_TIMER--></p>
		</div>
	</div>
	
		<div id='sideMenuBg'></div>
		
		<br style='clear:both;margin:0px;padding:0px;' />
HTML;
	
$login_menu = <<<HTML
		</div>
	<div id='sideMenu'>
		<div id='notifications'><!--[NOTIFICATIONS]--></div>
	
	<ul class='menu'>
	<h2><p>Login</p></h2>
		<div id='login'>
			<form action='$link' method='post'>
			<span>Username</span><br />
			<input type='text' name='user_name' /><br />
			<span>Password</span><br />
			<input type='password' name='password' /><br />
			<span>Login Code</span><br />
			<!--CAPTCHA-->
			<input type='submit' name='login' value='Login' />
			</form>
			<p>
				<a class='link' style='font-size:16px;padding-right:18px;' href='{$link}register.php'>Create an account</a>
			</p>
		</div>
	</div>
	<div id='sideMenuBg'></div>
		
		<br style='clear:both;margin:0px;padding:0px;' />
HTML;
	
$footer = <<<HTML
	</div>
	<div id='footer'>
		<p>Shinobi Chronicles v<!--[VERSION_NUMBER]--> &bull; Copyright &copy; LM Visions &bull; :<!--[PAGE_LOAD_TIME]-->:</p>
	</div>
</body>
</html>
HTML;
