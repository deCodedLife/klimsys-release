<?php
if ( ( $requestData->context->form == "clients" ) && $requestData->context->row_id )
    $formFieldValues[ "client_id" ] = $requestData->context->row_id;

