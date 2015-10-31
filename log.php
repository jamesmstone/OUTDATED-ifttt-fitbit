<?php

	/**
	 * Debug logging
	 */
	$DEBUG = 0;

	function __log($message, $level = "NOTICE") {
		global $DEBUG;

		if ($DEBUG) {

			error_log("$level: $message");
			mail("jamesmstone@hotmail.com", "IFTTT FITBIT PAGODABOX:" . $level, $message);

		}
	}