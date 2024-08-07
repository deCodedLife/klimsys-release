<?php

/**
 * подстановка id клиента
 */
if ( ( $requestData->context->form == "bonus" ) && $requestData->context->row_id )
    $formFieldValues[ "id" ] = $requestData->context->row_id;

