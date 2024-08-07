<?php

$publicAppPath = $API::$configs[ "paths" ][ "public_app" ];
require_once( $publicAppPath . '/custom-libs/workdays/createEvents.php' );

global $API, $requestData;

$start_at    = "";
$end_at      = "";
$cabinet     = 0;
$clients     = [];
$services    = [];
$employee    = 0;
$assistant   = 0;
$store_id    = 0;
$consumables = [];
$objectTable = $requestData->objectTable ?? "visits";


/**
 * Подтягиваем данные из существующего посещения
 */
if ( isset( $requestData->id ) ) {

    $visitDetail = $API->DB->from( $objectTable )
        ->where( "id", $requestData->id )
        ->fetch();

    $end_at    = $visitDetail[ "end_at" ];
    $start_at  = $visitDetail[ "start_at" ];
    $store_id  = $visitDetail[ "store_id" ];
    $cabinet   = $visitDetail[ "cabinet_id" ];
    $employee  = $visitDetail[ "user_id" ];
    $assistant = $visitDetail[ "assist_id" ];

    /**
     * Заполнение клиентов, сотрудников и услуг
     */
    foreach ( $API->DB->from( "visits_clients" )
                  ->where( "visit_id", $requestData->id ) as $visit_client )
        $clients[] = $visit_client[ "client_id" ];

    foreach ( $API->DB->from( "visits_services" )
                  ->where( "visit_id", $requestData->id ) as $visit_service )
        $services[] = $visit_service[ "service_id" ];

} // if. isset( $requestData->id )


$start_at    = $requestData->start_at ?? $start_at;
$end_at      = $requestData->end_at ?? $end_at;
$cabinet     = $requestData->cabinet_id ?? $cabinet;

if ( $requestData->clients_id ?? false ) $clients = $requestData->clients_id;
else if ( $requestData->client_id ?? false ) $clients = [ $requestData->client_id ];

if ( property_exists( $requestData, "services_id" ) ) $services = $requestData->services_id;
if ( property_exists( $requestData, "service_id" ) ) $services = [ $requestData->service_id ];

//$services    = $requestData->services_id ?? $services;
$employee    = $requestData->user_id ?? $employee;
$assistant   = $requestData->assist_id ?? $assistant;
$store_id    = $requestData->store_id ?? $store_id;

$use_assistant = false;

if ( property_exists( $API->request->data, "cabinet_id" ) ) {

    if ( !property_exists( $requestData, "cabinet_id" ) ) $API->returnResponse( "Выберите кабинет!", 500 );
    if ( !$requestData->cabinet_id ) $API->returnResponse( "Выберите кабинет!", 500 );

}

if ( strtotime( $start_at ) > strtotime( $end_at ) )  {

    $API->returnResponse( "Некорректно указана дата", 400 );

}

if ( strtotime( $start_at ) < strtotime( date( "Y-m-d 00:00:00" ) ) ) {

    $API->returnResponse( "Нельзя создавать посещения за прошедшие дни", 400 );

}

if ( strtotime( $start_at ) == strtotime( $end_at ) ) {


    $modifiedEndData = new DateTime( $end_at );
    $modifiedEndData->modify( "+1 min" );

    $end_at = $modifiedEndData->format( "Y-m-d H:i:s" );
    $requestData->end_at = $end_at;

} // if ( $requestData->start_at == $requestData->end_at )


/**
 * Проверка времени филиала
 */
$storeData = $API->DB->from( "stores" )
    ->where( "id", $store_id )
    ->limit( 1 )
    ->fetch();

$isTimeCorrect = true;


if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $start_at )->format('Y-m-d') == DateTime::createFromFormat( 'Y-m-d H:i:s', $end_at )->format('Y-m-d') ) {

    if ( strtotime( DateTime::createFromFormat( 'Y-m-d H:i:s', $start_at )->format('H:i:s') ) < strtotime( $storeData[ "schedule_from" ] ) ) $isTimeCorrect = false;
    if ( strtotime( DateTime::createFromFormat( 'Y-m-d H:i:s', $end_at )->format('H:i:s') ) > strtotime( $storeData[ "schedule_to" ] ) ) $isTimeCorrect = false;

}

