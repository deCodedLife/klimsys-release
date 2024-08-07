<?php

/**
 * Добавление отчета в очередь на формирование
 */


$reportId = $API->DB->insertInto( "hardReports" )
    ->values( [
        "report_article" => $requestData->reportArticle,
        "status" => "waiting"
    ] )
    ->execute();


foreach ( $requestData->filters as $filterArticle => $filterValue ) {

    $API->DB->insertInto( "hardReports_filters" )
        ->values( [
            "report_id" => $reportId,
            "filter_article" => $filterArticle,
            "filter_value" => $filterValue
        ] )
        ->execute();

} // foreach. $requestData->filters