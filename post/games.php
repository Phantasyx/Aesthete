<?php

$open=true;
require "../lib/site.inc.php";
$controller = new \Steampunk\GamesController($site,$_POST,$_SESSION);

header("Location: ".$controller->getRedirect());