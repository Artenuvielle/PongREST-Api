<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	// Port which will be used to communicate with the gameserver, NOT the port of the Apache server
	$port = 10015;
	
	// Maximum Number of connections allowed with the gameserver
	// (only the localhost can connect to the socket, but there can be multiple accesses to the reciev.php at once)
	$max = 20;
	
	// API-dir ('subdir/' if the REST-requests are in http://domain:port/subdir/game/:key/...)
	$codebase_api = "api/";
?>