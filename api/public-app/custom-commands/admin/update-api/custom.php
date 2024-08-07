<?php

$output = shell_exec( "/var/www/docacrm/data/www/updateAPI.sh" );
$API->returnResponse( $output );