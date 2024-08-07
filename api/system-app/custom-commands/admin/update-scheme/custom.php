<?php

/**
 * @file
 * Обновление схем
 */


/**
 * Директория типа схем
 */
$schemeDir = "";


/**
 * Получение директории типа схем
 */
switch ( $requestData->scheme_type ) {

    case "command":
        $schemeDir = $API::$configs[ "paths" ][ "public_command_schemes" ];
        break;

    case "db":
        $schemeDir = $API::$configs[ "paths" ][ "public_db_schemes" ];
        break;

    case "object":
        $schemeDir = $API::$configs[ "paths" ][ "public_object_schemes" ];
        break;

    case "page":
        $schemeDir = $API::$configs[ "paths" ][ "public_page_schemes" ];
        break;

} // switch. $requestData->scheme_type


/**
 * Получение пути к схеме
 */
$schemePath = "$schemeDir/$requestData->scheme_name.json";


/**
 * Добавление директории схемы
 */

$schemeNamePath = explode( "/", $requestData->scheme_name );

if ( !is_dir( $schemeDir ) ) mkdir( $schemeDir );
if ( count( $schemeNamePath ) > 1 ) mkdir( $schemeDir . "/" . $schemeNamePath[ 0 ] );


/**
 * Обновление схемы
 */
if ( !file_put_contents( $schemePath, $requestData->scheme_body ) ) $API->returnResponse( false );


$API->returnResponse( true );