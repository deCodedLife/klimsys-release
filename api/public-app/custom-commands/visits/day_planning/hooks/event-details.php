<?php

/**
 * Формирование тела записи
 */

$visitServices = $API->DB->from( "visits_services" )
    ->where( "visit_id", $event[ "id"] );

foreach ($visitServices as $visitService) {

    $visitServiceDetail = $API->DB->from( "services" )
        ->where( "id", $visitService[ "service_id" ] )
        ->limit( 1 )
        ->fetch();


    $eventDetails[ "body" ] .= $visitServiceDetail[ "title" ] . ", ";

} // foreach. $visitServices

if ( $eventDetails[ "body" ] )
    $eventDetails[ "body" ] = substr( $eventDetails[ "body" ], 0, -2 );


$visitClientDetail = $API->DB->from( "clients" )
    ->where("id", $event[ "client_id"] )
    ->limit(1)
    ->fetch();

$event[ "clients" ] = $visitClientDetail;

$eventDetails[ "links" ][] = [
    "title" => $visitClientDetail[ "last_name" ] . " " . $visitClientDetail[ "first_name" ] . " " . $visitClientDetail[ "patronymic" ],
    "link" => "visits/update/" . $event[ "id" ]
];

/**
 * Определение цвета
 */

switch ( $event[ "status" ] ) {

    case "planning":
        $eventDetails[ "color" ] = "blue";
        $eventDetails[ "description" ] = "Запланировано";
        break;

    case "ended":
        $eventDetails[ "color" ] = "red";
        $eventDetails[ "description" ] = "Завершено";
        break;

    case "process":
        $eventDetails[ "color" ] = "pink";
        $eventDetails[ "description" ] = "На приеме";
        break;

    case "online":
        $eventDetails[ "color" ] = "light_blue";
        $eventDetails[ "description" ] = "Онлайн запись";
        break;

    case "repeated":
        $eventDetails[ "color" ] = "yellow";
        $eventDetails[ "description" ] = "Повторная";
        break;

    case "moved":
        $eventDetails[ "color" ] = "orange";
        $eventDetails[ "description" ] = "Перемещена";
        break;

    case "waited":
        $eventDetails[ "color" ] = "green";
        $eventDetails[ "description" ] = "Ожидание";
        break;

} // switch. $event[ "status" ]

//$eventDetails[ "description" ] = $eventDetails[ "description" ] . "\n" . $event[ "comment" ];
$eventDetails[ "description" ] = $event[ "comment" ];

/**
 * Добавление кнопок
 */

/**
 * "script": {
 * "object": "visitReports",
 * "command": "add",
 * "properties": {
 * "client_id": ":clients_id",
 * "user_id": ":user_id"
 * }
 * },
 */

if ( $event[ "status" ] ) $eventDetails[ "buttons" ][] = [
    "type"=>"print",
    "settings"=> [
        "title"=>"Печатать",
        "background"=>"dark",
        "icon"=>"print",
        "data" => [
            "script" => [
                "object" => "visitReports",
                "command" => "add",
                "properties" => [
                    "client_id" => $event[ "client_id" ],
                    "user_id" => $event[ "user_id" ],
                    "visit_id" => $event[ "id" ],
                ]
            ],
            "save_to" => [
                "object"=> "visitReports",
                "properties"=> [
                    "client_id"=> $event[ "clients" ][ 0 ][ "id" ],
                    "user_id"=> $event[ "user_id" ]
                ]
            ],
            "is_edit"=> true,
            "scheme_name"=> "visits",
            "row_id"=> $event[ "id" ]
        ]
    ]
];

if ( $event[ "status" ] == "waited" ) $eventDetails[ "buttons" ][] = [
    "type" => "script",
    "settings" => [
        "title" => "Ожидает вызова",
        "background" => "danger",
        "icon"=>"megaphone",
        "object" => "visits",
        "command" => "accept-patient",
        "data" => [
            "id" => $event[ "id" ]
        ]
    ]
];


if ( $event[ "status" ] == "process" ) $eventDetails[ "buttons" ][] = [
    "type" => "script",
    "settings" => [
        "title" => "Принять повторно",
        "background" => "danger",
        "icon"=>"megaphone",
        "object" => "visits",
        "command" => "accept-again",
        "data" => [
            "id" => $event[ "id" ]
        ]
    ]
];



if ( $event[ "status" ] == "process" ) $eventDetails[ "buttons" ][] = [
    "type" => "script",
    "required_permissions"=> [
        "manager_schedule"
    ],
    "settings" => [
        "title" => "Завершить",
        "background" => "dark",
        "icon"=>"door",
        "object" => "visits",
        "command" => "check-success",
        "data" => [
            "id" => $event[ "id" ]
        ]
    ]
];
