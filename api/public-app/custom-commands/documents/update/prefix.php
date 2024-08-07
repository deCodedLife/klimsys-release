<?php

$alreadyExisits = mysqli_fetch_array(
    mysqli_query(
        $API->DB_connection,
        "SELECT * FROM documents WHERE title = '$requestData->title' AND NOT id = $requestData->id"
    )
);

if ( $alreadyExisits )
    $API->returnResponse( "Документ с таким названием уже существует", 500 );


if ( !$requestData->owners_id ) $requestData->is_general = "Y";
else $requestData->is_general = "N";

$requestData->updated_at = date("Y-m-d H:i:s"); 