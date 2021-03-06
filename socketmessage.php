<?php
/* Copyright 2012 BrewPi/Elco Jacobs.
 * This file is part of BrewPi.

 * BrewPi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * BrewPi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with BrewPi.  If not, see <http://www.gnu.org/licenses/>.
 */

/* to make sockets work on windows, uncomment extension=php_sockets.dll in php.ini */

require_once('socket_open.php');

// Read config settings
if(file_exists('config.php')) {
	require_once('config.php');
}
else {
	die('ERROR: Unable to open required file (config.php)');
}

function startsWith($haystack, $needle)
{
	return !strncmp($haystack, $needle, strlen($needle));
}

error_reporting(E_ALL ^ E_WARNING);
if(isset($_POST['messageType'])){
	$messageType = $_POST['messageType'];
}
else{
	die("messageType not set");
}

if(isset($_POST['message'])){
	// message with data
	$data = $_POST['message'];
}

$sock = open_socket();
if($sock !== false){
	if(isset($data) && $data != ""){
		switch($messageType){
			default:
				socket_write($sock, $messageType . "=" . $data, 4096);
				break;
		}
	}
	else{
		// message without a data argument
		switch($messageType){
			case "checkScript":
				socket_write($sock, "ack", 4096);
				$answer = socket_read($sock, 4096);
				if($answer == "ack"){
					echo 1;
				}
				else{
					echo 0;
				}
				break;
			case "lcd":
				socket_write($sock, "lcd", 4096);
				$lcdText = socket_read($sock, 4096);
				if($lcdText !== false){
					echo str_replace(chr(0xB0), "&deg;", $lcdText); // replace degree sign with &Deg
				}
				else{
					echo false;
				}
				break;
			default:
				// just pass the command to the socket and read the answer if needed
				socket_write($sock, $messageType, 4096);

				if(startsWith($messageType, "get") || $messageType == "uploadProfile"){
					// return data expected, read from socket
					echo socket_read($sock, 4096);
				}
		}
	}
	socket_close($sock);
}
