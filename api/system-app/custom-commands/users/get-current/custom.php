<?php

/**
 * @file
 * Получение детальной информации о текущем Пользователе
 */


/**
 * Проверка авторизации
 */
if ( !$API::$userDetail->id ) $API->returnResponse( false );


/**
 * Получение детальной информации о текущем Пользователе
 */

$userDetail = $API->DB->from( "users" )
    ->where( [
        "id" => $API::$userDetail->id
    ] )
    ->limit( 1 )
    ->fetch();

if ( !$userDetail ) $API->returnResponse( false );


$API->returnResponse( $userDetail );