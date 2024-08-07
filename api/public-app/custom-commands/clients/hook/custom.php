<?php

/**
 * @file
 * Хуки Клиентов
 */


/**
 * Обновление полей формы
 */
$formFieldsUpdate = [];

if ( property_exists( $requestData, "phone" ) && count( str_split( $requestData->phone ) ) == 11 && $requestData->phone != null ) {

    $filters = [
        "phone" => $requestData->phone,
        "is_active" => "Y"
    ];

    if ( isset( $requestData->id ) ) $filters[ "not id" ] = intval( $requestData->id );

    $clientDetails = $API->DB->from( "clients" )
        ->where( $filters )
        ->fetch();

    if ( $clientDetails && $requestData->context->trigger == "phone" ) {

        $formFieldsUpdate[ "modal_info" ][] = "Пользователь с таким номером уже существует ${clientDetails[ "id" ]} ${clientDetails[ "last_name" ]} ${clientDetails[ "first_name" ]} ${clientDetails[ "patronymic" ]}";

    }

}

/**
 * Определение пола по Отчеству
 */
if ( $requestData->patronymic && $requestData->context->trigger == "patronymic" ) {

    if ( mb_substr( $requestData->patronymic, -2 ) === "ич" )
        $formFieldsUpdate[ "gender" ][ "value" ] = "M";
    elseif ( mb_substr( $requestData->patronymic, -2 ) === "на" )
        $formFieldsUpdate[ "gender" ][ "value" ] = "W";

} // if. $requestData->patronymic


if ( $requestData->is_representative == "Y"  ) {

    $formFieldsUpdate[ "present_last_name" ] = [
        "is_visible" => true
    ];
    $formFieldsUpdate[ "present_first_name" ] = [
        "is_visible" => true
    ];
    $formFieldsUpdate[ "present_patronymic" ] = [
        "is_visible" => true
    ];
    $formFieldsUpdate[ "present_passport_series" ] = [
        "is_visible" => true
    ];
    $formFieldsUpdate[ "present_passport_number" ] = [
        "is_visible" => true
    ];
    $formFieldsUpdate[ "present_passport_issued" ] = [
        "is_visible" => true
    ];

} else {

    $formFieldsUpdate[ "present_last_name" ] = [
        "is_visible" => false
    ];
    $formFieldsUpdate[ "present_first_name" ] = [
        "is_visible" => false
    ];
    $formFieldsUpdate[ "present_patronymic" ] = [
        "is_visible" => false
    ];
    $formFieldsUpdate[ "present_passport_series" ] = [
        "is_visible" => false
    ];
    $formFieldsUpdate[ "present_passport_number" ] = [
        "is_visible" => false
    ];
    $formFieldsUpdate[ "present_passport_issued" ] = [
        "is_visible" => false
    ];

}


$API->returnResponse( $formFieldsUpdate );
