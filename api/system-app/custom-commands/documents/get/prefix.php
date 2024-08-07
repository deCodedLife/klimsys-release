<?php

/**
 * @hook
 * Фильтр документов
 */


if ( file_exists( $public_customCommandDirPath . "/hooks/filter.php" ) )
require( $public_customCommandDirPath . "/hooks/filter.php" );


$requestData->sort_by = "title";
