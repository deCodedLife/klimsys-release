<?php

require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/createEvents.php";


$workdayInfo = $API->DB->from( "workDays" )
    ->where( "id", $requestData->id )
    ->fetch();

if ( !$workdayInfo ) $API->returnResponse();

$newSchedule = generateRuleEvents( $workdayInfo );

foreach ( $newSchedule as $schedule ) {

    if ( $workdayInfo[ "is_weekend" ] == 'Y' ) break;

    $searchQuery = "
    SELECT * 
    FROM visits 
    WHERE 
        (
            ( start_at >= '{$schedule[ "event_from" ]}' and start_at < '{$schedule[ "event_to" ]}' ) OR
            ( end_at > '{$schedule[ "event_from" ]}' and end_at < '{$schedule[ "event_to" ]}' ) OR
            ( start_at < '{$schedule[ "event_from" ]}' and end_at >= '{$schedule[ "event_to" ]}' ) 
       ) AND 
        user_id = {$schedule[ "user_id" ]} AND
        is_active = 'Y' AND
        store_id = {$schedule[ "store_id" ]}";

    $visit_id = false;
    $visits = mysqli_query( $API->DB_connection, $searchQuery );

    foreach ( $visits as $visit ) $visit_id = $visit[ "id" ];
    if ( $visit_id ) $API->returnResponse( "У сотрудника есть посещения $visit_id", 500 );

}

$API->DB->deleteFrom( "workDaysWeekdays" )
    ->where( "rule_id", $requestData->id )
    ->execute();

$API->DB->deleteFrom( "scheduleEvents" )
    ->where( "rule_id", $requestData->id )
    ->execute();

/**
 * Отправка события об обновлении расписания
 */
$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );
