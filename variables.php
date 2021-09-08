<?php
/* 
File: 		variables.php
Coder:		Levi Meahan
Created:	02/21/2012
Revised:	04/15/2014
Purpose:	Store global variables such as master link, settings for script to use
*/

$ENVIRONMENT = 'prod';

// Update for different website
$dev_link = "http://localhost/";
$prod_link = "https://shinobichronicles.com/";

/** @var string $link root link to webpage */
if($ENVIRONMENT == 'dev') {
	$link = $dev_link;
}
else {
	$link = $prod_link;
}

// Master on/off switch
$SC_OPEN = true;

// Training boost switches
$TRAIN_BOOST = 0; // Extra points per training, 0 for none
$LONG_TRAIN_BOOST = 0; // Extra points per long training, 0 for none

// Links for pages to be linked to from other pages
$members_link = $link . '?id=6';
$mod_link = $link . '?id=16';
$admin_link = $link . '?id=17';
$report_link = $link . '?id=18';
$battle_link = $link . '?id=19';
$spar_link = $link . '?id=22';
$mission_link = $link . '?id=23';

// Staff levels
$SC_MODERATOR = 1;
$SC_HEAD_MODERATOR = 2;
$SC_ADMINISTRATOR = 3;
$SC_HEAD_ADMINISTRATOR = 4;

//Chat variables
$CHAT_MAX_POST_LENGTH = 350;
$SC_STAFF_COLORS = array(
	$SC_MODERATOR => array(
		'staffBanner' => "moderator",
		'staffColor' => "009020",
		'pm_class' => 'moderator'
	),
	$SC_HEAD_MODERATOR => array(
		'staffBanner' => "head moderator",
		'staffColor' => "0090A0",
		'pm_class' => 'headModerator'
	),
	$SC_ADMINISTRATOR => array(
		'staffBanner' => "administrator",
		'staffColor' => "A00000",
		'pm_class' => 'administrator'
	),
	$SC_HEAD_ADMINISTRATOR => array(
		'staffBanner' => "head administrator",
		'staffColor' => "A00000",
		'pm_class' => 'administrator'
	)
);

// Default layout
$DEFAULT_LAYOUT = 'shadow_ribbon';
$VERSION_NUMBER = '0.8.0';

// Map size
$MAP_SIZE_X = 18;
$MAP_SIZE_Y = 12;

// Misc stuff
$SC_MAX_RANK = 3;
