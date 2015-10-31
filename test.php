<?php
	require("load.php");

	// Fitbit API call (get activities for specified date)
	$apiCall = $baseAPIUrl . "user/-/profile.xml";
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
?><!--                                                                                     
     #                                                                                        #     
      #                           ;                                                          #      
       #                          ;                                                         #       
        #                         ;                                                        #        
         #                        ;    ###   # #######    L###   ,###                     #         
          #                       ;   #   #  #    #   #  #    #  ,                       #          
           #                      ;       #  #    #   #  #    #                         #           
            #                     ;       #  #    #   #  ######  ##                    #            
             #                    ;   #####  #    #   #  #          #                 #             
              #                   ;   #   #  #    #   #  #                           #              
               #                  ;   #   #  #    #   G  ;          #               #
                #              ##;    # ##  #    #   #   :###   ###               #                
                 #                                                                #                 
                  #                                                              #                  
                   #                           iEDWDf                           #                   
                    #                     ################                     #                    
                     #                 ####              E###                 #                     
                      #             .###                    G##f             #                      
                       #           ##;                         ##,          #                       
                        #        ##f                            .##        #                        
                         #      ##                                ##;     #                         
                          #   j#t                                   ##   #                          
                           # ##                                      ## #                           
                            ##                                        ##                            
                           W##                                        ###                           
                          t#  #                                      #  ##                          
                          #    #                                    #    #t                         
                         #f     #                                  #      #                         
                        ##       #                                #       W#                        
                        #         #                              #         #f                       
                       #G          #                            #           #                       
                       #            #                          #            #W                      
                      #f             #                        #              #                      
                      #               #                      #               #t                     
                     ##                #                    #                ,#                     
                     #                  #                  #                  #                     
                     #                   #                #                   #f                    
                    W#                    #              #                    t#                    
                    #:                     #            #                      #                    
                    #                       #          #                       #                    
                    #                        #        #                        #                    
                    #                         #      #                         #f                   
                    #                          #    #                          ##                   
                   ;#                           #  #                           D#                   
                   f#                            ##                            f#                   
                   f#                            ##                            f#                   
                   t#                           #  #                           E#                   
                    #                          #    #                          ##                   
                    #                         #      #                         #f                   
                    #                        #        #                        #                    
                    #                       #          #                       #                    
                    #.                     #            #                      #                    
                    ##                    #              #                    i#                    
                     #                   #                #                   #L                    
                     #                  #                  #                  #                     
                     ##                #                    #                .#                     
                      #               #                      #               #j                     
                      #t             #                        #              #                      
                      .#            #                          #            ##                      
                       #f          #                            #           #                       
                        #         #                              #         #L                       
                        ##       #                                #       E#                        
                         #;     #                                  #      #                         
                          #    #                                    #    #L                         
                          G#  #                                      #  ##                          
                           ###                                        ###                           
                            ##                                        ##                            
                           # ##                                      ## #                           
                          #   E#,                                   ##   #                          
                         #      ##                                K#f     #                         
                        #        ##;                             ##        #                        
                       #           ##.                         ##f          #                       
                      #             t##G                    i##W             #                      
                     #                 ###E              f###;                #                     
                    #                    :################t                    #                    
                   #                          ,D####D;                          #                   
                  #                                                              #                  
                 #                                                                #                 
                #                                                                  #                
               #                                                                    #               
              #                  .####   #                                           #              
             #                   #       #                                            #             
            #                    ;       #                                             #            
           #                     #      ####   ####   #####    ####                     #           
          #                      .#t     #    #    #  #    #  #    #                     #          
         #                          ##   #   :     #  #    #  #    #                      #         
        #                             #  #   #     #  #    #  ######                       #        
       #                              #  #   #     #  #    #  #                             #       
      #                               W  #    :    #  #    #  #                              #      
     #                          ##   #   #    #   t:  #    #  #   #                           #     
    #                            ###t     #i   :##    #    #   ###:                            # 
 -->
<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Meta, title, CSS, favicons, etc. -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Settings - Fitbit IFTTT</title>
	<!-- Bootstrap core CSS -->
	<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- My CSS -->
	<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
	<!-- Load Lato Font -->
	<link href="style.css" rel="stylesheet" type="text/css">
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<a class="sr-only" href="#content">Skip to main content</a>
<!-- Events master nav -->
<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
	<div class="container">
		<div class="navbar-header">
			<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a href="http://www.jamesstone.com.au/" class="navbar-brand">James Stone</a>
		</div>
		<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
			<ul class="nav navbar-nav">
				<li>
					<a href="http://blog.jamesstone.com.au">Blog</a>
				</li>
				<li class="active">
					<a href="#">IFTTT Fitbit Channel</a>
				</li>
				<li>
			</ul>
		</nav>
	</div>
