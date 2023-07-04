<?php
session_start();

require "classes/System.php";
$system = new System();
$system->db->startTransaction();
$layout = $system->setLayoutByName("shadow_ribbon");

if(isset($_SESSION['user_id'])) {
    require_once 'classes.php';
    $player = User::loadFromId($system, $_SESSION['user_id']);
    $player->loadData();
    $layout = $system->setLayoutByName($player->layout);
}

$layout->renderBeforeContentHTML($system, $player ?? null, 'Terms');

?>

<table class='table'><tr><th>Terms of Service</th></tr>
<tr><td>
<!--START_EDIT-->

Shinobi-chronicles.com is a fan site: We did not create Naruto nor any of the characters and content in Naruto. While inspired by 
Naruto, the content of this site is fan-made and not meant to infringe upon any copyrights, it is simply here to further the 
continuing popularity of Japanese animation. In no event will shinobi-chronicles.com, 
its host, and any other companies and/or sites linked to shinobi-chronicles.com be liable to any party for any direct, indirect, 
special or other consequential damages for any use of this website, or on any other hyperlinked website, including, without limitation,
 any lost profits, loss of programs or other data on your information handling system or otherwise, even if we are expressly advised 
 of the possibility of such damages.<br />

<p>
Shinobi-chronicles.com accepts no responsibility for the actions of its members i.e. Self harm, vandalism, suicide, homicide, 
genocide, drug abuse, changes in sexual orientation, or bestiality. Shinobi-chronicles.com will not be held responsible and does not 
encourage any of the above actions or any other form of anti social behaviour. The staff of shinobi-chronicles.com reserve the 
right to issue bans and/or account deletion for rule infractions. Rule infractions will be determined at the discretion of the 
moderating staff.</p>

<p>Loans or transactions of real or in-game currency are between players. Staff take no responsibility for the 
completion of them. If a player loans real or in-game currency to another player, staff will not be responsible for ensuring the 
currency is returned.</p>

<p>Ancient Kunai(Premium credits) that have already been spent on in-game purchases of any kind or traded to another player cannot be 
	refunded. Staff are not responsible for lost shards or time on Forbidden Seals lost due to user bans.</p>
<br />
The Naruto series is created by and copyright Masashi Kishimoto and TV Tokyo, all rights reserved.
</td></tr></table>
<?php

$layout->renderAfterContentHTML($system, $player ?? null);
$system->db->commitTransaction();
