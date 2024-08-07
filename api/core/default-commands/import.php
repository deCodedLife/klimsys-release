<?php

$ignoreTypes = [
    "dadata_address",
    "dadata_city",
    "dadata_region",
    "dadata_passport",
    "dadata_country",
    "dadata_local_area",
    "dadata_street",
    "google_address",
    "password",
    "editor",
    "file",
    "image",
    "smart_list",
    "link_list"
];


if (
    property_exists( $requestData, "context" ) &&
    property_exists( $requestData->context, "template" )
) {

    $filePath = "/uploads/{$API::$configs[ "company" ]}/exel/{$API->request->object}.xlsx";
//    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/{$API->request->object}.xlsx";
    $fileUrl = "https://{$_SERVER[ "HTTP_HOST" ]}/$filePath";
    file_put_contents( $filePath, "." );

    $spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadSheet->getProperties()
        ->setTitle( "Astragreen" )
        ->setCompany( "Flowerbloom" )
        ->setCreator( "codedlife" );

    $workSheet = $spreadSheet->createSheet( 0 );
    $devSheet = $spreadSheet->createSheet( 1 );
    $workSheet->setTitle( $objectScheme[ "title" ] );
    $devSheet->setTitle( " " );

    $columnsCount = 0;
    $devSheet->setCellValue( "B1", "Не меняйте ничего на данной странице. Изменения могут привести к некорректной работе скрипта импорта данных" );

    foreach ( $objectScheme[ "properties" ] as $key => $property ) {

        if ( !$property[ "is_autofill" ] ) continue;

        $is_required = false;
        $is_ignored = false;

        if ( in_array( $property[ "field_type" ], $ignoreTypes ) ) $is_ignored = true;
        if ( in_array( "add", $property[ "require_in_commands" ] ?? [] ) ) $is_required = true;
        if ( !in_array( "import", $property[ "use_in_commands" ] ?? [] ) ) $is_ignored = true;
        if ( $property[ "field_type" ] == "list" && !$property[ "import_list" ] ) $is_ignored = true;

        if ( $is_required && $is_ignored ) {

            $API->returnResponse( "Невозможно сформировать импорт для объекта {$objectScheme[ "title" ]} 
            Нет обработчика для обязательного поля {$property[ "title" ]}", 500 );

        }

        if ( $property[ "data_type" ] == "array" ) continue;
        if ( $is_ignored ) continue;

        $cellName = $workSheet->getColumnDimensionByColumn( $columnsCount + 1 )->getColumnIndex() . '1';

        $title = $is_required ? "*" . $property[ "title" ] : $property[ "title" ];

        $workSheet->setCellValue( $cellName, $title );
        $devSheet->setCellValue( "A" . $columnsCount + 1, $property[ "article" ] );

        $columnsCount++;

    }

    $black = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK;

    $workSheet->getStyle( 'A1:' . $workSheet->getColumnDimensionByColumn( $columnsCount )->getColumnIndex() . '1' )
        ->applyFromArray([
            "font" => [
                "color" => [
                    "rgb" => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE
                ]
            ],
            "fill" => [
                "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                "color" => [
                    "rgb" => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK
                ]
            ]
        ]);

    if ( !is_dir( $_SERVER['DOCUMENT_ROOT'] . "/uploads/{$API::$configs[ "company" ]}/exel" ) )
        mkdir( $_SERVER['DOCUMENT_ROOT'] . "/uploads/{$API::$configs[ "company" ]}/exel" );

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadSheet, "Xlsx" );
    $writer->save( $_SERVER['DOCUMENT_ROOT'] . $filePath );

    $API->returnResponse( $fileUrl );

}

$local_file = $API->uploadFilesFromForm( 0, [ (array) $API->request->data->import_file[0] ], "exel" );
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$reader->setReadDataOnly(true);
$spreadSheet = $reader->load( $API::$configs[ "paths" ][ "root" ] . "/" . $local_file );


foreach ( $objectScheme[ "properties" ] as $property ) $properties[ $property[ "article" ] ] = $property;
$properties = $properties ?? [];

