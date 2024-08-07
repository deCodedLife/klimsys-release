<?php

namespace prodoctorov;


//global $api_url;
use Prodoctorov;


const API_URL = "https://api.prodoctorov.ru/v2";
//const API_URL = "https://mis-api.medflex.ru/v2";
///doctors/send_schedule/


/**
 * Отправка запроса на сервис Prodoctorov
 *
 * @param object | array $body
 *
 * @return bool|string
 */
function sendRequest( $url, $body ): bool | string {

    global $API, $api_url;

    if ( !key_exists( "token", $API::$configs ) || !key_exists( "prodoctorov", $API::$configs[ "token" ] ) )
        return "No token";

    $token = $API::$configs[ "token" ][ "prodoctorov" ];

    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL, $url );
    curl_setopt( $curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json' ,
        "Authorization: Token   $token"
    ] );
    curl_setopt( $curl, CURLOPT_POST, 1 );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($body) );

    $result = curl_exec( $curl );
    curl_close( $curl );

    return $result;

} // private function sendRequest $body


/**
 * Аналогично returnResponse с правками для api Продокторов
 *
 * @param bool|null  $data
 * @param int|string $status
 */
function sendResponse ( bool $data = null, int $status = 204 ) {

    /**
     * Формирование ответа на запрос
     */
    $result = [];
    if ( $status ) $result[ "status_code" ] = $status;
    if ( $data ) $result[ "detail" ] = $data;


    /**
     * Вывод ответа на запрос, и завершение работы скрипта
     */
    exit( json_encode( $result ) );

} // function. sendResponse


/**
 * Получение списка всех докторов по филиалу
 * @param int $hospital_id
 *
 * @return array
 */
function getDoctors( $store_id, $profession_id = null ) {

    global $API;

    $request = $API->DB->from( "users" )
        ->innerJoin( "users_stores on users_stores.user_id = users.id" )
        ->where( "users_stores.store_id", $store_id );

    if ( !empty( $profession_id ) ) {

        $request->innerJoin( "users_professions on users_professions.user_id = users.id" );
        $request->where( "users_professions.profession_id = $profession_id" );

    }

    return $request->fetchAll( "id" );

} // function getDoctors . $hospital_id


/**
 * Получение максимального времени приёма
 * @param $user_id
 *
 * @return int
 */
function getMaxWorktime( $user_id ) {

    global $API;

    $services = $API->DB->from( "services" )
        ->where( "title like ?", "Прием%первичный%" )
        ->fetchAll( "id" );

    $services_ids = array_keys( $services );

    $worktime = $API->DB->from( "workingTime" )
        ->select( null )
        ->select( "MAX(time)" )
        ->where( [
            "user" => $user_id,
            "row_id" => $services_ids
        ] )
        ->fetch();

    if ( !$worktime ) return 0;
    if ( intval( $worktime[ "MAX(worktime)" ] ) > 10 ) return intval( $worktime[ "MAX(worktime)" ] );
    else return 10;

} // function getMaxWorktime . $employee_id


/**
 * Получение списка посещений за период
 * @param string $start
 * @param string $end
 * @param int | null $employee_id
 *
 * @return array
 */
function visitsPerDay( $start, $end, $user_id = null ) {

    global $API;

    $visits_return = [];
    $visits = \visits\GetVisitsIDsByUser( "visits", $start, $end, $user_id );
    if ( empty( $visits ) ) return [];

    $visits = $API->DB->from( "visits" )->where( "id", $visits );

    foreach ( $visits as $visit ) {

        $start = \DateTime::createFromFormat( 'Y-m-d H:i:s', $visit[ "start_at" ] );
        $end = \DateTime::createFromFormat( 'Y-m-d H:i:s', $visit[ "end_at" ] );

        $visits_return[ $start->format('d') ][] = [
            "id" => $visit[ "id" ],
            "start" => $start,
            "end" => $end
        ];

    }

    return $visits_return;

} // function visitsByPeriod . $start, $end, $employee_id = null


/**
 * Обновить свободное время у врача
 * @param $visit_id
 */
function TakeAppointment( $visit_id, $isFree = null ) {

    global $API;

    if ( !key_exists( "token", $API::$configs ) || !key_exists( "prodoctorov", $API::$configs[ "token" ] ) )
        return;


    /**
     * Получение данных о посещении
     */
    $visitDetail = $API->DB->from( "visits" )
        ->where( "id", $visit_id )
        ->fetch();

    if ( !$isFree ) $isFree = intval( $visitDetail[ "is_active" ] ) == "Y";
    $isFree = intval( $visitDetail[ "is_active" ] ) == "Y" ? true : $isFree;


    /*
     * Формирование запроса для продокторов
     */
    $body = [
        "filial_id" => $visitDetail[ "store_id" ],
        "doctor_id" => $visitDetail[ "client_id" ],
        "date" => date( "Y-m-d", strtotime( $visitDetail[ "start_at" ] ) ),
        "cells" => [
            [
                "time_start" => date( "H:i", strtotime( $visitDetail[ "start_at" ] ) ),
                "time_end" => date( "H:i", strtotime( $visitDetail[ "end_at" ] ) ),
                "free" => $isFree
            ]
        ]
    ];


    $service_url = prodoctorov\API_URL . "/occupied_doctor_schedule_slot/";
    prodoctorov\sendRequest( $service_url, $body );

} // public function TakeAppointment( $visit_id ) {