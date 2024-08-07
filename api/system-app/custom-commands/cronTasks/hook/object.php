<?php

$formFieldsUpdate = [];

if ( !empty( $requestData->object ) ) $formFieldsUpdate[ "command" ][ "is_visible" ] = true;
else $formFieldsUpdate[ "command" ][ "is_visible" ] = false;

$API->returnResponse( $formFieldsUpdate );