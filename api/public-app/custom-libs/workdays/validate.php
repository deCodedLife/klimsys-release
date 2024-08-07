<?php


/**
 * Получение детальной информации о правиле
 */
$ruleDetails = $API->DB->from( "workDays" )
    ->where( "id", $requestData->id )
    ->fetch( );

$ruleWorkDays = [];
$ruleWorkDaysDetails = $API->DB->from( "workDaysWeekdays" )
    ->where( "rule_id", $requestData->id );

foreach ( $ruleWorkDaysDetails as $workDay )
    $ruleWorkDays[] = $workDay[ "workday" ];


/**
 * Заполнение информации о правиле
 */
$requestData->start_from = $requestData->start_from ?? date( 'Y-m-d', strtotime( $ruleDetails[ "event_from" ] ) );
$requestData->start_to = $requestData->start_to ?? date( 'Y-m-d', strtotime( $ruleDetails[ "event_to" ] ) );
$requestData->event_from = $requestData->event_from ?? date( 'H:i:s', strtotime( $ruleDetails[ "event_from" ] ) );
$requestData->event_to = $requestData->event_to ?? date( 'H:i:s', strtotime( $ruleDetails[ "event_to" ] ) );



/**
 * Получение периода
 */
$begin = DateTime::createFromFormat( "Y-m-d H:i:s", "$requestData->start_from $requestData->event_from" );
$end = DateTime::createFromFormat( "Y-m-d H:i:s", "$requestData->start_to  $requestData->event_to" );
$limit = DateTime::createFromFormat( "Y-m-d H:i:s", "$requestData->start_from $requestData->event_from" );
$limit->modify( "+1 month" );



/**
 * Перезаписываем объект requestData, чтобы затем использовать для создания записи
 * $API->DB->insert( "..." )->values( (array) $requestData )
 */
$requestData->event_from = $begin->format( "Y-m-d H:i:s" );
$requestData->event_to = $end->format( "Y-m-d H:i:s" );
unset( $requestData->start_from );
unset( $requestData->start_to );


/**
 * Инициализация значений
 */
$requestData->work_days = $requestData->work_days ?? $ruleWorkDays;
$requestData->store_id = $requestData->store_id ?? $ruleDetails[ "store_id" ];
$requestData->user_id = $requestData->user_id ?? $ruleDetails[ "user_id" ];


if ( !property_exists( $API->request->data, "cabinet_id" ) ) {

    $requestData->cabinet_id = $requestData->cabinet_id ?? $ruleDetails[ "cabinet_id" ];

} else {

    if ( is_null( $API->request->data->cabinet_id ) ) $requestData->cabinet_id = null;

}

$requestData->is_rule = $requestData->is_rule ?? $ruleDetails[ "is_rule" ] ?? 'Y';
$requestData->is_weekend = $requestData->is_weekend ?? $ruleDetails[ "is_weekend" ] ?? 'N';

if ( $begin->format( "Y-m-d" ) == $end->format( "Y-m-d" ) ) $requestData->is_rule = 'N';
else $requestData->is_rule = 'Y';


/**
 * Валидация времени
 */
if ( $end > $limit ) $API->returnResponse( "Расписание можно создать не боле чем на месяц", 402 );
if ( $begin > $end ) $API->returnResponse( "Период указан некорректно", 402 );

$storeDetails = $API->DB->from( "stores" )
    ->where( "id", $requestData->store_id )
    ->fetch( );


if ( $requestData->is_weekend !== 'Y' ) {

    $storeDetailsTitle = $storeDetails[ "title" ];
    if ( strtotime( $begin->format( "H:i:s" ) ) < strtotime( $storeDetails[ "schedule_from" ] ) )
        $API->returnResponse( "Расписание выходит за рамки графика филиала $storeDetailsTitle", 402 );

    if ( strtotime( $end->format( "H:i:s" ) ) > strtotime( $storeDetails[ "schedule_to" ] ) )
        $API->returnResponse( "Расписание выходит за рамки графика филиала $storeDetailsTitle", 402 );

}


/**
 * Формирование поискового запроса для выявления
 * корреляций по кабинету и времени в расписании
 *
 * В списке также присутствуют графики, которые
 * частично пересекаются с новым
 */
