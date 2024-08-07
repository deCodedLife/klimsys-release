<?php

if ( $requestData->store_id && $requestData->store_id == null ) {

    $API->returnResponse("Выберите филиал", 400);

}
