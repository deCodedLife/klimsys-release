<?php

if ( $API->request && property_exists( $API->request, "doctor" ) ) {

    $clientDetails = $API->request->client;
    $visitDetails = $API->request->appointment;
    $userDetails = $API->request->doctor;

    $jwt = $API->sendRequest( "users", "sign-in", [
        "email" => "public@oxbox.ru",
        "password" => "#_!_h%F02@ま45MねこJh5^v*_b2#v"
    ] );
    $API->request->jwt = $jwt;

    $clientsID = $API->sendRequest( "clients", "search", [ "search" => $clientDetails->mobile_phone ] );

    $advirtiseID = $API->DB->from( "advertise" )
        ->where(
            "( title like :title OR title like :online)",
            [
                ":title" => "Продокторов",
                ":online" => "Онлайн"
            ] )
        ->fetch();

    if ( !empty( $advirtiseID ) ) $advirtiseID = $advirtiseID[ "id" ];
    else $advirtiseID = 0;

    if ( empty( $clientsID ) ) {

        $request = [];
        $request[ "jwt" ] = $jwt;
        $request[ "first_name" ] = $clientDetails->first_name;
        $request[ "advertise_id" ] = $advirtiseID;

        if ( property_exists( $clientDetails, "last_name" ) ) $request[ "last_name" ] =  $clientDetails->last_name;
        if ( property_exists( $clientDetails, "second_name" ) ) $request[ "patronymic" ] =  $clientDetails->second_name;
        if ( property_exists( $clientDetails, "mobile_phone" ) ) $request[ "phone" ] =  $clientDetails->mobile_phone;
        if ( property_exists( $clientDetails, "birthday" ) ) $request[ "birthday" ] =  $clientDetails->birthday;

        $clientID = $API->sendRequest( "clients", "add", $request );

    } else {

        $clientDetails = (array) $clientsID[ 0 ];
        $clientID = $clientDetails[ "id" ];

        $API->DB->update( "clients" )
            ->set( "advertise_id", $advirtiseID )
            ->where( "id", $clientID )
            ->execute();

    }

    $services = $API->sendRequest( "services", "search", [
        "search" => "первичный",
        "users_id" => $userDetails->id
    ] );

    $serviceID = 0;


    foreach ( $services as $service ) {

        if ( $visitDetails->is_online && str_contains( $service->title, "онлайн" ) ) {
            $serviceID = $service->id;
            break;
        }
        if (
            !$visitDetails->is_online &&
            !str_contains( $service->title, "онлайн" ) &&
            !str_contains( $service->title, "вызов" )
        ) {
            $serviceID = $service->id;
            break;
        }

    }

    $API->request->jwt = $jwt;

    $serviceDetails = visits\getFullService( $serviceID, $userDetails->id );
    $visit_end = date( "Y-m-d H:i:s", strtotime( $visitDetails->dt_start . " +{$serviceDetails[ "take_minutes" ]} minutes" ) );


    $filters = [
        "event_from >= ?" => date( "Y-m-d 00:00:00", strtotime( $visitDetails->dt_start ) ),
        "event_to < ?" => date( "Y-m-d 23:59:59", strtotime( $visit_end ) ),
        "user_id" => $userDetails->id,
        "store_id" => $userDetails->lpu_id,
        "is_weekend" => 'Y'
    ];

    $is_weekend = $API->DB->from( "scheduleEvents" )
        ->where( $filters )
        ->fetch();

    if ( $is_weekend ) $API->returnResponse( false, 500 );
    unset( $filters[ "is_weekend" ] );
    $filters[ "is_rule" ] = 'N';

    $hasEvents = $API->DB->from( "scheduleEvents" )
        ->where( $filters )
        ->fetch();

    if ( !$hasEvents ) unset( $filters[ "is_rule" ] );

    $performerWorkSchedule = $API->DB->from( "scheduleEvents" )
        ->where( $filters )
        ->orderBy( "event_from ASC" )
        ->fetch();

    $request = [];

    $request[ "user_id" ] = $userDetails->id;
    $request[ "store_id" ] = $userDetails->lpu_id;
    $request[ "author_id" ] = 3;
    $request[ "clients_id" ] = [ $clientID ];
    $request[ "start_at" ] = date( "Y-m-d H:i:00", strtotime( $visitDetails->dt_start ) );
    $request[ "end_at" ] = $visit_end;
    $request[ "comment" ] = "Продокторов: " . $visitDetails->comment;
    $request[ "services_id" ] = [ $serviceID ];
    $request[ "notify" ] = true;
    $request[ "send_review" ] = true;
    $request[ "status" ] = "prodoctorov";
    $request[ "service" ] = $serviceID;
    $request[ "price" ] = $serviceDetails[ "price" ];
    $request[ "jwt" ] = $jwt;
    $request[ "context" ] = [ "from_prodoctorov" => true ];

    if ( $visitDetails->comment == "Клиника может позвонить" ) $request[ "comment" ] = "Продокторов";
    if ( $performerWorkSchedule ) $request[ "cabinet_id" ] = $performerWorkSchedule[ "cabinet_id" ];
    $status = $API->sendRequest( "visits", "add", $request, $_SERVER[ "HTTP_HOST" ], true );
    $status = (array) $status;
    
    if ( $status[ "status" ] != 200 ) exit( json_encode( [ "status_code" => 423, "detail" => $status[ "data" ] ] ) );
    else exit( json_encode( [ "status_code" => 204, "claim_id" => $status[ "data" ] ] ) );

}

if ( $API->request && property_exists( $API->request, "claim_id" ) ) {

    $API->sendRequest( "visits", "update", [
        "id" => $API->request->claim_id,
        "is_active" => false
    ] );
    exit( json_encode( [ "status_code" => 204 ] ) );

}