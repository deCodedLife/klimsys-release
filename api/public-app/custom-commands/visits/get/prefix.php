<?php

if ( $requestData->cabinet == "operating" ) {

    $cabinets = $API->DB->from( "cabinets" )
        ->where( "is_operating", 'Y' );

    $cabinets_ids = [];
    foreach ( $cabinets as $cabinet ) $cabinets_ids[] = $cabinet[ "id" ];
    $requestData->cabinet_id = $cabinets_ids;

}

/**
 * Фильтр Расписания по врачу
 */
if ( $requestData->context->block === "day_planning" ) {

    $requestData->start_at = date( "Y-m-d" );
    $requestData->user_id = $API::$userDetail->id;

} // if. $requestData->context->block === "day_planning"


/**
 * Фильтр по периоду
 */
if ( $requestData->sort_by === "period" ) $requestData->sort_by = "start_at";

if ( !$requestData->sort_by ) {

    $requestData->sort_by = "start_at";
    $requestData->sort_order = "desc";

}



/**
 * Фильтр по дате (до)
 */
if ( $requestData->end_at ) $requestData->end_at .= " 23:59:59";
