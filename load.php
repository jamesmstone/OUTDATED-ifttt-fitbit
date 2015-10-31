<?php
//load config
	require("config.php");

//database connect
	session_start();

	mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD) or die("MySQL Error: " . mysql_error());
	mysql_select_db($DB_NAME) or die("MySQL Error: " . mysql_error());

	/**
	 * Add subscription
	 *
	 * @throws FitBitException
	 *
	 * @param string $id   Subscription Id
	 * @param string $path Subscription resource path (beginning with slash). Omit to subscribe to all user updates.
	 *
	 * @return
	 */
	function addSubscription($id, $path = null, $subscriberId = null) {
		$headers = $this->getHeaders();
		$userHeaders = [];
		if ($subscriberId)
			$userHeaders['X-Fitbit-Subscriber-Id'] = $subscriberId;
		$headers = array_merge($headers, $userHeaders);


		if (isset($path))
			$path = '/' . $path;
		else
			$path = '';

		try {
			$this->oauth->fetch($this->baseApiUrl . "user/-" . $path . "/apiSubscriptions/" . $id . "." . $this->responseFormat, null, OAUTH_HTTP_METHOD_POST, $headers);
		} catch (Exception $E) {
		}

		$response = $this->oauth->getLastResponse();
		$responseInfo = $this->oauth->getLastResponseInfo();
		if (!strcmp($responseInfo['http_code'], '200') || !strcmp($responseInfo['http_code'], '201')) {
			$response = $this->parseResponse($response);

			if ($response)
				return $response;
			else
				throw new FitBitException($responseInfo['http_code'], 'Fitbit request failed. Code: ' . $responseInfo['http_code']);
		} else {
			throw new FitBitException($responseInfo['http_code'], 'Fitbit request failed. Code: ' . $responseInfo['http_code']);
		}
	}


	/**
	 * Delete user subscription
	 *
	 * @throws FitBitException
	 *
	 * @param string $id   Subscription Id
	 * @param string $path Subscription resource path (beginning with slash)
	 *
	 * @return bool
	 */
	function deleteSubscription($id, $path = null) {
		$headers = $this->getHeaders();
		if (isset($path))
			$path = '/' . $path;
		else
			$path = '';

		try {
			$this->oauth->fetch($this->baseApiUrl . "user/-" . $path . "/apiSubscriptions/" . $id . ".xml", null, OAUTH_HTTP_METHOD_DELETE, $headers);
		} catch (Exception $E) {
		}

		$responseInfo = $this->oauth->getLastResponseInfo();
		if (!strcmp($responseInfo['http_code'], '204')) {
			return true;
		} else {
			throw new FitBitException($responseInfo['http_code'], 'Fitbit request failed. Code: ' . $responseInfo['http_code']);
		}
	}


	/**
	 * Get list of user's subscriptions for this application
	 *
	 * @throws FitBitException
	 * @return
	 */
	function getSubscriptions() {
		$headers = $this->getHeaders();

		try {
			$this->oauth->fetch($this->baseApiUrl . "user/-/apiSubscriptions." . $this->responseFormat, null, OAUTH_HTTP_METHOD_GET, $headers);
		} catch (Exception $E) {
		}
		$response = $this->oauth->getLastResponse();
		$responseInfo = $this->oauth->getLastResponseInfo();
		if (!strcmp($responseInfo['http_code'], '200')) {
			$response = $this->parseResponse($response);

			if ($response)
				return $response;
			else
				throw new FitBitException($responseInfo['http_code'], 'Fitbit request failed. Code: ' . $responseInfo['http_code']);
		} else {
			throw new FitBitException($responseInfo['http_code'], 'Fitbit request failed. Code: ' . $responseInfo['http_code']);
		}
	}

	//August 23, 2012 at 11:01PM

	function dateconverter($date = "date") {
		if ($date == "date") {
			$date = date("F d, Y") . " at " . date("g:iA");
		}
		$date = explode(" at ", $date);
		$date = explode(" ", $date[0]);
		$date[1] = rtrim($date[1], ",");
		$date = strtotime($date[1] . " " . $date[0] . " " . $date[2]);
		$date = date("Y-m-d", $date);
		return $date;
	}

	function randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$alphabet = "hNF1yc5qCbW6TgufUmGxOon0M4LDZ82ASzBars9jewEiRk7QHJIYplKXPd3t";
		$pass = []; //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 35; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}

	function checklogin() {
		if (!isset($_SESSION['secret']) && !isset($_SESSION['token']) && $_SESSION['state'] !== 2) {
			// Redirect to the login.
			header('Location: ' . $iftttBlogUrl . 'login.php');
			exit;
		}
	}

	function checkIFTTTlogin($username, $password) {
		$checklogin = mysql_query("SELECT * FROM users WHERE IFTTTUsername = '" . mysql_real_escape_string($username) . "' AND IFTTTPassword = '" . mysql_real_escape_string($password) . "'");

		if (mysql_num_rows($checklogin) == 1) {
			$row = mysql_fetch_array($checklogin);
			return $row;
		} else {
			return "error";
		}
	}

	function check_input($value) {
// Stripslashes
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
// Quote if not a number
		if (!is_numeric($value)) {
			$value = "'" . mysql_real_escape_string($value) . "'";
		}
		return $value;
	}

?>