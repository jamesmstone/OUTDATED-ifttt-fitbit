<?php //variables

//Database
	if (isset($_SERVER['PLATFORM']) && $_SERVER['PLATFORM'] == 'PAGODABOX') {
		$DB_NAME = $_SERVER['DB1_NAME'];
		$DB_USER = $_SERVER['DB1_USER'];
		$DB_PASSWORD = $_SERVER['DB1_PASS'];
		$DB_HOST = $_SERVER['DB1_HOST'] . ':' . $_SERVER['DB1_PORT'];
	} else {
		$DB_NAME = 'fitbit';
		$DB_USER = 'root';
		$DB_PASSWORD = '';
		$DB_HOST = 'localhost';
	}


//fitbit api
	// Base URL
	$baseUrl = 'https://api.fitbit.com';

	// Base API URL
	$baseAPIUrl = 'https://api.fitbit.com/1/';

	// Request token path
	$req_url = $baseUrl . '/oauth/request_token';

	// Authorization path
	$authurl = $baseUrl . '/oauth/authorize';

	// Access token path
	$acc_url = $baseUrl . '/oauth/access_token';

	// Consumer key
	if (isset($_SERVER['PLATFORM']) && $_SERVER['PLATFORM'] == 'PAGODABOX') {
		$conskey = $_SERVER['FITBITCONSKEY'];
	} else {
		$conskey = '';//Consumer key here
	}

	// Consumer secret
	if (isset($_SERVER['PLATFORM']) && $_SERVER['PLATFORM'] == 'PAGODABOX') {
		$conssec = $_SERVER['FITBITCONSSEC'];
	} else {
		$conssec = '';//Consumer secret here
	}

//IFTTT
	//IFTTT Blog URL
	$iftttBlogUrl = 'http://ifttt.jamesstone.com.au/fitbit/';

//xmlrpc


	// Debug options
	$DEBUG = true;
?>