<?php

if ( $requestData->id ) {

    /**
     * Очистка старой структуры документа
     */

    $documentBlocks = $API->DB->from( "documentBlocks" )
        ->where( "document_id", $requestData->id );

    foreach ( $documentBlocks as $documentBlock )
        $API->DB->deleteFrom( "documents_" . $documentBlock[ "block_type" ] )
            ->where( "id", $documentBlock[ "block_id" ] )
            ->execute();

    $API->DB->deleteFrom( "documentBlocks" )
        ->where( "document_id", $requestData->id )
        ->execute();


    /**
     * Добавление структуры документа
     */

    foreach ( $requestData->structure as $documentBlock ) {

        /**
         * Добавление блока документа
         */

        $documentBlockId = null;

        switch ( $documentBlock->block_type ) {

            case "text":
            case "header":
            case "footer":

                $documentBlockId = $API->DB->insertInto( "documents_$documentBlock->block_type" )
                    ->values( [
                        "document_body" => $documentBlock->settings->document_body
                    ] )
                    ->execute();

                break;

            default:
                break;

        } // switch. $documentBlock->block_type

        if ( !$documentBlockId ) continue;


        /**
         * Добавление блока в структуру документа
         */
        $API->DB->insertInto( "documentBlocks" )
            ->values( [
                "document_id" => $requestData->id,
                "block_position" => $documentBlock->position,
                "block_type" => $documentBlock->block_type,
                "block_id" => $documentBlockId
            ] )
            ->execute();

    } // foreach. $requestData->structure

} // if. $requestData->id