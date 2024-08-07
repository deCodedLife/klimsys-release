<?php

$publicAppPath = $API::$configs[ "paths" ][ "public_app" ];
require_once $publicAppPath . '/custom-libs/sales/business_logic.php' ;
require_once "update-products.php";
require_once "sum-fields-update.php";