<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/


/*===========================================================================
	newuser.php
* @version $Id: newuser.php,v 1.35 2009-12-03 14:31:20 adia Exp $
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================

 	Purpose: The file displays the form that that the candidate user must fill
 	in with all the basic information.

==============================================================================
*/

include_once("../../hash_equals.php");

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
include 'auth.inc.php';

$urlPath = htmlspecialchars($_SERVER ['PHP_SELF'], ENT_QUOTES, 'UTF-8');

if(empty($_SESSION['key_newuser'])){
	$csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
	$_SESSION['key_newuser'] = $csrftoken_text;
}

$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_newtopic'],session_id());


$nameTools = $langUserDetails;
// Main body
$navigation[] = array("url"=>"registration.php", "name"=> $langNewUser);

$tool_content = "";	// Initialise $tool_content

if (isset($close_user_registration) and $close_user_registration == TRUE) {
	$tool_content .= "<div class='td_main'>$langForbidden</div>";
        draw($tool_content,0);
	exit;
 }
 
$lang = langname_to_code($language);

// display form
if (!isset($submit)) {
	// Main body
	@$tool_content .= "<form action='$urlPath' method='post'>
	<table width='99%' style='border: 1px solid #edecdf;'>
	<thead>
	<tr>
	<td>
	<table width='100%' align='left' class='FormData'>
	<thead>
	<tr>
	<th class='left' width='220'>$langName</th>
	<td colspan='2'><input type='text' name='prenom_form' value='$prenom_form' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langSurname</th>
	<td colspan='2'><input type='text' name='nom_form' value='$nom_form' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langUsername</th>
	<td colspan='2'><input type='text' name='uname' value='$uname' size='20' maxlength='20' class='FormData_InputText' />&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langPass</th>
	<td colspan='2'><input type='password' name='password1' size='20' maxlength='20' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langConfirmation</th>
	<td colspan='2'><input type='password' name='password' size='20' maxlength='20' class='FormData_InputText' />&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langEmail</th>
	<td valign='top'><input type='text' name='email' value='$email' class='FormData_InputText' /></td>
	<td><small>$langEmailNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langAm</th>
	<td colspan='2' valign='top'><input type='text' name='am' value='$am' class='FormData_InputText' /></td>
	</tr>
	<tr>
	<th class='left'>$langFaculty</th>
		<td colspan='2'><select name='department'>";
	$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
	while ($dep = mysql_fetch_array($deps)) {
		$tool_content .= "\n<option value='".$dep[1]."'>".$dep[0]."</option>";
	}
	$tool_content .= "\n</select>
	</td>
	</tr>
	<tr>
	<th class='left'>$langLanguage</th>
	<td width='1'>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
	<td><small>$langTipLang2</small></td>
	</tr>
	<tr>
	<th class='left'>&nbsp;</th>
	<td colspan='2'>
	<input type='hidden' name='auth' value='1' />
	<input type='submit' name='submit' value='".$langRegistration."' />
	<input type ='hidden'name='csrf' value = '{$csrftoken}'>
	</td>
	</tr>
	</thead>
	</table>
	<div align='right'><small>$langRequiredFields</small></div>
	</td>
	</tr>
	</thead>
	</table>
	</form>";

} else {

	if(hash_equals($csrftoken,$csrf))
	{
		unset($_SESSION['key_newuser']);
		$_SESSION['key_newuser']='';
	}
	else
	{

		session_destroy();
		$redirect='../../index.php'; 
        header("Location: $redirect");

        exit();

	}


    // Create connection
    $conn = mysqli_connect("db", "root", "gSQw8OyTPD");

    // Check connection
    if (mysqli_connect_errno()) {
      die("Connection failed: " . $conn->connect_error);
    }

    mysqli_set_charset($conn, "utf8");
    $stmt = mysqli_stmt_init($conn);


	// trim white spaces in the end and in the beginning of the word
	$uname = preg_replace('/\ +/', ' ', trim(isset($_POST['uname'])?$_POST['uname']:''));
	// registration
	$registration_errors = array();
	// check if there are empty fields
	if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($uname)) {
		$registration_errors[] = $langEmptyFields;
	} else {

		// check if the username is already in use

        $q2 = "SELECT username FROM `$mysqlMainDb`.user WHERE username=?";
        $tempUname = escapeSimple($uname);


        mysqli_stmt_prepare($stmt, $q2);
        mysqli_stmt_bind_param($stmt, "s", $tempName);
        mysqli_stmt_execute($stmt);
        
        $username_check = mysqli_stmt_get_result($stmt);
        if ($myusername = mysqli_fetch_array($username_check)) {
            $registration_errors[] = $langUserFree;
        }

        // Close statement
        mysqli_stmt_close($stmt);
/*
	// check if the username is already in use
		$q2 = "SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'";
		$username_check = mysql_query($q2);
		if ($myusername = mysql_fetch_array($username_check)) {
			$registration_errors[] = $langUserFree;
		}
*/
	}
	if (!empty($email) and !email_seems_valid($email)) {
		$registration_errors[] = $langEmailWrong;
	}
	$auth_method_settings = get_auth_settings($auth);
	if (!empty($auth_method_settings) and $auth != 1) {
		$password = $auth_method_settings['auth_name'];
	} else {
		// check if the two passwords match
		if ($password != $_POST['password1']) {
			$registration_errors[] = $langPassTwice;
		} elseif (strtoupper($password) == strtoupper($uname)
			or strtoupper($password) == strtoupper($nom_form)
			or strtoupper($password) == strtoupper($prenom_form)
			or strtoupper($password) == strtoupper($email)) {
				// if the passwd is too easy offer a password sugestion
				$registration_errors[] = $langPassTooEasy . ': <strong>' .
					substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8) . '</strong>';
			}
	}
	if (count($registration_errors) == 0) {
	$emailsubject = "$langYourReg $siteName";
			$uname = unescapeSimple($uname); // un-escape the characters: simple and double quote
			$password = unescapeSimple($password);
			if((!empty($auth_method_settings)) && ($auth!=1)) {
				$emailbody = "$langDestination $prenom_form $nom_form\n" .
					"$langYouAreReg $siteName $langSettings $uname\n" .
					"$langPassSameAuth\n$langAddress $siteName: " .
					"$urlServer\n$langProblem\n$langFormula" .
					"$administratorName $administratorSurname" .
					"$langManager $siteName \n$langTel $telephone \n" .
					"$langEmail: $emailhelpdesk";
		} else {
			$emailbody = "$langDestination $prenom_form $nom_form\n" .
				"$langYouAreReg $siteName $langSettings $uname\n" .
				"$langPass: $password\n$langAddress $siteName: " .
				"$urlServer\n$langProblem\n$langFormula" .
				"$administratorName $administratorSurname" .
				"$langManager $siteName \n$langTel $telephone \n" .
				"$langEmail: $emailhelpdesk";
		}
	
	send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
	$registered_at = time();
	$expires_at = time() + $durationAccount;  //$expires_at = time() + 31536000;
	
	// manage the store/encrypt process of password into database
	$authmethods = array("2","3","4","5");
	$uname = escapeSimple($uname);  // escape the characters: simple and double quote
	$password = escapeSimpleSelect($password);  // escape the characters: simple and double quote
	if(!in_array($auth,$authmethods)) {
		$password_encrypted = md5($password);
	} else {
		$password_encrypted = $password;
	}

	// Defense against XSS attack
	$nom_form = htmlspecialchars($nom_form, ENT_QUOTES, 'UTF-8');
	$prenom_form = htmlspecialchars($prenom_form, ENT_QUOTES, 'UTF-8');
	$uname = htmlspecialchars($uname, ENT_QUOTES, 'UTF-8');
	$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
	$am = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');


	$q1 = "INSERT INTO `$mysqlMainDb`.user
	(user_id, nom, prenom, username, password, email, statut, department, am, registered_at, expires_at, lang)
	VALUES ('NULL', ?, ?, ?, ?, ?,'5', ?, ?, ".$registered_at.",".$expires_at.", ?)";	

	$stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $q1);
    mysqli_stmt_bind_param($stmt, "sssssiss", $nom_form, $prenom_form, $uname, $password_encrypted, $email,
							$department, $am, $lang);
    mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

