<?php

require_once(__DIR__."/includes/userAreaIncludes.php");

echo $m->render('userLoggedIn', ["username"=>$auth->getUsername(), "user_area"=>true]);

require_once("../../../secure/scripts/ut_disconnect.php");

?>