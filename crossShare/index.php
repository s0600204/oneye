<?php
/*
  ___  _ __   ___ _   _  ___
 / _ \| '_ \ / _ \ | | |/ _ \
| (_) | | | |  __/ |_| |  __/
 \___/|_| |_|\___|\__, |\___|
                  |___/

oneye is released under the GNU Affero General Public License Version 3 (AGPL3)
 -> provided with this release in license.txt
 -> or via web at www.gnu.org/licenses/agpl-3.0.txt

Copyright © 2005 - 2010 eyeos Team (team@eyeos.org)
             since 2010 Lars Knickrehm (mail@lars-sh.de)
*/

define('INDEX_TYPE','crossShare');
if(!defined('EYE_INDEX')){
	//Maybe a redirection here?
	include_once('../index.php');
}

//if there are a shorturl in the url, like index.php/file
if(isset($_SERVER['PATH_INFO'])) {
	$myInfo = $_SERVER['PATH_INFO'];
	if($myInfo{0} == '/') {
		$myInfo = substr($myInfo, 1, strlen($myInfo)); // utf8
	}
} else {
	$myInfo="";
}

if (intval(ALLOW_CROSSSHARE) === 0) {
	echo '<!DOCTYPE html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
		<title>Disabled Access</title>
		<style type="text/css">
			body {
				font-family:sans-serif;
				margin:0px;
				padding: 10px 4px;
				background-color: #ffdddd;
				text-align: center;
			}
		</style>
	</head>
	<body>
		Cross-site sharing with <b>' .EYEOS_HOSTNAME. '</b> has been disabled by their system administrator
	</body>
</html>';
	die;
} elseif (isset($_GET['extern'])) {
	//Check if index.php is being used to load images/files from extern directory
	$myExtern = $_GET['extern'];
	//get the type for the header content-type
	if(isset($_GET['type'])) {
		$type = $_GET['type'];
	} else {
		$type = "";
	}
	//call to extern to throw the file
	//Only start session if we already have a session (keep in mind that extern doesn't have session)
	eyeSessions('checkAndSstartSession');
	extern('getFile', array($myExtern, $type), 1);
} elseif(isset($_GET['api'])) {
	require_once(EYE_ROOT.'/xml-rpc/server.eyecode');
	xmlrpc_parseRequest();
} elseif (isset($_GET['verify'])) {
	echo "confirmed";
} else {
	//Loading eyeWidgets definitions
	eyeWidgets('loadWidgets');

	//Starting a simple session
	eyeSessions('startSession');

	//If widget table does not exist, create it 
	eyeWidgets('checkTable');

	//if a shorturl is present
	if(!empty($myInfo)) {
		//check if the shorturl exists, and get the msg and checknum associated to it
		if(is_array($_SESSION['shortUrls'][$myInfo])) {
			$msg = $_SESSION['shortUrls'][$myInfo]['msg'];
			$checknum = $_SESSION['shortUrls'][$myInfo]['checknum'];
			$_GET['msg'] = $msg;
			$_REQUEST['msg'] = $msg;
			$_GET['checknum'] = $checknum;
			$_REQUEST['checknum'] = $checknum;
		}
	}
	//Checking if checknum and message are set
	if(isset($_GET['checknum']) && !empty($_GET['checknum'])) {
		if(isset($_REQUEST['params']) && !empty($_REQUEST['params'])) {
			$params = $_REQUEST['params'];
		} else {
			$params = null;
		}
		if(isset($_GET['msg'])) {
			$msg = $_GET['msg'];
		} else {
			$msg = null;
		}
		$array_msg = array($_GET['checknum'],$msg,$params);
		echo mmap('routemsg', $array_msg);
		$_SESSION['ping'] = time();
	} else {
		//if a ping response is received
		if(isset($_GET['msg']) && $_GET['msg'] == 'ping') {
			//throw a pong!
			header("Content-type:text/xml");//override header type
			echo "<eyeMessage><action><task>pong</task></action></eyeMessage>";
			$_SESSION['ping'] = time();
			exit;
		}
		//Loading the crossShare onEye code
		include_once("../crossShare/crossShare_eyeFiles.eyecode");
	}
}
?>