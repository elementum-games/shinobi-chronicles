<?php
if($_GET['password']) {
	echo sha1(str_rot13(sha1(trim($_GET['password']))));
}
?>