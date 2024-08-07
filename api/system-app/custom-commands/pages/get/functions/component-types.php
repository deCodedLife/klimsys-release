<?php

/**
 * @file
 * Обработка типов компонентов
 */


/**
 * Обработка фильтров
 *
 * @param $structureComponent  object  Структура компонента
 *
 * @return $array
 */
function processingComponentType_filter ( $structureComponent ) {

    global $API;


    /**
     * Сформированный список значений фильтра
     */
    $filterList = [];

    /**
     * Список св-в Объекта
     */
    $objectProperties = [];


    /**
     * Фиксированные значения фильтра
     */
    if ( ( $structureComponent[ "settings" ][ "list" ] ?? false ) ) return $structureComponent[ "settings" ][ "list" ];


    /**
     * Проверка обязательных параметров
     */
    if (
        !( $structureComponent[ "settings" ][ "donor_object" ] ?? false ) ||
        !( $structureComponent[ "settings" ][ "donor_property_title" ] ?? false ) ||
        !( $structureComponent[ "settings" ][ "donor_property_value" ] ?? false )
    ) return [];


    /**
     * Загрузка схемы объекта
     */
    $objectScheme = $API->loadObjectScheme( $structureComponent[ "settings" ][ "donor_object" ] );
    if ( !$objectScheme ) return false;


    /**
     * Получение св-в Объекта
     */
    foreach ( $objectScheme[ "properties" ] as $property )
        $objectProperties[ $property[ "article" ] ] = $property;


    /**
     * Получение записей из таблицы донора
     */
    $request = [];
    $request[ "context" ][ "block" ] = "select";
    $request[ "limit" ] = 100;

    if ( ( $structureComponent[ "settings" ][ "select" ] ?? false ) ) $request[ "select" ] = $structureComponent[ "settings" ][ "select" ];
    else $structureComponent[ "settings" ][ "select" ] = $request[ "select" ] = $structureComponent[ "settings" ][ "donor_property_title" ];
    if ( ( $structureComponent[ "settings" ][ "select_menu" ] ?? false ) ) $request[ "select_menu" ] = $structureComponent[ "settings" ][ "select_menu" ];

    $donorRows = $API->DB->from( $structureComponent[ "settings" ][ "donor_object" ] );
    if ( $objectScheme[ "is_trash" ] ) $donorRows->where( "is_active", "Y" );
    $donorRows->limit( 100 );

    /**
     * Фильтрация записей
     */

    if (
        $structureComponent[ "settings" ][ "recipient_property" ] &&
        ( $objectProperties[ $structureComponent[ "settings" ][ "recipient_property" ] ][ "list_donor" ][ "multiply_filter" ] ?? false )
    ) {

        foreach ( $objectProperties[ $structureComponent[ "settings" ][ "recipient_property" ] ][ "list_donor" ][ "multiply_filter" ] as $filterArticle => $filterValues ) {

            foreach ( $filterValues as $filterValue )
                $donorRows->where( $filterArticle, $filterValue );

        } // foreach. $objectProperties[ $structureComponent[ "settings" ][ "recipient_property" ] ][ "list_donor" ][ "multiply_filter" ]

    } // if. $structureComponent[ "recipient_property" ]


    foreach ( $donorRows as $row ) $ids[] = $row[ "id" ];


    $request[ "id" ] = $ids ?? [ 0 ];

    if ( $structureComponent[ "settings" ][ "is_search" ] ) return [];

    $donorRows = $API->sendRequest( $structureComponent[ "settings" ][ "donor_object" ], "get", $request );
    $filterList = (array) $donorRows;


//    /**
//     * Обработка записей из таблицы донора
//     */
//
//    if ( !$structureComponent[ "settings" ][ "is_search" ] ) foreach ( $donorRows as $donorRowKey => $donorRow ) {
//
//        /**
//         * Получение детальной информации о св-ве записи
//         */
//
//        $propertyDetail = $objectProperties[ $structureComponent[ "settings" ][ "donor_property_value" ] ];
//
//        if ( $structureComponent[ "settings" ][ "donor_property_value" ] === "id" )
//            $propertyDetail[ "data_type" ] = "integer";
//
//        if ( !$propertyDetail ) continue;
//
//
//        /**
//         * Обработка нестандартных св-в
//         */
//
//        switch ( $structureComponent[ "settings" ][ "donor_property_title" ] ) {
//
//            case "first_name":
//            case "last_name":
//
//                $fio = $donorRow[ "last_name" ] . " " . $donorRow[ "first_name" ];
//                if ( $donorRow[ "patronymic" ] ) $fio .= " " .  $donorRow[ "patronymic" ];
//
//                $donorRow[ $structureComponent[ "settings" ][ "donor_property_title" ] ] = $fio;
//
//        } // switch. $structureComponent[ "settings" ][ "donor_property_title" ]
//
//
//        /**
//         * Сформированный фильтр
//         */
//
//        settype(
//            $donorRow[ $structureComponent[ "settings" ][ "donor_property_value" ] ],
//            $propertyDetail[ "data_type" ]
//        );
//
//        $filterResult = [
//            "title" => $donorRow[ $structureComponent[ "settings" ][ "donor_property_title" ] ],
//            "value" => $donorRow[ $structureComponent[ "settings" ][ "donor_property_value" ] ]
//        ];
//
//
//        $filterList[] = $filterResult;
//
//    } // foreach. $donorRows


    return $filterList;

} // function. processingComponentType_filter