<?php

/**
 * Формирование URL для работы с сервисом
 */
$body = [];
$service_url = prodoctorov\API_URL . "/doctors/send_schedule/";

$first_month_day = (new \DateTime('now'))->modify(' - 2 week');
$last_month_day = (new \DateTime('now'))->modify(' + 2 week');

$month_start = $first_month_day->format( 'Y-m-d' ) . " 00:00:00";
$month_end = $last_month_day->format( 'Y-m-d' ) . " 23:59:59";

$max_cells = $API::$configs[ "prodoctorov" ][ "max_cells" ];

foreach ( $API->DB->from( "stores" ) as $store ) {

    /**
     * Получение списка докторов из филиала
     */
    $users_list = prodoctorov\getDoctors( $store[ "id" ] );
    $body[ "schedule" ][ "{$store[ "id" ]}" ] = $store[ "prodoctorov_article" ];


    /**
     * Формирование расписания
     */
    foreach ( $users_list as $user_id => $user ) {

        $appointments = [];
        $max_worktime = prodoctorov\getMaxWorktime( $user_id );
        $visits = prodoctorov\visitsPerDay( $month_start, $month_end, $user_id );
        $day_counter[ $user_id ] = [];

        if ( $max_worktime == 0 ) continue;

        $scheduleRequest = $API->sendRequest( "visits", "schedule", [
            "performers_article" => "user_id",
            "performers_table" => "users",
            "performers_title" => "first_name",
            "store_id" => $store[ "id" ],
            "start_at" => $month_start,
            "end_at" => $month_end,
            "user_id" => $user_id,
            "step" => 10
        ] );


        if ( empty( $scheduleRequest->schedule ) ) continue;


        /**
         * Формирование списка возможных посещений по дням
         */
        foreach ( $scheduleRequest->schedule as $day => $userEvent ) {

            $event = (array) $userEvent->$user_id;
            $day_counter[ $user_id ][ $day ] = 0;

            foreach ( $event[ "schedule" ] as $key => $scheduleEvent ) {

                $scheduleEvent = (array) $scheduleEvent;
                $nextStep = (array) $event[ "schedule" ][ $key + 1 ];

                if ( $scheduleEvent[ "status" ] == "empty" ) continue;

                $eventStart = $scheduleEvent[ "steps" ][ 0 ];
                $eventEnd = $nextStep[ "steps" ][ 0 ];

                if ( $scheduleEvent[ "status" ] !== "available" )
                {
                    $appointments[] = [
                        'dt' => $day,
                        'time_start' => $scheduleRequest->steps_list[ $eventStart ],
                        'time_end' => $scheduleRequest->steps_list[ $eventEnd ],
                        'free' => false
                    ];
                    continue;
                }

                if ( $nextStep[ "status" ] == "available" ) {

                    $nextStep = (array) $event[ "schedule" ][ $key + 2 ];
                    $eventEnd = $nextStep[ "steps" ][ 0 ];

                }

                $current = $eventStart;

                for ( $eventIndex = $eventStart; $eventIndex <= $eventEnd; $eventIndex++ )
                {
                    $time_interval = ($API::$configs[ "prodoctorov" ][ "time_interval" ] ?? 19);

                    $point = strtotime( $day . " ". $scheduleRequest->steps_list[ $current ] . " +$time_interval minutes" );
                    $end = strtotime( $day . " ".  $scheduleRequest->steps_list[ $eventIndex ] );

                    if ( $point > $end ) continue;
                    if ( $day_counter[ $user_id ][ $day ] > $max_cells && $max_cells != 0 ) continue;

                    $appointments[] = [
                        'dt' => $day,
                        'time_start' => $scheduleRequest->steps_list[ $current ],
                        'time_end' => $scheduleRequest->steps_list[ $eventIndex ],
                        'free' => $scheduleEvent[ "status" ] === "available"
                    ];

                    $current = $eventIndex;
                    $day_counter[ $user_id ][ $day ]++;
                }

            }

        } // foreach . $doctor_schedule as $day


        /**
         * Доп параметры
         */
        $doctor_name = $user[ "last_name" ] . " " . $user[ "first_name" ] . " " . $user[ "patronymic" ];

        $profession = $API->DB->from( "professions" )
            ->innerJoin( "users_professions on users_professions.profession_id = professions.id" )
            ->where( "users_professions.user_id", $user_id )
            ->fetch();

        $body[ "schedule" ][ "data" ][ "{$store[ "id" ]}" ][ "$user_id" ] = [
            "efio" => $doctor_name,
            "espec" => $profession[ "title" ],
            "cells" => $appointments
        ];


    } // foreach . $doctors_list as $doctor

}

//$API->returnResponse( $body );
$response = prodoctorov\sendRequest( $service_url, $body );
$API->returnResponse( $response );