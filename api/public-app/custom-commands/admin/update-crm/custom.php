<?php

$output = shell_exec( "/var/www/docacrm/data/www/updateCRM.sh" );
$API->returnResponse( $output );