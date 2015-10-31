<?php
	require_once(dirname(__FILE__) . '/load.php');
	$username = $_GET['username'];
	$password = $_GET['password'];
	echo checkIFTTTlogin($username, $password);
?>
	<br>
<?php
	print_r(checkIFTTTlogin($username, $password));
?>