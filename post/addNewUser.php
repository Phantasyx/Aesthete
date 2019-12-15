<?php

$open=true;
require "../lib/site.inc.php";
$controller = new \Steampunk\NewUserController($site,$_POST);
//phpinfo();
header("Location: ".$site->getRoot());