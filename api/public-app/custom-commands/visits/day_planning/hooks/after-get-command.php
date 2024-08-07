<?php
/**
 * Отфильтрованные Записи
 */
$filteredEvents = [];

$equipmentVisits = $API->DB->from( "equipmentVisits" )
    ->innerJoin( "clients on clients.id = equipmentVisits.client_id" )
    ->innerJoin( "services on services.id = equipmentVisits.service_id" )
    ->select( [
        "services.title",
        "equipmentVisits.user_id",
        "equipmentVisits.client_id",
        "equipmentVisits.id",
        "equipmentVisits.assist_id",
        "equipmentVisits.start_at",
        "equipmentVisits.end_at",
        "equipmentVisits.status",
        "clients.last_name",
        "clients.first_name",
        "clients.patronymic"
    ] )
    ->where( [

        "equipmentVisits.start_at >= ?" => $requestData->day . " 00:00:00",
        "services.is_active" => "Y",
        "clients.is_active" => "Y",
        "equipmentVisits.start_at <= ?" => $requestData->day . " 23:59:59",
        "equipmentVisits.is_active" => "Y"

    ] )
    ->orderBy( "equipmentVisits.id DESC" );


foreach ( $equipmentVisits as $equipmentVisit ) {

    if ( $equipmentVisit["user_id"] == $API::$userDetail->id || $equipmentVisit["assist_id"] == $API::$userDetail->id ) {

        $equipmentVisit["time"] = date("H:i", strtotime($equipmentVisit["start_at"]));
        $equipmentVisit["time"] .= " - " . date("H:i", strtotime($equipmentVisit["end_at"]));

        /**
         * Определение цвета
         */

        switch ($equipmentVisit["status"]) {

            case "planning":
                $equipmentVisit["color"] = "blue";
                $equipmentVisit["description"] = "Запланировано";
                break;

            case "ended":
                $equipmentVisit["color"] = "red";
                $equipmentVisit["description"] = "Завершено";
                break;

            case "process":
                $equipmentVisit["color"] = "pink";
                $equipmentVisit["description"] = "На приеме";
                break;

            case "online":
                $equipmentVisit["color"] = "light_blue";
                $equipmentVisit["description"] = "Онлайн запись";
                break;

            case "repeated":
                $equipmentVisit["color"] = "yellow";
                $equipmentVisit["description"] = "Повторная";
                break;

            case "moved":
                $equipmentVisit["color"] = "orange";
                $equipmentVisit["description"] = "Перемещена";
                break;

            case "waited":
                $equipmentVisit["color"] = "green";
                $equipmentVisit["description"] = "Ожидание";
                break;

        } // switch. $event[ "status" ]



        $event["clients"][] = [
                "last_name" => $equipmentVisit[ "last_name" ],
                "first_name" => $equipmentVisit[ "first_name" ],
                "id" => $equipmentVisit[ "client_id" ],
                "patronymic" => $equipmentVisit[ "patronymic" ],
            ];

        $equipmentVisit["links"][] = [
            "title" => $equipmentVisit["last_name"] . " " . $equipmentVisit["first_name"] . " " . $equipmentVisit["patronymic"],
            "link" => "equipmentVisits/update/" . $equipmentVisit["id"]
        ];


        /**
         * Добавление кнопок
         */

        /**
         * "script": {
         * "object": "visitReports",
         * "command": "add",
         * "properties": {
         * "client_id": ":client_id",
         * "user_id": ":user_id"
         * }
         * },
         */

        if ($equipmentVisit["status"]) $equipmentVisit["buttons"][] = [
            "type" => "print",
            "settings" => [
                "title" => "Печатать",
                "background" => "dark",
                "icon" => "print",
                "data" => [
                    "script" => [
                        "object" => "visitReports",
                        "command" => "add",
                        "properties" => [
                            "client_id" => $equipmentVisit["client_id"],
                            "user_id" => $equipmentVisit["user_id"],
                            "visit_id" => $equipmentVisit["id"],
                        ]
                    ],
                    "save_to" => [
                        "object" => "visitReports",
                        "properties" => [
                            "client_id" => $equipmentVisit["client_id"],
                            "user_id" => $equipmentVisit["user_id"]
                        ]
                    ],
                    "is_edit" => true,
                    "scheme_name" => "equipmentVisits",
                    "row_id" => $equipmentVisit["id"]
                ]
            ]
        ];

        if ($equipmentVisit["status"] == "waited") $equipmentVisit["buttons"][] = [
            "type" => "script",
            "settings" => [
                "title" => "Ожидает вызова",
                "background" => "danger",
                "icon" => "megaphone",
                "object" => "equipmentVisits",
                "command" => "accept-patient",
                "data" => [
                    "id" => $equipmentVisit["id"]
                ]
            ]
        ];

        if ($equipmentVisit["status"] == "process") $equipmentVisit["buttons"][] = [
            "type" => "script",
            "settings" => [
                "title" => "Принять повторно",
                "background" => "danger",
                "icon" => "megaphone",
                "object" => "equipmentVisits",
                "command" => "accept-again",
                "data" => [
                    "id" => $equipmentVisit["id"]
                ]
            ]
        ];

        if ($equipmentVisit["status"] == "process") $equipmentVisit["buttons"][] = [
            "type" => "script",
            "required_permissions" => [
                "manager_schedule"
            ],
            "settings" => [
                "title" => "Завершить",
                "background" => "dark",
                "icon" => "door",
                "object" => "equipmentVisits",
                "command" => "check-success",
                "data" => [
                    "id" => $equipmentVisit["id"]
                ]
            ]
        ];

        $filteredEvents[] = [

            "id" => $equipmentVisit[ "id" ],
            "body" => $equipmentVisit[ "title" ],
            "color" => $equipmentVisit[ "color" ],
            "links" => $equipmentVisit[ "links" ],
            "buttons" => $equipmentVisit[ "buttons" ],
            "start_at" => date("H:i", strtotime($equipmentVisit["start_at"])),
            "time" => $equipmentVisit[ "time" ],
            "user_id" => $equipmentVisit[ "user_id" ],
            "dateIssueCoupon" => $equipmentVisit[ "dateIssueCoupon" ],
            "assist_id" => $equipmentVisit[ "assist_id" ]

        ];


    }

} // foreach. $response[ "data" ]