$workSheet = $spreadSheet->getSheet( 0 );
$devSheet = $spreadSheet->getSheet( 1 );

$messageList = [];
$importFields = [];


for ( $i = 0; $i < $devSheet->getHighestRow(); $i++ ) {

    $cell = $devSheet->getCell( "A" . $i + 1 )->getValue();
    if ( empty( $cell ) ) continue;

    $importFields[] = $cell;

}


for ( $i = 1; $i < $workSheet->getHighestRow(); $i++ ) {

    $is_valid = true;
    $object = [];

    foreach ( $importFields as $key => $field ) {

        $property = $properties[ $field ];
        if ( empty( $property ) ) continue;
        $newObject = [];

        $cellName = $workSheet->getColumnDimensionByColumn( $key + 1 )->getColumnIndex() . $i + 1;
        $cellValue = $workSheet->getCell( $cellName )->getValue();

        if ( empty( $cellValue ) ) {

            $object[ $field ] = $cellValue;
            continue;

        }

        try {

            switch ( $property[ "data_type" ] ) {

                case "year":
                case "date":
                case "month":
                case "time":
                case "datetime":
                    $template = "Y-m-d";
                    if ( $property[ "data_type" ] == "year" ) $template = "Y";
                    if ( $property[ "data_type" ] == "month" ) $template = "m";
                    if ( $property[ "data_type" ] == "time" ) $template = "h:i:s";
                    if ( $property[ "data_type" ] == "datetime" ) $template .= " h:i:s";

                    $cellValue = PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp( $cellValue );
                    $cellValue = date( $template, $cellValue );
                    break;

                case "price":
                case "integer":
                case "float":

                    if ( !in_array( gettype( $cellValue ), [ "integer", "double" ] ) )
                        throw new Exception( "Данные в ячейке $cellName заполнены некорректно. Ожидается число или число с плавающей точкой" );

                    if ( $property[ "min_value" ] && $cellValue < $property[ "min_value" ] )
                        throw new Exception( "Данные в ячейке $cellName заполнены некорректно. Минимальное значение: " . $property[ "min_value" ] );

                    if ( $property[ "max_value" ] && $cellValue > $property[ "max_value" ] )
                        throw new Exception( "Данные в ячейке $cellName заполнены некорректно. Максимальное значение: " . $property[ "max_value" ] );

                    break;


                case "phone":
                    $phoneRegexp = $API::$configs[ "phone_regexp" ] ?? "/
                            (\d{1})?\D* # optional country code
                            (\d{3})?\D* # optional area code
                            (\d{3})\D*  # first three
                            (\d{2})     # last 2
                            (\d{2})     # last 2
                            (?:\D+|$)   # extension delimiter or EOL
                            (\d*)       # optional extension
                        /x";

                    if( preg_match( $phoneRegexp, $cellValue, $matches ) )
                        $cellValue = join( "", array_slice( $matches, 1) );
                    else
                        throw new Exception( "Данные в ячейке $cellName заполнены некорректно. Номер телефона 11 цифр с символом (+): +78002412331");
                    break;

                case "email":
                    if ( !preg_match( $API::$configs[ "email_regexp" ] ?? "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/x", $cellValue, $matches) )
                        throw new Exception( "Данные в ячейке $cellName заполнены некорректно. Почта. Обязательно наличие символа (@), а так же почтового домена: test@mail.ru");
                    break;

                case "checkbox":
                    if ( $cellValue != "Y" && $cellValue != "N" )
                        throw new Exception( "Данные в ячейке $cellName заполнены некорректно. Y - да, N - Нет");
                    break;

                default:
                    break;

            }

        } catch ( Exception $e ) {

            $messageList[] = $e->getMessage();
            $is_valid = false;
            continue;

        }

        $object[ $field ] = $cellValue;

    }

    if ( !$is_valid ) continue;

    $API->DB->insertInto( $API->request->object )
        ->values( $object )
        ->execute();

}


if ( empty( $messageList ) ) $API->returnResponse();
else {

    $message = $messageList[ 0 ] ?? "";
    $API->returnResponse( $message, 400 );

}