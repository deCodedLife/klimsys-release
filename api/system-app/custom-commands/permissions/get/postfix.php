<?php

/**
 * Сформированный список доступов
 */
$responsePermissions = [];

/**
 * Доступы роли
 */
$rolePermissions = [];


/**
 * Получение доступов роли
 */

if ( $requestData->role_id ) {

    $roles_permissions = $API->DB->from( "roles_permissions" )
        ->select( null )->select( "permission_id" )
        ->where( "role_id", $requestData->role_id );

    foreach ( $roles_permissions as $role_permission ) $rolePermissions[] = (int) $role_permission[ "permission_id" ];

} // if. $requestData->role_id


/**
 * Получение групп доступов
 */

$permissionGroups = $API->DB->from( "permissionGroups" );

foreach ( $permissionGroups as $permissionGroup ) {

    $responsePermissions[ $permissionGroup[ "id" ] ] = [
        "group_id" => (int) $permissionGroup[ "id" ],
        "group_title" => $permissionGroup[ "title" ],
        "group_description" => $permissionGroup[ "description" ],
        "group_parent" => (int) $permissionGroup[ "parent_id" ],
        "permissions" => []
    ];

} // foreach. $permissionGroups


/**
 * Обработка доступов
 */

foreach ( $response[ "data" ] as $permissionItem ) {

    /**
     * Проверка активности доступа
     */

    $isChecked = false;

    if ( in_array( (int) $permissionItem[ "id" ], $rolePermissions ) )
        $isChecked = true;


    $responsePermissions[ $permissionItem[ "group_id" ][ "value" ] ][ "permissions" ][] = [
        "id" => (int) $permissionItem[ "id" ],
        "title" => $permissionItem[ "title" ],
        "article" => $permissionItem[ "article" ],
        "description" => $permissionItem[ "description" ],
        "is_checked" => $isChecked
    ];

} // foreach. $response[ "data" ]


/**
 * Очистка пустых групп доступов
 */

foreach ( $responsePermissions as $responsePermissionKey => $responsePermission ) {

    if ( !$responsePermission[ "permissions" ] )
        unset( $responsePermissions[ $responsePermissionKey ] );

} // foreach. $responsePermissions


/**
 * Преобразование объекта доступов в массив
 */

$response[ "data" ] = $responsePermissions;
$responsePermissions = [];

foreach ( $response[ "data" ] as $permission ) $responsePermissions[] = $permission;


$response[ "detail" ] = [];
$response[ "data" ] = $responsePermissions;