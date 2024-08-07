<?php

/**
 * @file
 * Вывод рабочих дней сотрудника
 */

/**
 * Сформированный график
 */
$workScheduleReturn = [];

/**
 * Фильтр по датам
 */
$start = date( "Y-m-d" ) . " 00:00:00";
$end = date( "Y-m-d", strtotime( "+30 days", strtotime( date( "Y-m-d" ) ) ) ) . " 23:59:59";

if ( $requestData->service_id ) {

    $workingTime = $API->DB->from( "workingTime" )
        ->where( [
            "user_id" => $requestData->user_id,
            "row_id" => $requestData->service_id
        ] )
        ->limit( 1 )
        ->fetch();

    if ( $workingTime ) {

        $interval = $workingTime[ "time" ];

    } else {

        $serviceDetail = $API->DB->from( "services" )
            ->where( "id", $requestData->service_id )
            ->limit( 1 )
            ->fetch();

        $interval = $serviceDetail[ "take_minutes" ];

    }

} else {

    $interval = "20";

}

function getGraph ( $workSchedules ) {

    global $workScheduleReturn;
    global $interval;

    foreach ( $workSchedules as $workSchedule  ) {

        $workScheduleMonths = [ "янв", "фев", "мар", "апр", "мая", "июн", "июл", "авг", "сен", "окт", "ноя", "дек" ];
        $workScheduleWeek = [ "пн", "вт", "ср", "чт", "пт", "сб", "вс" ];

        $workScheduleMonth = $workScheduleMonths[ (int) date( "m", strtotime( date( $workSchedule[ "event_from" ] ) ) ) - 1 ];
        $workScheduleWeed = $workScheduleWeek[ (int) date( "N", strtotime( date( $workSchedule[ "event_from" ] ) ) ) - 1 ];
        $startTime = strtotime( $workSchedule[ "event_from" ] );
        $endTime = strtotime( $workSchedule[ "event_to" ] );

        $time = $startTime;

        $workScheduleReturn[ date("Y-m-d", strtotime( $workSchedule[ "event_from" ] ) ) ][ "title" ] = "<span>$workScheduleWeed</span> " . date("d", strtotime( $workSchedule[ "event_from" ] )). " " . $workScheduleMonth;
        while ( $time <= $endTime ) {

            if ( strtotime( date( "Y-m-d H:i", $time ) ) > strtotime(  date( "Y-m-d H:i" ) ) ) {

                $workScheduleReturn[ date( "Y-m-d", strtotime( $workSchedule[ "event_from" ] ) ) ][ "times" ][] = date( 'H:i', $time );

            }
            $time = strtotime( '+'.$interval.' minutes', $time );

        }

    } // foreach. $workSchedules

} // function. getGraph

/**
 * Получение графика работы сотрудника
 */

$workSchedule = $API->DB->from( "workDays" )
    ->where( [
        "user_id" => $requestData->user_id,
        "store_id" => $requestData->store_id,
        "event_from >= ?" => $start,
        "event_from <= ?" => $end
    ] );

getGraph( $workSchedule);

$API->returnResponse( $workScheduleReturn );
