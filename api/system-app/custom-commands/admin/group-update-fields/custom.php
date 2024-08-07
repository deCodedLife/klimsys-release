<?php

/**
 * @file
 * Поля для группового редактирования
 */


/**
 * Список св-в Объекта
 */
$objectProperties = [];


/**
 * Формирование пути к схеме объекта
 */

$publicSchemePath = $API::$configs[ "paths" ][ "public_object_schemes" ] . "/" . $requestData->scheme_name . ".json";
$systemSchemePath = $API::$configs[ "paths" ][ "system_object_schemes" ] . "/" . $requestData->scheme_name . ".json";


/**
 * Подключение схемы страницы
 */

$objectScheme = [];

if ( file_exists( $publicSchemePath ) ) $objectScheme = file_get_contents( $publicSchemePath );
elseif ( file_exists( $systemSchemePath ) ) $objectScheme = file_get_contents( $systemSchemePath );
else $API->returnResponse( "Отсутствует схема объекта", 500 );


/**
 * Декодирование схемы запроса
 */
try {

    $objectScheme = json_decode( $objectScheme, true );
    if ( $objectScheme === null ) $API->returnResponse( "Ошибка обработки схемы объекта", 500 );

} catch ( Exception $error ) {

    $API->returnResponse( "Несоответствие схеме объекта", 500 );

} // try. json_decode. $pageScheme


/**
 * Проверка доступов
 */
if ( !$API->validatePermissions( $objectScheme[ "required_permissions" ] ) )
    $API->returnResponse( "Нет доступа к объекту", 403 );


/**
 * Обработка св-в объекта
 */

foreach ( $objectScheme[ "properties" ] as $property ) {

    /**
     * Проверка участия поля в групповом редактировании
     */
    if ( $property[ "use_in_group_update" ] === false ) continue;

    /**
     * Проверка блокировки поля
     */
    if ( !in_array( "update", $property[ "use_in_commands" ] ) ) continue;


    /**
     * Обработка связанных таблиц
     */

    if (
        ( $property[ "list_donor" ][ "table" ] || $property[ "join" ][ "donor_table" ] ) &&
        ( $property[ "field_type" ] === "list" )
    ) {

        if ( $property[ "joined_field" ] )
            $blockField[ "joined_field" ] = $property[ "joined_field" ];


        /**
         * Определение типа связанной таблицы
         * (list_donor / join)
         */
        if ( !$property[ "list_donor" ][ "table" ] ) {

            $property[ "list_donor" ][ "table" ] = $property[ "join" ][ "donor_table" ];
            $property[ "list_donor" ][ "properties_title" ] = $property[ "join" ][ "property_article" ];

        } // if. !$fieldDetail[ "list_donor" ][ "table" ]


        /**
         * Загрузка схемы объекта связанной таблицы
         */
        $propertyObjectScheme = $API->loadObjectScheme( $property[ "list_donor" ][ "table" ] );
        if ( !$propertyObjectScheme ) continue;


        /**
         * Фильтр данных из связанной таблицы
         */

        $listFilter = [ "is_active" => "Y" ];

        if ( $property[ "list_donor" ][ "filters" ] ) {

            foreach ( $property[ "list_donor" ][ "filters" ] as $filterArticle => $filterValue )
                $listFilter[ $filterArticle ] = $filterValue;

        } // if. $fieldDetail[ "list_donor" ][ "filters" ]


        /**
         * Получение данных из связанной таблицы
         */
        $joinedTableRows = $API->DB->from( $property[ "list_donor" ][ "table" ] );
        if ( $propertyObjectScheme[ "is_trash" ] ) $joinedTableRows->where( $listFilter );


        /**
         * Обновление списка
         */
        foreach ( $joinedTableRows as $joinedTableRow ) {

            /**
             * Сформированный пункт списка
             */
            $joinedRow = [];

            /**
             * Название поля
             */
            $fieldTitle = $joinedTableRow[ $property[ "list_donor" ][ "properties_title" ] ];


            /**
             * Нестандартные названия полей
             */
            switch ( $property[ "list_donor" ][ "properties_title" ] ) {

                case "first_name":
                case "last_name":
                case "patronymic":

                    /**
                     * Получение ФИО
                     */

                    $fio = [
                        "first_name" => "",
                        "last_name" => "",
                        "patronymic" => ""
                    ];

                    foreach ( $propertyObjectScheme[ "properties" ] as $objectProperty ) {

                        if (
                            ( $objectProperty[ "article" ] === "first_name" ) ||
                            ( $objectProperty[ "article" ] === "last_name" ) ||
                            ( $objectProperty[ "article" ] === "patronymic" )
                        ) $fio[ $objectProperty[ "article" ] ] = $joinedTableRow[ $objectProperty[ "article" ] ];

                    } // foreach. $propertyObjectScheme[ "properties" ]

                    $fieldTitle = "${fio[ "last_name" ]} ${fio[ "first_name" ]} ${fio[ "patronymic" ]}";

                    break;

            } // switch. $fieldDetail[ "list_donor" ][ "properties_title" ]


            /**
             * Заполнение пункта списка
             */

            $joinedRow = [
                "title" => $fieldTitle,
                "value" => $joinedTableRow[ "id" ]
            ];

            if ( key_exists( "joined_field", $property ) )
                $joinedRow[ "joined_field_value" ] = $joinedTableRow[ $property[ "joined_field" ] ];


            $property[ "list" ][] = $joinedRow;

        } // foreach. $joinedTableRows

    } // if. $fieldDetail[ "field_type" ] === "list"


    /**
     * Вывод св-в
     */

    $returnProperty = [
        "title" => $property[ "title" ],
        "article" => $property[ "article" ],
        "data_type" => $property[ "data_type" ],
        "field_type" => $property[ "field_type" ],
        "settings" => $property[ "settings" ],
        "search" => $property[ "search" ]
    ];

    if ( $property[ "list" ] ) $returnProperty[ "list" ] = $property[ "list" ];

    $objectProperties[] = $returnProperty;

} // foreach. $objectScheme[ "properties" ]


$API->returnResponse( $objectProperties );