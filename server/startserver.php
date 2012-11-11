<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	//require_once('game.php');
	require_once('../config/config.php');
	require_once('server.php');
	$serv = new WebSocketServer($port);
	$serv->runServer($max);
?>