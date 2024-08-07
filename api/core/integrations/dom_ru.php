<?php

/**
 * Получение запросов от телефонии
 */
if ( $_POST[ "cmd" ] ) {

    $API->request->object = "dom_ru";
    $API->request->command = "events";


    foreach ( $_POST as $requestPropertyKey => $requestProperty )
        $API->request->data->$requestPropertyKey = $requestProperty;

} // if. $_POST[ "cmd" ]