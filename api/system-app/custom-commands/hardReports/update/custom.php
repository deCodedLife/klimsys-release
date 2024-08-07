<?php

/**
 * Обновление отчетов из очереди
 */


/**
 * Получение данных об отчете
 */

$updatingOrder = $API->DB->from( "hardReports" )
    ->where( "status != ?", "updated" )
    ->limit( 1 )
    ->fetch();

if ( !$updatingOrder ) $API->returnResponse( true );

$updatingOrderFilters = $API->DB->from( "hardReports_filters" )
    ->where( "report_id", $updatingOrder[ "id" ] );

foreach ( $updatingOrderFilters as $updatingOrderFilter )
    $updatingOrder[ "filters" ][ $updatingOrderFilter[ "filter_article" ] ] = $updatingOrderFilter[ "filter_value" ];


/**
 * Обновление статуса отчета
 */
if ( $updatingOrder[ "status" ] == "waiting" ) $API->DB->update( "hardReports" )
    ->set( "status", "updating" )
    ->where( [
        "id" => $updatingOrder[ "id" ]
    ] )
    ->execute();


/**
 * Подключение скрипта обработки отчета
 */

$hardOrderScriptPath = $API::$configs[ "paths" ][ "public_custom_commands" ] . "/hardReports/" . $updatingOrder[ "report_article" ] . "/custom.php";

if ( file_exists( $hardOrderScriptPath ) ) require_once( $hardOrderScriptPath );