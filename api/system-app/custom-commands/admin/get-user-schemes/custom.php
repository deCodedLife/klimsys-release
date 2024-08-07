<?php

/**
 * @file Получение пользовательских схем
 */


/**
 * Добавление элемента в позицию массива
 */
function addItemToArrayPosition ( $array, $insertItem, $position ) {

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
 * Сформированный список схем
 */
$resultUserSchemes = [];

/**
 * Ссылки системных объектов
 */
$systemObjectHrefs = [];


/**
 * Получение схемы меню
 */

$menuScheme = [];

if ( file_exists( $API::$configs[ "paths" ][ "public_app" ] . "/menu.json" ) )
    $menuScheme = file_get_contents( $API::$configs[ "paths" ][ "public_app" ] . "/menu.json" );
else
    $menuScheme = file_get_contents( $API::$configs[ "paths" ][ "system_app" ] . "/menu.json" );

if ( !$menuScheme ) $API->returnResponse( [] );

$menuScheme = json_decode( $menuScheme, true );


/**
 * Получение ссылок на системные объекты
 */

foreach ( $menuScheme[ "side" ] as $menuItem ) {

    if ( !$menuItem[ "children" ] ) $systemObjectHrefs[] = $menuItem[ "href" ];
    else foreach ( $menuItem[ "children" ] as $menuItemChild ) $systemObjectHrefs[] = $menuItemChild[ "href" ];

} // foreach. $menuScheme[ "side" ]


/**
 * Обработка системных схем
 */

foreach ( $systemObjectHrefs as $systemObjectHref ) {

    /**
     * Форма системного объекта
     */
    $systemSchemeForm = [];


    /**
     * Формирование пути к схеме страницы
     */

    $pagePath = "$systemObjectHref/add.json";

    $publicSchemePath = $API::$configs[ "paths" ][ "public_page_schemes" ] . "/$pagePath";
    $systemSchemePath = $API::$configs[ "paths" ][ "system_page_schemes" ] . "/$pagePath";


    /**
     * Подключение схемы страницы
     */

    $pageScheme = [];

    if ( file_exists( $publicSchemePath ) ) $pageScheme = file_get_contents( $publicSchemePath );
    elseif ( file_exists( $systemSchemePath ) ) $pageScheme = file_get_contents( $systemSchemePath );
    else continue;


    /**
     * Декодирование схемы страницы
     */
    try {

        $pageScheme = json_decode( $pageScheme, true );
        if ( $pageScheme === null ) continue;

    } catch ( Exception $error ) {

        continue;

    } // try. json_decode. $pageScheme


    /**
     * Получение формы добавления записи
     */

    foreach ( $pageScheme[ "structure" ] as $pageBlock ) {

        /**
         * Фильтр лишних блоков
         */
        if ( $pageBlock[ "type" ] !== "form" ) continue;
        if ( $pageBlock[ "settings" ][ "command" ] !== "add" ) continue;
        if ( $pageBlock[ "settings" ][ "object" ] !== $systemObjectHref ) continue;


        /**
         * Загрузка схемы объекта
         */

        $objectScheme = $API->loadObjectScheme( $pageBlock[ "settings" ][ "object" ] );

        if ( !$objectScheme ) continue;


        /**
         * Получение св-в объекта
         */

        foreach ( $pageBlock[ "settings" ][ "areas" ] as $area ) {

            /**
             * Схема области
             */
            $formArea = [
                "type" => "system",
                "size" => $area[ "size" ],
                "blocks" => []
            ];


            /**
             * Обход блоков области
             */
            foreach ( $area[ "blocks" ] as $block ) {

                /**
                 * Сформированный блок
                 */
                $resultBlock = [];


                /**
                 * Обработка полей
                 */
                foreach ( $block[ "fields" ] as $field ) {

                    /**
                     * Получение детальной информации о св-ве
                     */

                    foreach ( $objectScheme[ "properties" ] as $property ) {

                        if ( $property[ "article" ] !== $field ) continue;

                        $resultBlock[] = [
                            "type" => "system",
                            "title" => $property[ "title" ],
                            "article" => $property[ "article" ]
                        ];

                    } // foreach. $objectScheme[ "properties" ]

                } // foreach. $block[ "fields" ]

                if ( $resultBlock ) $formArea[ "blocks" ][] = [
                    "type" => "system",
                    "fields" => $resultBlock
                ];

            } // foreach. $area[ "blocks" ]


            if ( $formArea[ "blocks" ] ) $systemSchemeForm[] = $formArea;

        } // foreach. $pageBlock[ "settings" ][ "areas" ]

    } // foreach. $pageScheme[ "structure" ]


    if ( $systemSchemeForm )
        $resultUserSchemes[ $systemObjectHref ] = [
            "type" => "system",
            "title" => $objectScheme[ "title" ],
            "form" => $systemSchemeForm
        ];

} // foreach. $systemObjectHrefs


/**
 * Получение пользовательской схемы
 */

$userScheme = [];

if ( file_exists( $API::$configs[ "paths" ][ "public_user_schemes" ] . "/" . $API::$configs[ "company" ] . ".json" ) )
    $userScheme = file_get_contents( $API::$configs[ "paths" ][ "public_user_schemes" ] . "/" . $API::$configs[ "company" ] . ".json" );
else
    $userScheme = file_get_contents( $API::$configs[ "paths" ][ "public_user_schemes" ] . "/" . $API::$configs[ "company" ] . ".json" );

if ( !$userScheme ) $API->returnResponse( $resultUserSchemes );

$userScheme = json_decode( $userScheme, true );


/**
 * Обработка пользовательской схемы
 */

foreach ( $userScheme as $userObjectArticle => $userObject ) {

    /**
     * Добавление пользовательского объекта
     */
    if ( !array_key_exists( $userObjectArticle, $resultUserSchemes ) )
        $resultUserSchemes[ $userObjectArticle ] = [
            "type" => "custom",
            "title" => $userObject[ "title" ],
            "form" => []
        ];


    /**
     * Добавление пользовательских областей в форму
     */
    foreach ( $userObject[ "areas" ] as $userArea )
        $resultUserSchemes[ $userObjectArticle ][ "form" ] = addItemToArrayPosition(
            $resultUserSchemes[ $userObjectArticle ][ "form" ],
            [
                "type" => "custom",
                "size" => $userArea[ "size" ],
                "blocks" => []
            ],
            $userArea[ "position" ]
        );

    /**
     * Добавление пользовательских блоков в форму
     */
    foreach ( $userObject[ "blocks" ] as $userBlock )
        $resultUserSchemes[ $userObjectArticle ][ "form" ][ $userBlock[ "area_position" ] ][ "blocks" ] = addItemToArrayPosition(
            $resultUserSchemes[ $userObjectArticle ][ "form" ][ $userBlock[ "area_position" ] ][ "blocks" ],
            [
                "type" => "custom",
                "fields" => []
            ],
            $userBlock[ "block_position" ]
        );

    /**
     * Добавление пользовательских св-в в форму
     */
    foreach ( $userObject[ "properties" ] as $userPropertyArticle => $userProperty )
        $resultUserSchemes[ $userObjectArticle ][ "form" ][ $userProperty[ "area_position" ] ][ "blocks" ][ $userProperty[ "block_position" ] ][ "fields" ] = addItemToArrayPosition(
            $resultUserSchemes[ $userObjectArticle ][ "form" ][ $userProperty[ "area_position" ] ][ "blocks" ][ $userProperty[ "block_position" ] ][ "fields" ],
            [
                "type" => "custom",
                "title" => $userProperty[ "title" ],
                "field_type" => $userProperty[ "field_type" ],
                "article" => $userPropertyArticle
            ],
            $userProperty[ "property_position" ]
        );

} // foreach. $userScheme


$API->returnResponse( $resultUserSchemes );