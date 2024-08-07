<?php

/**
 * @file
 * Получение детальной информации об Исполнителях
 */

$performersRows = $API->DB->from( $requestData->performers_table )
    ->where( $performersFilter )
    ->orderBy( "$requestData->performers_title ASC" );


/**
 * @hook
 * Отработка стандартного get запроса
 */
if ( file_exists( $public_customCommandDirPath . "/hooks/get-performers.php" ) )
    require( $public_customCommandDirPath . "/hooks/get-performers.php" );


foreach ( $performersRows as $performersRow ) {

   /**
    * Формирование имени исполнителя
    */

    $performerName = $performersRow[ $requestData->performers_title ];

    if ( $requestData->performers_title === "first_name" ) {

        $performerName = $performersRow[ "last_name" ] . " ";

        if ( $performersRow[ "first_name" ] ) $performerName .= mb_substr( $performersRow[ "first_name" ], 0, 1 ) . ". ";
        if ( $performersRow[ "patronymic" ] ) $performerName .= mb_substr( $performersRow[ "patronymic" ], 0, 1 ) . ".";

    } // if. $requestData->performers_title === "first_name"

    $performersDetail[ $performersRow[ "id" ] ] = $performerName;

} // foreach. $performersRows

$performersDetail = json_decode( json_encode( $performersDetail ?? [] ) );

// $API->returnResponse( $performersDetail, 500 );