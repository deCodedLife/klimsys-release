<?php

/**
 * @file
 * Удаление схем
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
 * Проверка на недопустимые символы
 */
if ( strpos( $requestData->scheme_name, ".." ) ) $API->returnResponse( false );
if ( strpos( $requestData->scheme_name, "//" ) ) $API->returnResponse( false );


/**
 * Получение пути к схеме
 */
$schemePath = "$schemeDir/$requestData->scheme_name.json";


/**
 * Удаление схемы
 */
if ( !unlink( $schemePath ) ) $API->returnResponse( false );


$API->returnResponse( true );