<?php


$API->returnResponse( visits\getFullServiceDefault( 8328, 335 ) );


//$result = $API->sendRequest( "visits", "schedule", [
//    "performers_article" => "user_id",
//    "performers_table" => "users",
//    "performers_title" => "first_name",
//    "store_id" => 62,
//    "start_at" => "2024-05-01",
//    "end_at" => "2024-05-31",
//    "user_id" => 121,
//    "step" => 20
//] );




//$result = [];
//$scan_path = $API::$configs[ "paths" ][ "public_custom_commands" ];
//$folders = scandir( $scan_path ) ?? [];
//$folders = array_diff( $folders, array( ".", ".." ) );
//
//if ( empty( $folders ) ) $API->returnResponse();
//
//function propertiesGet ( array $objectScheme ): array
//{
//    $result = [];
//
//    foreach ( $objectScheme[ "properties" ] as $property ) {
//
//        if ( !$property[ "is_autofill" ] ) continue;
//        if ( $property[ "ignoreInLogs" ] ) continue;
//        $result[] = $property[ "title" ];
//
//    }
//
//    return $result;
//}
//
//foreach ( $folders as $folder ) {
//
//    $inner_dirs = scandir( join( "/", [ $scan_path, $folder ] ) ) ?? [];
//    $inner_dirs = array_diff( $inner_dirs, array( ".", ".." ) );
//
//    if ( empty( $inner_dirs ) ) continue;
//
//    $public_file_name = join( "/", [ $API::$configs[ "paths" ][ "public_object_schemes" ], $folder ] ) . ".json";
//    $system_file_name = join( "/", [ $API::$configs[ "paths" ][ "system_object_schemes" ], $folder ] ) . ".json";
//
//    if ( !file_exists( $public_file_name ) && file_exists( $system_file_name ) ) continue;
//    if ( file_exists( $public_file_name ) ) $objectName = file_get_contents( $system_file_name );
//    if ( file_exists( $public_file_name ) ) $objectName = file_get_contents( $public_file_name );
//
//    $objectScheme = (array) json_decode( $objectName, true ) ?? [];
//    $objectName = $objectScheme[ "title" ] ?? "";
//
//    if ( count( array_intersect( $inner_dirs, [ "add", "update", "remove" ] ) ) != 0 ) {
//
//        foreach ( $inner_dirs as $inner_dir ) {
//
//            switch ( $inner_dir ) {
//                case "add":
//                    $result[ $objectName . " добавление" ] = "Название";
//                    break;
//                case "update":
//                    $result[ $objectName . " редактирование" ] = propertiesGet( $objectScheme );
//                    break;
//                case "remove":
//                    $result[ $objectName . " удаление" ] = "Название";
//                    break;
//            }
//
//        }
//
//    }
//
//
//
//
//}
//
//
//$API->returnResponse( $result );


//$execCommand = [
//    "/opt/php74/bin/php",
//    "{$_SERVER[ "DOCUMENT_ROOT" ]}/index.php",
//    $_SERVER[ "DOCUMENT_ROOT" ],
//    $API::$configs[ "company" ],
//    $insertId,
//    $_SERVER[ "HTTP_HOST" ],
//    $API->request->jwt
//];
//$execCommand = join( ' ', $execCommand );
//
//$requestData->minutes = $requestData->minutes ? "*/$requestData->minutes" : "*";
//$requestData->hours = $requestData->hours ? "*/$requestData->hours" : "*";
//$requestData->days = $requestData->days ? "*/$requestData->days" : "*";
//$requestData->month = $requestData->month ? "*/$requestData->month" : "*";
//$requestData->weekdays = $requestData->weekdays ? "*/$requestData->weekdays" : "*";
//
//shell_exec( "crontab -l > tasks" );
//$cronJobs = file_get_contents( "tasks" );
//$cronJobs .= "$requestData->minutes $requestData->hours $requestData->days $requestData->month $requestData->weekdays     $execCommand\n";
//file_put_contents( "tasks", $cronJobs );
//shell_exec( "crontab < tasks" );

//file_put_contents( "test", "Neko neko ni" );

