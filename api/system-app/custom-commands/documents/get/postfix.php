<?php

/**
 * Детальная информация о документе
 */

if ( $requestData->id || $requestData->article ) {

    if ( $requestData->article ) $requestData->id = $response[ "data" ][ 0 ][ "id" ];


    /**
     * Структура документа
     */
    $documentStructure = [];


    /**
     * Обход структуры документа
     */

    $documentBlocks = $API->DB->from( "documentBlocks" )
        ->where( "document_id", $requestData->id )
        ->orderBy( "block_position asc" );

    foreach ( $documentBlocks as $documentBlock ) {

        /**
         * Получение детальной информации о блоке документа
         */
        $documentBlockDetail = $API->DB->from( "documents_" . $documentBlock[ "block_type" ] )
            ->where( "id", $documentBlock[ "block_id" ] )
            ->limit( 1 )
            ->fetch();

        /**
         * Игнорирование системных св-в
         */
        unset( $documentBlockDetail[ "id" ] );
        unset( $documentBlockDetail[ "is_system" ] );


        /**
         * Добавление блока документа в структуру
         */
        $documentStructure[] = [
            "block_position" => $documentBlock[ "block_position" ],
            "block_type" => $documentBlock[ "block_type" ],
            "settings" => $documentBlockDetail
        ];

    } // foreach. $documentBlocks

    $response[ "data" ][ 0 ][ "type_id" ] = (int) $response[ "data" ][ 0 ][ "type_id" ][ "value" ];
    $response[ "data" ][ 0 ][ "structure" ] = $documentStructure;

} // if. $requestData->id || $requestData->article


/**
 * @hook
 * Вывод документов
 */
if ( file_exists( $public_customCommandDirPath . "/hooks/result.php" ) )
    require( $public_customCommandDirPath . "/hooks/result.php" );