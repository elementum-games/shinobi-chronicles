<?php
/** @var System $system */

require_once 'layout/_common.php';
$heading = coreHeading($system->getCssFileLink('style/blue_scroll/layout.css')) .
<<<HTML
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
	<li><a id='topMenuOption-News' href='{$system->router->links['news']}'>News</a></li>
	<li><a id='topMenuOption-Discord' href='{$system->router->links['discord']}' target='_blank'>Discord</a></li>
	<li><a id='topMenuOption-Manual' href='{$system->router->base_url}manual.php'>Manual</a></li>
	<li><a id='topMenuOption-Github' href='https://github.com/elementum-games/shinobi-chronicles' target='_blank'>Github</a></li>
	<li><a id='topMenuOption-Rules' href='{$system->router->base_url}rules.php'>Rules</a></li>
	<li><a id='topMenuOption-Terms' href='{$system->router->base_url}terms.php'>Terms of Service</a></li>
	<li><a id='topMenuOption-Manual' href='{$system->router->base_url}support.php'>Support</a></li>
</ul>
HTML;

$side_menu_start = <<<HTML
	</div>
	<div id='sideMenu' class='sm-tmp-class [side-menu-location-status-class]'>
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
			<form action='{$system->router->base_url}' method='post'>
			<span>Username</span><br />
			<input type='text' name='user_name' /><br />
			<span>Password</span><br />
			<input type='password' name='password' /><br />
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
	</div>
	<div id='footer'>
		<p>Shinobi Chronicles v<!--[VERSION_NUMBER]--> &bull; Copyright &copy; LM Visions &bull; :<!--[PAGE_LOAD_TIME]-->:</p>
	</div>
</body>
</html>
HTML;

return new Layout(
    key: 'blue_scroll',
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