//$API->DB->insertInto( "cronTasks" )
//    ->values( [
//        "title" => "blyat",
//        "object" => "dev",
//        "command" => "any",
//        "run_configuration" => "period",
//        "days" => 1,
//        "hours" => "0",
//        "minutes" => 0
//    ])
//    ->execute();

//ini_set( "display_errors", true );
//
//$roles = $API->DB->from( "permissions" )
//    ->orderBy( "id DESC" );
//
//
//foreach ( $roles as $role ) {
//
//    $API->DB->update( "permissions" )
//        ->set( "id", $role[ "id" ] + 1 )
//        ->where( "id", $role[ "id" ] )
//        ->execute();
//
//}
//
//
//$publicRole = $API->DB->from( "roles_permissions" )
//    ->fetch();

//$API->DB->update( "roles" )
//    ->set( "id", 3 )
//    ->where( "id", $publicRole[ "id" ] )
//    ->execute();

//
//foreach ( $API->DB->from( "roles_permissions" ) as $role ) {
//
//    $API->DB->update( "roles_permissions" )
//        ->set( "permission_id", $role[ "permission_id" ] - 1 )
//        ->where( "id", $role[ "id" ] )
//        ->execute();
//
//}

//$API->DB->update( "users" )
//    ->set( "role_id", 3 )
//    ->where( "email", "public@oxbox.ru" )
//    ->execute();

//$rolesPermissions = $API->DB->from( "roles_permissions" )
//    ->where( "role_id > ?", 2 );
//
//foreach ( $rolesPermissions as $role ) {
//
//    $API->DB->update( "roles_permissions" )
//        ->set( "role_id", $role[ "role_id" ] + 1 )
//        ->where( "id", $role[ "id" ] )
//        ->execute();
//
//}


//$visits = $API->DB->from( "visits" )
//    ->select( null )
//    ->select( [
//        "SUM( CASE WHEN is_active = 'Y' THEN 1 ELSE 0 END ) AS active",
//        "SUM( CASE WHEN is_active = 'N' THEN 1 ELSE 0 END ) AS not_active",
//        "SUM( CASE WHEN status = 'ended' THEN 1 ELSE 0 END ) AS ended",
//        "SUM( CASE WHEN status = 'canceled' THEN 1 ELSE 0 END ) AS canceled",
//        "SUM( CASE WHEN is_payed = 'Y' THEN price ELSE 0 END ) as price",
//        "client_id"
//    ] )
//    ->groupBy( "client_id" )
//    ->fetchAll( "client_id" );


//$visits = $API->DB->from( "visits" )
//    ->select( null )
//    ->select( "COUNT( id )" )
//    ->where( "is_active", 'N' )
//    ->fetch();

//$API->returnResponse( $visits->getQuery(false) );
//$API->returnResponse( $visits );

//н " if is_active = N"
//$start_at = date( "Y-01-01 00:00:00" );
//$end_at = date( "Y-m-d 23:59:59" );
//
//$request = $API->DB->from( "visits" )
//    ->innerJoin( "visits_clients ON visits_clients.visit_id = visits.id" )
//    ->where( [
//        "visits.start_at > ?" => $start_at,
//        "visits.start_at < ? " => $end_at,
//        "visits.store_id" => 62,
//        "visits.is_active" => "Y",
//        "visits.is_payed" => "N"
//    ] );
//
////$API->returnResponse( $request->getQuery(false) );
////$API->returnResponse( $request->fetchColumn( 0 ) );
////$API->returnResponse( $request->fetchAll( "id" ) );
//$API->returnResponse( array_keys( $request->fetchAll( 'id' ) ) );

//
//$groups = $API->DB->from( "permissions" )
//    ->where( "group_id", 2 );
//
//foreach ( $groups as $group ) {
//
//    $API->DB->update( "permissions" )
//        ->set( [
//            "group_id" => $group[ "group_id" ] - 1
//        ] )
//        ->where( "id", $group[ "id" ] )
//        ->execute();
//
//}
//
//$sqlQuery = "SELECT * FROM `visits` WHERE (user_id = 141 OR assist_id = 141) AND start_at BETWEEN '2024-02-29 00:00:00' AND '2024-02-29 23:59:59';";
//$visits = mysqli_query(
//    $API->DB_connection,
//    $sqlQuery
//);
//foreach ( $visits  as $visit) {
//    $API->returnResponse($visit);
//
//}


