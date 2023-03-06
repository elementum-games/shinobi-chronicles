<?php


/** @var System $system */

$extra_meta_tags = '';
if($system->environment == System::ENVIRONMENT_DEV) {
    $extra_meta_tags = '<meta name="robots" content="noindex" />';
}

/** @noinspection HtmlUnknownTarget */
$heading = <<<HTML
<!doctype HTML public>
<html lang="en">
<head>
	<title>Shinobi Chronicles RPG</title>
	<link rel='stylesheet' type='text/css' href='./style/rainbow_road/layout.css' />
	<link rel="icon" href="images/icons/favicon.ico" type="image/x-icon" />
	<script type='text/javascript' src='./scripts/jquery-2.1.0.min.js'></script>
	<script type='text/javascript' src="./scripts/jquery-ui.js"></script>
	<script type='text/javascript' src="./scripts/functions.js"></script>
	<script type='text/javascript' src="./scripts/timer.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="naruto, rpg, online, game, anime, manga, mmorpg" />
	<meta name="description" content="Shinobi Chronicles: An online browser-based RPG inspired by the anime/manga Naruto." />
	$extra_meta_tags
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
		<img id='banner' src='./style/rainbow_road/images/new_banner.png' />
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
	<li><a href='{$system->link}'>News</a></li>
	<li><a href='{$system->links['discord']}' target='_blank'>Discord</a></li>
	<li><a href='{$system->link}manual.php'>Manual</a></li>
	<li><a href='{$system->links['github']}' target='_blank'>GitHub</a></li>
	<li><a href='{$system->link}rules.php'>Rules</a></li>
	<li><a href='{$system->link}terms.php'>Terms</a></li>
	<li><a href='{$system->link}support.php'>Support</a></li>
</ul>
HTML;

$side_menu_start = <<<HTML
	</div>
	<div id='sideMenu' class='sm-tmp-class {$side_menu_location_status_class}'>
		<div id='notifications'><!--[NOTIFICATIONS]--></div>
	
	<ul class='menu'>
	<h2><p>User Menu</p></h2>
HTML;

$village_menu_start = <<<HTML
	<h2><p>Village Menu</p></h2>
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
			<form action='{$system->link}' method='post'>
			<span>Username</span><br />
			<input type='text' name='user_name' /><br />
			<span>Password</span><br />
			<input type='password' name='password' /><br />
			<input type='submit' name='login' value='Login' />
			</form>
			<p>
				<a class='link' style='font-size:16px;padding-right:18px;' href='{$system->link}register.php'>Create an account</a>
			</p>
		</div>
	</div>
	<div id='sideMenuBg'></div>
		
		<br style='clear:both;margin:0px;padding:0px;' />
HTML;
	
$footer = <<<HTML
	</div>
	<div id='footer'>
		<p>
		    Shinobi Chronicles v<!--[VERSION_NUMBER]--> &bull; Copyright &copy; LM Visions &bull; :<!--[PAGE_LOAD_TIME]-->: &bull; Layout by Uraraka
		</p>
	</div>
</body>
</html>
HTML;
