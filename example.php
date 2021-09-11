<?php
session_start();

require("classes.php");

$user = new TestUser(1);
$user->loadData();

// Form processing
if($_GET['reset']) {
	$user->reset();
	$user = new TestUser(1);
	$user->loadData();
}

if($_GET['decrement']) {
	$user->money -= 10;
	if($user->money < 0) {
		$user->money = 0;
	}
	$user->updateData();
}
if($_GET['increment']) {
	$user->money += 10;
	$user->updateData();
}

// Output
echo "Name: " . $user->user_name . "<br />
	Health: " . $user->health . "<br />
	Max health: " . $user->max_health . "<br />
	Money: " . $user->money . "<br />
	<br />
	<a href='./test.php?decrement=1' style='text-decoration:none;'><button>Decrement</button></a>
	<a href='./test.php?increment=1' style='text-decoration:none;'><button>Increment</button></a>
	<a href='./test.php?reset=1' style='text-decoration:none;'><button>Reset</button></a>
	<a href='./test.php' style='text-decoration:none;'><button>Refresh</button></a>
	
	";



?>