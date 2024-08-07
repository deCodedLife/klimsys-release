<?php

/**
 * @file
 * Подключение сторонних библиотек
 */


/**
 * Подключение composer
 */

if ( file_exists( $API::$configs[ "paths" ][ "root" ] . "/vendor/autoload.php" ) ) {

    require_once( $API::$configs[ "paths" ][ "root" ] . "/vendor/autoload.php" );

} else {

    $API->returnResponse( "Composer не отвечает", 500 );

} // if. file_exists. /vendor/autoload.php


/**
 * Подключение JWT
 */
use \Firebase\JWT\JWT;
$API->JWT = new JWT;


/**
 * Подключение FluentPDO
 */
$pdo = new PDO(
    "mysql:host={$API::$configs[ "db" ][ "host" ]};dbname={$API::$configs[ "db" ][ "name" ]};charset=UTF8;port=3306",
    $API::$configs[ "db" ][ "user" ],
    $API::$configs[ "db" ][ "password" ],
    [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
);
$API->DB = new \Envms\FluentPDO\Query( $pdo );
$API->DB_connection = mysqli_connect(
    $API::$configs[ "db" ][ "host" ],
    $API::$configs[ "db" ][ "user" ],
    $API::$configs[ "db" ][ "password" ],
    $API::$configs[ "db" ][ "name" ]
);


/**
 * Подключение Sphinx
 */
require_once( $API::$configs[ "paths" ][ "libs" ] . "/sphinx/api/sphinxapi.php" );