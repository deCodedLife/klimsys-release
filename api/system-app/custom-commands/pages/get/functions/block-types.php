<?php

/**
 * @file
 * Обработка типов блоков
 */


/**
 * Обработка списков в формах
 *
 * @param $fieldDetail  array    Детальная информация о поле
 * @param $blockField   array    Детальная информация о блоке
 * @param $rowId        integer  ID записи
 */
function addListToForm ( $fieldDetail, $blockField ) {

    global $API;


    if ( $fieldDetail[ "joined_field" ] ) {

        $blockField[ "joined_field" ] = $fieldDetail[ "joined_field" ];

        $blockField[ "joined_field_filter" ] = $fieldDetail[ "joined_field" ];
        if ( $fieldDetail[ "joined_field_filter" ] ) $blockField[ "joined_field_filter" ] = $fieldDetail[ "joined_field_filter" ];

    } // if. $fieldDetail[ "joined_field" ]


    /**
     * Не формировать список при поиске
     */
    if ( $fieldDetail[ "search" ] ) return $blockField;


    /**
     * Умные списки
     */
    if ( $blockField[ "list_donor" ] ) {

        $blockField[ "settings" ][ "object" ] = $blockField[ "list_donor" ][ "table" ];

        $select = $fieldDetail[ "list_donor" ][ "select" ] ?? $blockField[ "list_donor" ][ "properties_title" ];
        $blockField[ "settings" ][ "select" ] = $select;
        if ( $fieldDetail[ "list_donor" ][ "select_menu" ] ) $blockField[ "settings" ][ "select_menu" ] = $fieldDetail[ "list_donor" ][ "select_menu" ];

        unset( $blockField[ "list_donor" ] );

    } // if. $blockField[ "list_donor" ]


    /**
     * @hook
     * Формирование полей списка
     */

    $listConstructorHookPath = $API::$configs[ "paths" ][ "public_custom_commands" ] . "/pages/get/hooks/list/" . $fieldDetail[ "list_donor" ][ "table" ] . ".php";

    if ( file_exists( $listConstructorHookPath ) )
        require( $listConstructorHookPath );


    return $blockField;

} // function. addListToForm


/**
 * Обработка полей формы
 */
