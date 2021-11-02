<?php 

include_once("../../hash_equals.php");

$require_login = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"../profile/profile.php", "name"=> $langModifProfile);
$uid = intval($uid);

if(empty($_SESSION['key_unreguser'])){
	$csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
	$_SESSION['key_unreguser'] = $csrftoken_text;
}

$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_unreguser'],session_id());

if ($_POST['submit'])	{

	if(hash_equals($csrftoken,$csrf))
	{
		echo 'correct token';

		unset($_SESSION['key_unreguser']);
		$_SESSION['key_unreguser']='';
		if (empty($_SESSION['key_unreguser']))
			echo 'to kanei empty';

	}
	else
	{

		session_destroy();
		$redirect='../../../index.php'; 
        header("Location: $redirect");

        exit();

	}

	if ($_POST['persoStatus'] == "no") {
		header("Location: ../profile/profile.php");
	}
	
	else {
		if (isset($uid)) {
			
			$tool_content =  "<table width=99%><tbody>";
			$tool_content .=  "<tr>";
			$tool_content .=  "<td class=\"success\">";
			db_query("DELETE from user WHERE user_id = '$uid'");
			if (mysql_affected_rows() > 0) {
				$tool_content .=  "<p><b>$langDelSuccess</b></p>";
				$tool_content .=  "<p>$langThanks</p>";
				$tool_content .=  "<br><a href='../../index.php?logout=yes'>$langLogout</a>";
				unset($_SESSION['uid']);
			} else {
				$tool_content .=  "<p>$langError</p>";
				$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p><br>";
				//			exit;
			}
			
			$tool_content .= "</td></tr></tbody></table>";  
			
		}
		
	}

}

if (isset($_SESSION['uid'])) {
	draw($tool_content, 1);
} else {
	draw($tool_content, 0);
}
