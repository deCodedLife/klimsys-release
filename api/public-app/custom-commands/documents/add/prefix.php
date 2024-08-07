<?php

$alreadyExisits = $API->DB->from( "documents" )
    ->where( "title", $requestData->title )
    ->fetch();


if ( $alreadyExisits )
    $API->returnResponse( "Документ с таким названием уже существует", 500 );

if ( !$requestData->owners_id ) {

    $requestData->is_general = "Y";

} else {

    $requestData->is_general = "N";

}