//
//$start_at = "2024-01-01 00:00:00";
//$end_at = "2024-01-12 23:00:00";
//
//$sqlFilters = [
//    "start_at >= ?" => $start_at,
//    "end_at <= ?" => $end_at,
//    "is_payed" => "Y",
//    "is_active" => "Y"
//];
//
//$visitList = $API->DB->from( "visits" )
//    ->where( $sqlFilters );
//
//foreach ( $visitList as $visit ) {
//
//    $services = $API->DB->from( "visits_services" )
//        ->where( "visit_id", $visit[ "id" ] );
//
//    if ( count( $services ) != 0 ) continue;
//    if ( $visit[ "id" ] == "523801" ) continue;
//    if ( $visit[ "id" ] == "523802" ) continue;
//    if ( $visit[ "id" ] == "523809" ) continue;
//    if ( $visit[ "id" ] == "523810" ) continue;
//    if ( $visit[ "id" ] == "523813" ) continue;
//    if ( $visit[ "id" ] == "523814" ) continue;
//
//    $saleServices = $API->DB->from( "salesProductsList" )
//        ->innerJoin( "salesList on salesList.id = salesProductsList.sale_id" )
//        ->innerJoin( "saleVisits on saleVisits.sale_id = salesList.id" )
//        ->where( "saleVisits.visit_id", $visit[ "id" ] );
//
//    foreach ( $saleServices as $service ) {
//
//        $API->DB->insertInto( "visits_services" )
//            ->values( [
//                "visit_id" => $visit[ "id" ],
//                "service_id" => $service[ "product_id" ]
//            ] )
//            ->execute();
//
//    };
//
//}






// Пересоздать эвенты для расписания
//require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/createEvents.php";
//
//foreach ( $API->DB->from( "users" ) as $userDetails ) {
//
//    $schedule = $API->DB->from( "workDays" )
//        ->where( "user_id", $userDetails[ "id" ] );
//
//    $API->DB->deleteFrom( "scheduleEvents" )
//        ->where( "user_id", $userDetails[ "id" ] )
//        ->execute();
//
//    foreach ( $schedule as $day ) {
//
//        $newSchedule = generateRuleEvents( $day );
//
//        foreach ( $newSchedule as $item ) {
//
//            $item[ "rule_id" ] = $item[ "id" ];
//            unset( $item[ "id" ] );
//
//            $API->DB->insertInto( "scheduleEvents" )
//                ->values( $item )
//                ->execute();
//
//        }
//
//    }
//
//}




//
//$API->DB->update( "visits" )
//    ->set( "status", "planning" )
//    ->where( "status", "planned" )
//    ->execute();
//
//$sqlQuery = "SELECT * FROM `visits` WHERE (NOT cast(start_at as date) = cast(end_at as date));";
//$visits = mysqli_query(
//    $API->DB_connection,
//    $sqlQuery
//);
//
//foreach ( $visits as $visit ) {
//
//    $visitService = $API->DB->from( "visits_services" )
//        ->where( "visit_id", $visit[ "id" ] )
//        ->limit(1)
//        ->fetch();
//
//    if ( $visit[ "user_id" ] == 217 ) $visit[ "equipment_id" ] = 1;
//    if ( $visit[ "user_id" ] == 293 ) $visit[ "equipment_id" ] = 2;
//    if ( $visit[ "user_id" ] == 294 ) $visit[ "equipment_id" ] = 3;
//
//    $visit[ "user_id" ] = $visit[ "assist_id" ];
//    $visit[ "service_id" ] = $visitService[ "service_id" ];
//
//    unset( $visit[ "advert_id" ] );
//    unset( $visit[ "is_online" ] );
//
//    $API->DB->insertInto( "equipmentVisits" )
//        ->values( $visit )
//        ->execute();
//
//    $API->DB->delete( "visits" )
//        ->where( "id", $visit[ "id" ] )
//        ->execute();
//
//}

