<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	require_once('../config/config.php');
	set_error_handler('handleError');
	$sock = socket_create(AF_INET,SOCK_STREAM,0);
	try {
		socket_connect($sock,'127.0.0.1',$port);
		socket_write($sock,'TERM');
	} catch (Exception $e) {
		echo json_encode(array("ERROR"=>"Could not complete request to server. (Is it not running?)"));
	}
	
	function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
		if (0===error_reporting()) {
			return false;
		}
		throw new Exception($errstr); 
	}
?>