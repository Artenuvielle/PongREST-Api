<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	require_once("../config/config.php");
	require_once("game_page.php");
	// Get method (PUT/POST/GET)
	$verb = strtoupper($_SERVER["REQUEST_METHOD"]);
	$params = split("/",$_SERVER['PATH_INFO']);
	// Connect to game-server
	$sock = socket_create(AF_INET,SOCK_STREAM,0);
	
	set_error_handler('handleError');
	try {
		socket_connect($sock,'127.0.0.1',$port);
		
		// Pass inputs to server 
		if(isset($params[0]) && $params[0] == "") {
			if(isset($params[1]) && $params[1] == "game") {
				if(isset($params[2]) && $params[2] != "") {
					if(isset($params[3])) {
						if(isset($params[4]) && $params[4] != "") {
							if ($params[3]=="player" && isset($params[4]) && $params[4]!="") {
								if (!isset($params[5]) || $params[5] == "") { // Path: /game/:key/player/:playername
									if($verb=="PUT") {
										write_to_socket($sock,"return(CheckForGame('$params[2]',\$games)->loginPlayer('$params[4]'));");
									}
								} else {
									if(isset($params[6])) {
										if($params[6]=="up") { // Path: /game/:key/player/:playername/:secret/up
											if ($verb=="POST") {
												write_to_socket($sock,"return(CheckForGame('$params[2]',\$games)->moveUp('$params[4]','$params[5]'));");
											}
										} elseif ($params[6]=="down") { // Path: /game/:key/player/:playername/:secret/down
											if ($verb=="POST") {
												write_to_socket($sock,"return(CheckForGame('$params[2]',\$games)->moveDown('$params[4]','$params[5]'));");
											}
										} else nofunction();
									} else nofunction(); 
								}
							} else nofunction();
						} else {
							if($params[3]=="status") {  // Path: /game/:key/status
								if ($verb=="GET") {
									write_to_socket($sock,"return(CheckForGame('$params[2]',\$games)->getStatus());");
								} else badrequest();
							} elseif($params[3]=="config") {  // Path: /game/:key/config
								if($verb=="GET") {
									write_to_socket($sock,"return(Game::\$config);");
								} else badrequest();
							} elseif($params[3]=="start") { // Path: /game/:key/config
								if($verb=="POST") {
									write_to_socket($sock,"return(CheckForGame('$params[2]',\$games)->start());");
								} else badrequest();
							} elseif($params[3]=="" && $verb=="GET") {
								render_HTML($params[2],$_SERVER["HTTP_HOST"],$codebase_api); // Path: /game/:key/
							} else nofunction();
						}
					} else nofunction();
				} else nofunction();
			} else nofunction();
		} else nofunction();
		
		// Exit connection to server
		socket_write($sock,'EXIT'.chr(0),1024);
	} catch (Exception $e) {
		echo json_encode(array("ERROR"=>"Could not complete request to server. (Is it not running?)"));
	}
	
	function write_to_socket($sock,$value) {
		socket_write($sock,$value.chr(0),1024);
		getFeedback($sock,TRUE);
	}
	
	function nofunction() {
		echo json_encode(array("ERROR"=>"Could not access the PATH_INFO='".$_SERVER['PATH_INFO']."'."));
	}
	
	function badrequest() {
		echo json_encode(array("ERROR"=>"Server cannot use this request type on this object."));
	}
	
	function getFeedback($socket,$shallecho) {
		$s = trim(socket_read($socket,1024));
		if($shallecho) echo $s;
	}
	
	function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
		if (0===error_reporting()) {
			return false;
		}
		throw new Exception($errstr); 
	}
?>