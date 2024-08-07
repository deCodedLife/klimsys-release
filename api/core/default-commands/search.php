<?php

/**
 * @file Стандартная команда search.
 * Используется для поиска записей в базе данных
 */


/**
 * Найденные записи
 */
$rows = [];


/**
 * Проверка наличия таблицы в схеме запроса
 */
if ( !$objectScheme[ "table" ] ) $API->returnResponse( "Отсутствует таблица в схеме запроса", 500 );

if ( $requestData->is_test ) {

    $API->returnResponse( $requestData->search, 500 );


} else {

    /**
     * Объявление объекта sphinx
     */

    $Sphinx = new SphinxClient();



    $Sphinx->SetSortMode( SPH_SORT_RELEVANCE );
    $Sphinx->SetArrayResult( true );


    if ( substr($requestData->search, 0, 1) == '+' ) {
        $requestData->search = str_replace( '+', '', $requestData->search );
        $requestData->search = str_replace( ')', '', $requestData->search );
        $requestData->search = str_replace( '(', '', $requestData->search );
        $requestData->search = str_replace( '-', '', $requestData->search );
        $requestData->search = str_replace( ' ', '', $requestData->search );
    }

    /**
     * Поиск совпадения
     */
    $requestData->limit = $requestData->limit ?? 50;

    $db_name = $API::$configs[ "db" ][ "name" ];

    $Sphinx->SetLimits( 0, $requestData->limit );
    $searchIdList = $Sphinx->Query(
        $requestData->search,
        str_replace( "-", "_", $db_name ) . "_" . $objectScheme[ "table" ]
    );

}

$searchIdList[ "matches" ][] = [
    "id" => intval( $requestData->search ),
    "weight" => "1",
    "attrs" => []
];


unset( $requestData->search );


if ( $searchIdList[ "matches" ] ) {

    $findRowsId = [];

    foreach ( $searchIdList[ "matches" ] as $searchId ) $findRowsId[] = $searchId[ "id" ];
    $searchRequest = (array) $requestData;

    unset( $searchRequest[ "limit" ] );
    unset( $searchRequest[ "context" ] );
    unset( $searchRequest[ "select" ] );

    $searchRequest[ "id" ] = $findRowsId;
    $rows = $API->DB->from( $objectScheme[ "table" ] )
        ->where( $searchRequest )
        ->limit( $requestData->limit )
        ->orderBy( $sort ) ?? [];


} // if. $searchIdList[ "matches" ]


/**
 * Обработка ответа
 */

$response[ "detail" ][ "rows_count" ] = count( $searchIdList[ "matches" ] );
$response[ "detail" ][ "pages_count" ] = ceil(
    $response[ "detail" ][ "rows_count" ] / $requestData->limit
);


$response[ "data" ] = $API->getResponseBuilder( $rows, $objectScheme, $requestData->context );

