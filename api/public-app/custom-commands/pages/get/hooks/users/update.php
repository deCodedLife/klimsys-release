<?php
//ini_set( "display_errors", true );

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date( 'Y-m-d', strtotime("-1 months") ),
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ],
    [
        "property" => "status",
        "value" => "ended",
    ],
    [
        "property" => "user_id",
        "value" => ":id",
    ]

];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ][ 1 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date( 'Y-m-d', strtotime("-1 months") ),
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ],
    [
        "property" => "status",
        "value" => "ended",
    ],
    [
        "property" => "user_id",
        "value" => ":id",
    ]

];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ][ 2 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date( 'Y-m-d', strtotime("-1 months") ),
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ],
    [

        "property" => "status",
        "value" => "ended",

    ],
    [
        "property" => "user_id",
        "value" => ":id",
    ]

];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ][ 0 ][ "components" ][ "filters" ][ 3 ][ "settings" ][ "is_multi" ] = true;

$user = $API->DB->from( "users" )
    ->where( "id", $pageDetail[ "row_detail" ][ "id" ] )
    ->fetch();

$userStores = $API->DB->from( "users_stores" )
    ->where( "user_id", $pageDetail[ "row_detail" ][ "id" ] )
    ->limit( 1 )
    ->fetch();


if ( $userStores ) {

    $pageScheme[ "structure" ][ 1 ][ "settings" ][ "workDays" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [

        [
            "property" => "store_id",
            "value" => (int)$userStores[ "store_id" ]
        ],
        [
            "property" => "user_id",
            "value" => ":id"
        ]

    ];

}

$removeEquipment = true;
$equipmentIndex = 2;

if ( $user[ "salary_type" ] != "rate_kpi" ) {

    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ][ 1 ] );
    $pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ] = array_values( $pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ] );
    $equipmentIndex -= 1;

} else {

    $removeEquipment = false;

}

$equipmentServices = $API->DB->from( "services_users" )
    ->innerJoin( "service_equipment on service_equipment.service_id = services_users.service_id" )
    ->innerJoin( "services_second_users on services_second_users.service_id = service_equipment.service_id" );

foreach ( $equipmentServices as $service ) {

    if ( $service[ "user_id" ] == $pageDetail[ "row_detail" ][ "id" ]) {

        $removeEquipment = false;

    }

}

if ( $removeEquipment ) {

    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ][ $equipmentIndex ] );
    $pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ] = array_values( $pageScheme[ "structure" ][ 1 ][ "settings" ][ "salaryDetail" ][ "body" ] );

}