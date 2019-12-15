<?php

$open = true;
require "../lib/site.inc.php";
$controller = new \Steampunk\PasswordValidateController($site,$_POST);
header("location: ".$controller->getRedirect());