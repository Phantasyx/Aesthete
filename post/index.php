<?php

$open=true;
require "../lib/site.inc.php";

$controller = new \Steampunk\IndexController($site,$_SESSION,$_POST);
print_r($_POST);
header("location: ".$controller->getRedirect());
//phpinfo();