function addFieldToForm ( $objectScheme, $objectProperties, $structureBlock, $field, $formFieldValues ) {

    global $API;
    global $pageDetail;


    /**
     * Сформированное поле формы
     */
    $blockField = [];


    /**
     * Получение детальной информации о поле формы
     */
    $fieldDetail = $objectProperties[ $field ];
    if ( !$fieldDetail ) return $blockField;

    if ( !$fieldDetail[ "require_in_commands" ] ) $fieldDetail[ "require_in_commands" ] = [];

    /**
     * Проверка обязательности поля
     */
    $isRequired = false;
    if ( in_array( $pageDetail[ "url" ][ 1 ], $fieldDetail[ "require_in_commands" ] ?? [] ) ) $isRequired = true;

    /**
     * Проверка видимости поля
     */
    $isVisible = true;
    if ( $fieldDetail[ "is_visible" ] === false ) $isVisible = false;

    /**
     * Обновление формы, при изменении поля
     */
    $onChangeSubmit = false;
    if ( $fieldDetail[ "on_change_submit" ] === true ) $onChangeSubmit = true;


    /**
     * Проверка блокировки поля
     */

    $isDisabled = false;

    if (
        ( $fieldDetail[ "is_disabled" ] === true ) ||
        ( !in_array( $structureBlock[ "settings" ][ "command" ], $fieldDetail[ "use_in_commands" ] ?? [] ) )
    ) $isDisabled = true;


    /**
     * Получение аннотации
     */
    if ( !$fieldDetail[ "annotation" ] ) $fieldDetail[ "annotation" ] = "";

    /**
     * Размер поля
     */
    $propertySize = 4;
    if ( $fieldDetail[ "size" ] ) $propertySize = $fieldDetail[ "size" ];


    /**
     * Формирование поля формы
     */

    $blockField = [
        "title" => $fieldDetail[ "title" ],
        "article" => $fieldDetail[ "article" ],
        "size" => $propertySize,
        "annotation" => $fieldDetail[ "annotation" ],
        "data_type" => $fieldDetail[ "data_type" ],
        "field_type" => $fieldDetail[ "field_type" ],
        "is_edit" => $fieldDetail[ "is_edit" ],
        "settings" => $fieldDetail[ "settings" ],
        "search" => $fieldDetail[ "search" ],
        "description" => $fieldDetail[ "description" ],
        "is_required" => $isRequired,
        "is_disabled" => $isDisabled,
        "is_visible" => $isVisible,
        "is_clearable" => $fieldDetail[ "is_clearable" ],
        "on_change_submit" => $onChangeSubmit
    ];

    if ( $fieldDetail[ "min_value" ] ) $blockField[ "min_value" ] = $fieldDetail[ "min_value" ];
    if ( $fieldDetail[ "max_value" ] ) $blockField[ "max_value" ] = $fieldDetail[ "max_value" ];


    /**
     * Обработка хуков
     */

    if ( $fieldDetail[ "is_hook" ] )
        $blockField[ "hook" ] = $pageDetail[ "section" ];

//    if ( $fieldDetail[ "article" ] == "patronymic" )
//        $API->returnResponse( [ $blockField, $fieldDetail ], 500 );


    /**
     * Обработка связанных таблиц
     */
    if (
        ( $fieldDetail[ "list_donor" ][ "table" ] || $fieldDetail[ "join" ][ "donor_table" ] ) &&
        ( $fieldDetail[ "field_type" ] === "list" )
    ) {

        $blockField = addListToForm( $fieldDetail, $blockField );

        if ( $fieldDetail[ "join" ][ "donor_table" ] ) {

            $blockField[ "settings" ][ "object" ] = $fieldDetail[ "join" ][ "donor_table" ];
            $select = $fieldDetail[ "join" ][ "select" ] ?? $fieldDetail[ "join" ][ "property_article" ];
            $select_menu = $fieldDetail[ "join" ][ "select_menu" ];

        } else {

            $blockField[ "settings" ][ "object" ] = $fieldDetail[ "list_donor" ][ "table" ];
            $select = $fieldDetail[ "list_donor" ][ "select" ] ?? $fieldDetail[ "list_donor" ][ "properties_title" ];
            $select_menu = $fieldDetail[ "list_donor" ][ "select_menu" ];

        } // if. $fieldDetail[ "join" ][ "donor_table" ]


        $blockField[ "settings" ][ "select" ] = $select;
        if ( $select_menu ) $blockField[ "settings" ][ "select_menu" ] = $select_menu;

    }

    /**
     * Обработка булева
     */
    if ( $fieldDetail[ "field_type" ] == "checkbox" ) {

        $listItem[ "value" ] = $listItem[ "value" ] === 'Y';

    }


    /**
     * Обработка умных списков
     */
    if ( $fieldDetail[ "field_type" ] == "smart_list" ) {

        if ( !key_exists( "is_headers_shown", $fieldDetail[ "settings" ] ) )
            $blockField[ "settings" ][ "is_headers_shown" ] = true;

        foreach ( $fieldDetail[ "settings" ][ "properties" ] as $propertyKey => $property ) {

            /**
             * Загрузка схемы объекта
             */

            $propertyObjectScheme = $API->loadObjectScheme( $property[ "list_donor" ][ "table" ], false );

            if ( !$propertyObjectScheme ) continue;


            $generatedField = addListToForm( $property, $property );
            if ( !$generatedField ) continue;


            $blockField[ "settings" ][ "properties" ][ $propertyKey ] = $generatedField;

        } // foreach. $fieldDetail[ "settings" ][ "properties" ]

    } // if. $fieldDetail[ "field_type" ] == "smart_list"


    /**
     * Обработка кастомных списков
     */
    if (
        $fieldDetail[ "custom_list" ]
    ) {

        foreach ( $fieldDetail[ "custom_list" ] as $listItem ) {

            $blockField[ "list" ][] = [
                "title" => $listItem[ "title" ],
                "value" => $listItem[ "value" ]
            ];

        } // foreach. $fieldDetail[ "custom_list" ]

    } // if. ( $fieldDetail[ "field_type" ] === "list" ) && $fieldDetail[ "custom_list" ]


    /**
     * Заполнение значения поля
     */

    if ( $pageDetail[ "row_detail" ] ) {

        /**
         * Получение значения поля
         */
        $blockField[ "value" ] = $pageDetail[ "row_detail" ][ $fieldDetail[ "article" ] ];


        /**
         * Обработка списков
         */

        if (
            ( $fieldDetail[ "field_type" ] == "list" ) ||
            ( $fieldDetail[ "field_type" ] == "radio" )
        ) {

            switch ( gettype( $blockField[ "value" ] ) ) {

                case "array":

                    $blockFieldValues = [];

                    foreach ( $blockField[ "value" ] as $blockFieldValue )
                        $blockFieldValues[] = $blockFieldValue->value;

                    $blockField[ "value" ] = $blockFieldValues;

                    break;

                case "object":
                    $blockField[ "value" ] = $blockField[ "value" ]->value;

            } // switch. gettype( $blockField[ "value" ] )

        } // if. $fieldDetail[ "field_type" ] == "list"


        /**
         * Перевод значения в указанный в схеме тип
         */
        $validTypes = ["bool", "boolean", "int", "integer", "float", "double", "string", "array", "object", "null"];
        if ( in_array( $fieldDetail[ "field_type" ], $validTypes ) )
            settype( $blockField[ "value" ], $fieldDetail[ "data_type" ] );


        /**
         * Получение значения связанной таблицы
         */
        if (
            !$blockField[ "value" ] &&
            ( $blockField[ "field_type" ] === "list" ) &&
            ( $blockField[ "data_type" ] === "array" )
        ) {

            /**
             * Схема св-ва
             */
            $objectSchemeProperty = $objectProperties[ $blockField[ "article" ] ];


            /**
             * Значения св-ва
             */
            $objectPropertyValues = $API->DB->from( $objectSchemeProperty[ "join" ][ "connection_table" ] )
                ->where( $objectSchemeProperty[ "join" ][ "insert_property" ], $pageDetail[ "row_detail" ][ "id" ] );


            /**
             * Добавление значений
             */
            foreach ( $objectPropertyValues as $objectPropertyValue )
                $blockField[ "value" ][] = $objectPropertyValue[
                $objectSchemeProperty[ "join" ][ "filter_property" ]
                ];

        } // if. !$blockField[ "value" ]


        /**
         * Добавление значения связанной таблицы
         */
        foreach ( $blockField[ "list" ] as $joinedTableRow ) {

            if ( !$fieldDetail[ "list_donor" ][ "table" ] ) continue;
            if ( $joinedTableRow[ "value" ] != $blockField[ "value" ] ) continue;

            $queryParams = $blockField[ "value" ];
            if ( is_object( $blockField[ "value" ] ) ) $queryParams = $blockField[ "value" ]->value;

            $blockField[ "value" ] = $API->DB->from( $fieldDetail[ "list_donor" ][ "table" ] )
                ->where( "id", $queryParams )
                ->fetch();

            $blockField[ "value" ] = $blockField[ "value" ][ "id" ] ?? null;

        } // foreach. $blockField[ "list" ]


        /**
         * Обработка системных св-в
         */
        if ( $fieldDetail[ "data_type" ] === "password" ) $blockField[ "value" ] = null;

    } // if. $pageDetail[ "row_detail" ]


    /**
     * Заполнение значения поля из хука
     */
    if ( $formFieldValues[ $fieldDetail[ "article" ] ] ?? false ) {

        if ( gettype( $formFieldValues[ $fieldDetail[ "article" ] ] ) !== "array" ) {

            $blockField[ "value" ] = $formFieldValues[ $fieldDetail[ "article" ] ];

        } else {

            /**
             * Заполнение св-в поля
             */
            foreach ( $formFieldValues[ $fieldDetail[ "article" ] ] as $fieldProperty => $fieldPropertyValue )
                $blockField[ $fieldProperty ] = $fieldPropertyValue;

        } // if. gettype( $formFieldValues[ $fieldDetail[ "article" ] ] ) !== "array"

    } // if. $formFieldValues[ $fieldDetail[ "article" ] ]


    /**
     * Обработка значений св-в блока типа info
     */

    if ( $structureBlock[ "type" ] === "info" ) {

        switch ( $fieldDetail[ "data_type" ] ) {

            case "boolean":

                if ( $blockField[ "value" ] ) $blockField[ "value" ] = "Да";
                else $blockField[ "value" ] = "Нет";

                break;

            case "integer":

                if ( $fieldDetail[ "list_donor" ][ "table" ] ) {

                    $objectScheme = $API->loadObjectScheme($fieldDetail[ "list_donor" ][ "table" ]);

                    $query = $API->DB->from( $objectScheme[ "table" ] )
                        ->where( "id", $blockField[ "value" ] )
                        ->limit( 1 )
                        ->fetch();


                    $blockField[ "value" ] = $query[ $fieldDetail[ "list_donor" ][ "properties_title" ] ];
                }

                break;

            case "array":

                if ( $fieldDetail[ "join" ] ) {

                    $titles = [];

                    foreach ( $blockField[ "value" ] as $value ) {

                        $objectScheme = $API->loadObjectScheme($fieldDetail[ "join" ][ "donor_table" ]);

                        $query = $API->DB->from( $objectScheme[ "table" ] )
                            ->where( "id", $value )
                            ->limit( 1 )
                            ->fetch();

                        $titles[] = $query[ $fieldDetail[ "join" ][ "property_article" ] ];

                    }

                    $blockField[ "value" ] = $titles;
                }

                break;

            case "string":

                if ( $fieldDetail[ "custom_list" ] ) {

                    foreach ( $fieldDetail[ "custom_list" ] as $item ) {

                        if ( $item[ "value" ] == $blockField[ "value" ] ) {

                            $blockField[ "value" ] = $item[ "title" ];

                        }

                    }

                }
                break;


        } // switch. $fieldDetail[ "data_type" ]

    } // if. $structureBlock[ "type" ] === "info"


    /**
     * Обработка файлов
     */

    if ( $blockField[ "data_type" ] == "file" ) {

        $resultFiles = [];


        foreach ( $blockField[ "value" ] as $filePath ) {

            $fileDetail = pathinfo( $filePath );

            $resultFiles[] = [
                "title" => $fileDetail[ "filename" ],
                "path" => $filePath,
                "extension" => $fileDetail[ "extension" ]
            ];

        } // foreach. $blockField[ "value" ]


        $blockField[ "value" ] = $resultFiles;

    } // if. $blockField[ "data_type" ] == "file"

    if ( $blockField[ "field_type" ] == "image" ) {

        if ( $blockField[ "value" ] != null ) {

            $domain = "https://" . $_SERVER['SERVER_NAME'];
            $blockField[ "value" ] = $domain . str_replace($domain, "", $blockField[ "value" ]);

        }

        $blockField[ "settings" ][ "is_editor" ] = false;

    }

    /**
     * Учет поля формы
     */
    return $blockField;

} // function. addFieldToForm