if ( !$isTimeCorrect ) $API->returnResponse( "Время посещения выходит за рамки графика работы филиала", 400 );
//$API->returnResponse( [ $start_at, $end_at ], 500 );
//{"status":500,"data":["2023-12-04 18:11:00","2023-12-04 18:12:00"],"detail":[]}

/**
 * Получение расходников
 */
foreach ( $services as $service ) {

    $service_consumables = $API->DB->from( "services_consumables" )
        ->where( "row_id", $service );



    foreach ( $service_consumables as $consumable )
        $consumables[ $consumable[ "consumable_id" ] ][ "count" ] += $consumable->count;

} // foreach. $services


/**
 * Проверка расходников
 */
foreach ( $consumables as $consumable_id => $consumable ) {

    $warehouse = $API->DB->from( "warehouses" )
        ->where( [
            "store_id" => $store_id,
            "consumable_id" => $consumable_id
        ] )
        ->limit( 1 )
        ->fetch();

    if ( $consumable[ "count" ] > $warehouse[ "count" ] )
        $API->returnResponse( "Недостаточно расходников", 400 );

} // foreach ( $consumables as $consumable_id => $consumable )


/**
 * Ищем все посещения за запрашиваемый период
 * Сорян, но периоды я таки скопировал(
 */
//$existingVisits = $API->DB->from( "visits" )
//    ->where( "
//        ( start_at >= :start and start_at < :end ) OR
//        ( end_at > :start and end_at < :end ) OR
//        ( start_at < :start and end_at > :end ) AND
//        is_active = :is_active AND
//        store_id = :store AND
//        user_id not in ( :users )
//    ",
//    [
//        ":start" => $start_at,
//        ":end" => $end_at,
//        ":is_active" => "Y",
//        ":store" => $store_id,
//        ":users" => "260,135"
//    ]);

//->fetchAll();
//if ( is_array( $requestData->id ) ) $existingVisits->where( "id not in ( :visits )", [ ":visitss" => join( ",", $requestData->id ) ] );
//else $existingVisits->where( "not id = :visits", [ "visits" => $requestData->id ] );

//$API->returnResponse( $existingVisits->getQuery( false ) );

$getVisitsQuery = "SELECT * FROM $objectTable WHERE
    (
        ( start_at >= '$start_at' and start_at < '$end_at' ) OR
        ( end_at > '$start_at' and end_at < '$end_at' ) OR
        ( start_at < '$start_at' and end_at > '$end_at' ) 
    ) AND
    is_active = 'Y' AND
    store_id = $store_id
";


/**
 * Отсекаем редактируемое посещение
 */
if ( is_array( $requestData->id ) ) $getVisitsQuery .= " AND id NOT IN (" . join( ", ", $requestData->id ) . ") ";
else $getVisitsQuery .= $requestData->id ? " AND NOT id = {$requestData->id}" : "";

$existingVisits = mysqli_query(
    $API->DB_connection,
    $getVisitsQuery
);



/**
 * Проверяем кабинет на занятость
 * @param $cabinetID
 * @param $visits
 * @return bool
 */
function isCabinetOccupied( $cabinetID, $visits ): bool {

    global $API;

    /**
     * Получение информации по кабинету
     */
    $cabinetDetails = $API->DB->from( "cabinets" )
        ->where( "id", $cabinetID )
        ->fetch();

    /**
     * Если не учитывается занятость
     */
    if ( $cabinetDetails[ "is_employment" ] == "Y" ) return false;

    /**
     * Проверка занятости
     */
    foreach ( $visits as $visit ) {

        if ( $visit[ "cabinet_id" ] == $cabinetID )
            $API->returnResponse( "Кабинет занят. Посещение {$visit["id"]}", 500 );

    }

    return false;

} // function isCabinetOccupied( $cabinetID, $visits )

