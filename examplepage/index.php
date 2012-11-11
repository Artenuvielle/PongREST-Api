<?php
		/**
		 * @copyright	Copyright (C) René Martin, 2012. All rights reserved.
		 * @license		GNU General Public License version 2 or later; see LICENSE.txt
		 **/
	require_once('../config/config.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Coding Contest - Pong</title>
		<style type="text/css">
			body {
				background: black;
			}
			
			#titleHolder {
				z-index:100;
				position: fixed;
				top: 20px;
				right: 20px;
				width: 460px;
				height: 120px;
			}
			
			#leftPaddle {
				z-index:1;
				width:2%;
				height:16%;
				background-color:red;
				position:absolute;
				left:0px;
				top:30%;
			}
			
			#ballHolder {
				z-index:3;
				position: absolute;
				top: 38%;
				left: 2%;
				width: 96%;
				height: 62%;
			}
			
			#rightPaddle {
				z-index:1;
				width:2%;
				height:16%;
				background-color:blue;
				position:absolute;
				right:0px;
				bottom:20%;
			}
			
			#mainContainer {
				z-index:50;
				position:relative;
				margin:auto;
				padding:10px;
				width:800px;
				height:600px;
				background-color:#EEE;
			}
		</style>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript"><!--
			var titlearr = new Array(1,1,2,3,3,2,4,3,4,0,
									 2,2,1,1,1,3,1,1,1,3,1,1,1,3,1,0,
									 1,3,1,1,1,3,1,1,1,3,1,1,1,3,1,0,
									 4,2,1,3,1,1,1,3,1,2,4,0,
									 1,6,3,2,1,3,1,5,1,0,
									 1,17,4);
			var titlewidth = 23;
			var titleheight = 6;
			var factor = 20;
			
			$(document).ready( function(){
				drawTitle();
				drawBall();
				centerMain();
			});

			window.onresize = function(){
				drawBall();
				centerMain();
			};

			function centerMain() {
				var windowHeight = getWindowHeight();
				if (windowHeight > 0) {
					var contentElement = document.getElementById('mainContainer');
					var contentHeight = contentElement.offsetHeight;
					if (windowHeight - contentHeight > 0) {
						contentElement.style.position = 'relative';
						contentElement.style.top = ((windowHeight / 2) - (contentHeight / 2)) + 'px';
					}
					else {
						contentElement.style.position = 'static';
					}
				}
			}

			function getWindowHeight() {
				var windowHeight = 0;
				if (typeof(window.innerHeight) == 'number') {
					windowHeight = window.innerHeight;
				}
				else {
					if (document.documentElement && document.documentElement.clientHeight) {
						windowHeight = document.documentElement.clientHeight;
					}
					else {
						if (document.body && document.body.clientHeight) {
							windowHeight = document.body.clientHeight;
						}
					}
				}
				return windowHeight;
			}

			function drawTitle() {
				tcanvas = $('#title')[0];
				tcontext = tcanvas.getContext('2d');
				var x = 0,y = 0;
				tcontext.canvas.width=titlewidth*factor;
				tcontext.canvas.height=titleheight*factor;
				tcontext.fillStyle='#75787B';
				for(var i = 0; i<titlearr.length;i++) {
					if(i%2==0) {
						myrectangle(tcontext,x,y,titlearr[i]*factor,factor);
					}
					x+=(titlearr[i]*factor);
					if(x>=titlewidth*factor) {
						x=0;
						y+=factor;
					}
				}
			}

			function drawBall() {
				bcanvas = $('#ball')[0];
				bcontext = bcanvas.getContext('2d');
				var x = 0,y = 0,r=getWindowHeight()*0.02;
				bcontext.canvas.width=800;
				bcontext.canvas.height=getWindowHeight()*0.62;
				var iter = 400;
				var dis = 1.5;
				for(var i = 0; i<iter;i++) {
					mycircle(bcontext,x,y,r,'rgba(255,255,255,'+i/(iter*20)+')');
					x+=Math.abs(dis);
					y+=dis;
					if(y+r>bcontext.canvas.height)
						dis*=(-1);
				}
				mycircle(bcontext,x,y,r,'rgba(255,255,255,1)');
			}

			function myrectangle(context, x, y, width, height) {
				context.beginPath();
				context.rect(x, y, width, height);
				context.fill();
			}

			function mycircle(context, x, y, radius, color) {
				context.beginPath();
				context.arc(x, y, radius, 0, 2 * Math.PI, false);
				context.fillStyle = color;
				context.fill();
			}
		--></script>
	</head>
	<body>
		<div id="titleHolder"><canvas id="title"></canvas></div>
		<div id="leftPaddle"></div>
		<div id="rightPaddle"></div>
		<div id="ballHolder"><canvas id="ball"></canvas></div>
		<div id="mainContainer">
			<?php
				if(isset($_GET["game"])) {
					$file = 'http://'.$_SERVER["HTTP_HOST"].'/'.$codebase_api."game/".$_GET["game"]."/";
					echo file_get_contents($file);
					echo "</br><a href='index.php'>zurück</a>";
				} else {
					echo "<form method='get' action='index.php'>Spielname: <input name='game' value='foo' /><input type='submit' value='ansehen'/></form>";
				}
			?>
		</div>
	</body>
</html>
