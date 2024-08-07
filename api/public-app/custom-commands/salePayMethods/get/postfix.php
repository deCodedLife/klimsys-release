<?php

foreach ( $response[ "data" ] as $key => $value ) {

    $value[ "id" ] = $value[ "article" ];
    $response[ "data" ][ $key ] = $value;

}