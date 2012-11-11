<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	require_once('gamepage.php');
	class ServerConnect {
		private $socket;
		private $codebase;
		private $paths;
		
		public function __construct($socketport,$base) {
			$this->codebase = $base;
			$this->paths = array();
			// Connect to game-server
			$this->socket = socket_create(AF_INET,SOCK_STREAM,0);
			socket_connect($this->socket,'127.0.0.1',$socketport);
		}
		
		public function registerPath($verb,$path,$callback) {
			$this->paths[count($this->paths)] = new RESTPath($verb,preg_split("~/~",$path),$callback);
		}
		
		// Call back functions which were registered if the $path and $type is correct 
		public function handleREST($type, $params) {
			$possiblepaths = $this->paths;
			for($i = 0; $i < count($params);$i++)
			{
				if(count($possiblepaths)==1 || $i == count($params) - 1) {  // Check request type if only 1 path is left over
					foreach($possiblepaths as $pat) {
						if(count($pat->getParams())-1==$i) {
							if ($pat->getVerb() == $type) {
								$this->_tryToCall($pat->getCallback(),$pat->getCallbackP()); // Callback function
							} else {
								$this->_ErrorBadRequestType();
							}
							break;
						}
					}
				}
				foreach($possiblepaths as $name=>$p) {  // Check if possible paths are not requested
					$res = $this->_equalsREST($params[$i],$p->getParam($i));
					if($res == null) {
						unset($possiblepaths[$name]);
					} else if (gettype($res)=='string') {
						$p->addCallbackP($res);
					}
				}
				if(count($possiblepaths)==0) {  // False request if no possible paths are left over
					$this->_errorNoSuchPath();
					break;
				}
			}
		}
		
		public function getStatus($p) { // Path: /game/:key/status/
			$this->_writeToSocket("return(CheckForGame('$p[0]',\$games)->getStatus());");
		}
		
		public function getConfig($p) { // Path: /game/:key/config/
			$this->_writeToSocket("return(Game::\$config);");
		}
		
		public function postStart($p) { // Path: /game/:key/config/
			$this->_writeToSocket("return(CheckForGame('$p[0]',\$games)->start());");
		}
		
		public function putPlayer($p) { // Path: /game/:key/player/:playername/
			$this->_writeToSocket("return(CheckForGame('$p[0]',\$games)->loginPlayer('$p[1]'));");
		}
		
		public function postPlayerDown($p) { // Path: /game/:key/player/:playername/:secret/down/
			$this->_writeToSocket("return(CheckForGame('$p[0]',\$games)->moveDown('$p[1]','$p[2]'));");
		}
		
		public function postPlayerUp($p) { // Path: /game/:key/player/:playername/:secret/up/
			$this->_writeToSocket("return(CheckForGame('$p[0]',\$games)->moveUp('$p[1]','$p[2]'));");
		}
		
		public function getHTML($p) { // Path: /game/:key/
			render_HTML($p[0],$_SERVER["HTTP_HOST"],$this->codebase);
		}
		
		// Compare 2 parts of a REST-request-path and return $s1 other if $s2 starts with ':'
		private function _equalsREST($s1,$s2) {
			if ($s1==$s2) {	return true; }
			if (substr($s2,0,1)==':') {	return $s1;	}
			return null;
		}
		
		// Try to callback a function
		private function _tryToCall($c,$p) {
			if (is_callable($c)) {
				call_user_func($c,$p);
			}
		}
		
		// Write into socket and wait for feedback 
		private function _writeToSocket($value, $shallecho = true) {
			socket_write($this->socket, $value.chr(0),1024);
			$this->_getFeedback($this->socket, $shallecho);
		}
		
		// Called if REST-request-path was not registered
		private function _errorNoSuchPath() {
			echo json_encode(array("ERROR"=>"Could not access the PATH_INFO='".$_SERVER['PATH_INFO']."'."));
		}

		// Called if REST-request is of wrong request-type
		private function _errorBadRequestType() {
			echo json_encode(array("ERROR"=>"Server cannot use this request type on this object."));
		}
		
		// Wait for feedback from the server
		private function _getFeedback($socket,$shallecho) {
			$s = trim(socket_read($socket, 1024));
			if($shallecho) echo $s;
		}
		
		
		public function __destruct() {
			// Exit connection to server
			socket_write($this->socket, 'EXIT'.chr(0), 1024);
		}
	}
	
	class RESTPath {
		private $verb;
		private $params = array();
		private $callback;
		private $callbackp = array();
		public function __construct($v,$p,$c) {
			$this->verb = $v;
			$this->params = $p;
			$this->callback = $c;
		}
		
		public function getVerb() { return $this->verb; }
		public function getParams() { return $this->params; }
		public function getParam($i) { return $this->params[$i]; }
		public function getCallback() { return $this->callback; }
		public function addCallbackP($p) { $this->callbackp[count($this->callbackp)] = $p; }
		public function getCallbackP() { return $this->callbackp; }
	}
?>