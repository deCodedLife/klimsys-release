<?php

//ini_set( "display_errors", true );

/**
 * Вывод шапки документа
 */
if ( $requestData->article ) {

    $documentDetail = $API->DB->from( "documents" )
        ->where( "article", $requestData->article )
        ->limit(1)
        ->fetch();

    /**
     * Исключения
     */
    if ( $requestData->article != "talon" && $documentDetail[ "use_header" ] === 'Y' ) {

        /**
         * Сформированный документ
         */
        $headerBody = "";


        /**
         * Получение шапки документа
         */

        $documentHeaderDetail = $API->DB->from( "documents" )
            ->where( "article", "header" )
            ->limit( 1 )
            ->fetch();

        $documentHeaderBlocks = $API->DB->from( "documentBlocks" )
            ->where( "document_id", $documentHeaderDetail[ "id" ] )
            ->orderBy( "block_position asc" );

        foreach ( $documentHeaderBlocks as $documentBlock ) {

            /**
             * Получение детальной информации о блоке документа
             */
            $documentBlockDetail = $API->DB->from( "documents_" . $documentBlock[ "block_type" ] )
                ->where( "id", $documentBlock[ "block_id" ] )
                ->limit( 1 )
                ->fetch();


            /**
             * Добавление блока документа в структуру
             */
            $headerBody = $documentBlockDetail[ "document_body" ];

        } // foreach. $documentHeaderBlocks


        $response[ "data" ][ 0 ][ "structure" ][ 0 ][ "settings" ][ "document_body" ] = $headerBody . $response[ "data" ][ 0 ][ "structure" ][ 0 ][ "settings" ][ "document_body" ];

    } // if. $requestData->article != "talon"

} // if. $requestData->article
