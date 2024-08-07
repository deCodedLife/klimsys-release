<?php

/**
 * Получение информации о клиенте
 */

$clientDetails = $API->DB->from( "clients" )
    ->where( "id", $pageDetail[ "row_detail" ][ "clients_id" ] )
    ->limit( 1 )
    ->fetch();

$clientGroups = [];
$clientGroupsQuery = $API->DB->from( "clientsGroupsAssociation" )
    ->where( "client_id", $pageDetail[ "row_detail" ][ "clients_id" ] );

foreach ( $clientGroupsQuery as $group )
    $clientGroups[] = $group[ "clientGroup_id" ];


/**
 * Заполнение формы клиента
 */

foreach ( $generatedTab[ "settings" ][ "areas" ] as $areaKey => $area )
    foreach ( $area[ "blocks" ] as $blockKey => $block )
        foreach ( $block[ "fields" ] as $fieldKey => $field ) {

            if ( $field[ "article" ] == "client_groups" ) {

                $generatedTab[ "settings" ][ "areas" ][ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ][ $fieldKey ][ "value" ] = $clientGroups;
                $generatedTab[ "settings" ][ "areas" ][ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ][ $fieldKey ][ "is_disabled" ] = false;
                continue;

            }

            if ( $field[ "article" ] == "is_representative" )
                $clientDetails[ "is_representative" ] = $clientDetails[ "is_representative" ] === 'Y';

            $generatedTab[ "settings" ][ "areas" ][ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ][ $fieldKey ][ "value" ] = $clientDetails[ $field[ "article" ] ];
            $generatedTab[ "settings" ][ "areas" ][ $areaKey ][ "blocks" ][ $blockKey ][ "fields" ][ $fieldKey ][ "is_disabled" ] = false;

        }