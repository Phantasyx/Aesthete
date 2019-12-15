<?php
/**
 * Created by PhpStorm.
 * User: jingqiyang
 * Date: 4/6/17
 * Time: 6:46 PM
 */

/**
 * @file
 * A file loaded for all pages on the site.
 */
require __DIR__ . "/../vendor/autoload.php";
$site = new Steampunk\Site();
$localize = require 'localize.inc.php';
if(is_callable($localize)) {
    $localize($site);
}
// Start the session system
session_start();
$user = null;
if(isset($_SESSION[Steampunk\User::SESSION_NAME])) {
    $user = $_SESSION[Steampunk\User::SESSION_NAME];
}
// redirect if user is not logged in
if(!isset($open) && $user === null) {
    $root = $site->getRoot();
    header("location: $root/");
    exit;
}

define("GAME_SESSION", 'Game');

define("WELCOME_SESSION", 'welcome');


if(!isset($_SESSION[WELCOME_SESSION])) {
    $_SESSION[WELCOME_SESSION] = new Steampunk\Welcome();
}

if(!isset($_SESSION[GAME_SESSION])) {
    $_SESSION[GAME_SESSION] = new Steampunk\Game();
}

$Game=$_SESSION[GAME_SESSION];
$welcome = $_SESSION[WELCOME_SESSION];