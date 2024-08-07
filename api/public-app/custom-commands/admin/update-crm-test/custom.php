<?php

$output = shell_exec( "/var/www/docacrm/data/www/updateCRM_test.sh" );
$API->returnResponse( $output );