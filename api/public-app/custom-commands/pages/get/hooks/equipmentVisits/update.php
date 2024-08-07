<?php

/**
 * Отключение кнопок "Удалить посещение", "Сохранить" и "Оплатить"
 */
if ( $pageDetail[ "row_detail" ][ "is_payed" ] == "Y" ) {

    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 5 ] );
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 0 ] );

}

/**
 * Определение того, стоит ли скрывать кнопку оплаты
 */
function shouldHideButton(): bool {

    global $API, $pageDetail;
    $isPayed = $pageDetail[ "row_detail" ][ "is_payed" ];

    if ( $isPayed == 'Y' ) return true;

    /**
     * Получение информации из таблицы продаж
     */
    $listedInSales = $API->DB->from( "salesEquipmentVisits" )
        ->innerJoin( "salesEquipmentVisits ON salesEquipmentVisits.sale_id = salesList.id" )
        ->where( "salesEquipmentVisits.visit_id", $pageDetail[ "row_detail" ][ "id" ] )
        ->limit( 1 )
        ->fetch();

    if ( !$listedInSales ) return false;


    if ( $listedInSales[ "status" ] == "done" ) return true;
    if ( $listedInSales[ "status" ] == "waiting" ) return true;

    // if sale status is error
    return false;

} // function shouldHideButton(): bool


/**
 * Отключение возможности оплатить посещения, в процессе и после оплаты
 */

if ( shouldHideButton() )
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 0 ] );


/**
 * Отключение кнопки "Акт вып работ"
 */
if ( $pageDetail[ "row_detail" ][ "status" ]->value === "planning" ) {

    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 3 ] );

}

/**
 * Отключение кнопки "Талон"
 */
if ( $pageDetail[ "row_detail" ][ "status" ]->value === "ended" ) {

    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 1 ] );

}

/**
 * Кнопка "Печать договора"
 */
if ( $clientDetail[ "is_contract" ] == "Y" )
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 2 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 0 ] );

if ( !$API->validatePermissions( [ "manager_schedule", "director_schedule" ], true ) ) {

    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ] );
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 5 ] );
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 6 ] );
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 7 ] );
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 8 ] );
    unset( $pageScheme[ "structure" ][ 1 ][ "settings" ][ 9 ] );

}