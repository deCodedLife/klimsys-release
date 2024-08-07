<?php

/**
 * @file
 * Хуки Сотрудников
 */


/**
 * Обновление полей формы
 */
$formFieldsUpdate = [];


/**
 * Определение пола по Отчеству
 */
if ( $requestData->patronymic ) {

    if ( mb_substr( $requestData->patronymic, -2 ) === "ич" )
        $formFieldsUpdate[ "gender" ] = "M";
    elseif ( mb_substr( $requestData->patronymic, -2 ) === "на" )
        $formFieldsUpdate[ "gender" ] = "W";

} // if. $requestData->patronymic


/**
 * Блок "Процент от продаж услуг"
 */

if ( $requestData->salary_type == "rate_percent" ) {

    $formFieldsUpdate[ "services_user_percents" ][ "is_visible" ] = true;

} else {

    $formFieldsUpdate[ "services_user_percents" ][ "is_visible" ] = false;

} // if. $requestData->is_percent == "Y"

if ( $requestData->salary_type == 'rate_kpi' ) {

    $formFieldsUpdate[ "sales" ][ "is_visible" ] = true;
    $formFieldsUpdate[ "services" ][ "is_visible" ] = true;
    $formFieldsUpdate[ "promotions" ][ "is_visible" ] = true;

} else {

    $formFieldsUpdate[ "sales" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "services" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "promotions" ][ "is_visible" ] = false;

}


$API->returnResponse( $formFieldsUpdate );