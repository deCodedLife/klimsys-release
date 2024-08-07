 <?php

if ( $API->request->data->context->block == "select"  ) {

    $sort = "num asc";


}

if ( !$requestData->sort_by ) {

    $sort = "title asc";

}

$requestSettings[ "filter" ][ "is_active" ] = "Y";
