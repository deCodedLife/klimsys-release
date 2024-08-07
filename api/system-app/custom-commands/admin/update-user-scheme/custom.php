<?php

/**
 * Сформированная схема пользователя
 */
$resultUserScheme = [];

/**
 * Подключение базы данных
 */
$DB_connection = mysqli_connect(
    $API::$configs[ "db" ][ "host" ],
    $API::$configs[ "db" ][ "user" ],
    $API::$configs[ "db" ][ "password" ],
    $API::$configs[ "db" ][ "name" ]
);
if ( !$DB_connection ) $API->returnResponse( "Не удалось подключится к базе данных" );


/**
 * Получение текущей схемы пользователя
 */
$requestData->scheme_body = str_replace( "'", "\"", $requestData->scheme_body );
$currentScheme = json_decode( $requestData->scheme_body, true );


/**
 * Обход объектов
 */

foreach ( $currentScheme as $schemeObjectArticle => $schemeObject ) {

    /**
     * Добавление объекта в сформированную схему
     */
    if ( $schemeObject[ "type" ] == "custom" )
        $resultUserScheme[ $schemeObjectArticle ][ "title" ] = $schemeObject[ "title" ];


    /**
     * Обход областей формы
     */

    foreach ( $schemeObject[ "form" ] as $formAreaPosition => $formArea ) {

        /**
         * Добавление области в сформированную схему
         */
        if ( $formArea[ "type" ] == "custom" ) {

            $resultUserScheme[ $schemeObjectArticle ][ "areas" ][] = [
                "position" => $formAreaPosition,
                "size" => $formArea[ "size" ]
            ];

        } // if. $formArea[ "type" ] == "custom"


        /**
         * Обход блоков формы
         */

        foreach ( $formArea[ "blocks" ] as $formBlockPosition => $formBlock ) {

            /**
             * Добавление блока в сформированную схему
             */
            if ( $formBlock[ "type" ] == "custom" ) {

                $resultUserScheme[ $schemeObjectArticle ][ "blocks" ][] = [
                    "area_position" => $formAreaPosition,
                    "block_position" => $formBlockPosition
                ];

            } // if. $formBlock[ "type" ] == "custom"


            /**
             * Обход полей формы
             */

            foreach ( $formBlock[ "fields" ] as $formPropertyPosition => $formProperty ) {

                /**
                 * Добавление поля в сформированную схему
                 */
                if ( $formProperty[ "type" ] == "custom" ) {

                    $resultUserScheme[ $schemeObjectArticle ][ "properties" ][ $formProperty[ "article" ] ] = [
                        "title" => $formProperty[ "title" ],
                        "field_type" => $formProperty[ "field_type" ],
                        "area_position" => $formAreaPosition,
                        "block_position" => $formBlockPosition,
                        "property_position" => $formPropertyPosition
                    ];

                } // if. $formProperty[ "type" ] == "custom"

            } // foreach. $formBlock[ "fields" ]

        } // foreach. $formArea[ "blocks" ]

    } // foreach. $schemeObject[ "form" ]

} // foreach. $currentScheme


/**
 * Обновление схемы
 */
if (
    !file_put_contents(
        $API::$configs[ "paths" ][ "public_user_schemes" ] . "/" . $API::$configs[ "company" ] . ".json",
        json_encode( $resultUserScheme )
    )
) $API->returnResponse( false );


/**
 * Обновление базы данных
 */

foreach ( $resultUserScheme as $objectArticle => $object ) {

    /**
     * Проверка типа схемы (системная/пользовательская)
     */
    $isUserScheme = false;
    if ( $object[ "title" ] ) $isUserScheme = true;


    if ( $isUserScheme ) {

        /**
         * Определение названия таблицы
         */
        $clearObjectArticle = $objectArticle;
        $objectArticle = "us__$objectArticle";


        /**
         * Добавление пользовательской таблицы
         */

        mysqli_query(
            $DB_connection,
            "CREATE TABLE IF NOT EXISTS `$objectArticle` ( id INT PRIMARY KEY AUTO_INCREMENT )"
        );

        mysqli_query(
            $DB_connection,
            "ALTER TABLE `$objectArticle` ADD `is_system` char(1) DEFAULT 'N'"
        );

    } // if. $isUserScheme


    /**
     * Добавление пользовательских св-в
     */

    foreach ( $object[ "properties" ] as $propertyArticle => $property ) {

        $isContinue = false;


        /**
         * Определение названия св-ва
         */
        $propertyArticle = "us__$propertyArticle";


        /**
         * Формирование команды на добавление св-ва
         */

        $addPropertyCommand = "ALTER TABLE `$objectArticle` ADD $propertyArticle";

        switch ( $property[ "field_type" ] ) {

            case "string":
                $addPropertyCommand .= " varchar(255)";
                break;

            case "textarea":
                $addPropertyCommand .= " text";
                break;

            case "integer":
                $addPropertyCommand .= " int";
                break;

            case "float":
                $addPropertyCommand .= " float";
                break;

            default:
                $isContinue = true;

        } // switch. $property[ "field_type" ]

        if ( $isContinue ) continue;


        mysqli_query( $DB_connection, $addPropertyCommand );

    } // foreach. $object[ "properties" ]

} // foreach. $resultUserScheme


$API->returnResponse( true );