/**
 * Добавление элемента в позицию массива
 */
function addItemToArrayPosition ( $array, $insertItem, $position ) {

    global $API;

    if ( !$array ) return [ $insertItem ];


    $isAdded = false;
    $resultArray = [];

    foreach ( $array as $arrayItemKey => $arrayItem ) {

        if ( $arrayItemKey == $position ) {

            if ( !$isAdded ) $resultArray[] = $insertItem;
            $isAdded = true;

        } // if. $arrayItemKey == $position

        $resultArray[] = $arrayItem;

    } // foreach. $array

    if ( !$isAdded ) $resultArray[] = $insertItem;


    return $resultArray;

} // function. addItemToArrayPosition


/**
 * Обработка списков
 *
 * @param $structureBlock  object  Структура блока
 *
 * @return mixed
 */
function processingBlockType_list ( $structureBlock ) {

    global $API;
    global $pageDetail;


    /**
     * Заголовки списка.
     * Используются для вывода в админке
     */
    $listHeaders = [];

    /**
     * Фильтр списка.
     * Передается в get запрос
     */
    $listFilters = [];


    /**
     * Проверка обязательных св-в
     */
    if ( !$structureBlock[ "settings" ][ "object" ] ) return false;


    /**
     * Загрузка схемы объекта
     */

//    $API->returnResponse( [$objectScheme, $structureBlock[ "settings" ][ "object" ]], 403 );
    $objectScheme = $pageDetail[ "row_scheme" ]; //$API->loadObjectScheme( $structureBlock[ "settings" ][ "object" ] );


    if ( !$objectScheme ) return false;

    /**
     * Формирование фильтров списка
     */

    foreach ( $structureBlock[ "settings" ][ "filters" ] as $listFilter ) {

        /**
         * Подстановка переменных
         */

        if ( $listFilter[ "value" ][ 0 ] === ":" ) {

            /**
             * Обработка переменной
             */

            /**
             * Получение переменной в строке
             */
            $stringVariable = substr( $listFilter[ "value" ], 1 );


            /**
             * Получение значения из списка
             */
            if ( gettype( $pageDetail[ "row_detail" ][ $stringVariable ] ) === "array" )
                $pageDetail[ "row_detail" ][ $stringVariable ] = $pageDetail[ "row_detail" ][ $stringVariable ][ 0 ]->value;

            /**
             * Формирование строки
             */
            $listFilter[ "value" ] = (int) $pageDetail[ "row_detail" ][ $stringVariable ];

        } // if. $widgetFilter[ "value" ][ 0 ] === ":"


        $listFilters[ $listFilter[ "property" ] ] = $listFilter[ "value" ];

    } // foreach. $structureBlock[ "settings" ][ "filters" ]

    /**
     * Формирование заголовков списка
     */

    foreach ( $objectScheme[ "properties" ] as $property ) {

        if ( $property[ "is_default_in_list" ] ) $listHeaders[] = [
            "title" => $property[ "title" ],
            "article" => $property[ "article" ],
            "type" => $structureBlock[ "headers" ][ $property[ "article" ] ] ?? $property[ "field_type" ]
        ];

    } // foreach. $objectScheme[ "properties" ]


    return [
        "headers" => $listHeaders,
        "filters" => $listFilters
    ];

} // function. processingBlockType_list


