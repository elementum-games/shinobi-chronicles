<?php

function getNewGeishaLayout(System $system, bool $enable_mobile_layout): Layout {
    $react_dev_tags = <<<HTML
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.development.js" crossorigin></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.development.js" crossorigin></script>
HTML;

    $react_prod_tags = <<<HTML
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js" crossorigin></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js" crossorigin></script>
HTML;

    if($system->isDevEnvironment()) {
        $extra_meta_tags = '<meta name="robots" content="noindex" />';
        $react_tags = $react_dev_tags;

        if($enable_mobile_layout && !isset($_SESSION['user_id'])) {
            $extra_meta_tags .= '<meta name="viewport" content="width=device-width, initial-scale=1" />';
        }
    }
    else {
        $extra_meta_tags = '';
        $react_tags = $react_prod_tags;
    }

    $heading = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Shinobi Chronicles RPG</title>

	<link rel='stylesheet' type='text/css' href='{$system->getCssFileLink("style/geisha/geisha.css")}' />
	<link rel='stylesheet' type='text/css' href='{$system->getCssFileLink("style/common.css")}' />
	<link rel='stylesheet' type='text/css' href='{$system->getCssFileLink("style/new_geisha/new_geisha.css")}' />
	<link rel='stylesheet' type='text/css' href='{$system->getCssFileLink("style/new_geisha/new_geisha_legacy.css")}' />
	<link rel="icon" href="images/icons/favicon.ico" type="image/x-icon" />
	<script type='text/javascript' src='./scripts/jquery-2.1.0.min.js'></script>
	<script type='text/javascript' src="./scripts/jquery-ui.js"></script>
	{$react_tags}
	<script type='text/javascript' src="./scripts/functions.js"></script>
	<script type='text/javascript' src="./scripts/timer.js"></script>
    <script type='text/javascript' src='./scripts/react-transition-group.js'></script>
	<script type='text/javascript' src='./scripts/luxon.min.js'></script>
	<script type='text/javascript' src="./scripts/prop-types.min.js"></script>
	<script type='text/javascript' src="./scripts/Recharts.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="naruto, rpg, online, game, anime, manga, mmorpg" />
	<meta name="description" content="Shinobi Chronicles: An online browser-based RPG inspired by the anime/manga Naruto." />
	{$extra_meta_tags}
</head>
<body>
HTML;


    $header = <<<HTML
		</div>
	</div>
HTML;

    $body_start = <<<HTML
<div id='content'>
		<div class='contentHeader'>
		[HEADER_TITLE]
		</div>
HTML;


    $top_menu = <<<HTML
<ul>
	<li><a href='{$system->router->links['news']}'>News</a></li>
	<li><a href='{$system->router->links['discord']}' target='_blank'>Discord</a></li>
	<li><a href='{$system->router->base_url}manual.php'>Manual</a></li>
	<li><a href='{$system->router->links['github']}'>GitHub</a></li>
	<li><a href='{$system->router->base_url}rules.php'>Rules</a></li>
	<li><a href='{$system->router->base_url}support.php'>Support</a></li>
</ul>
HTML;

    $side_menu_start = <<<HTML
	</div>
	<div id='sideMenu' class='sm-tmp-class [side-menu-location-status-class]'>

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

		<br style='clear:both;margin:0;padding:0;' />
HTML;

    $login_menu = <<<HTML
		</div>
	<div id='sideMenu'>
		<div id='notifications'><!--[NOTIFICATIONS]--></div>

	<ul class='menu'>
	<h2><p>Login</p></h2>
		<div id='login'>
			<form action='{$system->router->base_url}' method='post'>
			<span>Username</span><br />
			<input type='text' name='user_name' /><br />
			<span>Password</span><br />
			<input type='password' name='password' /><br />
			<span>Login Code</span><br />
			<!--CAPTCHA-->
			<input type='submit' name='login' value='Login' />
			</form>
			<p>
				<a class='link' style='font-size:16px;padding-right:18px;' href='{$system->router->base_url}register.php'>Create an account</a>
			</p>
		</div>
	</div>
	<div id='sideMenuBg'></div>

		<br style='clear:both;margin:0;padding:0;' />
HTML;

    $footer = <<<HTML
	<div id="footer">
		<div class="footer-left"></div>
		<div class="footer-right">
			<div class="footer_text">
				Shinobi Chronicles v<!--[VERSION_NUMBER]--> &bull; Copyright &copy; LM Visions
				<a href="{$system->router->base_url}terms.php">Terms of Service</a>
				<a href="https://www.vecteezy.com/free-png/stone">Stone PNG by Vecteezy</a>
			</div>
		</div>
    </div>
</body>
</html>
HTML;

    return new Layout(
        key: 'new_geisha',
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

}
