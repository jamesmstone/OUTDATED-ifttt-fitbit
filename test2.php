<?php
	$body = $_GET['body'];

	list($weight, $date) = explode(",", $body);


	require_once(dirname(__FILE__) . '/load.php');
	require 'fitbitphp.php';

	$fitbit = new FitBitPHP($conskey, $conssec);

	$fitbit->setOAuthDetails('d3f4df585445526bf1ade9c8242bb8fd', '2c9313b5bff62c76a549fe3cd5e458b3'); //WONT WORK NEED TO FILL IN
	$xml = $fitbit->logWeight($weight, $date);

	print_r($xml);
?>