/**
 * Обработка форм
 *
 * @param $structureBlock  object  Структура блока
 *
 * @return mixed
 */
function processingBlockType_form ( $structureBlock ) {

    global $API;
    global $requestData;
    global $pageDetail;
    global $formFieldValues;
    global $userScheme;


    /**
     * Св-ва для автозаполнения
     */
    $formData = $structureBlock[ "settings" ][ "data" ];

    /**
     * Области формы.
     * Используются для вывода в админке
     */
    $formAreas = $structureBlock[ "settings" ][ "areas" ];

    /**
     * Список св-в Объекта
     */
    $objectProperties = [];

    /**
     * Тип формы.
     * application/json (обычная) или multipart/form-data (с загрузкой файлов)
     */
    $formType = "application/json";


    /**
     * Проверка обязательных св-в
     */
    if ( !$structureBlock[ "settings" ][ "object" ] ) return false;


    /**
     * Загрузка схемы объекта
     */
    $objectScheme = $API->loadObjectScheme( $structureBlock[ "settings" ][ "object" ] );
    if ( !$objectScheme ) return false;


    if( $requestData->context->type == "import" ) {
        $objectScheme = $API->mergeProperties(
            $objectScheme,
            $API->loadObjectScheme( "import_template" )
        );
    }


    /**
     * Получение св-в Объекта
     */
    foreach ( $objectScheme[ "properties" ] as $property )
        $objectProperties[ $property[ "article" ] ] = $property;


    /**
     * Обработка св-в для автозаполнения
     */

    foreach ( $formData as $propertyKey => $propertyValue ) {

        /**
         * Подстановка переменных
         */

        if ( $propertyValue[ 0 ] === ":" ) {

            /**
             * Обработка переменной
             */

            /**
             * Получение переменной в строке
             */
            $stringVariable = substr( $propertyValue, 1 );

            /**
             * Значение переменной в строке
             */
            $stringValue = $pageDetail[ "row_detail" ][ $stringVariable ];


            if ( $stringValue ) {

                /**
                 * Получение значения из списка
                 */
                if ( ( gettype( $stringValue ) === "array" ) && $stringValue[ 0 ]->value )
                    $stringValue = (int) $stringValue[ 0 ]->value;

            } else {

                /**
                 * Обработка контекста
                 */

                if ( $requestData->context && $requestData->context->{$stringVariable} )
                    $stringValue = $requestData->context->{$stringVariable};

                if ( $stringVariable === "id" )
                    $stringValue = (int) $requestData->context->row_id;

            } // if. $stringValue


            /**
             * Формирование строки
             */
            $propertyValue = $stringValue;

        } // if. $propertyValue[ 0 ] === ":"


        $formData[ $propertyKey ] = $propertyValue;

    } // foreach. $formData


    /**
     * Обработка областей формы
     */
    foreach ( $formAreas as $areaKey => $area ) {

        /**
         * Обработка блоков формы
         */
        foreach ( $area[ "blocks" ] as $blockKey => $block ) {

            /**
             * Обработанные поля формы
             */
            $blockFields = [];


            /**
             * Обработка полей формы
             */
            foreach ( $block[ "fields" ] as $fieldKey => $field ) {

                /**
                 * Получение детальной информации о поле формы
                 */
                $fieldDetail = $objectProperties[ $field ];
                if ( !$fieldDetail ) continue;

                /**
                 * Проверка наличия полей с загрузкой файлов
                 */
                if ( $fieldDetail[ "data_type" ] === "image" ) $formType = "multipart/form-data";
                if ( $fieldDetail[ "data_type" ] === "file" ) $formType = "multipart/form-data";
                $generatedBlock = addFieldToForm( $objectScheme, $objectProperties, $structureBlock, $field, $formFieldValues );
//                $API->returnResponse([ $generatedBlock ],400);
                if ( $generatedBlock ) $blockFields[] = $generatedBlock;

            } // foreach. $block[ "fields" ]


            /**
             * Обновление полей формы
             */
            $formAreas[ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ] = $blockFields;

        } // foreach. $area[ "blocks" ]

    } // foreach. $formAreas


    /**
     * Учет пользовательских схем
     */

    if ( $userScheme ) {

        $userSchemeObject = $structureBlock[ "settings" ][ "object" ];
        $userSchemeObject = $userScheme->$userSchemeObject;


        /**
         * Добавление пользовательских областей
         */
        foreach ( $userSchemeObject->areas as $area )
            $formAreas = addItemToArrayPosition( $formAreas, [
                "size" => $area->size,
                "blocks" => []
            ], $area->position );


        /**
         * Добавление пользовательских блоков и св-в
         */
        foreach ( $userSchemeObject->properties as $propertyArticle => $property ) {

            /**
             * Получение значения
             */

            $propertyValue = "";

            if ( $pageDetail[ "row_detail" ][ "us__$propertyArticle" ] )
                $propertyValue = $pageDetail[ "row_detail" ][ "us__$propertyArticle" ];


            $formAreas[ $property->area_position ][ "blocks" ][ $property->block_position ][ "fields" ] = addItemToArrayPosition(
                $formAreas[ $property->area_position ][ "blocks" ][ $property->block_position ][ "fields" ], [
                "title" => $property->title,
                "article" => $propertyArticle,
                "size" => 4,
                "data_type" => $property->field_type,
                "field_type" => $property->field_type,
                "is_required" => false,
                "is_disabled" => false,
                "is_visible" => true,
                "is_clearable" => $property->is_clearable,
                "value" => $propertyValue
            ], $property->property_position
            );

            if ( !$formAreas[ $property->area_position ][ "blocks" ][ $property->block_position ][ "title" ] )
                $formAreas[ $property->area_position ][ "blocks" ][ $property->block_position ][ "title" ] = "";

        } // foreach. $userSchemeObject->properties


        /**
         * Очистка ключей массива
         */

        $resultAreas = [];

        foreach ( $formAreas as $area ) {

            $resultBlocks = [];

            foreach ( $area[ "blocks" ] as $block )
                $resultBlocks[] = $block;

            $resultAreas[] = [
                "size" => $area[ "size" ],
                "blocks" => $resultBlocks
            ];

        } // foreach. $formAreas

        $formAreas = $resultAreas;

    } // if. $userSchemePath


    return [
        "type" => $formType,
        "data" => $formData,
        "areas" => $formAreas
    ];

} // function. processingBlockType_form


