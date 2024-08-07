<?php


/**
 * Получение информации о клиенте
 */

$clientDetails = $API->DB->from( "clients" )
    ->where( "id", $pageDetail[ "row_detail" ][ "client_id" ]->value )
    ->limit( 1 )
    ->fetch();

/**
 * Заполнение формы клиента
 */

foreach ( $generatedTab[ "settings" ][ "areas" ] as $areaKey => $area )
    foreach ( $area[ "blocks" ] as $blockKey => $block )
        foreach ( $block[ "fields" ] as $fieldKey => $field ){

            $generatedTab[ "settings" ][ "areas" ][ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ][ $fieldKey ][ "value" ] = $clientDetails[ $field[ "article" ] ];
            $generatedTab[ "settings" ][ "areas" ][ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ][ $fieldKey ][ "is_disabled" ] = false;

        }
