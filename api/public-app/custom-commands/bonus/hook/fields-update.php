<?php

$formFieldsUpdate = [];

/**
 * Заполнение и отправка формы
 */

$clientDetails = $API->DB->from( "clients" )
    ->where( "id", $requestData->clients_id )
    ->fetch();

$formFieldsUpdate[ "bonuses" ][ "value" ] = $clientDetails[ "bonuses" ];

