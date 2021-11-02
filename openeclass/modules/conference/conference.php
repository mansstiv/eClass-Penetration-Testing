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
$require_help = TRUE;
$helpTopic = 'Conference';
$tool_content = "";
include '../../include/baseTheme.php';
include_once("../../hash_equals.php");

if(!isset($MCU))
	$MCU="";

include_once("../../hash_equals.php");
/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_CHAT');
/**************************************/

$nameTools = $langConference;


if (isset($_GET['hide']))
{

    if (isset($_GET['token'])){

        if(empty($_SESSION['key_baseTheme'])){
            $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
            $_SESSION['key_baseTheme'] = $csrftoken_text;
        }

        $csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_baseTheme'],session_id());

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
}


// guest user not allowed
if (check_guest()) {
	$tool_content .= "
       <table width=\"99%\">
       <tbody>
       <tr>
         <td class=\"extraMessage\"><p>$langNoGuest</p></td>
       </tr>
       </tbody>
       </table>";
	draw($tool_content, 2, 'conference');
}

if (!($uid) or !($_SESSION['uid'])) {
	$tool_content .= "
       <table width=\"99%\">
       <tbody>
       <tr>
         <td class=\"extraMessage\"><p>$langNoAliens</p></td>
       </tr>
       </tbody>
       </table>";
	draw($tool_content, 2, 'conference');
}


$head_content = '<script type="text/javascript">
function prepeare_message()
{
    document.chatForm.chatLine.value=document.chatForm.msg.value;
    document.chatForm.msg.value = "";
    document.chatForm.msg.focus();
    return true;
}
</script>';

if ($is_adminOfCourse) {

    if(empty($_SESSION['key_messageList_actions'])){
        $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
        $_SESSION['key_messageList_actions'] = $csrftoken_text;
    }

    $csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_messageList_actions'],session_id());

    $tool_content .= "
      <div id=\"operations_container\">
        <ul id=\"opslist\">
          <li><a href='messageList.php?reset=true&token=$csrftoken' target='messageList' class=small_tools>$langWash</a></li>
          <li><a href='messageList.php?store=true&token=$csrftoken' target='messageList' class=small_tools>$langSave</a></li>
        </ul>
      </div>";
}

if(empty($_SESSION['key_messageList_form'])){
    $csrftoken_text = md5(openssl_random_pseudo_bytes(32,$cstrong));
    $_SESSION['key_messageList_form'] = $csrftoken_text;
}

$csrftoken = hash_hmac('sha256','this is a random text:newtopic' . $_SESSION['key_messageList_form'],session_id());

$tool_content .= "
<form name='chatForm' action='messageList.php' method='get' target='messageList' onSubmit='return prepeare_message();'>
  <table width='99%' class='FormData'>
  <thead>
  <tr>
    <th>&nbsp;</th>
    <td>

      <b>$langTypeMessage</b><br />
      <input type='text' name='msg' size='80'style='border: 1px solid #CAC3B5; background: #fbfbfb;'>
      <input type='hidden' name='chatLine'>
      <input type ='hidden'name='csrf' id='csrf' value = '{$csrftoken}'>
      <input type='submit' name='submit' value=' >> '>

    </td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><iframe frameborder='0' src='messageList.php' width='100%' height='300' name='messageList' style='background: #fbfbfb; border: 1px solid #CAC3B5;'><a href='messageList.php'>Message list</a></iframe></td>
  </tr>
  </thead>
  </table>
</form>
  ";
add_units_navigation(TRUE);
draw($tool_content, 2, 'conference', $head_content);
