<?php
	require_once(dirname(__FILE__) . '/load.php');

	require_once(dirname(__FILE__) . '/config.php');
	require_once(dirname(__FILE__) . '/log.php');

	require 'fitbitphp.php';


	error_reporting(-1);
	ini_set('display_errors', 0);
	$request_body = file_get_contents('php://input');
	$xml = simplexml_load_string($request_body);

	__log("PHP://input xml: " . $xml);
	__log("PHP://input: " . $request_body);
	__log("$xml->methodName: " . $xml->methodName);


	if (!$xml)
		die("Ooops! No XML Payload: You possibly want to <a href=\"index.html\">read the documentation!</a>");

	switch ($xml->methodName) {

		//wordpress blog verification
		case 'mt.supportedMethods':
			success('metaWeblog.getRecentPosts');
			break;
		//first authentication request from ifttt
		case 'metaWeblog.getRecentPosts':
			__log("metaWeblog.getRecentPosts");

			//send a blank blog response
			//this also makes sure that the channel is never triggered
			$obj = new stdClass;
			//get the parameters from xml
			$obj->user = (string)$xml->params->param[1]->value->string;
			$obj->pass = (string)$xml->params->param[2]->value->string;
			if (checkIFTTTlogin($obj->user, $obj->pass) == "error") {
				failure(401);
			} else {
				success('<array><data></data></array>');
				break;
			}
		case 'metaWeblog.newPost':
			__log("metaWeblog.newPost");


			//@see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.newPost
			$obj = new stdClass;
			//get the parameters from xml
			$obj->user = (string)$xml->params->param[1]->value->string;
			$obj->pass = (string)$xml->params->param[2]->value->string;
			__log("Username: " . $obj->user);
			__log("Password: " . $obj->pass);

			$user = checkIFTTTlogin($obj->user, $obj->pass);
			if ($user == "error") {
				__log("checkIFTTTlogin:401");
				failure(401);
			} else {

				//@see content in the wordpress docs
				$content = $xml->params->param[3]->value->struct->member;
				foreach ($content as $data) {

					switch ((string)$data->name) {
						//we use the tags field for providing webhook URL
						case 'mt_keywords':
							$url = $data->xpath('value/array/data/value/string');
							$url = (string)$url[0];
							$body = $url;
							break;

						//the passed categories are parsed into an array
						case 'categories':
							$categories = [];
							foreach ($data->xpath('value/array/data/value/string') as $cat)
								array_push($categories, (string)$cat);
							$obj->categories = $categories;
							break;

						//this is used for title/description
						default:
							$obj->{$data->name} = (string)$data->value->string;
					}
				}


				//authenticate fitbit connection
				$fitbit = new FitBitPHP($conskey, $conssec);
				$fitbit->setOAuthDetails($user['FitBitToken'], $user['FitBitSecret']);

				__log("$xml->params->param[3]->value->struct->member[0]->value->string: " . $xml->params->param[3]->value->struct->member[0]->value->string);
				$body = $xml->params->param[3]->value->struct->member[1]->value->string;
				switch ($xml->params->param[3]->value->struct->member[0]->value->string) {
					case 'logBody':
						list($date, $weight, $fat, $bicep, $calf, $chest, $forearm, $hips, $neck, $thigh, $waist) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logWeight($weight, $date);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logActivity':
						list($date, $activityId, $duration, $calories, $distance, $distanceUnit, $activityName) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logActivity($date, $activityId, $duration, $calories, $distance, $distanceUnit, $activityName);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logFood':
						list($date, $foodId, $mealTypeId, $unitId, $amount, $foodName, $calories, $brandName, $nutrition) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logFood($date, $foodId, $mealTypeId, $unitId, $amount, $foodName, $calories, $brandName, $nutrition);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logWater':
						list($date, $amount, $waterUnit) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logWater($date, $amount, $waterUnit);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logSleep':
						list($date, $duration) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logWeight($date, $duration);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logHeartRate':
						list($date, $tracker, $heartRate, $time) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logHeartRate($date, $tracker, $heartRate, $time);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logBloodPressure':
						list($date, $systolic, $diastolic, $time) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logBloodPressure($date, $systolic, $diastolic, $time);
						success('<string>' . $response->status_code . '</string>');
						break;
					case 'logGlucose':
						list($date, $tracker, $glucose, $hba1c, $time) = explode(",", $body);
						$date = dateconverter($date);
						$xml = $fitbit->logGlucose($date, $tracker, $glucose, $hba1c, $time);
						success('<string>' . $response->status_code . '</string>');
						break;
					default:
						//__log("$obj->{$data->name}: Failure 404");
						failure(404);
						break;
				}
				/*
				//Make the webrequest
				//Only if we have a valid url
				if (valid_url($url, true)) {
					// Load Requests Library
					include('requests/Requests.php');
					Requests::register_autoloader();

					$headers  = array(
						'Content-Type' => 'application/json'
					);
					$response = Requests::post($url, $headers, json_encode($obj));

					if ($response->success)
						success('<string>' . $response->status_code . '</string>');
					else
						failure($response->status_code);
				} else {
					//since the url was invalid, we return 400 (Bad Request)
					failure(400);
				}*/
			}
	}


	/** Copied from wordpress */
	function success($innerXML) {


		$xml = <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <params>
	<param>
	  <value>
	  $innerXML
	  </value>
	</param>
  </params>
</methodResponse>

EOD;
		output($xml);
	}

	function output($xml) {
		$length = strlen($xml);
		header('Connection: close');
		header('Content-Length: ' . $length);
		header('Content-Type: text/xml');
		header('Date: ' . date('r'));
		echo $xml;
		exit;
	}

	function failure($status) {


		$xml = <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <fault>
	<value>
	  <struct>
		<member>
		  <name>faultCode</name>
		  <value><int>$status</int></value>
		</member>
		<member>
		  <name>faultString</name>
		  <value><string>Request was not successful.</string></value>
		</member>
	  </struct>
	</value>
  </fault>
</methodResponse>

EOD;
		output($xml);
	}

	/** Used from drupal */
	function valid_url($url, $absolute = false) {
		if ($absolute) {
			return (bool)preg_match("
	  /^                                                      # Start at the beginning of the text
	  (?:https?):\/\/                                # Look for ftp, http, https or feed schemes
	  (?:                                                     # Userinfo (optional) which is typically
		(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*      # a username or a username and password
		(?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@          # combination
	  )?
	  (?:
		(?:[a-z0-9\-\.]|%[0-9a-f]{2})+                        # A domain name or a IPv4 address
		|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\])         # or a well formed IPv6 address
	  )
	  (?::[0-9]+)?                                            # Server port number (optional)
	  (?:[\/|\?]
		(?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})   # The path and query (optional)
	  *)?
	$/xi", $url);
		} else {
			return (bool)preg_match("/^(?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})+$/i", $url);
		}
	}

?>