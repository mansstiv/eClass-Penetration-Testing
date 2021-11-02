<?php 
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

$nameTools = $langAddPage;
$tool_content = "";

// Check if user=prof or assistant

if($is_adminOfCourse) 
{ 

	if(isset($submit))
	{


			if(empty($_SESSION['key_import_hidden'])){
				$csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
				$_SESSION['key_import_hidden'] = $csrftoken_text;
			}
			$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_import_hidden'],session_id());
			if(hash_equals($csrftoken,$csrf))
			{

				unset($_SESSION['key_import_hidden']);
				$_SESSION['key_import_hidden']='';
			}
			else
			{
				session_destroy();
				$redirect='../../../index.php'; 
		        header("Location: $redirect");
		        exit();
			}

		// UPLOAD FILE TO "documents" DIRECTORY + INSERT INTO documents TABLE
		$updir = "$webDir/courses/$currentCourseID/page/"; //path to upload directory
		$size = "20000000"; //file size ex: 5000000 bytes = 5 megabytes
		if (($file_name != "") && ($file_size <= "$size" )) {

		$file_name = str_replace(" ", "", $file_name);
		$file_name = str_replace("é", "e", $file_name);
		$file_name = str_replace("è", "e", $file_name);
		$file_name = str_replace("ê", "e", $file_name);
		$file_name = str_replace("à", "a", $file_name);

		@copy("$file", "$updir/$file_name")
		or die("<p>$langCouldNot</p></tr>");
		$sql = 'SELECT MAX(`id`) FROM `accueil` ';
		$res = db_query($sql,$dbname);
		while ($maxID = mysql_fetch_row($res)) {
			$mID = $maxID[0];
		}
		if($mID<101) $mID = 101;
		else $mID = $mID+1;

		db_query("INSERT INTO accueil VALUES (
					$mID,
					'$link_name',
					'../../courses/$currentCourse/page/$file_name \"target=_blank',
					'external_link',
					'1',
					'0',
					'',
					'HTML_PAGE'
					)", $currentCourse);
		
		$tool_content .=  "<table><tbody><tr>
		<td class=\"success\">$langOkSent.</td></tr>
		</tbody>
		</table>";
		}
		else 
		{
			$tool_content .= "<table><tbody><tr>
			<td class=\"caution\">$langTooBig</td>
			</tr></tbody></table>";
			draw($tool_content, 2);
		}	// else
	}	// if submit

// if not submit

else
	{

					if(empty($_SESSION['key_import_hidden'])){
				$csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
				$_SESSION['key_import_hidden'] = $csrftoken_text;
			}
			$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_import_hidden'],session_id());
		$chaaange = htmlspecialchars($_SERVER[PHP_SELF], ENT_QUOTES, 'UTF-8');

		$tool_content .=  "<p>$langExplanation</p>
		<form method=\"POST\" action=\"$chaaange?submit=yes\" enctype=\"multipart/form-data\">
		<table><thead><tr><th>$langSendPage :</th>
		<td><input type=\"file\" name=\"file\" size=\"35\" accept=\"text/html\"></td>
		</tr><tr><th>$langPgTitle :</th>
		<td><input type=\"Text\" name=\"link_name\" size=\"50\"></td>
		</tr></thead></table>
		<br>
		<input type=\"Submit\" name=\"submit\" value=\"$langAdd\">
		  	<input type ='hidden'name='csrf' value = '{$csrftoken}'>

		</form>";
	}	// else
}	// if uid=prof_id

else {
	// Print You are not identified as responsible for this course
	$tool_content .=  "<table><tbody><tr><td class=\"caution\">$langNotAllowed
	</td></tr></tbody></table>";
}	// else

$tool_content .=  "";
draw($tool_content, 2);
?>

