<?php
	require("load.php");

	if ($_SESSION['state'] == 2) {
		// Redirect to the settings.
		header('Location: ' . $iftttBlogUrl . 'settings.php');
		exit;
	} else {
		// Start session to store the information between calls
		if (!isset($_SESSION)) {
			session_start();
		}

		// In state=1 the next request should include an oauth_token.
		// If it doesn't go back to 0
		if (!isset($_GET['oauth_token']) && $_SESSION['state'] == 1) $_SESSION['state'] = 0;

		try {
			// Create OAuth object
			$oauth = new OAuth($conskey, $conssec, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);

			// Enable ouath debug (should be disabled in production)
			$oauth->enableDebug();

			if ($_SESSION['state'] == 0) {
				// Getting request token. Callback URL is the Absolute URL to which the server provder will redirect the User back when the obtaining user authorization step is completed.
				$request_token_info = $oauth->getRequestToken($req_url, $callbackUrl);

				// Storing key and state in a session.
				$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
				$_SESSION['state'] = 1;

				// Redirect to the authorization.
				header('Location: ' . $authurl . '?oauth_token=' . $request_token_info['oauth_token']);
				exit;
			} else if ($_SESSION['state'] == 1) {
				// Authorized. Getting access token and secret
				$oauth->setToken($_GET['oauth_token'], $_SESSION['secret']);
				$access_token_info = $oauth->getAccessToken($acc_url);

				// Storing key and state in a session.
				$_SESSION['state'] = 2;
				$_SESSION['token'] = $access_token_info['oauth_token'];
				$_SESSION['secret'] = $access_token_info['oauth_token_secret'];
			}

			// Setting asccess token to the OAuth object
			$oauth->setToken($_SESSION['token'], $_SESSION['secret']);

			// Performing API call
			$oauth->fetch($apiCall);

			// Getting last response
			$response = $oauth->getLastResponse();

			// Initializing the simple_xml object using API response
			$xml = simplexml_load_string($response);
		} catch (OAuthException $E) {
			print_r($E);
		}
	}
?>