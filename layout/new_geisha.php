<?php

require_once 'layout/_common.php';

$heading = coreHeading('style/geisha/geisha.css') .
<<<HTML
<body>
	<link rel='stylesheet' type='text/css' href='style/new_geisha/new_geisha.css' />
	<div id='container'>
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

		<br style='clear:both;margin:0px;padding:0px;' />
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

		<br style='clear:both;margin:0px;padding:0px;' />
HTML;

$footer = <<<HTML
	<div id="footer">
		<div class="footer-left"></div>
		<div class="footer-right">
			<div class="footer_text">
				Shinobi Chronicles v<!--[VERSION_NUMBER]--> &bull; Copyright &copy; LM Visions <a href="{$system->router->base_url}terms.php">Terms of Service</a>
				<br />
				<a href="https://www.vecteezy.com/free-png/stone">Stone PNG by Vecteezy</a>
			</div>
		</div>
    </div>
</body>
</html>
HTML;

$hotbarModule = "templates/hotbar.php";

$sidebarModule = "templates/sidebar.php";

$headerModule = "templates/header.php";

$topbarModule = "templates/topbar.php";

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
	hotbarModule: $hotbarModule,
	sidebarModule: $sidebarModule,
	headerModule: $headerModule,
	topbarModule: $topbarModule,
);
