<?php

$import_object = [];
$htmlTable = "
<p><b>* - обязательные поля</b></p>
<br>
<table>
    <tr>
        <th>Свойство</th>
        <th>Описание</th>    
    </tr>
";


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


foreach ( ( $objectScheme[ "properties" ] ?? [] ) as $property ) {

    if ( !$property[ "is_autofill" ] ) continue;

    $is_required = false;
    $is_ignored = false;

    if ( in_array( $property[ "field_type" ], $ignoreTypes ) ) $is_ignored = true;
    if ( in_array( "add", ( $property[ "require_in_commands" ] ?? [] ) ) ) $is_required = true;
    if ( !in_array( "import", $property[ "use_in_commands" ] ?? [] ) ) $is_ignored = true;
    if ( $property[ "field_type" ] == "list" && !$property[ "import_list" ] ) $is_ignored = true;

    if ( $is_required && $is_ignored ) {

        $htmlTable = "<p>
            Невозможно сформировать импорт для объекта {$objectScheme[ "title" ]} <br> 
            Нет обработчика для обязательного поля {$property[ "title" ]}</p>";

        $import_object[ "import_headers" ] = $htmlTable;
        $API->returnResponse( [ $import_object ] );

    }

    if ( $property[ "data_type" ] == "array" ) continue;
    if ( $is_ignored ) continue;

    switch ( $property[ "field_type" ] ) {

        case "year":
            $dataType = "Год - 2024";
            break;

        case "layout":
            $dataType = "HTML код \<h1\>Hello world!\</h1\>";
            break;

        case "float":
            $dataType = "Число с плавающей точкой";
            if ( $property[ "max_value" ] ) $dataType .= ". Максимум {$property[ "max_value" ]}";
            if ( $property[ "min_value" ] ) $dataType .= ". Минимум {$property[ "min_value" ]}";
            break;

        case "time":
            $dataType = "Время - 13:28:31";
            break;

        case "month":
            $dataType = "Месяц - 05";
            break;

        case "string":
            $dataType = "Строка до 255 символов";
            break;

        case "price":
            $dataType = "Целое число или число с двумя знаками после запятой";
            break;

        case "phone":
            $dataType = "Номер телефона 11 цифр с символом (+): +78002412331";
            break;

        case "date":
            $dataType = "Дата в формате ГОД-месяц-день (2024-01-01)";
            break;

        case "email":
            $dataType = "Почта. Обязательно наличие символа (@), а так же почтового домена: test@mail.ru";
            break;

        case "checkbox":
            $dataType = "Y - да <br> N - Нет";
            break;

        case "datetime":
            $dataType = "Дата и время в формате ГОД-месяц-день часы:минуты:секунды 2024-01-01 10:20:59";
            break;

        case "integer":
            $dataType = "Число";
            break;

        case "textarea":
            $dataType = "Текст";
            break;

    }

    if ( $property[ "import_list" ] ) {

        $dataType = [];

        foreach ( $property[ "import_list" ] as $item )
            $dataType[] = "{$item[ "value" ]} - {$item[ "title" ]}";

        $dataType = join( "<br>", $dataType );

    }

    if ( $property[ "max_value" ] ) $dataType .= ". Максимум {$property[ "max_value" ]}";
    if ( $property[ "min_value" ] ) $dataType .= ". Минимум {$property[ "min_value" ]}";

    $insertTitle = $property[ "title" ];
    if ( $is_required ) $insertTitle = "<b>*$insertTitle</b>";

    $htmlTable .= "<tr><td>$insertTitle</td>";
    $htmlTable .= "<td>$dataType</td>";
    $htmlTable .= "</tr>";

}

$htmlTable .= "</table>";

$import_object[ "import_headers" ] = $htmlTable;
$response[ "data" ] = [ $import_object ];