/**
 * Обработка документов
 *
 * @param $structureBlock  object  Структура блока
 *
 * @return mixed
 */
function processingBlockType_document ( $structureBlock ) {

    global $API;
    global $pageDetail;
    global $formFieldValues;


    /**
     * Список св-в Документа
     */
    $documentProperties = [];

    /**
     * Список св-в Объекта
     */
    $objectProperties = [];


    /**
     * Проверка обязательных св-в
     */
    if ( !$structureBlock[ "settings" ][ "object" ] ) return false;


    /**
     * Загрузка схемы объекта
     */
    $objectScheme = $API->loadObjectScheme( $structureBlock[ "settings" ][ "object" ] );
    if ( !$objectScheme ) return false;


    /**
     * Получение св-в Объекта
     */
    foreach ( $objectScheme[ "properties" ] as $property )
        $objectProperties[ $property[ "article" ] ] = $property;


    /**
     * Обработка св-в документа
     */
    foreach ( $structureBlock[ "settings" ][ "fields_list" ] as $documentField ) {

        /**
         * Получение детальной информации о поле формы
         */
        $fieldDetail = $objectProperties[ $documentField ];
        if ( !$fieldDetail ) continue;

        if ( !$fieldDetail[ "require_in_commands" ] ) $fieldDetail[ "require_in_commands" ] = [];


        /**
         * Проверка обязательности поля
         */
        $isRequired = false;
        if ( in_array( $pageDetail[ "url" ][ 1 ], $fieldDetail[ "require_in_commands" ] ?? [] ) ) $isRequired = true;

        /**
         * Проверка видимости поля
         */
        $isVisible = true;
        if ( $fieldDetail[ "is_visible" ] === false ) $isVisible = false;


        /**
         * Проверка блокировки поля
         */

        $isDisabled = false;

        if (
            ( $fieldDetail[ "is_disabled" ] === true ) ||
            ( !in_array( $structureBlock[ "settings" ][ "command" ], $fieldDetail[ "use_in_commands" ] ?? [] ) )
        ) $isDisabled = true;


        /**
         * Формирование поля формы
         */

        $blockField = [
            "title" => $fieldDetail[ "title" ],
            "article" => $fieldDetail[ "article" ],
            "data_type" => $fieldDetail[ "data_type" ],
            "field_type" => $fieldDetail[ "field_type" ],
            "settings" => $fieldDetail[ "settings" ],
            "search" => $fieldDetail[ "search" ],
            "description" => $fieldDetail[ "description" ],
            "is_required" => $isRequired,
            "is_disabled" => $isDisabled,
            "is_visible" => $isVisible,
            "is_clearable" => $fieldDetail[ "is_clearable" ]
        ];

        if ( $fieldDetail[ "min_value" ] ) $blockField[ "min_value" ] = $fieldDetail[ "min_value" ];
        if ( $fieldDetail[ "max_value" ] ) $blockField[ "max_value" ] = $fieldDetail[ "max_value" ];
        if ( $fieldDetail[ "field_type" ] == "checkbox" && !$listItem ) $blockField[ "value" ] = true;


        /**
         * Обработка хуков
         */
        if ( $fieldDetail[ "is_hook" ] )
            $blockField[ "hook" ] = $objectScheme[ "table" ];


        /**
         * Обработка связанных таблиц
         */

        if (
            ( $fieldDetail[ "list_donor" ][ "table" ] || $fieldDetail[ "join" ][ "donor_table" ] ) &&
            ( $fieldDetail[ "field_type" ] === "list" )
        ) {

            if ( $fieldDetail[ "joined_field" ] ) {

                $blockField[ "joined_field" ] = $fieldDetail[ "joined_field" ];

                $blockField[ "joined_field_filter" ] = $fieldDetail[ "joined_field" ];
                if ( $fieldDetail[ "joined_field_filter" ] ) $blockField[ "joined_field_filter" ] = $fieldDetail[ "joined_field_filter" ];

            } // if. $fieldDetail[ "joined_field" ]


            /**
             * Определение типа связанной таблицы
             * (list_donor / join)
             */
            if ( !$fieldDetail[ "list_donor" ][ "table" ] ) {

                $fieldDetail[ "list_donor" ][ "table" ] = $fieldDetail[ "join" ][ "donor_table" ];
                $fieldDetail[ "list_donor" ][ "properties_title" ] = $fieldDetail[ "join" ][ "property_article" ];

            } // if. !$fieldDetail[ "list_donor" ][ "table" ]


            /**
             * Загрузка схемы объекта связанной таблицы
             */
            $propertyObjectScheme = $API->loadObjectScheme( $fieldDetail[ "list_donor" ][ "table" ] );
            if ( !$propertyObjectScheme ) continue;


            /**
             * Фильтр данных из связанной таблицы
             */

            $listFilter = [ "is_active" => "Y" ];

            if ( $fieldDetail[ "list_donor" ][ "filters" ] ) {

                foreach ( $fieldDetail[ "list_donor" ][ "filters" ] as $filterArticle => $filterValue )
                    $listFilter[ $filterArticle ] = $filterValue;

            } // if. $fieldDetail[ "list_donor" ][ "filters" ]


            /**
             * Получение данных из связанной таблицы
             */
            $joinedTableRows = $API->DB->from( $fieldDetail[ "list_donor" ][ "table" ] );
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
                $fieldTitle = $joinedTableRow[ $fieldDetail[ "list_donor" ][ "properties_title" ] ];


                /**
                 * Нестандартные названия полей
                 */
                switch ( $fieldDetail[ "list_donor" ][ "properties_title" ] ) {

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

                        foreach ( $propertyObjectScheme[ "properties" ] as $property ) {

                            if (
                                ( $property[ "article" ] === "first_name" ) ||
                                ( $property[ "article" ] === "last_name" ) ||
                                ( $property[ "article" ] === "patronymic" )
                            ) $fio[ $property[ "article" ] ] = $joinedTableRow[ $property[ "article" ] ];

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

                if ( $fieldDetail[ "joined_field" ] )
                    $joinedRow[ "joined_field_value" ] = $joinedTableRow[ $fieldDetail[ "joined_field" ] ];


                $blockField[ "list" ][] = $joinedRow;

            } // foreach. $joinedTableRows

        } // if. $fieldDetail[ "field_type" ] === "list"

        /**
         * Обработка кастомных списков
         */
        if ( ( $fieldDetail[ "field_type" ] === "list" ) && $fieldDetail[ "custom_list" ] ) {

            foreach ( $fieldDetail[ "custom_list" ] as $listItem ) {

                $blockField[ "list" ][] = [
                    "title" => $listItem[ "title" ],
                    "value" => $listItem[ "value" ]
                ];

            } // foreach. $fieldDetail[ "custom_list" ]

        } // if. ( $fieldDetail[ "field_type" ] === "list" ) && $fieldDetail[ "custom_list" ]


        /**
         * Учет поля документа
         */
        $documentProperties[] = $blockField;

    } // foreach. $structureBlock[ "settings" ][ "fields_list" ]


    return $documentProperties;

} // function. processingBlockType_document


/**
 * Обработка виджетов аналитики
 *
 * @param $structureBlock  object  Структура блока
 *
 * @return mixed
 */
function processingBlockType_analyticWidgets ( $structureBlock ) {

    global $API;
    global $pageDetail;


    /**
     * Сформированные настройки виджета
     */

    $isHard = false;
    if ( $structureBlock[ "settings" ][ "is_hard" ] ?? false ) $isHard = true;

    $widgetSettings = [
        "is_hard" => $isHard,
        "widgets_group" => $structureBlock[ "settings" ][ "widgets_group" ],
        "filters" => []
    ];

    if ( $structureBlock[ "settings" ][ "linked_filter" ] ?? false ) $widgetSettings[ "linked_filter" ] = $structureBlock[ "settings" ][ "linked_filter" ];


    /**
     * Формирование фильтров списка
     */

    foreach ( $structureBlock[ "settings" ][ "filters" ] as $widgetFilter ) {

        /**
         * Подстановка переменных
         */

        if ( $widgetFilter[ "value" ][ 0 ] === ":" ) {

            /**
             * Обработка переменной
             */

            /**
             * Получение переменной в строке
             */
            $stringVariable = substr( $widgetFilter[ "value" ], 1 );


            /**
             * Получение значения из списка
             */
            if ( gettype( $pageDetail[ "row_detail" ][ $stringVariable ] ?? "" ) === "array" )
                $pageDetail[ "row_detail" ][ $stringVariable ] = $pageDetail[ "row_detail" ][ $stringVariable ][ 0 ]->value;

            /**
             * Формирование строки
             */
            $widgetFilter[ "value" ] = (int) ( $pageDetail[ "row_detail" ][ $stringVariable ] ?? 0 );

        } // if. $widgetFilter[ "value" ][ 0 ] === ":"


        $widgetSettings[ "filters" ][ $widgetFilter[ "property" ] ] = $widgetFilter[ "value" ];

    } // foreach. $structureBlock[ "settings" ][ "filters" ]


    return $widgetSettings;

} // function. processingBlockType_analyticWidgets


/**
 * Обработка календарей
 *
 * @param $structureBlock  object  Структура блока
 *
 * @return mixed
 */
function processingBlockType_calendar ( $structureBlock ) {

    global $API;
    global $pageDetail;


    /**
     * Сформированные настройки
     */
    $settings = [
        "object" => $structureBlock[ "settings" ][ "object" ],
        "events" => $structureBlock[ "settings" ][ "events" ],
        "filters" => [],
        "context" => [],
        "context_keys" => $structureBlock[ "settings" ][ "context_keys" ] ?? []
    ];


    /**
     * Формирование фильтров
     */

    foreach ( $structureBlock[ "settings" ][ "filters" ] as $filter ) {

        /**
         * Подстановка переменных
         */

        if ( $filter[ "value" ][ 0 ] === ":" ) {

            /**
             * Обработка переменной
             */

            /**
             * Получение переменной в строке
             */
            $stringVariable = substr( $filter[ "value" ], 1 );


            /**
             * Получение значения из списка
             */
            if ( gettype( $pageDetail[ "row_detail" ][ $stringVariable ] ) === "array" )
                $pageDetail[ "row_detail" ][ $stringVariable ] = $pageDetail[ "row_detail" ][ $stringVariable ][ 0 ]->value;

            /**
             * Формирование строки
             */
            $filter[ "value" ] = (int) $pageDetail[ "row_detail" ][ $stringVariable ];

        } // if. $filter[ "value" ][ 0 ] === ":"


        $settings[ "filters" ][ $filter[ "property" ] ] = $filter[ "value" ];

    } // foreach. $structureBlock[ "settings" ][ "filters" ]


    /**
     * Формирование контекста
     */

    foreach ( $structureBlock[ "settings" ][ "context" ] as $contextKey => $context ) {

        /**
         * Подстановка переменных
         */

        if ( $context[ 0 ] === ":" ) {

            /**
             * Обработка переменной
             */

            /**
             * Получение переменной в строке
             */
            $stringVariable = substr( $context, 1 );


            /**
             * Получение значения из списка
             */
            if ( gettype( $pageDetail[ "row_detail" ][ $stringVariable ] ) === "array" )
                $pageDetail[ "row_detail" ][ $stringVariable ] = $pageDetail[ "row_detail" ][ $stringVariable ][ 0 ]->value;

            /**
             * Формирование строки
             */
            $context = (int) $pageDetail[ "row_detail" ][ $stringVariable ];

        } // if. $context[ "value" ][ 0 ] === ":"


        $settings[ "context" ][ $contextKey ] = $context;

    } // foreach. $structureBlock[ "settings" ][ "context" ]


    return $settings;

} // function. processingBlockType_analyticWidgets