<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	function render_HTML($gamename,$host,$base) {
?>
		<div id="wrapper" style="width:800px;height:550px;">
			<div style="float: right;"><canvas id="myCanvas" style="border:1px solid black;background:white;"></canvas></div>
			<div style="float:left;">
				<div style="margin:5px">
					<input id="player1" value="Player1" /><button id="registerpl1">registrieren</button><br />
					<button id="pl1up" disabled="disabled">Spieler 1 hoch</button><button id="pl1dw" disabled="disabled">Spieler 1 runter</button><br />
				</div>
				<div style="margin:5px">
					<input id="player2" value="Player2"  disabled="disabled"/><button id="registerpl2" disabled="disabled">registrieren</button><br />
					<button id="pl2up" disabled="disabled">Spieler 2 hoch</button><button id="pl2dw" disabled="disabled">Spieler 2 runter</button><br />
				</div>
				<div style="margin:5px">
					Status: <span id="state">no connection</span>
				</div>
				<div style="margin:5px">
					Bewegungs-Status: <span id="movestate">no move yet</span>
				</div>
				<div style="margin:5px">
					Spielstand: <span id="pointsstate">0:0</span>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript"><!--
			(function() {
				var canvas;
				var context;
				var x, y;
				var paddleLeft, paddleRight;
				var radius = 10;
				var config;
				var secrets = new Array();

				function getBaseUrl() {
					return 'http://<?php echo($host); ?>/';
				}

				function getGameName() {
					return '<?php echo($gamename); ?>';
				}

				function circle(x, y, radius, color) {
					context.beginPath();
					context.arc(x, y, radius, 0, 2 * Math.PI, false);
					context.fillStyle = color;
					context.fill();
				}

				function rectangle(x, y, width, height, color) {
					context.beginPath();
					context.rect(x, y, width, height);
					context.fillStyle = color;
					context.fill();
				}

				$('#registerpl1').click(function(){
					var s = $('#player1').val();
					if(s!="") requestNewPlayer(updatePlayers, s);
				});

				$('#registerpl2').click(function(){
					var s = $('#player2').val();
					if(s!="") requestNewPlayer(updatePlayers, s);
				});

				$('#pl1up').click(function(){ requestMove(updateMoves, $('#player1').val(), 'up'); });
				$('#pl1dw').click(function(){ requestMove(updateMoves, $('#player1').val(), 'down'); });
				$('#pl2up').click(function(){ requestMove(updateMoves, $('#player2').val(), 'up'); });
				$('#pl2dw').click(function(){ requestMove(updateMoves, $('#player2').val(), 'down'); });

				function updatePlayers(err, response, pl) {
					if(response!='game full')
					{
						secrets[pl] = response;
					}
				}

				function updateMoves(err, response) {
					$('movestate').html(response);
				}

				function updateField(err, status)
				{
					if (status) {
						$('#state').html(status.status);
						$('#pointsstate').html(status.scoreLeft+':'+status.scoreRight);
						var b=(status.players.left!=null);
						$('#player1').prop('disabled', b);
						$('#registerpl1').prop('disabled', b);
						b=(status.players.left==null || status.players.right!=null);
						$('#player2').prop('disabled', b);
						$('#registerpl2').prop('disabled', b);
						b=(secrets[$('#player1').val()]==null);
						$('#pl1up').prop('disabled', b);
						$('#pl1dw').prop('disabled', b);
						b=(secrets[$('#player2').val()]==null);
						$('#pl2up').prop('disabled', b);
						$('#pl2dw').prop('disabled', b);
						radius = config.BALL_RADIUS;
						rectangle(0, 0, config.FIELD_WIDTH, config.FIELD_HEIGHT, 'white');
						x = status.ball[0];
						y = status.ball[1];
						paddleLeft = status.paddleLeft;
						paddleRight = status.paddleRight;
						circle(x, y, radius, 'green');
						rectangle(0, paddleLeft-config.PADDLE_HEIGHT/2,	config.PADDLE_WIDTH, config.PADDLE_HEIGHT, 'red');
						rectangle(config.FIELD_WIDTH-config.PADDLE_WIDTH, paddleRight-config.PADDLE_HEIGHT/2, config.PADDLE_WIDTH, config.PADDLE_HEIGHT, 'blue');
						if(status.status!='finished')
							setTimeout(function() {
								requestStatus(updateField);
							}, 100);
					} else {
						setTimeout(function() {
							requestStatus(updateField);
						}, 100);
					}
				}

				function requestConfig(configDone) {
					request('<?php echo($base); ?>game/' + getGameName() + '/config/', 'GET', true, configDone);
				}

				function requestStatus(statusDone) {
					request('<?php echo($base); ?>game/' + getGameName() + '/status/', 'GET', true, statusDone);
				}

				function requestNewPlayer(playerDone,playername) {
					request('<?php echo($base); ?>game/' + getGameName() + '/player/' + playername + '/', 'PUT', false, playerDone, playername);
				}

				function requestMove(moveDone,playername,direction) {
					request('<?php echo($base); ?>game/' + getGameName() + '/player/' + playername + '/' + secrets[playername] + '/' + direction + '/', 'POST', false, moveDone);
				}

				function request(url, method, isjson, requestDone, params) {
					var request = new XMLHttpRequest();
					var src = getBaseUrl() + url;
					request.open(method, src, true); ;
					request.onreadystatechange = function () {
						if (request.readyState != 4 || request.status != 200) {
							return;
						}
						if (isjson) {
							requestDone(null, JSON.parse(request.responseText), params);
						} else {
							requestDone(null, request.responseText, params);
						}
					};
					request.send();
				}

				$(document).ready(function(){
					canvas = document.getElementById('myCanvas');
					context = canvas.getContext('2d');
					requestConfig(function(err, newConfig) {
						config = newConfig;
						context.canvas.width = config.FIELD_WIDTH;   // Resize drawing area
						context.canvas.height = config.FIELD_HEIGHT;
						$('#myCanvas').width(config.FIELD_WIDTH);    // Resize html element
						$('#myCanvas').height(config.FIELD_HEIGHT);
						updateField();
					});
				});
			}());
		--></script>
<?php
	}
?>