<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	require_once('game.php');
	require_once('../config/config.php');
	function CheckForGame($gamename,&$games) {
		if($gamename != "") {
			if (!isset($games[$gamename])) {
				$games[$gamename] = new Game();
				consolelog("Created game ".$gamename);
			}
			return $games[$gamename];
		}
		return null;
	}
	// PHP game-server
	error_reporting(E_ERROR);
	// Configuration variables
	$host = "127.0.0.1";
	$client = array();
	$games = array();
	 
	// No timeouts, flush content immediatly
	set_time_limit(0);
	ob_implicit_flush();
	 
	// Server functions
	function consolelog($msg){
		$msg = "[".date('Y-m-d H:i:s')."] ".$msg;
		print($msg."\n");
	}
	
	// Initialize game-server
	$sock = socket_create(AF_INET,SOCK_STREAM,0) or die("[".date('Y-m-d H:i:s')."] Could not create socket\n");
	socket_bind($sock,$host,$port) or die("[".date('Y-m-d H:i:s')."] Could not bind to socket\n");
	socket_listen($sock) or die("[".date('Y-m-d H:i:s')."] Could not set up socket listener\n");
	consolelog("Server started at ".$host.":".$port);
	
	while(true){
		//socket_set_block($sock);
		
		// Setup clients listen socket for reading
		$read[0] = $sock;
		for($i = 0;$i<$max;$i++){
			if($client[$i]['sock'] != null)
				$read[$i+1] = $client[$i]['sock'];
		}
		// Set up a blocking call to socket_select()
		$ready = socket_select($read,$write = NULL, $except = NULL, $tv_sec = 0);
		// If a new connection is being made add it to the clients array
		if(in_array($sock,$read)){
			for($i = 0;$i<$max;$i++){
				if($client[$i]['sock']==null){
					if(($client[$i]['sock'] = socket_accept($sock))<0){
						consolelog("socket_accept() failed: ".socket_strerror($client[$i]['sock']));
					} else {
						consolelog("Client #".$i." connected");
					}
					break;
				} elseif($i == $max - 1) {
					consolelog("Too many clients");
				}
			}
			if(--$ready <= 0)
				continue;
		}
		// Check if any client has to say something
		for($i=0;$i<$max;$i++){
			if(in_array($client[$i]['sock'],$read)){
				$input = socket_read($client[$i]['sock'],1024);
				if($input==null){
					unset($client[$i]);
				}
				// Split clients comands
				$n = trim($input);
				$com = split(" ",$n);
				foreach ($com as $c)
				{
					consolelog("Client #".$i." said '".$c."'");
					if($c=="EXIT"){
						if($client[$i]['sock']!=null){
							// Disconnect requested
							socket_close($client[$i]['sock']);
							unset($client[$i]['sock']);
							consolelog("Disconnected client #".$i);
							if($i == $adm){
								$adm = -1;
							}
						}
					} elseif($c=="TERM") {
						// Server termination requested
						socket_close($sock);
						consolelog("Terminated server (requested by client #".$i.")");
						exit();
					} elseif($c!="") {
						// Respond to commands
						$result = eval($c);
						if($result != NULL) {
							if(gettype($result) == "array") {
								socket_write($client[$i]['sock'],json_encode($result).chr(0));
							} elseif($result != "") {
								socket_write($client[$i]['sock'],$result.chr(0));
							}
						} else {
							socket_write($client[$i]['sock'],json_encode(array("ERROR"=>"Server could not execute your request.")).chr(0));
						}
					} else {
						socket_write($client[$i]['sock'],"Unknown command!".chr(0));
					}
				}
			}
		}
		// Update all games and delete old games
		foreach ($games as $name=>$g)
		{
			if(!($g->run())) {
				unset($games[$name]);
				consolelog("Deleting game ".$name);
			}
		}
	}
	// Close the master sockets
	socket_close($sock);
?>