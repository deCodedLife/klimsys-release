<?php

/**
 * Получение детальной информации о посещении
 *
 * @param $visitID
 * @return mixed
 */

$saleVisits = [];


function getVisitDetails( $visitID ) {

    global $API;
    return $API->DB->from( "visits" )
        ->where( "id", $visitID )
        ->fetch();

} // function. getVisitDetails $visitID



/**
 * Формирование списка комбинированных посещений
 */


if ( $requestData->is_combined == 'Y' ) {

    /**
     * Получение всех, неоплаченных клиентом, посещений
     */

    $combinedVisits = $API->DB->from( "visits" )
        ->innerJoin( "visits_clients ON visits_clients.visit_id = visits.id" )
        ->where( [
            "visits.store_id" => (int) $requestData->store_id,
            "visits_clients.client_id" => $requestData->client_id,
            "visits.is_active" => "Y",
            "visits.is_payed" => "N"
        ] );

    foreach ( $combinedVisits as $combinedVisit )
        $saleVisits[] = getVisitDetails( $combinedVisit[ "id" ] );

} else {

    foreach ( !$is_return ? [ $requestData->id ] : $requestData->visits_ids as $visit_id )
        $saleVisits[] = getVisitDetails( $visit_id );

} // if. $requestData->is_combined == 'Y'