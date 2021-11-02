<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

$require_current_course = TRUE;
include '../../include/baseTheme.php';
include_once("../../hash_equals.php");
$urlPath = htmlspecialchars($_SERVER ['PHP_SELF'], ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<head>
<meta http-equiv="refresh" content="30; url=<?= $urlPath ?>" />
<title>Chat messages</title>
<style type="text/css">
span { color: #727266; }
div { font-size: 90%; } 
body { font-family: Verdana, Arial, Helvetica, sans-serif; }
</style>
</head>
<body>
<?
include '../../include/lib/textLib.inc.php';

// support for math symbols
include('../../include/phpmathpublisher/mathpublisher.php');

if (isset($_GET['submit']))
{
	if(empty($_SESSION['key_messageList_form'])){
	    $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
	    $_SESSION['key_messageList_form'] = $csrftoken_text;
	}

	$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_messageList_form'],session_id());

	if(!hash_equals($csrftoken, $_GET['csrf']))
	{
		session_destroy();
		$redirect='../../../index.php'; 
        header("Location: $redirect");

        exit();
	}
}


$coursePath=$webDir."courses";
$fileChatName   = $coursePath.'/'.$currentCourseID.'.chat.txt';
$tmpArchiveFile = $coursePath.'/'.$currentCourseID.'.tmpChatArchive.txt';
$pathToSaveChat = $coursePath.'/'.$currentCourseID.'/document/';

$nick = uid_to_name($uid);

// How many lines to show on screen
define('MESSAGE_LINE_NB',  40);
// How many lines to keep in temporary archive
// (the rest are in the current chat file)
define('MAX_LINE_IN_FILE', 80);

if ($GLOBALS['language'] == 'greek')
	$timeNow = date("d-m-Y / H:i",time());
else
	$timeNow = date("Y-m-d / H:i",time());

if (!file_exists($fileChatName)) {
	$fp = fopen($fileChatName, 'w')
		or die ('<center>$langChatError</center>');
	fclose($fp);
}

// chat commands

// reset command

if(empty($_SESSION['key_messageList_actions'])){
    $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
    $_SESSION['key_messageList_actions'] = $csrftoken_text;
}

$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_messageList_actions'],session_id());

if (isset($_GET['reset']) && $is_adminOfCourse) {

	if (isset($_GET['token']))
	{
	    $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

	    if(!$pageWasRefreshed ) {
	        if(!hash_equals($csrftoken,$_GET['token']))
	        {
	            session_destroy();
	            $redirect='../../../index.php'; 
	            header("Location: $redirect");

	            exit();
	        }
	    }
	}
    else 
    {
        session_destroy();
        $redirect='../../../index.php'; 
        header("Location: $redirect");

        exit();        
    }

	$fchat = fopen($fileChatName,'w');
	fwrite($fchat, $timeNow." ---- ".$langWashFrom." ---- ".$nick." --------\n");
	fclose($fchat);
	@unlink($tmpArchiveFile);
}

// store
if (isset($_GET['store']) && $is_adminOfCourse) {

	if (isset($_GET['token']))
	{
	    $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

	    if(!$pageWasRefreshed ) {
	        if(!hash_equals($csrftoken,$_GET['token']))
	        {
	            session_destroy();
	            $redirect='../../../index.php'; 
	            header("Location: $redirect");

	            exit();
	        }
	    }
	}
    else 
    {
        session_destroy();
        $redirect='../../../index.php'; 
        header("Location: $redirect");

        exit();        
    }

	$saveIn = "chat.".date("Y-m-j-B").".txt";
	$chat_filename = date("YmdGis").randomkeys("8").".txt";

	buffer(implode('', file($fileChatName)), $tmpArchiveFile);
	if (copy($tmpArchiveFile, $pathToSaveChat.$chat_filename)) {
                $alert_div=$langSaveMessage;
        } else {
                $alert_div= $langSaveErrorMessage;
        }
	echo $alert_div;
	db_query("INSERT INTO document SET path='/$chat_filename', filename='$saveIn',
		date=NOW(), date_modified=NOW()", $currentCourseID);
	exit;
}

// add new line
if (isset($chatLine) and trim($chatLine) != '') {
	$fchat = fopen($fileChatName,'a');
	$chatLine = mathfilter($chatLine, 12, '../../courses/mathimg/');
	fwrite($fchat,$timeNow.' - '.$nick.' : '.stripslashes($chatLine)."\n");
	fclose($fchat);
}

// display message list
$fileContent  = file($fileChatName);

$FileNbLine   = count($fileContent);
$lineToRemove = $FileNbLine - MESSAGE_LINE_NB;
if ($lineToRemove < 0) $lineToRemove = 0;
$tmp = array_splice($fileContent, 0 , $lineToRemove);
$fileReverse = array_reverse($fileContent);

foreach ($fileReverse as $thisLine) {
	// Defense against XSS attack
	$thisLine = htmlspecialchars($thisLine, ENT_QUOTES, 'UTF-8');
	$newline = preg_replace('/ : /', '</span> : ', $thisLine);
	if (strpos($newline, '</span>') === false) {
		$newline .= '</span>';
	}
 	echo '<div><span>', $newline, "</div>\n";
}

echo "</body></html>\n";


/*
 * For performance reason, buffer the content
 * in a temporary archive file
 * once the chat file is too large
 */

if ($FileNbLine > MAX_LINE_IN_FILE) {
	buffer(implode("",$tmp), $tmpArchiveFile);
	// clean the original file
	$fp = fopen($fileChatName, "w");
	fwrite($fp, implode("", $fileContent));
}

function buffer($content, $tmpFile) {
	$fp = fopen($tmpFile, "a");
	fwrite($fp, $content);
}
