<?php



/**
 * Подстановка геолокации
 */
if ( $requestData->address )  {

    /**
     * запроса к API
     */
    $apiRequest = curl_init('https://geocode-maps.yandex.ru/1.x/?apikey=99d27a37-a2bc-4a4a-ac4f-99ad602977f3&format=json&geocode=' . urlencode($requestData->address));
    curl_setopt($apiRequest, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($apiRequest, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($apiRequest, CURLOPT_HEADER, false);
    $response = curl_exec($apiRequest);
    curl_close($apiRequest);

    /**
     * Извлечение координат
     */
    $response = json_decode($response, true);
    $geolocation = $response['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];

    $requestData->geolocation = $geolocation;

}

//if ( !$requestData->phone && !$requestData->second_phone ) {
//
//    $API->returnResponse( "Необходимо указать основной или дополнительный телефон", 400 );
//
//}