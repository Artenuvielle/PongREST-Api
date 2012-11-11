<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	require_once("../config/config.php");
	require_once("serverconnect.php");
	// Get method (PUT/POST/GET)
	$verb = strtoupper($_SERVER["REQUEST_METHOD"]);
	$params = preg_split("~/~",$_SERVER['PATH_INFO']);
	
	set_error_handler('handleError');
	
	try {
		
		$connection = new ServerConnect($port, $codebase_api);
		
		// Register REST-Paths
		$connection->registerPath("GET","/game/:gamename/",array($connection,'getHTML'));
		$connection->registerPath("GET","/game/:gamename/config/",array($connection,'getConfig'));
		$connection->registerPath("GET","/game/:gamename/status/",array($connection,'getStatus'));
		$connection->registerPath("POST","/game/:gamename/start/",array($connection,'postStart'));
		$connection->registerPath("PUT","/game/:gamename/player/:playername/",array($connection,'putPlayer'));
		$connection->registerPath("POST","/game/:gamename/player/:playername/:secret/up/",array($connection,'postPlayerUp'));
		$connection->registerPath("POST","/game/:gamename/player/:playername/:secret/down/",array($connection,'postPlayerDown'));

		// Pass inputs to server 
		$connection->handleREST($verb,$params);
		 
	} catch (Exception $e) { // Handle errors in API
		echo json_encode(array("ERROR"=>"Could not complete request to server. (Is it not running?)"));
	}
	
	function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
		if (0===error_reporting()) {
			return false;
		}
		throw new Exception($errstr); 
	}
?>