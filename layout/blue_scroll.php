<?php


/** @var System $system */

/** @noinspection HtmlUnknownTarget */
$heading = <<<HTML
<!doctype HTML public>
<html lang="en">
<head>
	<title>Shinobi Chronicles RPG</title>
	<link rel='stylesheet' type='text/css' href='./style/blue_scroll/layout.css' />
	<link rel="icon" href="images/icons/favicon.ico" type="image/x-icon" />
	<script type='text/javascript' src='./scripts/jquery-2.1.0.min.js'></script>
	<script type='text/javascript' src="./scripts/jquery-ui.js"></script>
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
        <div id="mainBanner">
            <img src="./style/blue_scroll/images/banner.png">
        </div>	
		<div id='topMenu'>
            <div id='topMenuWrapper'>
HTML;
            
            
$header = <<<HTML
		    </div>
			
			<div id="systemTimeWrapper">
				<div id="systemTime"></div>
				<script type="text/javascript">
					currentTimeStringUTC({$system->timezoneOffset}, 'systemTime');
					setInterval(() => {
						currentTimeStringUTC({$system->timezoneOffset}, 'systemTime');
					}, 1000);
				</script>
			</div>
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
	<li><a id='topMenuOption-News' href='{$system->link}'>News</a></li>
	<li><a id='topMenuOption-Discord' href='https://discord.gg/Kx52dbXEf3' target='_blank'>Discord</a></li>
	<li><a id='topMenuOption-Manual' href='{$system->link}manual.php'>Manual</a></li>
	<li><a id='topMenuOption-Rules' href='{$system->link}rules.php'>Rules</a></li>
	<li><a id='topMenuOption-Terms' href='{$system->link}terms.php'>Terms of Service</a></li>
</ul>
HTML;

$side_menu_start = <<<HTML
	</div>
	<div id='sideMenu'>
		<div id='notifications'><!--[NOTIFICATIONS]--></div>
	
	<ul class='menu'>
	<h2>User Menu</h2>
HTML;

$village_menu_start = <<<HTML
	<h2>Village Menu</h2>
HTML;

$action_menu_header = <<<HTML
	<h2>Activity Menu</h2>
HTML;

$staff_menu_header = <<<HTML
<h2>Staff Menu</h2>
HTML;

$side_menu_end = <<<HTML
		</ul>
		<div id='logout'>
			<a id='sideMenuOption-Logout' href='./?logout=1'>Logout</a>
			<p id='logoutTimer' style='margin-top:5px;'><!--LOGOUT_TIMER--></p>
		</div>
	</div>
		
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
		<p>Shinobi Chronicles v<!--[VERSION_NUMBER]--> &bull; Copyright &copy; LM Visions &bull; :<!--[PAGE_LOAD_TIME]-->:</p>
	</div>
</body>
</html>
HTML;