/*
	$q1 = "INSERT INTO `$mysqlMainDb`.user
	(user_id, nom, prenom, username, password, email, statut, department, am, registered_at, expires_at, lang)
	VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email','5',
		'$department','$am',".$registered_at.",".$expires_at.",'$lang')";
*/

	$inscr_user = mysqli_stmt_get_result($stmt);
	
	$last_id = mysqli_insert_id($conn);	
	$myQuery = "SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id=?";
	$stmt = mysqli_stmt_init($conn);

    mysqli_stmt_prepare($stmt, $myQuery);
    mysqli_stmt_bind_param($stmt, "i", $last_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);

	while ($myrow = mysqli_fetch_array($result)) {
		$uid=$myrow[0];
		$nom=$myrow[1];
		$prenom=$myrow[2];
	}

	mysqli_stmt_close($stmt);
/*

	$result=mysql_query("SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id='$last_id'");		
	while ($myrow = mysql_fetch_array($result)) {
		$uid=$myrow[0];
		$nom=$myrow[1];
		$prenom=$myrow[2];
	}
*/
	$myQuery = "INSERT INTO `$mysqlMainDb`.loginout (loginout.idLog, loginout.id_user, loginout.ip, 
														loginout.when, loginout.action)
				VALUES ('', ?, ?, ?, 'LOGIN')";

	$stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt, $myQuery);
    mysqli_stmt_bind_param($stmt, "iss", $uid, $REMOTE_ADDR, date("Y-m-d"));
    mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);

/*
	mysql_query("INSERT INTO `$mysqlMainDb`.loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action)
	VALUES ('', '".$uid."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");
*/

	$_SESSION['uid'] = $uid;
	$_SESSION['statut'] = 5;
	$_SESSION['prenom'] = $prenom;
	$_SESSION['nom'] = $nom;
	$_SESSION['uname'] = $uname;
	
	// registration form
	$tool_content .= "<table width='99%'><tbody><tr>" .
			"<td class='well-done' height='60'>" .
			"<p>$langDear $prenom $nom,</p>" .
			"<p>$langPersonalSettings</p></td>" .
			"</tr></tbody></table><br /><br />" .
			"<p>$langPersonalSettingsMore</p>";
	} else {
		// errors exist - registration failed
		$tool_content .= "<table width='99%'><tbody><tr>" .
				"<td class='caution' height='60'>";
		foreach ($registration_errors as $error) {
			$tool_content .= "<p>$error</p>";
		}
		
		$tool_content .= "<p><a href='$urlPath?prenom_form=$_POST[prenom_form]&nom_form=$_POST[nom_form]&uname=$_POST[uname]&email=$_POST[email]&am=$_POST[am]'>$langAgain</a></p>" .
					"</td></tr></tbody></table><br /><br />";

	}

	// Close connection
	mysqli_close($conn);

} // end of registration

draw($tool_content,0);
?>
