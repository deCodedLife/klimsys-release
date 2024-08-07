<?php

/**
 * @file
 * Получение схем
 */


/**
 * Запрошенные схемы
 */
$result = [];

/**
 * Типы запрашиваемых схем
 */
$schemeTypes = [];


/**
 * Получение типов запрашиваемых схем
 */
if ( $requestData->scheme_type ) $schemeTypes[] = $requestData->scheme_type;
else $schemeTypes = [ "command", "db", "object", "page" ];


/**
 * Получение детальной информации о схеме.
 * Используется, когда запрошена конкретная схема
 */

if ( $requestData->scheme_type && $requestData->scheme_name ) {

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

    if ( !$schemeDir ) $API->returnResponse( [] );


    /**
     * Получение пути к схеме
     */
    $schemePath = "$schemeDir/$requestData->scheme_name.json";


    /**
     * Подключение схемы
     */
    if ( file_exists( $schemePath ) ) $scheme = file_get_contents( $schemePath );
    else $API->returnResponse( "Отсутствует схема", 500 );


    /**
     * Декодирование схемы объекта
     */
    try {

        $scheme = json_decode( $scheme, true );

        if ( $scheme === null ) $API->returnResponse( "Ошибка обработки схемы объекта", 500 );

    } catch ( Exception $error ) {

        $API->returnResponse( "Несоответствие схеме объекта", 500 );

    } // try. json_decode. $scheme


    $API->returnResponse( $scheme );

} // if. $requestData->scheme_type && $requestData->scheme_name


/**
 * Обход типов схем.
 * Используется, когда запрошен общий список схем
 */

foreach ( $schemeTypes as $schemeType ) {

    $result[ $schemeType ] = [];


    /**
     * Директория типа схем
     */
    $schemeDirPath = "";


    /**
     * Получение директории типа схем
     */
    switch ( $schemeType ) {

        case "command":
            $schemeDirPath = $API::$configs[ "paths" ][ "public_command_schemes" ];
            break;

        case "db":
            $schemeDirPath = $API::$configs[ "paths" ][ "public_db_schemes" ];
            break;

        case "object":
            $schemeDirPath = $API::$configs[ "paths" ][ "public_object_schemes" ];
            break;

        case "page":
            $schemeDirPath = $API::$configs[ "paths" ][ "public_page_schemes" ];
            break;

    } // switch. $schemeType

    if ( !$schemeDirPath ) continue;


    /**
     * Обход директории типа схем
     */

    $schemeDir = dir( $schemeDirPath );

    while ( ( $schemeFile = $schemeDir->read() ) !== false ) {

        if ( ( $schemeFile === "." ) || ( $schemeFile === ".." ) ) continue;


        /**
         * Проверка, является ли схема файлом
         */
        if ( strpos( $schemeFile, "." ) ) {

            /**
             * Обновление списка запрошенных схем
             */
            $result[ $schemeType ][] = $schemeFile;

        } else {

            /**
             * Обход внутренней директории схем
             */

            $schemeSubDir = dir( "$schemeDirPath/$schemeFile" );

            while ( ( $schemeSubFile = $schemeSubDir->read() ) !== false ) {

                if ( ( $schemeSubFile === "." ) || ( $schemeSubFile === ".." ) ) continue;


                /**
                 * Обновление списка запрошенных схем
                 */
                $result[ $schemeType ][ $schemeFile ][] = $schemeSubFile;

            } // while. $schemeSubDir->read()


        } // if. strpos( $schemeFile, "." )

    } // while. $schemeDir->read()

    $schemeDir->close();

} // foreach. $schemeTypes


$response[ "data" ] = $result;