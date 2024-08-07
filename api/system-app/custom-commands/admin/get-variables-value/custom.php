<?php

/**
 * Сформированный список значений
 */
$resultValues = [];


/**
 * Получение схемы объекта
 */
$objectScheme = $API->loadObjectScheme( $requestData->scheme_name );


/**
 * Исполнение стандартного get-запроса
 */

$rowDetail = $API->sendRequest( $requestData->scheme_name, "get", [
    "id" => $requestData->row_id
] );



if ( !$rowDetail ) $API->returnResponse( [] );

$rowDetail = $rowDetail[ 0 ];

$requestBody = (object) [];
$requestBody->id = $requestData->row_id;
$requestBody->context = (object) [ "object" => $rowDetail ];
//$API->returnResponse( $requestBody );
$customCommand = $API->sendRequest( $requestData->scheme_name, "variables", (array) $requestBody );
$API->mergeObjects( $rowDetail, $customCommand );


/**
 * Обработка значений
 */
foreach ( $rowDetail as $propertyArticle => $propertyValue ) {


    /**
     * Сформированное значение св-ва
     */
    $resultPropertyValue = $propertyValue;


    /**
     * Получение схемы св-ва объекта
     */

    $objectPropertyScheme = [];

    foreach ( $objectScheme[ "properties" ] as $objectProperty ) {

        if ( $objectProperty[ "article" ] != $propertyArticle ) continue;
        $objectPropertyScheme = $objectProperty;

    } // foreach. $objectScheme[ "properties" ]

    if ( !$objectPropertyScheme ) continue;


    if ( $propertyArticle === "service_id" && $requestData->scheme_name == "equipmentVisits" )
        $propertyArticle = "services_id";

    if ( $propertyArticle === "client_id" && $requestData->scheme_name == "equipmentVisits" )
        $propertyArticle = "clients_id";

    /**
     * Обработка кастомных списков
     */

    if ( isset( $propertyValue->title ) ) {

        /**
         * Обнуление сформированного значения св-ва
         */
        $resultPropertyValue = null;


        if ( gettype( $propertyValue->value ) === "integer" ) {

//            if ( !$propertyValue->value ) {
//                $resultPropertyValue[] = $innerPropertyValue;
//                continue;
//            }

            /**
             * Таблица внутреннего св-ва
             */

            $innerPropertyTable = "";

            if ( $objectPropertyScheme[ "list_donor" ][ "table" ] ) $innerPropertyTable = $objectPropertyScheme[ "list_donor" ][ "table" ];
            if ( $objectPropertyScheme[ "join" ][ "donor_table" ] ) $innerPropertyTable = $objectPropertyScheme[ "join" ][ "donor_table" ];

            if ( !$innerPropertyTable ) continue;


            /**
             * Получение значений внутреннего св-ва
             */
            $innerPropertyRows = $API->sendRequest( $innerPropertyTable, "get", [
                "id" => $propertyValue->value
            ] );


            /**
             * Обработка значений внутреннего св-ва
             */

            foreach ( $innerPropertyRows as $innerPropertyRowDetail ) {

                /**
                 * Обработка значений внутренней записи
                 */

                foreach ( $innerPropertyRowDetail as $innerPropertyRowArticle => $innerPropertyRow ) {

                    if (
                        ( gettype( $innerPropertyRow ) === "array" ) ||
                        ( gettype( $innerPropertyRow ) === "object" )
                    ) continue;

                    $resultPropertyValue[ $innerPropertyRowArticle ] = $innerPropertyRow;

                } // foreach. $innerPropertyRowDetail

            } // foreach. $innerPropertyRows

        } else {

            $resultPropertyValue = $propertyValue->value;

        } // if. gettype( $propertyValue->value ) === "integer"

    } // if. isset( $propertyValue->title )


    /**
     * Обработка списков и связанных объектов
     */

    if ( gettype( $propertyValue ) === "array" ) {

        /**
         * Обнуление сформированного значения св-ва
         */
        $resultPropertyValue = null;


        /**
         * Таблица внутреннего св-ва
         */

        $innerPropertyTable = "";

        if ( $objectPropertyScheme[ "list_donor" ][ "table" ] ) $innerPropertyTable = $objectPropertyScheme[ "list_donor" ][ "table" ];
        if ( $objectPropertyScheme[ "join" ][ "donor_table" ] ) $innerPropertyTable = $objectPropertyScheme[ "join" ][ "donor_table" ];

        if ( !$innerPropertyTable ) continue;


        /**
         * Обход значений св-ва
         */

        foreach ( $propertyValue as $innerPropertyValue ) {

            if ( !$innerPropertyValue->value ) {
                $resultPropertyValue[] = $innerPropertyValue;
                continue;
            }

            /**
             * Получение значений внутреннего св-ва
             */
            $innerPropertyRows = $API->sendRequest( $innerPropertyTable, "get", [
                "id" => $innerPropertyValue->value
            ] );


            /**
             * Обработка значений внутреннего св-ва
             */

            foreach ( $innerPropertyRows as $innerPropertyRowDetail ) {

                /**
                 * Сформированный список внутренних записей
                 */
                $resultInnerPropertyValues = [];


                /**
                 * Обработка значений внутренней записи
                 */

                foreach ( $innerPropertyRowDetail as $innerPropertyRowArticle => $innerPropertyRow ) {

                    $resultInnerPropertyValues[ $innerPropertyRowArticle ] = $innerPropertyRow;

                } // foreach. $innerPropertyRowDetail


                $resultPropertyValue[] = $resultInnerPropertyValues;

            } // foreach. $innerPropertyRows

        } // foreach. $propertyValue

    } // if. gettype( $propertyValue ) === "array"


    $resultValues[ $propertyArticle ] = $resultPropertyValue;

} // foreach. $rowDetail


$API->returnResponse( $resultValues );