</header>
<!-- Docs page layout -->
<div class="header" id="content">
	<div class="container">
		<h1>Fitbit IFTTT Channel</h1>

		<p>
			A 3rd Party IFTTT Fitbit Channel
		</p>

		<div id="carbonads-container">
			<div class="carbonad">
				<div id="azcarbon">
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="col-md-9" role="main">
			<!-- Details
  ================================================== -->
			<div class="bs-docs-section">
				<div class="page-header">
					<h1 id="Setup">IFTTT Wordpress Details</h1>
				</div>
				<p>
					<?php
						// add user
						$checkfitbitid = mysql_query("SELECT * FROM users WHERE FitbitID = '" . $xml->user->encodedId . "'");

						if (mysql_num_rows($checkfitbitid) == 1) {
							$UsernameQuery = mysql_query("SELECT `IFTTTUsername` FROM `users` WHERE `FitbitID` LIKE '" . $xml->user->encodedId . "'");
							$PasswordQuery = mysql_query("SELECT `IFTTTPassword` FROM `users` WHERE `FitbitID` LIKE '" . $xml->user->encodedId . "'");
							echo "<h1>Welcome Back " . $xml->user->displayName . "</h1>";
							echo "<p>Below are your IFTTT wordpress details:</p>";
							echo "<p>Blog URL:<code>" . $iftttBlogUrl . "</code></p>";
							echo "<p>Username:<code>" . mysql_result($UsernameQuery, 0) . "</code></p>";
							echo "<p>Password:<code>" . mysql_result($PasswordQuery, 0) . "</code></p>";
							echo '<p><a href="' . $iftttBlogUrl . '/logout.php">Logout</a></p>';
						} else {

							$IFTTTPassword = randomPassword();
							while (true) {
								$IFTTTUsername = randomPassword();
								$IFTTTUsernameCheckQuery = mysql_query("SELECT `IFTTTUsername` FROM `users` WHERE `IFTTTUsername` = '" . $IFTTTUsername . "'");
								if (mysql_num_rows($IFTTTUsernameCheckQuery) < 1) {
									break;
								}
							}

							$registerquery = mysql_query("INSERT INTO users (`FitbitID`, `FitbitName`, `FitbitAvatar`, `IFTTTUsername`, `IFTTTPassword`,`FitBitToken`,`FitBitSecret`) VALUES('" . $xml->user->encodedId . "', '" . $xml->user->displayName . "','" . $xml->user->avatar . "','" . $IFTTTUsername . "', '" . $_SESSION['token'] . "', '" . $_SESSION['secret'] . "')");
							if ($registerquery) {
								echo "<h1>Welcome " . $xml->user->displayName . "</h1>";
								echo "<p>Your account was successfully created.</p>";
								echo "<p>Below are your IFTTT wordpress details:</p>";
								echo "<p>Blog URL:<code>" . $iftttBlogUrl . "</code></p>";
								echo "<p>Username:<code>" . $IFTTTUsername . "</code></p>";
								echo "<p>Password:<code>" . $IFTTTPassword . "</code></p>";
								echo '<p><a href="' . $iftttBlogUrl . '/logout.php">Logout</a></p>';

								//create table for user activities
								mysql_query("CREATE TABLE IF NOT EXISTS `" . $xml->user->encodedId . "Activities` (
							`activityId` int(11) NOT NULL,
							`calories` int(11) NOT NULL,
							`description` varchar(300) NOT NULL,
							`distance` int(11) NOT NULL,
							`duration` int(11) NOT NULL,
							`name` varchar(100) NOT NULL,
							PRIMARY KEY (`activityId`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

								//subscribe to user activities
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
								addSubscription($xml->user->encodedId, "/activities", $subscriberId = null);
								echo getSubscriptions();

							} else {
								echo "<h1>Error 1</h1>";
								echo "<p>Current number of beta users maxed out - sorry</p>";
								echo mysql_error();
							}
						}

					?>

				</p>

				<p><a href="index.html">Back</a> to documentation </p>

			</div>
		</div>
		<div class="col-md-3">
			<!--Side Menu
================================================== -->
			<div class="bs-sidebar hidden-print" role="complementary">
				<ul class="nav bs-sidenav">
					<li><a href="#">Details</a></li>
					<li><a href="index.html">Back</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="application.js"></script>
<script>
	//Filter
	$(function () {
		function lastfm(event) {
			$(".lastfm").animate({
				opacity: "toggle",
				height: "toggle"
			}, 1000, function () {
				// Animation complete.
			});
			$('#lastfmtoggle').parent().toggleClass("on");
			return false;
		}

		$('#lastfmtoggle').click(lastfm);
	});
</script>
<script>
	$(function () {
		$(".meter > span").each(function () {
			$(this)
				.data("origWidth", $(this).width())
				.width(0)
				.animate({
					width: $(this).data("origWidth")
				}, 1200);
		});
	});
</script>
</body>
</html>
	
