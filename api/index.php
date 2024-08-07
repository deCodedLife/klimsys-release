<?php
/**
 * @file
 * Точка входа API.
 * Клиентская часть приложения. Отсюда происходит
 */

try {


//ini_set( "display_errors", true );
    ini_set( 'display_errors',false );
    ini_set( 'error_reporting', E_ALL );

    /**
     * Формирование HTTP заголовков
     */
    header( "Access-Control-Allow-Origin: *" );
    header( "Content-Type: application/json; charset=utf-8" );
    header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );
    header( "Access-Control-Allow-Methods: POST" );
//echo shell_exec( "which php" ), phpversion();

    if ( empty( $_SERVER[ "DOCUMENT_ROOT" ] ) ) $_SERVER[ "DOCUMENT_ROOT" ] = $argv[ 1 ] ?? ".";

    /**
     * Подключение ядра
     */

    if ( file_exists( $_SERVER[ "DOCUMENT_ROOT" ] . "/core/init_core.php" ) ) {

        require_once( $_SERVER[ "DOCUMENT_ROOT" ] . "/core/init_core.php" );

    } else {

        $result = [
            "status" => 500,
            "data" => "Ядро API не отвечает"
        ];


        /**
         * Вывод ответа на запрос, и завершение работы скрипта
         */
        exit( json_encode( $result ) );

    } // if. file_exists. /core/init_core.php



    /**
     * Подключение конфигурации API
     */

    if ( file_exists( $_SERVER[ "DOCUMENT_ROOT" ] . "/core/api_configs.php" ) ) {

        require_once( $_SERVER[ "DOCUMENT_ROOT" ] . "/core/api_configs.php" );

    } else {

        $API->returnResponse( "Конфигурация API не отвечает", 500 );

    } // if. file_exists. /core/api_configs.php


    /**
     * Подключение конфигурации Приложения
     */

    if ( file_exists( $_SERVER[ "DOCUMENT_ROOT" ] . "/core/app_configs.php" ) ) {

        require_once( $_SERVER[ "DOCUMENT_ROOT" ] . "/core/app_configs.php" );

    } else {

        $API->returnResponse( "Конфигурация Приложения не отвечает", 500 );

    } // if. file_exists. /core/app_configs.php


    /**
     * Подключение сторонних библиотек
     */

    if ( file_exists( $API::$configs[ "paths" ][ "core" ] . "/init_libs.php" ) ) {

        require_once( $API::$configs[ "paths" ][ "core" ] . "/init_libs.php" );

    } else {

        $API->returnResponse( "Библиотеки не отвечают", 500 );

    } // if. file_exists. /core/init_libs.php


    /**
     * Подключение языков
     */

    if ( file_exists( $API::$configs[ "paths" ][ "core" ] . "/init_langs.php" ) ) {

        require_once( $API::$configs[ "paths" ][ "core" ] . "/init_langs.php" );

    } else {

        $API->returnResponse( "Не удалось загрузить язык", 500 );

    } // if. file_exists. /core/init_langs.php


    /**
     * Подключение маршрутизации
     */

    if ( file_exists( $API::$configs[ "paths" ][ "core" ] . "/router.php" ) ) {

        require_once( $API::$configs[ "paths" ][ "core" ] . "/router.php" );

    } else {

        $API->returnResponse( "Маршрутизатор не отвечает", 500 );

    } // if. file_exists. $API::$configs[ "paths" ][ "core" ] /router.php

} catch (Throwable $exception) {

    $API->returnResponse( [ $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTrace() ], 500 );

}