<?php

$settings = $API->DB->from( "settings" )
    ->limit( 1 )
    ->fetch();

$API->returnResponse( $settings );