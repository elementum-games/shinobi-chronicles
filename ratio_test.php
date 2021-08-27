<?php

$base_skill = 300;
$base_regen = 10;
$base_ratio = 0.03;

$max_skill = 9500;

$ratio = $base_ratio;

for($skill = $base_skill; $skill < $max_skill; $skill += 200) {
	echo "$skill skill x $ratio -> " . round($skill * $ratio, 4) . " regen<br />";
}

?>