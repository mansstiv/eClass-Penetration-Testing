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
$require_prof = true;


include_once("../../hash_equals.php");

include '../../include/baseTheme.php';

$nameTools = $langDelCourse;
$tool_content = "";

if($is_adminOfCourse) {
	if(isset($delete)) {


			if (isset($_GET['token'])){

		    if(empty($_SESSION['key_delete_course_admin'])){
		        $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
		        $_SESSION['key_delete_course_admin'] = $csrftoken_text;
		    }

		    $csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_delete_course_admin'],session_id());

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
		else {
		    session_destroy();
		    $redirect='../../../index.php'; 
		    header("Location: $redirect");

		    exit();        
		}


		mysql_select_db("$mysqlMainDb",$db);
		mysql_query("DROP DATABASE `$currentCourseID`");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours WHERE code='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours_user WHERE cours_id='$cours_id'");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours_faculte WHERE code='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.annonces WHERE cours_id='$cours_id'");
		##[BEGIN personalisation modification]############
		mysql_query("DELETE FROM `$mysqlMainDb`.agenda WHERE lesson_code='$currentCourseID'");
		##[END personalisation modification]############
		@mkdir("../../courses/garbage");
		rename("../../courses/$currentCourseID", "../../courses/garbage/$currentCourseID");
		$tool_content .= "<p class=\"success_small\">$langTheCourse <b>($intitule $currentCourseID)</b>  $langHasDel</p><br />
		<p align=\"right\"><a href=\"../../index.php\">".$langBackHome." ".$siteName."</a></p>";
                unset($currentCourseID);
                unset($_SESSION['dbname']);
		draw($tool_content, 1);
		exit();
	} else {

	    if(empty($_SESSION['key_delete_course_admin'])){
	        $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
	        $_SESSION['key_delete_course_admin'] = $csrftoken_text;
	    }

	    $csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_delete_course_admin'],session_id());

	    $urlPath = htmlspecialchars($_SERVER ['PHP_SELF'], ENT_QUOTES, 'UTF-8');

		$tool_content .= "
		<table width=\"99%\">
		<tbody>
		<tr>
		<td class=\"caution_NoBorder\" height='60' colspan='3'>
			<p>$langByDel_A <b>$intitule ($currentCourseID) </b>&nbsp;?  </p>
		</td>
		</tr>
		<tr>
		<th rowspan='2' class='left' width='220'>$langConfirmDel :</th>
		<td width='52' align='center'><a href=\"".$urlPath."?delete=yes&token=$csrftoken\">$langYes</a></td>
		<td><small>$langByDel</small></td>
		</tr>
		<tr>
		<td align='center'><a href=\"infocours.php\">$langNo</a></td>
		<td>&nbsp;</td>
		</tr>
		</tbody>
		</table>";
		
		$tool_content .= "<p align=\"right\"><a href=\"infocours.php\">$langBack</a></p>
		</ul>
		</div>";
	} // else
} else  {
	$tool_content .= "<center><p>$langForbidden</p></center>";
}

draw($tool_content, 2, 'course_info');
?>
