<?php

/**
 * @var System $system
 */
require __DIR__ . "/../_authenticate_admin.php";

$jutsu_power = 5;
$jutsu_type = 'ninjutsu';

$player1_jutsu_type = $jutsu_type;
$player2_jutsu_type = $jutsu_type;

// Display form
?>


<a href='/admin/combat_simulator/vs.php'>VS</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a href='/admin/combat_simulator/rank_curve.php'>Damage/Level Curve</a>
&nbsp;&nbsp;|&nbsp;&nbsp;
<a href='/admin/combat_simulator/speed_graph.php'>Speed Graph</a>
<br />