//$testQuery = $API->DB->from( "users" )
//    ->where( "is_active", 'Y' );
//$ids = [ 1, 2, 120, 121, 123 ];
//
//
//foreach ( $ids as $id ) {
//    $testQuery->where( "id", $id );
//}
//
//foreach ( $testQuery as $user ) {
//    $users[] = $user;
//}

//$API->returnResponse( $users );



//$API->returnResponse( $API::$userDetail->id );

//$API->DB->insertInto( "atolCashboxes" )
//    ->values( [
//        "store_id" => 62,
//        "cashbox_id" => "1_yashlek"
//    ] )
//    ->execute();


//set_time_limit( 0 );


//$visits = $API->DB->from( "visits" );
//
//foreach ( $visits as $visit ) {
//
//    $API->returnResponse( $visit );
//
//    $visitClient = $API->DB->from( "visits_clients" )
//        ->where( "visit_id", $visit[ "id" ] )
//        ->limit( 1 )
//        ->fetch();
//
//    $clientDetail = $API->DB->from( "clients" )
//        ->where( "id", $visitClient[ "client_id" ] )
//        ->limit( 1 )
//        ->fetch();
//
//
//    $API->DB->update( "visits" )
//        ->set( [
//            "advert_id" => $clientDetail[ "advertise_id" ]
//        ] )
//        ->where( [
//            "id" => $visit[ "id" ]
//        ] )
//        ->execute();
//
//
//    sleep( 0.01 );
//
//}


//$oldPassword = "81dc9bdb52d04dc20036dbd8313ed055";
//$newPassword = md5( "840QwertY320" );
//
//$users = $API->DB->update( "users" )
//    ->set( "password", $newPassword )
//    ->where( "not id = ?", 2 )
//    ->execute();


//mysqli_query(
//    $API->DB_connection,
//    "drop table visits_users"
//);
//
//header('X-Accel-Buffering: no');
//header( "Content-Type: text/event-stream" );
//header('Cache-Control: no-store');
//
//session_start();
//ob_end_flush();
//ob_start();
//
//$testData = [
//    "cashboxID" => 200,
//    "task" => [
//        "test" => 100
//    ]
//];
//
//$i = 0;
//
//while ( true ) {
//
//    if ( connection_aborted() ) break;
//    echo json_encode( $testData );
//    echo "\n\n";
//
//    ob_flush();
//    flush();
//
//    sleep( random_int(10, 100) / 100 );
//    break;
//
//}
//
//exit();


//$API->returnResponse( $rules );

// $API->DB->delete( "promotionObjects" )
	// ->where( "id", 121 )
	// ->execute();/

//$activePromitions = $Discounts->GetActiveDiscounts();
//
//$Modifier = new Сashbox\Modifier;
//$Modifier->Items = [ 2 ];
//$Modifier->Type = MODIFIER_TYPES[ 0 ];
//
////$Modifier->Type = MODIFIER_TYPES[ 3 ];

//$API->returnResponse();







//$API->returnResponse( "Nya" );

//mysqli_query(
//    $API->DB_connection,
//    "DROP TABLE servicesGroups"
//);
//exit(200);
//mysqli_query($API->DB_connection, "UPDATE clients set bonuses = 100, deposit = 100");

//mysqli_query($API->DB_connection, "DELETE FROM visits");
//mysqli_query($API->DB_connection, "DELETE FROM sales;");
//mysqli_query($API->DB_connection, "DELETE FROM salesServices;");
//mysqli_query($API->DB_connection, "DELETE FROM salesVisits");

//$API->DB->update( "sales" )
//    ->set( [
//        "status" => "done"
//    ] )
//    ->where( "id", 93 )
//    ->execute();
//
//$API->DB->update( "visits" )
//    ->set( "is_payed", 'Y' )
//    ->where( "id", 246 )
//    ->execute();

//
//$API->DB->delete( "salesServices" )
//    ->where( "id", 22)
//    ->execute();

//$API->DB->update( "salesServices" )
//    ->set( [
//        "status" => "waiting",
//        "pay_type" => "sellReturn"
//    ] )
//    ->where( "id", 40 )
//    ->execute();




//$API->DB->update("sales")
//    ->set