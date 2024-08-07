<?php

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
 * Добавление пользовательских пунктов меню
 */

if ( $userScheme ) {

    foreach ( $userScheme as $objectArticle => $object )
        if ( $object->title ) $menuScheme[ "side" ][] = [
            "title" => localizationText( $object->title ),
            "href" => $objectArticle,
            "children" => [],
            "icon" => "bullet-list"
        ];

} // if. $userScheme


/**
 * Формирование меню
 */

$returnMenu = [
    "top" => $menuScheme[ "top" ],
    "side" => []
];

foreach ( $menuScheme[ "side" ] as $menuKey => $menuValue ) {

    /**
     * Проверка прав
     */
    if ( !$API->validatePermissions( $menuValue[ "required_permissions" ] ) )
        continue;

    if ( !$API->validatePermissions( $menuValue[ "available_permissions" ], true ) )
        continue;




    /**
     * Локализация заголовка
     */
    $menuValue[ "title" ] = localizationText( $menuValue[ "title" ] );


    /**
     * Формирование дочерних пунктов меню
     */

    $returnMenuChildren = [];

    foreach ( $menuValue[ "children" ] as $menuChildKey => $menuChildValue ) {

        /**
         * Проверка прав
         */
        if ( !$API->validatePermissions( $menuChildValue[ "required_permissions" ] ) )
            continue;


        /**
         * Локализация дочернего пункта
         */
        $menuChildValue[ "title" ] = localizationText( $menuChildValue[ "title" ] );

        $returnMenuChildren[] = $menuChildValue;

    } // foreach. $menuValue[ "children" ]


    if ( !empty( $menuValue[ "children" ] ) && empty( $returnMenuChildren ) ) continue;
    $menuValue[ "children" ] = $returnMenuChildren;


    $returnMenu[ "side" ][] = $menuValue;

} // foreach. $menuScheme[ "side" ]

$response[ "data" ] = $returnMenu[ "side" ];