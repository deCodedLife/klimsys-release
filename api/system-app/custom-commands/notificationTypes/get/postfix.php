<?php

/**
 * Сформированный список типов уведомлений
 */
$responseNotificationTypes = [];

/**
 * Типы уведомлений роли
 */
$roleNotificationTypes = [];


/**
 * Получение типы уведомлений роли
 */

if ( $requestData->role_id ) {

    $roles_notificationTypes = $API->DB->from( "roles_notificationTypes" )
        ->select( null )->select( "notificationType_id" )
        ->where( "role_id", $requestData->role_id );

    foreach ( $roles_notificationTypes as $role_notificationType ) $roleNotificationTypes[] = (int) $role_notificationType[ "notificationType_id" ];

} // if. $requestData->role_id


/**
 * Обработка доступов
 */

foreach ( $response[ "data" ] as $notificationTypeItem ) {

    /**
     * Проверка активности типа уведомлений
     */

    $isChecked = false;

    if ( in_array( (int) $notificationTypeItem[ "id" ], $roleNotificationTypes ) )
        $isChecked = true;


    $responseNotificationTypes[] = [
        "id" => (int) $notificationTypeItem[ "id" ],
        "title" => $notificationTypeItem[ "title" ],
        "article" => $notificationTypeItem[ "article" ],
        "is_checked" => $isChecked
    ];

} // foreach. $response[ "data" ]


$response[ "detail" ] = [];
$response[ "data" ] = $responseNotificationTypes;