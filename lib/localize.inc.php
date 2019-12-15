<?php
/**
 * Created by PhpStorm.
 * User: jingqiyang
 * Date: 4/6/17
 * Time: 6:45 PM
 */
/**
 * Function to localize our site
 * @param $site The Site object
 */
return function(Steampunk\Site $site) {
// Set the time zone
    date_default_timezone_set('America/Detroit');
    $site->setEmail('greissmo@cse.msu.edu');
    $site->setRoot('/~greissmo/project2');
    $site->dbConfigure('mysql:host=mysql-user.cse.msu.edu;dbname=greissmo',
        'greissmo',       // Database user
        '3KEq9uxpNABaFPZ6',     // Database password
        'p2_');            // Table prefix
};