foreach ( $response[ "data" ] as $visit ) {

    $visit[ "start_at" ] = substr( $visit[ "time" ], 0, 5);
    if ( $visit[ "user_id" ] == $API::$userDetail->id || $visit[ "assist_id" ] == $API::$userDetail->id ) $filteredEvents[] = $visit;

} // foreach. $response[ "data" ]

$is_queue = $API->DB->from( "users" )
    ->where( "id", $API::$userDetail->id )
    ->limit(1)
    ->fetch()[ "is_queue" ];

if ( $is_queue == "Y" ) {

    usort($filteredEvents, function($a, $b) {
        if ($a[ "dateIssueCoupon" ] != null && $b[ "dateIssueCoupon" ] != null) {

            return $a["dateIssueCoupon"] <=> $b[ "dateIssueCoupon" ];

        } elseif ($a["dateIssueCoupon"] == null && $b[ "dateIssueCoupon" ] != null) {

            return 1;

        } elseif ($a["dateIssueCoupon"] != null && $b[ "dateIssueCoupon" ] == null) {

            return -1;

        } else {
            return $a[ "start_at" ] <=> $b[ "start_at" ];
        }
    });

} else {

    usort($filteredEvents, function($a, $b) {

        $timeA =  $a[ "start_at" ];
        $timeB =  $b[ "start_at" ];

        return $timeA <=> $timeB;
    });

}

$response[ "data" ] = $filteredEvents;