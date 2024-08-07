<?php

if ( !$requestData->phone && !$requestData->second_phone ) {

    $API->returnResponse( "Необходимо указать основной или дополнительный телефон", 400 );

}