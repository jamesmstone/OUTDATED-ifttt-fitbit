<?php
	require("load.php");
// unset cookies
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach ($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$name = trim($parts[0]);
			setcookie($name, '', time() - 1000);
			setcookie($name, '', time() - 1000, '/');
		}
	}

// Redirect to index.
	header('Location: ' . $iftttBlogUrl);
	exit;
?>