if ( $objectTable !== "equipmentVisits" ) isCabinetOccupied( $cabinet, $existingVisits );


/**
 * Проверка на занятость клиента
 * @param $clients
 * @param $visits
 * @return bool
 */
function isClientBusy( $client, $visits ): int {

    global $API, $objectTable;

    /**
     * Проход по всем посещениях
     */
//    $a=[];foreach($visits as $v)$a[]=$v;$API->returnResponse( $a, 500 );
    foreach ( $visits as $visit ) {

        /**
         * Запрос на получение клиентов у существующих посещений
         */
        if ( $objectTable == "equipmentVisits" ) {

            if ( $visit[ "client_id" ] == $client ) return $visit[ "user_id" ];
            continue;

        }

        $visit_clients = mysqli_query(
            $API->DB_connection,
            "SELECT * FROM visits_clients WHERE visit_id = {$visit[ 'id' ]} AND client_id = $client"
        );

        /**
         * Если в выдаче есть клиенты, то они - заняты
         */
        if ( mysqli_num_rows( $visit_clients ) != 0 ) return $visit[ "user_id" ];

    }

    return 0;

} // function isClientBusy( $clients, $visits )



/**
 *  Обход всех клиентов
 */
foreach ( ( $clients ?? [] ) as $client ) {

    if ( $objectTable === "equipmentVisits" ) continue;

    /**
     * Проверка занят ли клиент
     */
    $busyVisitID = isClientBusy( $client, $existingVisits );
    if ( $busyVisitID == 0 ) continue;

    /**
     * Получение детальной информации о клиенте
     */
    $client_details = $API->DB->from( "clients" )
        ->where( "id", $client )
        ->fetch();

    /**
     * Получение имени сотрудника у которого клиент на приёме
     */
    $employee_details = mysqli_fetch_array(
        mysqli_query(
            $API->DB_connection,
            "SELECT last_name FROM users WHERE id = $busyVisitID"
        )
    )[0] ?? $busyVisitID;

    $API->returnResponse( "Клиент {$client_details[ 'last_name' ]} на приёме у врача {$employee_details}", 400 );

} // foreach ( $clients as $client )



/**
 * Проверка дополнительного сотрудника
 * @param $serviceDetails
 * @param $employees
 * @return bool
 */
function employeesAccountedFor( $serviceDetails, $employee ): bool {

    global $API;

    /**
     * Получение списка вторых исполнителей
     */
    $service_second_employees = $API->DB->from( "services_second_users" )
        ->where( "service_id", $serviceDetails[ "id" ] );

    if ( count( $service_second_employees ) == 0 ) return true;
    if ( !$employee ) return false;

    /**
     * Если нет нужного сотрудника, то выкинуть ошибку
     */
    foreach ( $service_second_employees as $second_employee )
        if ( $second_employee[ "user_id" ] == $employee ) return true;

    return false;

} // function employeesAccountedFor( $services, $employees )



/**
 * Проверка второго исполнителя для каждой услуги
 */
foreach ( $services as $service ) {

    $serviceDetails = $API->DB->from( "services" )
        ->where( "id", $service )
        ->fetch();

    if ( $serviceDetails[ "is_remote" ] == 'Y' && $API->request->command != "update" ) $requestData->status = "remote";
    if ( $serviceDetails[ "is_consider_second_performer_time" ] == "Y" ) $use_assistant = true;

    $accountedFor = employeesAccountedFor( $serviceDetails, $assistant );

    if ( $assistant && !$accountedFor )
        $API->returnResponse( "Выбранный ассистент не указан в услуге {$serviceDetails[ 'title' ]}", 500 );


    if ( !$accountedFor )
        $API->returnResponse( "Укажите второго исполнителя для услуги {$serviceDetails[ 'title' ]}", 500 );

    $service_second_employees = $API->DB->from( "services_second_users" )
        ->where( "service_id", $serviceDetails[ "id" ] );

    if ( count( $service_second_employees ) == 0 ) continue;
    if ( $serviceDetails[ "is_consider_second_performer_time" ] == "N" ) $employees = [ $employees[ 0 ] ];

} // foreach ( $services as $service )



