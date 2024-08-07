<?php

if ( ( $requestData->context->form == "users" ) && $requestData->context->row_id ) {

    $formFieldValues[ "user_id" ] = $requestData->context->row_id;

}