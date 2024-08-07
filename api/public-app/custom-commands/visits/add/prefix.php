<?php

/**
 * Формирование талона
 */
$serviceDetail = $API->DB->from( "services" )
    ->where( "id", $requestData->services_id[ 0 ] )
    ->limit( 1 )
    ->fetch();

$from_prodoctorov = false;
if ( property_exists( $API->request->data, "context" ) && property_exists( $API->request->data->context, "from_prodoctorov" ) )
    $from_prodoctorov = true;


if ( !$from_prodoctorov )
{
    /**
     * Статус "Повторное" у Посещения и Клиентов
     */

    foreach ( $requestData->clients_id as $clientId ) {

        /**
         * Получение посещений Клиента
         */

        $clientVisits = $API->DB->from( "visits" )
            ->innerJoin( "visits_clients ON visits_clients.visit_id = visits.id" )
            ->where( [
                "visits_clients.client_id" => intval( $clientId ),
                "visits.status" => "ended",
                "visits.is_active" => "Y"
            ] );

        if ( count( $clientVisits ) > 0 ) {

            $requestData->status = "repeated";

            $API->DB->update( "clients" )
                ->set( "is_repeat", "Y" )
                ->where( "id", $clientId )
                ->execute();

        }

    } // foreach. $requestData->clients_id
}


/**
 * Заполнение недостающих полей для записи не из црм
 */

if ( $API->isPublicAccount() && !$from_prodoctorov ) {

    $requestData->status = "online";
    $requestData->price = 0;


    /**
     * Определение времени приёма
     */
    $customTime = $API->DB->from( "workingTime" )
        ->where( [
            "user" => $requestData->user_id,
            "row_id" => $requestData->services_id[ 0 ] ?? 0
        ] )
        ->fetch();

    if ( $customTime ) $customTime = $customTime[ "time" ];
    else $customTime = $serviceDetail[ "take_minutes" ] ?? 60;

    $requestData->end_at = date(
        "Y-m-d H:i:s",
        strtotime( "$requestData->start_at + $customTime minute" )
    );


    //Получение кабинета в котором принимает Врач
    $events = $API->DB->from( "scheduleEvents" )
        ->where( [
            "event_from >= ?" => date( "Y-m-d 00:00:00", strtotime( $requestData->start_at ) ),
            "event_to <= ?" => date( "Y-m-d 23:59:59", strtotime( $requestData->end_at ) ),
            "user_id" => $requestData->user_id
        ] )
        ->fetchAll();

    foreach ( $events as $eventItem ) {

        if ( strtotime( $requestData->start_at ) < strtotime( $eventItem[ "event_from" ] ) ) continue;
        if ( strtotime( $requestData->end_at ) > strtotime( $eventItem[ "event_to" ] ) ) continue;
        $event = $eventItem;

    }

    if ( empty( $event ) )
        $API->returnResponse( "Ошибка. Посещение выходит за рамки работы сотрудника", 403 );


    if ( $event ) $requestData->cabinet_id = $event[ "cabinet_id" ];


    // Получение стоимости приёма
    foreach ( $requestData->services_id as $service ) {

        $serviceInfo = visits\getFullService( $service, $requestData->user_id );
        $requestData->price += $serviceInfo[ "price" ];

        if ( $serviceInfo[ "is_remote" ] ) $requestData->status = "remote";

    } // foreach ( $requestData->services_id as $service ) {



} // if ( $API->isPublicAccount() )


/**
 * Определение рекламного источника
 */

$clientDetail = $API->DB->from( "clients" )
    ->where( "id", $requestData->client_id )
    ->limit( 1 )
    ->fetch();

$requestData->advert_id = $clientDetail[ "advertise_id" ];

function translit ( $value ) {

    $converter = array(
        'a' => 'а',    'b' => 'б',    'v' => 'в',    'g' => 'г',    'd' => 'д',
        'e' => 'е',    'zh' => 'ж',   'z' => 'з',    'i' => 'и',    'yu' => 'ю',
        'y' => 'й',    'k' => 'к',    'l' => 'л',    'm' => 'м',    'n' => 'н',
        'o' => 'о',    'p' => 'п',    'r' => 'р',    's' => 'с',    't' => 'т',
        'u' => 'у',    'f' => 'ф',    'h' => 'х',    'c' => 'ц',    'ch' => 'ч',
        'sh' => 'ш',   'sch' => 'щ', 'ya' => 'я',

        'A' => 'А',    'B' => 'Б',    'V' => 'В',    'G' => 'Г',    'D' => 'Д',
        'E' => 'Е',    'Zh' => 'Ж',   'Z' => 'З',    'I' => 'И',    'Ya' => 'Я',
        'Y' => 'Й',    'K' => 'К',    'L' => 'Л',    'M' => 'М',    'N' => 'Н',
        'O' => 'О',    'P' => 'П',    'R' => 'Р',    'S' => 'С',    'T' => 'Т',
        'U' => 'У',    'F' => 'Ф',    'H' => 'Х',    'C' => 'Ц',    'Ch' => 'Ч',
        'Sh' => 'Ш',   'Sch' => 'Щ',  'Ы' => 'Y',    'Yu' => 'Ю',
    );

    if ( !$converter[ $value ] ) return $value;
    return strtr( $value, $converter );

} // function. translit


/**
 * Валидация посещения
 */
$publicAppPath = $API::$configs[ "paths" ][ "public_app" ];
require_once ( $publicAppPath . "/custom-libs/visits/validate.php" );


$requestData->talon = mb_strtoupper(
        translit( mb_substr( $serviceDetail[ "title" ], 0, 1 ) )
    ) . " ";

$lastVisitDetail = $API->DB->from( "visits" )
    ->select( null )->select( "id" )
    ->orderBy( "id desc" )
    ->limit( 1 )
    ->fetch();

$talonNumber = $lastVisitDetail[ "id" ];
if ( $talonNumber < 100 ) $talonNumber += 100;

$talonNumber = (string) $talonNumber;
$talonNumber = substr( $talonNumber, -3 );

$requestData->talon .= $talonNumber;