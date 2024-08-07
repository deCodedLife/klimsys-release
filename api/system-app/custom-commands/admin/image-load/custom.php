<?php

/**
 * @file Загрузка изображений
 */

$API->returnResponse(
    $API->uploadImagesFromForm( $requestData->image_key, $requestData->image, $requestData->scheme_name )
);