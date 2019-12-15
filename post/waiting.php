<?php

require "../lib/site.inc.php";

$controller = new \Steampunk\WaitingController($site,$_SESSION[\Steampunk\User::SESSION_NAME]);
//print_r($controller->getRedirect());
header("location: ".$controller->getRedirect());
//phpinfo();