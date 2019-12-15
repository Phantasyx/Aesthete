<?php

$open=true;
require "../lib/site.inc.php";
$controller = new \Steampunk\GameSetupController($site,$_SESSION);

//phpinfo();
header("Location: ".$controller->getRedirect());