<?php

/**
 * Текущий пользователь
 */
$currentUser = $API->getCurrentUser();


/**
 * Виджет зарплаты
 */

if ( $currentUser ) {

    $userDetail = $API->DB->from( "users" )
        ->where( "id", $currentUser->id )
        ->fetch();

    if ( !$userDetail[ "domru_login" ] || $userDetail[ "domru_login" ] == "" ) unset( $response[ "data" ][ "dom_ru" ] );
    if ( $userDetail[ "salary_type" ] != "rate_kpi" ) unset( $response[ "data" ][ "salary_widget" ] );

} // if. $currentUser