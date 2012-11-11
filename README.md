René Martin, Student der Medieninformatik (1. Semester) an der Fakultät Infomatik, Mathematik und Naturwissenschaften der HTWK Leipzig

Dokumentation "Pong-REST-API", Coding Contest
=============================================


Eine der Aufgaben des 4. Coding-Contest (05. - 11.11.2012) ist es in PHP einen Server zu schreiben, welcher per REST-API Eingaben zu einem Pong Spiel erhält, verwaltet und das gesamte Spiel simuliert.
Diese Lösung basiert auf einem Apache-Server, welcher die REST-Anfragen entgegennimmt und weiterleitet, sowie einen permanent laufendem PHP-Script, welches über einen Websocket die weitergeleiteten Informationen an die laufenden Spiele verteilt und diese bearbeitet.

->Einrichtung
-------------

Wie bereits genannt, wird ein Apache-Server benötigt, auf dem das Modul "mod_rewrite" aktiviert ist.

Zum testen kann auch das integrierte Apache-Modul von XAMPP (http://www.apachefriends.org/de/xampp.html) benutzt werden (das Modul mod_rewrite ist hier automatisch aktiviert).

Des weiteren sollte eine Konsole verfügbar sein, aus der PHP-Scripte ausgeführt werden können (alle Tests wurden auf der aktuellste PHP-Version durchgeführt).

Alle Dateien dieses Git sollten in einem Unterverzeichniss des Apache-roots liegen (im folgenden wird angenommen, dass dieses das Unterverzeichnis "contest/" ist und keinerlei Namensänderungen vorgenommen wurden).

Zunächst muss die Datei "contest/config/config.php" angepasst werden. Darin wird der Port definiert, über welchen der Websocket laufen soll, wieviele Clients maximal gleichzeitig eine Anfrage an den Websocket machen können und das Unterverzeichnis der API (in unserem Fall "contest/api/").

Anschließend muss in der Datei "contest/api/.htaccess" die Zeile

	RewriteBase /api/

zu

	RewriteBase /contest/api/

verändert werden.

Abschließend muss in der Konsole aus dem root-Verzeichnis des Apache-Server folgender Befehl eingegeben werden:

	php contest/server/startserver.php

Nun kann über die im Apache eingestellte URL entsprechende abfragen an die REST-API gemacht werden (z.B. GET an http://domain:port/contest/api/game/:key/status/)

Um später den Websocket-Server zu beenden muss in einer weiteren Konsole aus dem root-Verzeichnis der Aufruf

	php contest/server/stopserver.php

gemacht werden.


-> Testen
---------

Zum Testen kann die Seite http://domain:port/contest/api/game/:key/ benutzt werden, welche eine graphische Oberfläche des simulierten Spiels darstellt.

Des weiteren wurde zur Benutzerfreundlichkeit eine erweiterte grafische Oberfläche gestaltet, welche man unter http://domain:port/contest/examplepage/ findet.


-> Selbstständigkeitserklärung
------------------------------

Jeglicher Quellcode (PHP, Javascript und HTML) wurde von mir selbst erstellt, mit den Ausnahmen der Inhalte der Dateien "server/game.php" und "api/game_page.php" basiert auf der Pongengine von Stephan Hoyer, dem JQuery-Framework und dem genutzten Apache-Server.


-> Erklärung der Dateien
------------------------

	api/

		.htaccess 	// Enthält Regeln für den Apache-Server zum routen aller nicht existierenden Pfade auf die recieve.php 

		recieve.php	// PHP-Skript zum initialisieren des REST-Handlings
		
		gamepage.php	// Enthält Funktion zum rendern der einfachen graphischen Oberfläche zum testen
		
		serverconnect.php	// Enthält Klasse, welche REST-Anfragen mit callbacks auf den Websocket umleitet

	api/
		
		index.php	// Beispielsseite, auf der die geschriebene API mit einer erweiterten graphischen Oberfläche zum testen eingebunden wurde

	config/
		
		config.php	// wichtige Grundeinstellungen, auf die mehrere Skripte zugreifen

	server/
		
		startserver.php	// PHP-Skript zum starten des Websocket-Server (sollte von der Konsole ausgeführt werden)
		
		stopserver.php	// PHP-Skript zum stopen eines laufenden Websocket-Server
		
		game.php	// Enthält Klasse, die ein Spiel simuliert
		
		server.php	// Enthält Klasse, welche alle Instanzen der Klasse Game verwaltet und vom Websocket Befehle entgegen nimmt


->REST-API
----------

### Erhalte Spiel Status

* Route: GET contest/api/game/:key/status

### Erhalte Spiel-Konfiguration

* Route: GET contest/api/game/:key/config

### Startet das Spiel

* Route: POST contest/api/game/:key/start

### Logt Spieler ein (gibt geheimen Spieler-String zur�ck)

* Route: PUT contest/api/game/:key/player/:playername/

### Bewege Paddle nach oben

* Route: POST contest/api/game/:key/player/:playername/:secret/up/

### Bewege Paddle nach unten

* Route: POST contest/api/game/:key/player/:playername/:secret/down/



René Martin, 11.11.2012

Kontakt: renem1@gmx.net