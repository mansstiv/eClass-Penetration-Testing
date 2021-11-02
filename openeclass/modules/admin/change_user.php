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

/*===========================================================================
	change_user.php
==============================================================================
	@Description: Allows platform admin to login as another user without
         asking for a password
==============================================================================
*/

$require_admin = TRUE;
include_once("../../hash_equals.php");
include '../../include/baseTheme.php';
$nameTools = $langChangeUser;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$tool_content = '';


if(empty($_SESSION['key_change_user'])){
$csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
$_SESSION['key_change_user'] = $csrftoken_text;
}


$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_change_user'],session_id());


if (isset($_POST['username'])) {

    if(hash_equals($csrftoken,$csrf))
    {

        //unset($_SESSION['key_change_user']); 
        //$_SESSION['key_change_user']='';


    }
    else
    {
        session_destroy();
        $redirect='../../../index.php';
        header("Location: $redirect");
        exit();

    }

	$result = db_query("SELECT user_id, nom, username, password, prenom, statut, email, iduser is_admin, perso, lang
                FROM user LEFT JOIN admin
                ON user.user_id = admin.iduser
                WHERE username=" . autoquote($_POST['username']));

    
	if (mysql_num_rows($result) > 0) {

        unset($_SESSION['key_change_user']);
        $_SESSION['key_change_user']='';
                $myrow = mysql_fetch_array($result);
                $_SESSION['uid'] = $myrow["user_id"];
                $_SESSION['nom'] = $myrow["nom"];
                $_SESSION['prenom'] = $myrow["prenom"];
                $_SESSION['statut'] = $myrow["statut"];
                $_SESSION['email'] = $myrow["email"];
                $_SESSION['is_admin'] = $myrow["is_admin"];
                $userPerso = $myrow["perso"];
                $userLanguage = $myrow["lang"];
	        if ($userPerso == "yes" and isset($_SESSION['perso_is_active'])) {
        		$_SESSION['user_perso_active'] = false;
                } else {
        		$_SESSION['user_perso_active'] = true;
                }
        	if ($userLanguage == "en") {
	        	$_SESSION['langswitch'] = "english";
	        	$langChangeLang = $_SESSION['langLinkText'] = "Ελληνικά";
	        	$switchLangURL = $_SESSION['langLinkURL'] = "?localize=el";
	        } elseif ($userLanguage == "el") {
        		$_SESSION['langswitch'] = "greek";
	        	$langChangeLang = $_SESSION['langLinkText'] = "English";
		        $switchLangURL = $_SESSION['langLinkURL'] = "?localize=en";
        	}
		$language = $_SESSION['langswitch'];
                header('Location: ' . $urlServer);
                exit;
        } else {
                $tool_content = "<div class='caution_small'>" . sprintf($langChangeUserNotFound, $_POST['username']) . "</div>";
        }
} 

$urlPath = htmlspecialchars($_SERVER ['PHP_SELF'], ENT_QUOTES, 'UTF-8');

$tool_content .= "<form action='$urlPath' method='post'>$langUsername: <input type='text' name='username' />
<input type ='hidden'name='csrf' value = '{$csrftoken}'></form>";
draw($tool_content,3,'admin');
