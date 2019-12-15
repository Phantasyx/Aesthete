<?php

$open=true;
require "../lib/site.inc.php";
//phpinfo();
$controller = new \Steampunk\NewGameController($site,$_SESSION,$_POST);
header("location: ".$controller->getRedirect());