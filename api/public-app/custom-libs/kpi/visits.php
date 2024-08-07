<?php

$requestData->start_at = $start_at ?? $requestData->start_at ?? date( "Y-m-d" );
$requestData->end_at = $end_at ?? $requestData->end_at ?? date( "Y-m-d" );
$requestData->user_id = $user_id ?? $requestData->user_id;
$requestData->service = $requestData->service ?? $requestData->service_id ?? null;

if ( is_array( $requestData->service ) ) {

    if ( empty( $requestData->service ) ) $requestData->service = null;
    else $requestData->service = $requestData->service[ 0 ];

}

/*
 * Запрос данных для расчёта KPI
 */
function VisitsStat( $table ): array {

    global $API, $requestData;

    $visits_ids = visits\GetVisitsIDsByAuthor(
        $table,
        $requestData->start_at . " 00:00:00",
        $requestData->end_at . " 23:59:59",
        $requestData->user_id
    );
    if ( empty( $visits_ids ) ) return [];

    if ( property_exists( $requestData, "service" ) && $requestData->service ) {

        $visits_ids = visits\serviceFilter( $requestData->service, $visits_ids );

    }

    $request = $API->DB->from( $table )
        ->select( null )
        ->select( [ "COUNT( $table.id ) as count", "ROUND( SUM( $table.price ), 2 ) as summary" ] )
        ->where( "id", $visits_ids );

    switch ( $table )
    {
        case "visits":
            $services = $API->DB->from( "visits_services" )
                ->select( null )
                ->select( "COUNT( visits_services.id ) as services" )
                ->where( "visit_id", $visits_ids )
                ->fetch()[ "services" ] ?? 0;
            $request->select( "$services as services" );
            break;
        case "equipmentVisits":
            $request->select( [ "COUNT( equipmentVisits.service_id ) as services" ] );
            break;
    }

    return $request->fetch() ?? [ "count" => 0, "services" => 0, "summary" => 0 ];

}

$visitsInfo = VisitsStat( "visits" );
$equipmentInfo = VisitsStat( "equipmentVisits" );

$visits_count = ( $visitsInfo[ "count" ] ?? 0 ) + ( $equipmentInfo[ "count" ] ?? 0 );
$sales_summary = ( $visitsInfo[ "summary" ] ?? 0 ) + ( $equipmentInfo[ "summary" ] ?? 0 );
$services_count = ( $visitsInfo[ "services" ] ?? 0 + $equipmentInfo[ "services" ] ?? 0 );