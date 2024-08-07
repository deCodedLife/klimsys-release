<?php

/**
 * @file
 * Получение доступных переменных
 */


/**
 * Сформированный список переменных
 */
$resultVariablesList = [];

/**
 * Список доступных объектов
 */
$objectsList = [];


/**
 * Обход директории объектов
 */
function scanObjectDir ( $objectsDirPath ) {

    global $objectsList;


    $objectsDir = dir( $objectsDirPath );

    while ( ( $objectScheme = $objectsDir->read() ) !== false ) {

        if ( $objectScheme == "." || $objectScheme == ".." ) continue;

        $objectsList[] = substr( $objectScheme, 0, strpos( $objectScheme, "." ) );

    } // while. $objectsDir->read()

    $objectsDir->close();

} // function. scanObjectDir


/**
 * Формирование списка объектов
 */

scanObjectDir( $API::$configs[ "paths" ][ "public_object_schemes" ] );
scanObjectDir( $API::$configs[ "paths" ][ "system_object_schemes" ] );

$objectsList = array_unique( $objectsList );


/**
 * Формирование списка переменных
 */

foreach ( $objectsList as $objectArticle ) {

    if (
        ( $objectArticle !== "visits" )
    ) continue;


    /**
     * Получение схемы объекта
     */

    $objectScheme = $API->loadObjectScheme( $objectArticle );
    if ( !$objectScheme[ "properties" ] ) continue;

    $resultVariablesList[ $objectArticle ] = [
        "title" => $objectScheme[ "title" ],
        "variables" => []
    ];


    /**
     * Получение св-в объекта
     */

    foreach ( $objectScheme[ "properties" ] as $property ) {

        if ( $property[ "is_variable" ] === false ) continue;


        /**
         * Исключения
         */

        $isContinue = false;

        switch ( $property[ "data_type" ] ) {

            case "password":
            case "boolean":
                $isContinue = true;

        } // switch. $property[ "data_type" ]

        if ( $isContinue ) continue;


        $resultVariablesList[ $objectArticle ][ "variables" ][ $property[ "article" ] ] = [
            "title" => $property[ "title" ],
            "field_type" => $property[ "field_type" ]
        ];


        /**
         * Получение внутренних св-в
         */

        if ( $property[ "list_donor" ] || $property[ "join" ] ) {

            /**
             * Схема внутреннего объекта
             */

            $innerObject = "";

            if ( $property[ "list_donor" ][ "table" ] ) $innerObject = $property[ "list_donor" ][ "table" ];
            if ( $property[ "join" ][ "donor_table" ] ) $innerObject = $property[ "join" ][ "donor_table" ];

            if ( !$innerObject ) continue;



            /**
             * Получение внутренней схемы объекта
             */

            $innerObjectScheme = $API->loadObjectScheme( $innerObject, false );

            if ( $innerObjectScheme[ "properties" ] ) {

                foreach ( $innerObjectScheme[ "properties" ] as $innerProperty ) {

                    if ( $innerProperty[ "is_variable" ] === false ) continue;


                    /**
                     * Игнорирование вложенных св-в
                     */
                    if ( $innerProperty[ "list_donor" ] ) continue;
                    if ( $innerProperty[ "join" ] ) continue;


                    /**
                     * Исключения
                     */

                    $isContinue = false;

                    switch ( $innerProperty[ "data_type" ] ) {

                        case "password":
                        case "boolean":
                            $isContinue = true;

                    } // switch. $innerProperty[ "data_type" ]

                    if ( $isContinue ) continue;


                    $resultVariablesList[ $objectArticle ][ "variables" ][ $property[ "article" ] ][ "inner_variables" ][ $innerProperty[ "article" ] ] = [
                        "title" => $innerProperty[ "title" ],
                        "field_type" => $innerProperty[ "field_type" ]
                    ];

                } // foreach. $innerObjectScheme[ "properties" ]

            } // if. $innerObjectScheme[ "properties" ]

        } // if. $property[ "list_donor" ] || $property[ "join" ]

    } // foreach. $objectScheme[ "properties" ]

} // foreach. $objectsList


$response[ "data" ] = $resultVariablesList;