<?php

$open=true;
require "../lib/site.inc.php";
$controller = new \Steampunk\ChooseGameTypeController($site,$_POST);
//phpinfo();
header("Location: ".$controller->getRedirect());