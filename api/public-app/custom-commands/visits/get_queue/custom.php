<?php
/**
 * @file
 * Электронная очередь
 */


$resultVisits = $API->sendRequest( "equipmentVisits", "get_queue", (array) $requestData );
ini_set( "disable_functions", "" );


$start = date( "Y-m-d" ) . " 00:00:00";
$end = date( "Y-m-d" ) . " 23:59:59";

$visits = $API->DB->from( "visits" )
    ->where( [
        "store_id" => $requestData->store_id,
        "status" => "process",
        "start_at >= ?" => $start,
        "start_at <= ?" => $end,
        "is_active" => "Y"
    ] )
    ->orderBy( "start_at asc" )
    ->limit( 5 );

/**
 * Формирование списка посещений
 */

foreach ( $visits as $visit ) {

    $cabinetDetail = $API->DB->from( "cabinets" )
        ->where( "id", $visit[ "cabinet_id" ] )
        ->limit( 1 )
        ->fetch();


    /**
     * Повторное оповещение
     */
    $isAlert = false;
    if ( $visit[ "is_alert" ] == "Y" ) $isAlert = true;


    /**
     * Формирование пути к файлу озвучки
     */

    if ( !is_dir( $API::$configs[ "paths" ][ "company_uploads" ] ) )
        mkdir( $API::$configs[ "paths" ][ "company_uploads" ] );

    $talonFilePath = $API::$configs[ "paths" ][ "company_uploads" ] . "/talons";
    if ( !is_dir( $talonFilePath ) ) mkdir( $talonFilePath );


    /**
     * Формирование названия файла озвучки
     */
    $talonFileName = str_replace( " ", "", $visit[ "talon" ] );
    $talonFileName = $talonFileName . "-" . $cabinetDetail[ "id" ];


    /**
     * Озвучка
     */

    $voice = null;

    if ( !$isAlert ) {

        /**
         * Получение настроек
         */
        $settings = $API->DB->from( "settings" )
            ->limit( 1 )
            ->fetch();


        /**
         * Формирование текста для озвучки
         */

        $synthText = substr( $visit[ "talon" ], 0, 2 ) . "," . substr( $visit[ "talon" ], 2 );
        $synthText = "Пациент $synthText, пройдите в кабинет " . $cabinetDetail[ "title" ];


        if ( !file_exists( "$talonFilePath/$talonFileName.wav" ) ) {

            /**
             * Формирование аудио файла
             */

            shell_exec( "curl -X POST \
               -H \"Authorization: Bearer `yandex_cloud iam create-token`\" \
               --data-urlencode \"text=$synthText\" \
               -d \"lang=ru-RU&voice=filipp&folderId=" . $settings[ "folder_id" ] . "&sampleRateHertz=48000&speed=0.8&format=lpcm\" \
              \"https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize\" > $talonFilePath/$talonFileName.raw" );

            chmod( "$talonFilePath/$talonFileName.raw", 777 );
            
            shell_exec("sox -r 48000 -b 16 -e signed-integer -c 1 $talonFilePath/$talonFileName.raw $talonFilePath/$talonFileName.wav" );
            unlink( "$talonFilePath/$talonFileName.raw" );

            chmod( "$talonFilePath/$talonFileName.wav", 777 );

        } // if. !file_exists( $talonFilePath )


        $voice = substr( $talonFilePath, strpos( $talonFilePath, "/uploads" ) );
        $voice .= "/$talonFileName.wav";

    } // if. !$isAlert


    $voice = substr( $talonFilePath, strpos( $talonFilePath, "/uploads" ) );
    $voice .= "/$talonFileName.wav";

    if ( !file_exists( "$talonFilePath/$talonFileName.wav" ) )
        $voice = null;


    /**
     * Получение врача
     */

    $doctorDetail = $API->DB->from( "users" )
        ->where( "id", $visit[ "user_id" ] )
        ->limit( 1 )
        ->fetch();

    

    /**
     * Получение специальности врача
     */

    $doctorProfession = $API->DB->from( "users_professions" )
        ->where( "user_id", $doctorDetail[ "id" ] )
        ->limit( 1 )
        ->fetch();

    $doctorProfession = $API->DB->from( "professions" )
        ->where( "id", $doctorProfession[ "profession_id" ] )
        ->limit( 1 )
        ->fetch();


    $resultVisits[] = [
        "id" => $visit[ "id" ],
        "is_alert" => $isAlert,
        "object" => "visits", // Обязательный
        "talon" => $visit[ "talon" ],
        "cabinet" => $cabinetDetail[ "title" ],
        "detail" => [
            $doctorProfession[ "title" ],
            "{$doctorDetail[ "last_name" ]} {$doctorDetail[ "first_name" ]} {$doctorDetail[ "patronymic" ]}"
        ],
        "voice" => $voice
    ];

} // foreach. $visits as $visit


$API->returnResponse( $resultVisits );
