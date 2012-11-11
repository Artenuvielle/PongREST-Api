<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	class Game {
		static $config = array(
			"FIELD_HEIGHT" => 500,
			"FIELD_WIDTH" => 500,
			
			"BALL_RADIUS" => 5,
			
			"PADDLE_HEIGHT" => 80,
			"PADDLE_WIDTH" => 10,
			"PADDLE_STEP" => 20,
			"ACCELORATOR" => 10,
			"ACCELORATE_PER_ROUND" => 0.0001,
			
			"TIME_QUANTUM" => 10,
			"INITIAL_BALL_SPEED" => 2,
			"WAIT_BEFORE_START" => 1000,
			
			// these canstants determine, how many paddle moves are allowed in which
			// number of steps
			"NUMBER_OF_PADDLE_MOVES" => 10,
			"NUMBER_OF_STEPS" => 10,
			
			"SCORE_TO_WIN" => 10,
		);
	
		static $STATUS_LOGIN = 'login';
		static $STATUS_READY = 'ready';
		static $STATUS_STARTED = 'started';
		static $STATUS_FINISHED = 'finished';
	
		private $ball;
		private $ballDelta;
		private $paddleLeft;
		private $paddleRight;
		private $players;
		private $status;
		private $autoStart;
		private $leftMoveCounter;
		private $rightMoveCounter;
		private $scoreLeft;
		private $scoreRight;
		private $winner;
		private $lastCall;
		
		// Constructor, sets the default values
		public function __construct() {
			$this->ball = array(self::$config['FIELD_WIDTH'] / 2, self::$config['FIELD_HEIGHT'] /2);
			$this->ballDelta = array(0, 0);
			$this->paddleLeft = self::$config['FIELD_WIDTH'] / 2;
			$this->paddleRight = self::$config['FIELD_HEIGHT'] / 2;
			$this->players = array('left'=> null, 'right'=> null);
			$this->status = self::$STATUS_LOGIN;
			$this->autoStart = true;
			$this->leftMoveCounter = 0;
			$this->rightMoveCounter = 0;
			$this->scoreLeft = 0;
			$this->scoreRight = 0;
			$this->lastCall = microtime(TRUE);
		}
		
		// Resets ball
		public function resetBall() {
			$this->ball = array(self::$config['FIELD_WIDTH'] / 2, self::$config['FIELD_HEIGHT'] /2);
			$this->ballDelta = array(random(self::$config['INITIAL_BALL_SPEED']), random(self::$config['INITIAL_BALL_SPEED']));
		}
		
		// Starts game if it's not running 
		public function start() {
			if ($this->status == self::$STATUS_FINISHED) {
				return "allready finished";
			} elseif ($this->status == self::$STATUS_FINISHED) {
				return "allready running";
			}
			$this->status = self::$STATUS_STARTED;
			$this->resetBall();
			return "game started";
		}
		
		// See if one Player has won yet
		public function checkWinner() {
			if ($this->scoreLeft >= self::$config['SCORE_TO_WIN']) {
				$this->status = self::$STATUS_FINISHED;
				$this->winner = 'left';
			}
			if ($this->scoreRight >= self::$config['SCORE_TO_WIN']) {
				$this->status = self::$STATUS_FINISHED;
				$this->winner = 'right';
			}
		}
		
		// Main iteration, updates ball position
		private function _step() {
			if ($this->ball[0] >= self::$config['FIELD_WIDTH'] - self::$config['BALL_RADIUS'] - self::$config['PADDLE_WIDTH']) {
				if ($this->ball[1] > $this->paddleRight - self::$config['PADDLE_HEIGHT']/2 && $this->ball[1] < $this->paddleRight + self::$config['PADDLE_HEIGHT']/2)
				{
					// Right paddle got the ball
					$this->ballDelta[0] *= -1;
					$this->ballDelta[1] += ($ball[1] - $paddleRight) / self::$config['ACCELORATOR'];
				} else {
					// Right paddle missed the ball
					$this->resetBall();
					$this->scoreLeft += 1;
					$this->checkWinner();
				}
			}
			if ($this->ball[0] <= self::$config['BALL_RADIUS'] + self::$config['PADDLE_WIDTH']) {
				if ($this->ball[1] > $this->paddleLeft - self::$config['PADDLE_HEIGHT']/2 && $this->ball[1] < $this->paddleLeft + self::$config['PADDLE_HEIGHT']/2)
				{
					// Left paddle got the ball
					$this->ballDelta[0] *= -1;
					$this->ballDelta[1] += ($ball[1] - $paddleLeft) / self::$config['ACCELORATOR'];
				} else {
					// Left paddle missed the ball
					$this->resetBall();
					$this->scoreRight += 1;
					$this->checkWinner();
				}
			}
			// Reflect ball at Walls
			if ($this->ball[1] >= self::$config['FIELD_HEIGHT'] - self::$config['BALL_RADIUS'] ||	$this->ball[1] <= self::$config['BALL_RADIUS']) {
				$this->ballDelta[1] *= -1;
			}
			// Move ball
			$this->ball[0] += $this->ballDelta[0];
			$this->ball[1] += $this->ballDelta[1];
			$this->ballDelta[0] *= 1 + self::$config['ACCELORATE_PER_ROUND'];
			$this->ballDelta[1] *= 1 + self::$config['ACCELORATE_PER_ROUND'];
			
			$allowedMovesPerStep = self::$config['NUMBER_OF_PADDLE_MOVES']/self::$config['NUMBER_OF_STEPS'];
			$this->leftMoveCounter = max($this->leftMoveCounter - $allowedMovesPerStep, 0);
			$this->rightMoveCounter = max($this->rightMoveCounter - $allowedMovesPerStep, 0);
			$this->lastCall = microtime(TRUE);
		}
		
		// Called once in each server cycle, checks if an iteration has to be made
		public function run() {
			if($this->status == self::$STATUS_READY) {
				if((microtime(TRUE) - $this->lastCall) * 1000 >= self::$config['WAIT_BEFORE_START']) {
					$this->start();
					$this->_step();
				}
			} elseif($this->status == self::$STATUS_STARTED) {
				if((microtime(TRUE) - $this->lastCall) * 1000 >= self::$config['TIME_QUANTUM']) {
					$this->_step();
				}
			}
			// Return status or false if nothing happened for 10 minutes
			if((microtime(TRUE) - $this->lastCall) >= 600) {
				return false;
			} else {
				return true;
			}
		}
		
		// Player login; returns players secret
		public function loginPlayer($playername) {
			if (isset($this->players['right'])) {
				return 'game full';
			}
			$player = array(
				'name' => $playername,
				'secret' => createSecret()
			);
			if (isset($this->players['left'])) {
				$this->players['right'] = $player;
				if ($this->autoStart) {
					$this->status = self::$STATUS_READY;
					$this->lastCall = microtime(TRUE);
				}
			} else {
				$this->players['left'] = $player;
			}
			return $player['secret'];
		}
		
		// Move paddle from $playername down if his secret equals $secret
		public function moveDown($playername, $secret) {
			return $this->_move($playername, $secret, self::$config['PADDLE_STEP']);
		}
		
		// Move paddle from $playername up if his secret equals $secret
		public function moveUp($playername, $secret) {
			return $this->_move($playername, $secret, -self::$config['PADDLE_STEP']);
		}
		
		// Moves a paddle
		private function _move($playername, $secret, $distance) {
			if ($this->status == self::$STATUS_STARTED) {
				if ($this->players['left']['name'] == $playername && $this->players['left']['secret'] == $secret) {
					if ($this->leftMoveCounter >= self::$config['NUMBER_OF_PADDLE_MOVES']) {
						return 'too many moves';
					}
					$this->paddleLeft = $this->_getNewPaddlePosition($this->paddleLeft, $distance);
					$this->leftMoveCounter += 1;
					return 'ok';
				}
				if ($this->players['right']['name'] == $playername && $this->players['right']['secret'] == $secret) {
					if ($this->rightMoveCounter >= self::$config['NUMBER_OF_PADDLE_MOVES']) {
						return 'too many moves';
					}
					$this->paddleRight = $this->_getNewPaddlePosition($this->paddleRight, $distance);
					$this->rightMoveCounter += 1;
					return 'ok';
				}
				return 'not your game';
			} else {
				return 'game not running';
			}
		}
		
		// Checks if new position would be out of bounds
		private function _getNewPaddlePosition($position,$d) {
			$ret =  $position + $d;
			$ret = min($ret,self::$config['FIELD_HEIGHT']-self::$config['PADDLE_HEIGHT']/2);
			return max($ret,self::$config['PADDLE_HEIGHT']/2);
		}
		
		// Returns status of the game 
		public function getStatus() {
			return array(
				'ball' => $this->ball,
				'ballDelta'=> $this->ballDelta,
				'paddleLeft'=> $this->paddleLeft,
				'paddleRight'=> $this->paddleRight,
				'players'=> array(
					'left'=> $this->players['left']['name'],
					'right'=> $this->players['right']['name']
				),
				'status'=> $this->status,
				'autoStart'=> $this->autoStart,
				'leftMoveCounter'=> $this->leftMoveCounter,
				'rightMoveCounter'=> $this->rightMoveCounter,
				'scoreLeft'=> $this->scoreLeft,
				'scoreRight'=> $this->scoreRight
			);
		}
	}
	
	// Creates a random string with a-z A-Z 0-9
	function createSecret($lenth) {
		if(!isset($lenth)) $length = 5;
		$s="";
		$possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		for($i = 0; $i<$length;$i++)
			$s .= $possible[mt_rand(0,strlen($possible)-1)];
		return $s;
	}
	
	// Returns a random value between -1 and -0.5 or 0.5 and 1
	function random($value) {
		$direction = mt_rand(0,1) < 0.5 ? -1 : 1;
		$frac = 100000;
		$ret = $direction * (rand(0,$frac) * $value / (2 * $frac) + $value / 2);
  		return $ret;
	}
?>