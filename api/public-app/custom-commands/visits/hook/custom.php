<?php

/**
 * @file
 * Хуки на Запись к врачу
 */
$formFieldsUpdate = [];
$hasAssist = false;

//$API->returnResponse( $requestData );
//$API->returnResponse( $API->request );

if ( $requestData->context->trigger == "store_id" ) {

    $formFieldsUpdate[ "cabinet_id" ][ "value" ] = null;

}


/**
 * Проход
 */
foreach ( $requestData->services_id as $service ) {

    if ( $requestData->context->trigger == "services_id" ) {

        $serviceDetail = $API->DB->from( "services" )
            ->where( "id", $service )
            ->fetch();

        $modalExists = false;

        foreach ( $formFieldsUpdate[ "modal_info" ] as $info ) {

            if ( $info == $serviceDetail[ "preparation" ] ) $modalExists = true;

        }

        if ( !$modalExists && $serviceDetail[ "preparation" ] ) $formFieldsUpdate[ "modal_info" ][] = $serviceDetail[ "preparation" ];

    }

}

/**
 * Расчет стоимости и времени выполнения Записи
 */
if ( $requestData->services_id && $requestData->user_id ) {

    /**
     * Стоимость Записи
     */
    $visitPrice = 0;

    /**
     * Время выполнения Записи (мин)
     */
    $visitTakeMinutes = 0;


    /**
     * Проверка наличия обязательных св-в
     */
    if ( !$requestData->start_at ) $API->returnResponse( [] );


    /**
     * Обход услуг
     */
    foreach ( $requestData->services_id as $serviceId ) {

        $second_users = $API->DB->from( "services_second_users" )
            ->where( "service_id", $serviceId );

        if ( count( $second_users ) != 0 ) $hasAssist = true;


        /**
         * Получение детальной информации об услуге
         */
        $serviceDetail = visits\getFullService( $serviceId, $requestData->user_id );
        $visitPrice += floatval( $serviceDetail[ "price" ] );
        $visitTakeMinutes += intval( $serviceDetail[ "take_minutes" ] );

    } // foreach. $requestData->services_id

    /**
     * Обновление полей формы
     */

    $formFieldsUpdate[ "price" ] = [
        "value" => $visitPrice
    ];

    if ( $requestData->context->trigger == "start_at" ) {

        $formFieldsUpdate[ "end_at" ] = [
            "value" => date(
                "Y-m-d H:i:s", strtotime(
                    "+$visitTakeMinutes minutes", strtotime( $requestData->start_at )
                )
            )
        ];

    }

    if ( !$requestData->id ) {

        $formFieldsUpdate[ "end_at" ] = [
            "value" => date(
                "Y-m-d H:i:s", strtotime(
                    "+$visitTakeMinutes minutes", strtotime( $requestData->start_at )
                )
            )
        ];

    }
    

} // if. $requestData->services_id && $requestData->users_id


if ( $requestData->clients_id ) {

    $clientsInfo = [];

    foreach ($requestData->clients_id as $clientId) {

        $clientDetail = $API->DB->from( "clients" )
            ->where("id", $clientId)
            ->limit(1)
            ->fetch();


        if ( $clientDetail[ "phone" ] ) {
//            $API->returnResponse($clientDetail[ "phone" ] );

            $phoneFormat = ", +" . sprintf("%s (%s) %s-%s-%s",
                    substr($clientDetail[ "phone" ], 0, 1),
                    substr($clientDetail[ "phone" ], 1, 3),
                    substr($clientDetail[ "phone" ], 4, 3),
                    substr($clientDetail[ "phone" ], 7, 2),
                    substr($clientDetail[ "phone" ], 9)
                );

        } else {

            $phoneFormat = ", +" . sprintf("%s (%s) %s-%s-%s",
                    substr($clientDetail[ "second_phone" ], 0, 1),
                    substr($clientDetail[ "second_phone" ], 1, 3),
                    substr($clientDetail[ "second_phone" ], 4, 3),
                    substr($clientDetail[ "second_phone" ], 7, 2),
                    substr($clientDetail[ "second_phone" ], 9)
                );

        }

        $clientsInfo[] = [
            "link" => "clients/card/$clientId",
            "title" => "№{$clientDetail[ "id" ]} {$clientDetail[ "last_name" ]} {$clientDetail[ "first_name" ]} {$clientDetail[ "patronymic" ]}$phoneFormat"
        ];

    }

    $formFieldsUpdate[ "clients_info" ] = [ "is_visible" => true, "value" => $clientsInfo ];

} else {

    $formFieldsUpdate[ "clients_info" ] = [ "is_visible" => false ];

}


if ( $requestData->start_at ) {

    if ( !$visitTakeMinutes ) {

        $visits = $API->DB->from( "visits" )
            ->where( "id", $requestData->id )
            ->limit( 1 )
            ->fetch();

        $visits[ "start_at" ] = strtotime($visits[ "start_at" ]);
        $visits[ "end_at" ] = strtotime($visits[ "end_at" ]);

        $diff = abs($visits[ "start_at" ] - $visits[ "end_at" ]);
        $minutes = $diff / 60;

        if ( !$requestData->id ) {

            $formFieldsUpdate[ "end_at" ] = [
                "value" => date(
                    "Y-m-d H:i:s", strtotime(
                        "+$minutes minutes", strtotime( $requestData->start_at )
                    )
                )
            ];

        }


    }

}


if ( $hasAssist ) $formFieldsUpdate[ "assist_id" ][ "is_visible" ] = true;
else $formFieldsUpdate[ "assist_id" ][ "is_visible" ] = false;


$API->returnResponse( $formFieldsUpdate );