/**
 * Проверка на занятость сотрудников
 * @param $emoployees
 * @param $services
 * @param $visits
 * @return bool
 * @throws \Envms\FluentPDO\Exception
 */
function isEmployeeBusy( $employee, $visits ): int {

    global $API;
    /**
     * Проход по всем посещениям
     */
    foreach ( $visits as $visit ) {

        /**
         * Получение всех сотрудников из посещения
         * PS: Запрос $API->DB->from... ->where( "user_id in (?)", join( ... ) ) не отрабатывает(
         */

        // TODO
        if ( $visit[ "user_id" ] == $employee ) return $visit[ "id" ];
//        if ( $visit[ "assist_id" ] == $employee ) return $visit[ "id" ];

    } // foreach ( $visits as $visit )

    return 0;

} // function isEmployeeBusy( $employees, $visits ): bool


/**
 * Проверка графика работы исполнителя
 */
function checkWorkDays( $employee_id, $store_id, $start_at, $end_at ): void {

    global $API;

    $userDetails = $API->DB->from( "users" )
        ->where( "id", $employee_id )
        ->fetch();

    $query = "
    SELECT * 
    FROM workDays 
    WHERE 
        (
            ( event_from >= '$start_at' and event_from < '$end_at' ) OR
            ( event_to > '$start_at' and event_to < '$end_at' ) OR
            ( event_from <= '$start_at' and event_to >= '$end_at' ) 
        ) AND
        user_id = $employee_id AND
        store_id = $store_id";


    $start =  strtotime( $start_at );
    $end =  strtotime( $end_at );

    $employeeWorkdays = mysqli_query( $API->DB_connection, $query );
    $is_correct = false;

    foreach ( $employeeWorkdays as $workday ) {

        if ( $workday[ "is_rule" ] === 'N' ) {

            $is_correct = true;
            continue;

        }

        foreach ( generateRuleEvents( $workday ) as $event ) {

            $from = strtotime( $event[ "event_from" ] );
            $to =  strtotime( $event[ "event_to" ] );

            if (
                ( $from >= $start and $from < $end ) or
                ( $to >= $start and $to <= $end ) or
                ( $from < $start and $to > $end )
            ) {

                $is_correct = true;

            }

        }


    }

    if ( !$is_correct ) $API->returnResponse( "У сотрудника {$userDetails[ "last_name" ]} нет графика на выбранную дату", 500 );

}

if ( $requestData->objectTable !== "equipmentVisits" )
    checkWorkDays( $employee, $store_id, $start_at, $end_at );


/**
 * Проверка сотрудника на занятость
 */
$busyVisitID = isEmployeeBusy( $employee, $existingVisits );

if ( $use_assistant ) {
    $employee = $assistant;
    $busyVisitID = $busyVisitID != 0 ? $busyVisitID : isEmployeeBusy( $employee, $existingVisits );
}

if ( $busyVisitID != 0 && $objectTable !== "equipmentVisits" ) {

    $employee_details = $API->DB->from( "users" )
        ->where( "id", $employee )
        ->fetch();

    $store_details = $API->DB->from( "stores" )
        ->innerJoin( "visits ON stores.id = visits.store_id" )
        ->where( "visits.id", $busyVisitID )
        ->fetch();

    $API->returnResponse( "Сотрудник {$employee_details[ 'last_name' ]} занят. Филиал ({$store_details[ 'title' ]}). Посещение $busyVisitID", 500 );

}


