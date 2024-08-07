<?php

$headerDocument = $API->DB->from( "documents" )
    ->where( "article", "header" )
    ->limit(1)
    ->fetch();

if ( !$headerDocument ) $API->returnResponse( "Шапка не найдена", 500 );

$headerTextBlockID = $API->DB->from( "documentBlocks" )
    ->where( [
        "document_id" => $headerDocument[ "id" ],
        "block_type" => "text"
    ] )
    ->limit(1)
    ->fetch();

if ( !$headerTextBlockID ) $API->returnResponse( "Блок с текстом у шапки не найден", 500 );

$headerTextBody = $API->DB->from( "documents_text" )
    ->where( "id", $headerTextBlockID[ "block_id" ] )
    ->fetch();

if ( !$headerTextBody )  $API->returnResponse( "Блок с текстом у шапки не найден", 500 );
if ( !$requestData->body ) $API->returnResponse( "Требуется тело", 500 );

$API->returnResponse( $headerTextBody[ "document_body" ] . $requestData->body );