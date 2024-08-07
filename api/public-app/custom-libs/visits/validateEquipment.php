<?php

$requestData->objectTable = "equipmentVisits";

$visitDetail = $API->DB->from( "equipmentVisits" )
    ->where( "id", $requestData->id )
    ->fetch();


$requestData->services_id = [ $visitDetail[ "service_id" ] ];


require_once "validate.php";


/**
 * Проверка на занятость оборудования
 */
foreach ( $existingVisits as $visit ) {

    if ( $visit[ "is_active" ] === 'N' ) continue;
    if ( $visit[ "id" ] == $visitDetail[ "id" ] ) continue;
    if ( !$visit[ "assist_id" ] ) continue;

    if ( $visit[ "equipment_id" ] == $requestData->equipment_id ?? $visitDetail[ "equipment_id" ] )
        $API->returnResponse( "Оборудование занято {$visit[ "id" ]}", 500 );

}