$scheduleRules = $API->DB->from( "workDays" )
    ->where(
        "( 
            ( event_from >= :from and event_from < :to ) OR 
            ( event_to > :from and event_to < :to ) OR 
            ( event_from < :from and event_to >= :to ) OR 
            ( event_from > :day_start and event_to < :day_end ) 
        )",
        [
            ":from" => $requestData->event_from,
            ":to" => $requestData->event_to,
            ":day_start" => date( "Y-m-d 00:00:00", strtotime( $requestData->event_from ) ),
            ":day_end" => date( "Y-m-d 23:59:59", strtotime( $requestData->event_to ) )
        ])
    ->where( "store_id = :store", [ ":store" => $requestData->store_id ] )
    ->where( "( is_weekend is NULL OR is_weekend = 'N' )" );


if ( $requestData->id != $requestData->user_id )
    $scheduleRules->where( "NOT id = :id", [ ":id" => $requestData->id ] );

$scheduleRules = $scheduleRules->fetchAll( 'user_id[]' );


foreach ( $scheduleRules as $user => $events ) {

    if ( empty( array_keys( $scheduleRules ) ) ) continue;

    foreach ( $events as $event ) {


        $allDayEvents = array_merge(
            $allDayEvents ?? [],
            generateRuleEvents( $event )
        );

    }

    $filtered = array_merge(
        $filtered ?? [],
        filterRuleEvents( $allDayEvents ?? [] )
    );

}
$newSchedule = generateRuleEvents( (array) $requestData, $requestData->work_days ?? [] );


foreach ( $filtered ?? [] as $ruleEvent ) {

    /**
     * Не проверяем правила на отмену посещений
     */
    if ( $requestData->is_weekend === 'Y' ) break;
    if ( $ruleEvent[ "is_weekend" ] === 'Y' ) continue;


    /**
     * Получаем время события коррелирующего правила
     */
    $eventStartFrom = strtotime( $ruleEvent[ "event_from" ] );
    $eventEndsAt = strtotime( $ruleEvent[ "event_to" ] );


    /**
     * Проходимся по событиям нового правила
     */
    foreach ( $newSchedule as $newEvent ) {

        /**
         * Получаем время события нового правила
         */
        $newEventStartFrom = strtotime( $newEvent[ "event_from" ] );
        $newEventEndsAt = strtotime( $newEvent[ "event_to" ] );

        /**
         * Находим корреляцию по времени
         */
        if (
            ( $eventStartFrom >= $newEventStartFrom and $eventStartFrom < $newEventEndsAt ) or
            ( $eventEndsAt > $newEventStartFrom and $eventEndsAt < $newEventEndsAt ) or
            ( $eventStartFrom < $newEventStartFrom and $eventEndsAt >= $newEventEndsAt )
        ) {

//                if ( $ruleEvent[ "user_id" ] === $newEvent[ "user_id" ] )
            if (
                $ruleEvent[ "is_rule" ] != $newEvent[ "is_rule" ] &&
                $ruleEvent[ "user_id" ] == $newEvent[ "user_id" ]
            ) continue;


            /**
             * Проверяем занят ли кабинет
             */
            if ( $ruleEvent[ "cabinet_id" ] == $newEvent[ "cabinet_id" ] && $ruleEvent[ "cabinet_id" ] != 0  ) {

                /**
                 * Получаем информацию по сотруднику в событии коррелирующего правила
                 */
                $employeeDetails = $API->DB->from( "users" )
                    ->where( "id", $ruleEvent[ "user_id" ] )
                    ->fetch( );

                $employeeFio = "{$employeeDetails[ "last_name" ]} ";
                $employeeFio .= mb_substr( $employeeDetails[ "first_name" ], 0, 1) . ". ";
                $employeeFio .= mb_substr( $employeeDetails[ "patronymic" ], 0, 1) . ". ";

                $API->returnResponse( "Кабинет занимает врач $employeeFio {$ruleEvent[ "id" ]}", 500 );

            } // if ( $ruleEvent[ "cabinet_id" ] == $newEvent[ "cabinet_id" ] ) {


            /**
             * Если кабинет не занят, то возможной причиной корреляции стало уже
             * существующее правило для сотрудника
             */
            if ( $ruleEvent[ "user_id" ] === $newEvent[ "user_id" ] ) {

                $eventDate = date( "d-m", strtotime( $ruleEvent[ "event_from" ] ) );

                $eventTimeFrom = date( "H:i", strtotime( $ruleEvent[ "event_from" ] ) );
                $eventTimeTo = date( "H:i", strtotime( $ruleEvent[ "event_to"] ) );

                $API->returnResponse( "У сотрудника уже есть расписание на $eventDate с $eventTimeFrom по $eventTimeTo", 500 );

            } //  if ( $ruleEvent[ "user_id" ] == $newEvent[ "user_id" ] ) {

        } // if ( correlation )

    } // foreach ( $newSchedule as $newEvent ) {

} // foreach ( $scheduleEvents as $event )