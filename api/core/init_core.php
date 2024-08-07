<?php

/**
 * @file
 * Инициализация ядра
 */


class API {

    /**
     * Конфигурация
     */
    static public $configs = [

        /**
         * Пути
         */
        "paths" => [],

        /**
         * База данных
         */
        "db" => []

    ]; // $configs

    /**
     * Тело запроса
     */
    public $request = null;

    /**
     * База данных (PDO)
     */
    public $DB = null;

    /**
     * Подключение базы данных (MySQL)
     */
    public $DB_connection = null;

    /**
     * JWT объект
     */
    public $JWT = null;

    /**
     * JWT код
     */
    public $JWT_code = null;

    /**
     * Подключенные модули
     */
    public $modules = [];

    /**
     * Детальная информация о текущем Пользователе
     */
    static public $userDetail = null;


    /**
     * Возвращение ответа API на запрос
     *
     * @param $data    mixed    Тело ответа
     * @param $status  integer  Статус ответа
     * @param $detail  array    Дополнительная информация в запросе
     *
     * @return null
     */
    public function returnResponse ( $data = true, $status = 200, $detail = [] ) {

        /**
         * Формирование ответа на запрос
         */
        $result = [
            "status" => $status,
            "data" => $data,
            "detail" => $detail
        ];


        /**
         * Вывод ответа на запрос, и завершение работы скрипта
         */
        exit( json_encode( $result ) );

    } // function. returnResponse


    /**
     * Загрузка схемы
     *
     * @param  $schemePath  string  Путь к схеме
     *
     * @return mixed
     */
    public function loadScheme ( $schemePath ) {

        /**
         * Подключение схемы
         */
        if ( file_exists( $schemePath ) ) $scheme = file_get_contents( $schemePath );
        else $this->returnResponse( "Отсутствует схема", 500 );


        /**
         * Декодирование схемы
         */
        try {

            $scheme = json_decode( $scheme, true );

            if ( $scheme === null ) $this->returnResponse( "Ошибка обработки схемы", 500 );

        } catch ( Exception $error ) {

            $this->returnResponse( "Несоответствие схеме", 500 );

        } // try. json_decode. $scheme


        return $scheme;

    } // function. loadScheme


    function mergeProperties( $object1, $object2 ): array {

        if ( !$object1 || !$object2 ) return $object1;
        if ( !array_key_exists( "properties", $object1 ) ) $object1[ "properties" ] = [];
        if ( !array_key_exists( "properties", $object2 ) ) $object2[ "properties" ] = [];
//        if ( !$object1[ "properties" ] || !$object2[ "properties" ] ) return $object1;

        $object1[ "properties" ] = array_merge(
            $object1[ "properties" ],
            $object2[ "properties" ]
        );

        return $object1;

    }



    /**
     * Совмещение значений 2-х объектов
     *
     * @param $object1
     * @param $object2
     * @return mixed|void
     */
    function mergeObjects( $object1, $object2 )
    {
        if ( !$object1 || !$object2 ) return;

        foreach ( $object2 as $customArticle => $customValue ) {

            foreach ( $object1 as $propertyArticle => $propertyValue ) {

                if ( $propertyArticle !== $customArticle ) continue;
                $object1->$propertyArticle = $customValue;

            }

        }

        return $object1;
    }

    /**
     * Рекурсивный импорт файлов
     *
     * @param $path
     * @return void
     */
    function require_files( $path ) {

        if ( !is_dir( $path ) ) return;
        $files = array_diff( scandir( $path ), [ "..", "." ] );

        foreach ( $files as $file ) {

            $filePath = join( '/', [ $path, $file ] );

            if ( is_dir( $filePath ) ) {
                $this->require_files( $filePath );
                continue;
            }

            try {
                require_once $filePath;
            } catch ( Throwable $exception ) {
                $this->returnResponse( "Не удалось загрузить", $file );
            }

        }

    } // function requeire_files( $path )


    /**
     * Рекурсивная функция для сборки select параметров
     *
     * @param $property
     * @param $object
     * @param $properties
     * @param $exclusion
     * @return string
     */
    public function selectPropertiesHandler( $property, $object, $properties, $exclusion = true ): string {
        if ( is_array( $property ) ) {

            $handled_properties = [];

            foreach ( $property as $item ) {

                $value = $this->selectPropertiesHandler( $item, $object, $properties );
                if ( $property == "id" ) $value = "№$value";

                if ( empty( $value ) ) continue;
                if ( $exclusion ) return $value;
                $handled_properties[] = $value;

            }

            return join( ", ", ( $handled_properties ?? [] ) );

        }
        if (
            !isset( $object[ $property ] ) ||
            $object[ $property ] == "null" ||
            $object[ $property ] == ""
        ) return "";

        $rowValue = $object[ $property ];
        if ( $property == "id" ) $rowValue = "№$rowValue";

        return $this->typesHandler( (array) $properties[ $property ], $rowValue );

    }


    /**
     *
     *
     * @param $properties
     * @param $data
     * @return string
     */
    function typesHandler( $properties, $data ): string
    {
        $postfix = '';

        /**
         * TODO: Implement all types
         */
        switch ( $properties[ "field_type" ] ) {
            case "price":
                $currency = $this::$configs[ "system_components" ][ "currency" ] ?? "₽";
                return "({$data}$currency)$postfix";

            case "list":
                return $data[ "title" ];


            case "phone":
                $phoneRegexp = $this::$configs[ "phone_regexp" ] ?? "/
                            (\d{1})?\D* # optional country code
                            (\d{3})?\D* # optional area code
                            (\d{3})\D*  # first three
                            (\d{2})     # last 2
                            (\d{2})     # last 2
                            (?:\D+|$)   # extension delimiter or EOL
                            (\d*)       # optional extension
                        /x";

                if( preg_match( $phoneRegexp, $data, $matches ) )
                    return "+{$matches[1]} ({$matches[2]})-{$matches[3]}-{$matches[4]}-{$matches[5]}$postfix";
                else return "+$data";

            default:
                return $data;

        } // switch ( $objectProperties[ $property ][ "field_type" ] )
    }

    public function getObjectScheme( string $object ) : array
    {
        $objectScheme = $objectScheme ?? [];

        $commandScheme = $this->loadCommandScheme( "$object/get" );

        if ( is_array( $commandScheme[ "object_scheme" ] ) ) {

            foreach ( $commandScheme[ "object_scheme" ] as $scheme ) {

                $objectScheme = $this->mergeProperties(
                    $this->loadObjectScheme( $scheme ),
                    $objectScheme
                );

            }

        } else {

            $objectScheme = $this->mergeProperties(
                $this->loadObjectScheme( $commandScheme[ "object_scheme" ] ),
                $objectScheme
            );

        }

        return $objectScheme;
    }


    public function selectHandler( $rows, $objectScheme ) {

        global $response, $public_customCommandDirPath, $API;
        $objectProperties = [];

        foreach ( $objectScheme[ "properties" ] as $schemeProperty )
            $objectProperties[ $schemeProperty[ "article" ] ] = $schemeProperty;

        $response[ "data" ] = $rows;

        if ( file_exists( $public_customCommandDirPath . "/postfix.php" ) )
            require $public_customCommandDirPath . "/postfix.php";

        foreach ( $response[ "data" ] as $key => $row ) {

            $response[ "data" ][ $key ] = [
                "title" => $this->selectPropertiesHandler( $this->request->data->select ?? [ [ "title" ] ], $row, $objectProperties, false ),
                "value" => $row[ "value" ] ?? $row[ "id" ]
            ];

            if ( $this->request->data->select_menu )
                $response[ "data" ][ $key ][ "menu_title" ] = $this->selectPropertiesHandler( $this->request->data->select_menu, $row, $objectProperties, false );

        }

        return $response[ "data" ];

    }


    /**
     * Получение публичных и системных схем
     *
     * @param  $schemePath  string  Путь к схеме
     *
     * @return array
     */
    public function getPublicAndSystemSchemes ( $schemePath ) {

        /**
         * Склеенная схема
         */
        $resultSchemes = [
            "system" => [],
            "public" => []
        ];

        /**
         * Публичная схема
         */
        $publicScheme = [];

        /**
         * Системная схема
         */
        $systemScheme = [];

        /**
         * Путь к схеме
         */
        $schemePath .= ".json";


        /**
         * Подключение схем
         */

        $path_elements = explode( "/", $schemePath );

        $project_scheme = $this::$configs[ "company" ] . "_" . $path_elements[ count( $path_elements ) - 1 ];
        $path_elements[ count( $path_elements ) - 1 ] = $project_scheme;
        $path_elements = join( "/", $path_elements );

        if (
            file_exists( $this::$configs[ "paths" ][ "system_app" ] . $path_elements ) ||
            file_exists( $this::$configs[ "paths" ][ "public_app" ] . $path_elements )
        ) $schemePath = $path_elements;


        if ( file_exists( $this::$configs[ "paths" ][ "system_app" ] . $schemePath ) )
            $resultSchemes[ "system" ] = $this->loadScheme( $this::$configs[ "paths" ][ "system_app" ] . $schemePath );

        if ( file_exists( $this::$configs[ "paths" ][ "public_app" ] . $schemePath ) )
            $resultSchemes[ "public" ] = $this->loadScheme( $this::$configs[ "paths" ][ "public_app" ] . $schemePath );

        return $resultSchemes;

    } // function. getPublicAndSystemSchemes


    /**
     * Загрузка схемы команды
     *
     * @param  $commandSchemePath  string  Путь к схеме команды
     *
     * @return mixed
     */
    public function loadCommandScheme ( $commandSchemePath ) {

        /**
         * Сформированная схема команды
         */
        $resultScheme = null;



        /**
         * Подключение схем команды
         */
        $commandSchemes = $this->getPublicAndSystemSchemes( "/command-schemes/$commandSchemePath" );

        /**
         * Склейка схем команды
         */
        foreach ( $commandSchemes as $commandScheme )
            if ( $commandScheme ) $resultScheme = $commandScheme;


        if ( !$resultScheme )
            $this->returnResponse( "Отсутствует схема команды $commandSchemePath", 500 );

        return $resultScheme;

    } // function. loadCommandScheme

    /**
     * Загрузка схемы объекта
     *
     * @return mixed
     */
    public function loadObjectScheme ( $objectSchemeArticle, $isReturnError = true ) {

        global $userScheme;


        /**
         * Сформированная схема объекта
         */
        $resultScheme = [];

        /**
         * Св-ва схем объекта
         */
        $objectSchemeProperties = [];


        /**
         * Подключение схем объекта
         */
        $objectSchemes = $this->getPublicAndSystemSchemes(
            "/object-schemes/$objectSchemeArticle"
        );


        /**
         * Склейка схем объекта
         */

        foreach ( $objectSchemes as $objectScheme ) {

            if ( !$objectScheme ) continue;


            $resultScheme[ "title" ] = $objectScheme[ "title" ];
            $resultScheme[ "table" ] = $objectScheme[ "table" ];
            $resultScheme[ "is_trash" ] = $objectScheme[ "is_trash" ];

            if ( $objectScheme[ "action_buttons" ] ?? false )
                $resultScheme[ "action_buttons" ] = $objectScheme[ "action_buttons" ];

        } // foreach. $objectSchemes

        foreach ( $objectSchemes[ "public" ][ "properties" ] as $property )
            $objectSchemeProperties[ $property[ "article" ] ] = $property;

        foreach ( ( $objectSchemes[ "system" ][ "properties" ] ?? [] ) as $property ) {

            if ( $objectSchemeProperties[ $property[ "article" ] ] ) {

                $property[ "is_default_in_list" ] = $objectSchemeProperties[ $property[ "article" ] ][ "is_default_in_list" ];
                $property[ "is_autofill" ] = $objectSchemeProperties[ $property[ "article" ] ][ "is_autofill" ];
                continue;

            } // if. $objectSchemeProperties[ $property[ "article" ] ]

            $objectSchemeProperties[ $property[ "article" ] ] = $property;

        } // foreach. $objectSchemes[ "system" ][ "properties" ]

        if ( $objectSchemeProperties )
            $resultScheme[ "properties" ] = array_values( $objectSchemeProperties );


        /**
         * Обработка пользовательских объектов
         */

        if ( $userScheme ) {

            foreach ( $userScheme as $objectArticle => $object ) {

                if ( $objectArticle == $objectSchemeArticle ) {

                    if ( $object->title ) $resultScheme = [
                        "title" => $object->title,
                        "table" => "us__$objectArticle",
                        "is_trash" => false,
                        "properties" => []
                    ];

                } // if. $objectArticle

            } // foreach. $userScheme

        } // if. $userScheme


        if ( !$resultScheme && $isReturnError )
            $this->returnResponse( "Отсутствует схема объекта $objectSchemeArticle", 500 );

        return $resultScheme;

    } // function. loadObjectScheme


    /**
     * Пре-обработка запроса
     *
     * @param $objectScheme  object  Схема объекта, по которой будет проверяться запрос
     * @param $requestData   object  Тело запроса
     * @param $command       string  Команда запроса
     *
     * @return object
     */
    public function requestDataPreprocessor ( $objectScheme, $requestData, $command ) {

        global $userScheme;


        /**
         * Обработанный запрос
         */
        $processedRequest = [];


        /**
         * Обработка пользовательских св-в
         */

        if ( $userScheme ) {

            foreach ( $userScheme as $objectArticle => $object )
                if (
                    ( $objectArticle == $objectScheme[ "table" ] ) ||
                    ( "us__$objectArticle" == $objectScheme[ "table" ] )
                ) foreach ( $object->properties as $propertyArticle => $property )
                    if ( $requestData->{$propertyArticle} ) $processedRequest[ $propertyArticle ] = $requestData->{$propertyArticle};

        } // if. $userScheme


        /**
         * Обработка системных параметров
         */

        if ( $requestData->id ) {

            switch ( gettype( $requestData->id ) ) {

                case "integer":
                case "array":

                    $processedRequest[ "id" ] = $requestData->id;
                    break;

                case "string":

                    if ( ctype_digit( $requestData->id ) ) $processedRequest[ "id" ] = (int) $requestData->id;
                    break;

            } // switch. gettype( $requestData->id )

        } // if. $requestData->id

        if ( $requestData->page && ( gettype( $requestData->page ) === "integer" ) )
            $processedRequest[ "page" ] = $requestData->page;

        if ( $requestData->limit && ( gettype( $requestData->limit ) === "integer" ) )
            $processedRequest[ "limit" ] = $requestData->limit;

        if ( $requestData->select && ( gettype( $requestData->select ) === "array" ) )
            $processedRequest[ "select" ] = $requestData->select;

        if (
            $requestData->context &&
            (
                ( gettype( $requestData->context ) === "string" ) ||
                ( gettype( $requestData->context ) === "object" )
            )
        )
            $processedRequest[ "context" ] = $requestData->context;

        if ( $requestData->search && ( gettype( $requestData->search ) === "string" ) )
            $processedRequest[ "search" ] = $requestData->search;

        if ( $requestData->sort_by && ( gettype( $requestData->sort_by ) === "string" ) )
            $processedRequest[ "sort_by" ] = $requestData->sort_by;

        if ( $requestData->sort_order && ( gettype( $requestData->sort_order ) === "string" ) )
            $processedRequest[ "sort_order" ] = $requestData->sort_order;


        /**
         * Проверка на multipart/form-data
         */

        $isMulipart = false;

        foreach ( $objectScheme[ "properties" ] as $objectProperty ) {

            switch ( $objectProperty[ "data_type" ] ) {

                case "image":
                case "file":

                    $isMulipart = true;
                    break;

            } // switch.

        } // foreach. $objectScheme[ "properties" ]


        /**
         * Обработка множественных св-в (при multiform)
         */

        if ( $isMulipart ) {

            foreach ( $requestData as $propertyArticle => $propertyValue ) {

                if ( $propertyArticle[ 0 ] === "_" ) {

                    $propertyArticle = substr( $propertyArticle, 1 );
                    $propertyArticle = substr( $propertyArticle, 0, strpos( $propertyArticle, "__" ) );

                    $requestData->{$propertyArticle}[] = $propertyValue;

                } // if. $propertyArticle[ 0 ] === "_"

            } // foreach. $requestData

        } // if. $isMulipart


        /**
         * Обход св-в в схеме объекта
         */
        //1
        foreach ( $objectScheme[ "properties" ] as $objectProperty ) {

            if ( !$objectProperty[ "require_in_commands" ] ) $objectProperty[ "require_in_commands" ] = [];


            /**
             * Св-во в запросе
             */
            $requestProperty = $requestData->{ $objectProperty[ "article" ] };



            if (
                ( $requestProperty === null ) ||
                ( $requestProperty === "null" )
            ) {

                /**
                 * Проверка обязательных св-в
                 */

                if ( in_array( $command, $objectProperty[ "require_in_commands" ] ?? [] ) )
                    $this->returnResponse(
                        "Отсутствует обязательное св-во '{$objectProperty[ "title" ]}'",
                        400
                    );

                $object = (object) ( $requestData ?? "" );
                $article = $objectProperty[ "article" ] ?? "";

                if ( property_exists( $object, $article ) )
                    $processedRequest[ $objectProperty[ "article" ] ] = $requestData->{ $objectProperty[ "article" ] };

            } else {

                /**
                 * Игнорирование св-ва
                 */
                $isContinue = false;


                /**
                 * Проверка на использование поля в команде
                 */
                if ( !in_array( $command, $objectProperty[ "use_in_commands" ] ?? [] ) ) continue;


                /**
                 * Обработка нестандартных типов
                 */
                switch ( $objectProperty[ "data_type" ] ) {

                    case "boolean":

                        if ( $requestData->{ $objectProperty[ "article" ] } === null ) {

                            $isContinue = true;
                            break;

                        } // if. $requestData->{ $objectProperty[ "article" ] } === null


                        /**
                         * Обработка исключений
                         */
                        if (
                            ( $requestData->{ $objectProperty[ "article" ] } === "N" ) ||
                            ( $requestData->{ $objectProperty[ "article" ] } === "false" )
                        ) $requestData->{ $objectProperty[ "article" ] } = false;


                        /**
                         * Принудительный перевод в boolean
                         */
                        $requestData->{ $objectProperty[ "article" ] } = (boolean) $requestData->{ $objectProperty[ "article" ] };

                        /**
                         * Перевод boolean в Y/N
                         */
                        if ( $requestData->{ $objectProperty[ "article" ] } )
                            $requestData->{ $objectProperty[ "article" ] } = "Y";
                        else
                            $requestData->{ $objectProperty[ "article" ] } = "N";


                        break;

                    case "float":

                        /**
                         * Округление числа
                         */
                        $requestData->{ $objectProperty[ "article" ] } = round(
                            $requestData->{ $objectProperty[ "article" ] }, 2, PHP_ROUND_HALF_DOWN
                        );

                        break;

                    case "email":

                        /**
                         * Проверка правильности заполнения email
                         */

                        if ( !filter_var( $requestData->{ $objectProperty[ "article" ] }, FILTER_VALIDATE_EMAIL ) )
                            $this->returnResponse(
                                "Неправильно заполнен email",
                                400
                            );

                        /**
                         * Обработка email
                         */
                        $requestData->{ $objectProperty[ "article" ] } = mb_strtolower(
                            $requestData->{ $objectProperty[ "article" ] }
                        );

                        break;

                    case "password":

                        /**
                         * Проверка на минимальную длину
                         */
                        if ( mb_strlen( $requestData->{ $objectProperty[ "article" ] } ) < 6 )
                            $this->returnResponse(
                                "Пароль должен быть не короче 6 символов",
                                400
                            );

                        /**
                         * Кодирование пароля
                         */
                        $requestData->{ $objectProperty[ "article" ] } = md5(
                            $requestData->{ $objectProperty[ "article" ] }
                        );

                        break;

                    case "phone":

                        /**
                         * Валидация
                         */
                        $phoneRegexp = $this::$configs[ "phone_regexp" ] ?? "/
                            (\d{1})?\D* # optional country code
                            (\d{3})?\D* # optional area code
                            (\d{3})\D*  # first three
                            (\d{2})     # last 2
                            (\d{2})     # last 2
                            (?:\D+|$)   # extension delimiter or EOL
                            (\d*)       # optional extension
                        /x";

                        if( !preg_match( $phoneRegexp, $requestData->{ $objectProperty[ "article" ] }, $matches ) )
                            $this->returnResponse( "Номер телефона введён не верно", 502 );

                        /**
                         * Очистка лишних символов
                         */
                        $requestData->{ $objectProperty[ "article" ] } = preg_replace(
                            '/[^0-9]/', "", $requestData->{ $objectProperty[ "article" ] }
                        );

                        if ( mb_strlen( $requestData->{ $objectProperty[ "article" ] } ) != 11 )
                            $this->returnResponse( "Номер телефона введён не верно", 502 );

                        break;

                    case "file":
                    case "image":

                        /**
                         * Добавление св-ва в запрос
                         */
                        $processedRequest[ $objectProperty[ "article" ] ] = $requestData->{ $objectProperty[ "article" ] };

                        $isContinue = true;
                        break;


                } // switch. $objectProperty[ "data_type" ]


                if ( $isContinue ) continue;
                // 2

                /**
                 * Проверка типов св-в
                 */

                if ( gettype( $requestProperty ) !== $objectProperty[ "data_type" ] ) {

                    /**
                     * Ошибка типа св-ва
                     */
                    $is_error = true;

                    switch ( gettype( $requestProperty ) ) {

                        case "integer":

                            if (
                                $objectProperty[ "data_type" ] == "array" &&
                                (
                                    ( $command === "get" ) ||
                                    ( $command === "schedule" )
                                )
                            ) $is_error = false;

                        case "float":
                        case "double":

                            if (
                                ( $objectProperty[ "data_type" ] === "float" ) ||
                                ( $objectProperty[ "data_type" ] === "double" )
                            ) $is_error = false;

                            break;

                        case "string":

                            if (
                                ( $objectProperty[ "data_type" ] === "date" ) ||
                                ( $objectProperty[ "data_type" ] === "time" ) ||
                                ( $objectProperty[ "data_type" ] === "datetime" ) ||
                                ( $objectProperty[ "data_type" ] === "password" ) ||
                                ( $objectProperty[ "data_type" ] === "email" ) ||
                                ( $objectProperty[ "data_type" ] === "phone" ) ||
                                ( $objectProperty[ "data_type" ] === "boolean" ) ||
                                ( $objectProperty[ "data_type" ] === "image" ) ||
                                ( $objectProperty[ "data_type" ] === "file" )
                            ) $is_error = false;

                            if (
                                (
                                    ( $objectProperty[ "data_type" ] === "integer" ) ||
                                    ( $objectProperty[ "data_type" ] === "float" )
                                ) &&
                                ctype_digit( $requestProperty )
                            ) {

                                $requestData->{ $objectProperty[ "article" ] } = (int) $requestData->{ $objectProperty[ "article" ] };
                                $is_error = false;

                            } // if. ctype_digit( $requestData->{ $objectProperty[ "article" ] } )


                            if ( $objectProperty[ "data_type" ] == "array" ) {

                                $requestData->{ $objectProperty[ "article" ] } = [ $requestData->{ $objectProperty[ "article" ] } ];
                                $is_error = false;

                            } // if. $objectProperty[ "data_type" ] == "array"


                            if ( $isMulipart && ( $objectProperty[ "data_type" ] == "array" ) ) {

                                $requestData->{ $objectProperty[ "article" ] } = explode( ",", $requestData->{ $objectProperty[ "article" ] } );
                                $is_error = false;

                            } // if. $isMulipart

                            break;

                        case "array":

                            $is_error = false;

                            if (
                                ( $objectProperty[ "data_type" ] === "image" ) ||
                                ( $objectProperty[ "data_type" ] === "file" )
                            ) $is_error = false;

                            break;

                        case "image":
                        case "object":

                            if (
                                ( $objectProperty[ "data_type" ] === "array" )
                            ) $is_error = false;

                            break;

                    } // switch. gettype( $requestProperty )

                    /**
                     * Не проверять пустые значения
                     */
                    if ( !$requestProperty ) $is_error = false;


                    if ( $is_error ) $this->returnResponse(
                        "Неверный тип параметра '{$objectProperty[ "title" ]}' " . gettype( $requestProperty ),
                        400
                    );

                } // if. gettype( $requestProperty ) !== $objectProperty[ "data_type" ]


                /**
                 * Проверка минимального/максимального значения.
                 * Для типов integer и string
                 */

                if ( $objectProperty[ "min-value" ] || $objectProperty[ "max-value" ] ) {

                    /**
                     * Учет типа параметра
                     */
                    switch ( $objectProperty[ "data_type" ] ) {

                        /**
                         * Проверка чисел
                         */
                        case "integer":

                            /**
                             * Минимальное значение
                             */
                            if ( $objectProperty[ "min-value" ] && ( $objectProperty[ "min-value" ] > $requestProperty ) )
                                $this->returnResponse(
                                    "Значение '{$objectProperty[ "title" ]}' не должно быть менее " .
                                    $objectProperty[ "min-value" ]
                                    , 400 );

                            /**
                             * Максимальное значение
                             */
                            if ( $objectProperty[ "max-value" ] && ( $objectProperty[ "max-value" ] < $requestProperty ) )
                                $this->returnResponse(
                                    "Значение '{$objectProperty[ "title" ]}' не должно быть больше " .
                                    $objectProperty[ "max-value" ]
                                    , 400 );

                            break;

                        /**
                         * Проверка строк
                         */
                        case "string":

                            /**
                             * Минимальное значение
                             */
                            if ( $objectProperty[ "min-value" ] && ( $objectProperty[ "min-value" ] > strlen( $requestProperty ) ) )
                                $this->returnResponse(
                                    "Длина '{$objectProperty[ "title" ]}' не должна быть менее " .
                                    $objectProperty[ "min-value" ]
                                    , 400 );

                            /**
                             * Максимальное значение
                             */
                            if ( $objectProperty[ "max-value" ] && ( $objectProperty[ "max-value" ] < strlen( $requestProperty ) ) )
                                $this->returnResponse(
                                    "Длина '{$objectProperty[ "title" ]}' не должна быть больше " .
                                    $objectProperty[ "max-value" ]
                                    , 400 );

                            break;

                    } // switch. $param[ "data_type" ]

                } // if. $objectParam[ "min-value" ] || $objectParam[ "max-value" ]


                /**
                 * Проверка кастомных списков
                 */

                if ( $objectProperty[ "custom_list" ] ) {

                    $is_error = true;
                    if ( gettype( $requestProperty ) == "array" ) $is_error = false;

                    foreach ( $objectProperty[ "custom_list" ] as $customListItem )
                        if ( $requestProperty === $customListItem[ "value" ] ) $is_error = false;

                    if ( $is_error ) $this->returnResponse(
                        "Недопустимое значение '{$objectProperty[ "title" ]}'", 400
                    );

                } // if. $objectParam[ "custom_list" ]


                /**
                 * Добавление св-ва в запрос
                 */
                $processedRequest[ $objectProperty[ "article" ] ] = $requestData->{ $objectProperty[ "article" ] };

            } // if. $requestProperty === null

        } // foreach. $objectScheme[ "properties" ]

//        $this->returnResponse( $processedRequest );
        return (object) $processedRequest;

    } // function. requestDataPreprocessor


    /**
     * Обработка ответа на запросы типа get
     *
     * @param $rows           array    Строки для вывода
     * @param $objectScheme   object   Схема объекта
     * @param $context        object   Контекст вызова get запроса
     * @param $isCheckActive  boolean  Проверять активность записей
     *
     * @return array
     */
    public function getResponseBuilder ( $rows, $objectScheme, $context = [], $isCheckActive = true ) {

        global $requestData;


        /**
         * Ответ на запрос
         */
        $response = [];

        /**
         * Заголовки списка
         */
        $listHeaders = [ "ID" ];


        /**
         * Обработка записей
         */
        foreach ( $rows as $row ) {

            /**
             * Игнорирование удаленных записей
             */
            if ( $isCheckActive && ( $row[ "is_active" ] === "N" ) ) continue;


            /**
             * Обработка системных параметров
             */

            if ( $row[ "id" ] ) $row[ "id" ] = (int) $row[ "id" ];


            /**
             * Обработка нестандартных типов данных
             */
            foreach ( $objectScheme[ "properties" ] as $property ) {

                if ( !in_array( "get", $property[ "use_in_commands" ] ?? [] ) ) continue;

                /**
                 * Учет select
                 */
//                if ( $requestData->select && !in_array( $property[ "article" ], $requestData->select ) ) continue;

                /**
                 * Заполнение заголовков списка
                 */
                $listHeaders[] = $property[ "title" ];


                switch ( $property[ "data_type" ] ) {

                    case "boolean":

                        $row[ $property[ "article" ] ] = $row[ $property[ "article" ] ] === "Y";
                        break;

                    case "integer":

                        $row[ $property[ "article" ] ] = (int) $row[ $property[ "article" ] ];

                        break;

                    case "image":
                    case "file":

                        if ( !$property[ "settings" ][ "is_multiply" ] ) {

                            /**
                             * Проверка на существование изображения
                             */
                            if (
                                !$row[ $property[ "article" ] ] ||
                                !file_exists( $this::$configs[ "paths" ][ "root" ] . $row[ $property[ "article" ] ] )
                            ) {
                                $row[ $property[ "article" ] ] = "";
                                break;
                            }


                            /**
                             * Определение пути к изображению
                             */
                            $imagePath = "https://" . $_SERVER[ "HTTP_HOST" ] . $row[ $property[ "article" ] ];

                            $row[ $property[ "article" ] ] = $imagePath;

                        } else {

                            /**
                             * Проверка наличия изображений
                             */
                            if ( !$row[ $property[ "article" ] ] ) break;


                            /**
                             * Получение пути к директории изображений
                             */
                            $imagesDirPath = $this::$configs[ "paths" ][ "root" ] . $row[ $property[ "article" ] ];
                            if ( !is_dir( $imagesDirPath ) ) break;


                            /**
                             * Перевод св-ва изображений в массив
                             */
                            $row[ $property[ "article" ] ] = [];


                            /**
                             * Обход изображений записи
                             */

                            $imagesDir = dir( $imagesDirPath );

                            while ( ( $image = $imagesDir->read() ) !== false ) {

                                if ( $image === "." || $image === ".." ) continue;


                                /**
                                 * Получение пути к изображению записи
                                 */
                                $imageDirPath = substr( $imagesDirPath, strpos( $imagesDirPath, "/uploads" ) );

                                $imagePath = "https://" . $_SERVER[ "HTTP_HOST" ] . $imageDirPath . "/" . $image;
                                $row[ $property[ "article" ] ][] = $imagePath;

                            } // while. $imagesDir->read()

                            $imagesDir->close();

                        } // if. $property[ "settings" ][ "is_multiply" ]

                        break;

                } // switch. $property[ "data_type" ]


                /**
                 * Списки
                 */
                if (
                    $property[ "list_donor" ][ "table" ] && $property[ "list_donor" ][ "properties_title" ]
                ) {

                    /**
                     * Получение детальной информации о записи
                     */

                    $selectProperties = [
                        "id",
                        $property[ "list_donor" ][ "properties_title" ],
                    ];

                    if ( $property[ "list_donor" ][ "properties_title" ] === "last_name" ) {
                        $selectProperties[] = "first_name";
                        $selectProperties[] = "patronymic";
                    }

                    if ( !$property[ "list_donor" ][ "object" ] ) {

                        $detailRow = $this->DB->from( $property[ "list_donor" ][ "table" ] )
                            ->select( null )->select( $selectProperties )
                            ->where( [ "id" => $row[ $property[ "article" ] ] ] )
                            ->limit( 1 )
                            ->fetch();

                    } else {

                        $detailRow = $this->DB->from( $property[ "list_donor" ][ "object" ] )
                            ->select( null )->select( [ "id", $property[ "list_donor" ][ "properties_title" ] ] )
                            ->where( [ "id" => $row[ $property[ "article" ] ] ] )
                            ->limit( 1 )
                            ->fetch();

                    } // if. !$property[ "list_donor" ][ "object" ]


                    /**
                     * Игнорирование пустых записей
                     */
                    if (
                        !$detailRow[ $property[ "list_donor" ][ "properties_title" ] ] ||
                        !$detailRow[ "id" ]
                    ) {

                        $row[ $property[ "article" ] ] = null;
                        continue;

                    }

                    $rowTitle = $detailRow[ $property[ "list_donor" ][ "properties_title" ] ];

                    if ( $property[ "list_donor" ][ "properties_title" ] === "last_name" ) {

                        $rowTitle = $detailRow[ "last_name" ] . " ";
                        if ( $detailRow[ "first_name" ] ) $rowTitle .= mb_substr( $detailRow[ "first_name" ], 0, 1 ) . ". ";
                        if ( $detailRow[ "patronymic" ] ) $rowTitle .= mb_substr( $detailRow[ "patronymic" ], 0, 1 ) . ".";

                    }


                    /**
                     * Добавление пункта списка
                     */
                    $row[ $property[ "article" ] ] = [
                        "title" => $rowTitle,
                        "value" => (int) $detailRow[ "id" ]
                    ];

                } // if. $property[ "list_donor" ][ "table" ] && $property[ "list_donor" ][ "properties_title" ]


                /**
                 * Кастомные списки
                 */
                if ( $property[ "custom_list" ] ) {

                    /**
                     * Получение заголовка св-ва
                     */

                    $propertyTitle = "";

                    foreach ( $property[ "custom_list" ] as $customProperty ) {

                        if ( $row[ $property[ "article" ] ] == $customProperty[ "value" ] )
                            $propertyTitle = $customProperty[ "title" ];

                    } // foreach. $param[ "custom_list" ]


                    $row[ $property[ "article" ] ] = [
                        "title" => $propertyTitle,
                        "value" => $row[ $property[ "article" ] ]
                    ];

                } // if. $property[ "list_donor" ]


                /**
                 * Связанные таблицы
                 */
                if ( $property[ "join" ] ) {

                    /**
                     * Данные записи из связанных таблиц
                     */
                    $joinDetailInputValues = [];


                    /**
                     * Получение связанных записей
                     */
                    $joinRows = $this->DB->from( $property[ "join" ][ "connection_table" ] )
                        ->select( null )->select( $property[ "join" ][ "filter_property" ] )
                        ->where( [ $property[ "join" ][ "insert_property" ] => $row[ "id" ] ] );

                    /**
                     * Обработка связанных записей
                     */
                    foreach ( $joinRows as $joinRow ) {

                        /**
                         * Получение детальной информации о связанной записи
                         */
                        $joinDetailRow = $this->DB->from( $property[ "join" ][ "donor_table" ] )
                            ->where( [ "id" => $joinRow[ $property[ "join" ][ "filter_property" ] ] ] )
                            ->limit( 1 )
                            ->fetch();


                        /**
                         * Формирование заголовков пунктов списков
                         */

                        $rowTitle = $joinDetailRow[ $property[ "join" ][ "property_article" ] ];

                        if ( $property[ "join" ][ "property_article" ] === "last_name" ) {

                            $rowTitle = $joinDetailRow[ "last_name" ] . " ";
                            if ( $joinDetailRow[ "first_name" ] ) $rowTitle .= mb_substr( $joinDetailRow[ "first_name" ], 0, 1 ) . ". ";
                            if ( $joinDetailRow[ "patronymic" ] ) $rowTitle .= mb_substr( $joinDetailRow[ "patronymic" ], 0, 1 ) . ".";

                        }


                        $joinDetailInputValues[] = [
                            "title" => $rowTitle,
                            "value" => (int) $joinDetailRow[ "id" ]
                        ];

                    } // foreach. $joinRows as $joinRow


                    $row[ $property[ "article" ] ] = $joinDetailInputValues;

                } // if. $property[ "join" ]


                /**
                 * Умные списки
                 */
                if ( $property[ "field_type" ] === "smart_list" ) {

                    $row[ $property[ "article" ] ] = [];


                    /**
                     * Получение значений умного списка
                     */
                    $smartListRows = $this->DB->from( $property[ "settings" ][ "connection_table" ] )
                        ->where( "row_id", $row[ "id" ] );

                    /**
                     * Вывод значений умного списка
                     */
                    foreach ( $smartListRows as $smartListRow ) {

                        /**
                         * Исключение системных св-в
                         */
                        unset( $smartListRow[ "id" ] );
                        unset( $smartListRow[ "row_id" ] );
                        unset( $smartListRow[ "is_system" ] );

                        $row[ $property[ "article" ] ][] = $smartListRow;

                    } // foreach. $smartListRows

                } // if. $property[ "field_type" ] === "smart_list"

            } // foreach. $objectScheme[ "properties" ]


            /**
             * Очистка системных параметров
             */
            unset( $row[ "password" ] );
            unset( $row[ "is_system" ] );
            unset( $row[ "is_active" ] );


            /**
             * Обработка контекстов
             */

            switch ( $context->block ) {

                /**
                 * Списки
                 */
                case "list":

                    /**
                     * Вывод кнопок и ссылок в списке
                     */

                    if ( $objectScheme[ "action_buttons" ] ) {

                        $row[ "row_href_type" ] = "update";


                        foreach ( $objectScheme[ "action_buttons" ] as $actionButton ) {

                            /**
                             * Добавление кнопки в запись
                             */
                            $row[ "buttons" ][] = $this->buildActionButton( $actionButton, $row );

                        } // foreach. $objectScheme[ "action_buttons" ]

                    } // if. $objectScheme[ "action_buttons" ]

                    break;

            } // switch. $context->block


            $response[] = $row;

        } // foreach. $rows

        if ( $context->block == "select" )
            $response = $this->selectHandler( $response, $objectScheme );

        $listHeaders = array_unique( $listHeaders );




        /**
         * Обработка контекстов
         */

        switch ( $context->block ) {

            /**
             * CSV экспорт
             */
            case "csv":

                header( "Content-type: text/csv" );
                header( "Content-Disposition: attachment; filename=export.csv" );
                header( "Pragma: no-cache" );
                header( "Expires: 0" );

                $buffer = fopen( "php://output", "w" );

                fputcsv( $buffer, $listHeaders, ";" );

                foreach ( $response as $row ) {

                    $resultRow = [];

                    foreach ( $row as $property ) {

                        if ( gettype( $property ) === "array" ) $resultRow[] = (string) $property[ "title" ];
                        else $resultRow[] = (string) $property;

                    } // foreach. $row

                    fputcsv( $buffer, $resultRow, ";" );

                } // foreach. $response

                fclose( $buffer );
                exit();

            case "exel":

                header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
                header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
                header ( "Cache-Control: no-cache, must-revalidate" );
                header ( "Pragma: no-cache" );
                header ( "Content-type: application/vnd.ms-excel" );
                header ( "Content-Disposition: attachment; filename=matrix.xls" );


                /**
                 * Подключение библиотеки для работы с Exel
                 */
                require_once( $this::$configs[ "paths" ][ "libs" ] . "/phpExcel.php" );
                require_once( $this::$configs[ "paths" ][ "libs" ] . "/PHPExcel/Writer/Excel5.php" );


                /**
                 * Алфавит.
                 * Используется для вставки значений в ячейки
                 */
                $alphabet = [
                    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S",
                    "T", "U", "V", "W", "X", "Y", "Z"
                ];


                /**
                 * Создание Excel объекта
                 */
                $excelObject = new PHPExcel();

                /**
                 * Установка индекса активного листа
                 */
                $excelObject->setActiveSheetIndex( 0 );

                /**
                 * Получение активного листа
                 */
                $excelSheet = $excelObject->getActiveSheet();


                /**
                 * Заголовок таблицы
                 */
                $excelSheet->setTitle( $objectScheme[ "title" ] );


                /**
                 * Запрос get на объект
                 */
                $request = (array) $this->request->data;
                $request[ "context" ]->block = "list";
                unset( $request[ "select" ] );
                $objects = $this->sendRequest( $this->request->object, $this->request->command, $request );


                /**
                 * Формирование хэш таблицы на артикулы полей объект(а)ов
                 */
                foreach ( $objectScheme[ "properties" ] as $schemeProperty )
                    $objectSchemeProperties[ $schemeProperty[ "article" ] ] = $schemeProperty;

                if ( $this->request->object != $objectScheme[ "table" ] ) {

                    $additionalScheme = $this->loadObjectScheme( $objectScheme[ "table" ] );
                    foreach ( $additionalScheme[ "properties" ] as $schemeProperty )
                        $objectSchemeProperties[ $schemeProperty[ "article" ] ] = $schemeProperty;

                }


                /**
                 * Формирование заголовков
                 */
                $propertyKey = 0;
                array_unshift( $this->request->select, "id" );


                foreach ( $this->request->data->select as $property ) {

                    $excelSheet->setCellValue(
                        $alphabet[ $propertyKey ] . "1",
                        $objectSchemeProperties[ $property ][ "title" ] ?? ""
                    );

                    $propertyKey++;

                } // foreach. $response[ 0 ]



                /**
                 * Формирование тела отчета
                 */
                foreach ( $objects as $rowKey => $row ) {

                    $propertyKey = 0;


                    /**
                     * Запись объекта в таблицу
                     */
                    foreach ( $this->request->data->select as $property ) {

                        $data = property_exists( $row, $property ) ? $row->$property : "";

                        if ( is_object( $data ) )  $excelSheet->setCellValue( $alphabet[ $propertyKey ] . ( $rowKey + 2 ), (string) $data->title );
                        elseif ( is_array( $data ) ) {

                            /**
                             * Преобразования списка объектов в строку
                             */
                            $collected_array = join( ', ', ( array_map( fn ( $item ) => $item->title ?? "", $data ?? [] ) ?? [] ) );
                            $excelSheet->setCellValue( $alphabet[ $propertyKey ] . ( $rowKey + 2 ), $collected_array );

                        }
                        else $excelSheet->setCellValue( $alphabet[ $propertyKey ] . ( $rowKey + 2 ), (string) $data );

                        $propertyKey++;

                    }

                } // foreach. $response


                $objWriter = new PHPExcel_Writer_Excel5( $excelObject );
                $objWriter->save( "php://output" );

                exit();

                break;

        } // switch. $context->block


        return $response;

    } // function. getResponseBuilder


    /**
     * Сборка кнопки для вывода списка
     *
     * @param $button  object  Схема кнопки
     * @param $row     object  Запись, в которой находится кнопка
     */
    private function buildActionButton ( $button, $row ) {

        /**
         * Обработка типа кнопки
         */
        switch ( $button[ "type" ] ) {

            case "href":

                $button[ "settings" ][ "page" ] = $this->generatingStringFromVariables(
                    $button[ "settings" ][ "page" ], $row
                );

                break;

            case "script":
            case "print":

                /**
                 * Обход св-в запроса
                 */

                foreach ( $button[ "settings" ][ "data" ] as $buttonPropertyKey => $buttonPropertyValue ) {

                    if ( $buttonPropertyValue[ 0 ] === ":" )
                        $button[ "settings" ][ "data" ][ $buttonPropertyKey ] = $this->generatingStringFromVariables(
                            [ $buttonPropertyValue ], $row
                        );

                } // foreach. $button[ "settings" ][ "data" ]

                break;

        } // switch. $button[ "type" ]


        return $button;

    } // function. buildActionButton


    /**
     * Перевод изображения в формат WebP
     *
     * @param $imagePath  string  Путь к изображению
     */
    public function imageToWepP ( $imagePath ) {

        /**
         * Получение детальной информации об изображении
         */
        $imageDetails = pathinfo( $imagePath );

        /**
         * Получение названия изображения
         */
        $imageTitle = $imageDetails[ "filename" ];

        /**
         * Получение формата изображения
         */
        $imageExtension = $imageDetails[ "extension" ];

        /**
         * Получение пути к директории изображения
         */
        $imageDirPath = $imageDetails[ "dirname" ];


        /**
         * Создание изображения для функции imageWebp
         */

        $createdImage = null;

        switch ( $imageExtension ) {

            case "jpg":
            case "jpeg":
                $createdImage = imageCreateFromJpeg( $imagePath );
                break;

            case "png":
                $createdImage = imageCreateFromPng( $imagePath );
                break;

            case "gif":
                $createdImage = imageCreateFromGif( $imagePath );
                break;

        } // switch. $imageExtension

        $imageWebpPath = "$imageDirPath/$imageTitle.webp";

        imageWebp( $createdImage, $imageWebpPath );
        imagedestroy( $createdImage );


        if ( file_exists( $imageWebpPath ) ) {

            /**
             * Удаление не сжатого изображения
             */
            unlink( $imagePath );

            return true;

        } else return false;

    } // function. imageToWepP

    /**
     * Загрузка base-64 изображений
     *
     * @param $imageTitle   string  Название изображения
     * @param $base64Image  string  Base-64 изображение
     * @param $imageTable   string  Таблица объекта, к которому привязывается изображение
     */
    public function uploadBase64Image ( $imageTitle, $base64Image, $imageTable ) {

        /**
         * Чтение изображения
         */
        $imageSource = fopen( $base64Image, "r" );


        /**
         * Получение формата изображения
         */

        $imageExtension = substr(
            $base64Image, strpos( $base64Image, "/" ) + 1
        );
        $imageExtension = substr(
            $imageExtension, 0, strpos( $imageExtension, ";" )
        );

        if ( !$imageExtension ) return false;


        /**
         * Формирование названия изображения
         */
        $imageName = "$imageTitle.$imageExtension";

        /**
         * Формирование пути к директории с изображениями
         */
        $imagesDirPath = $this::$configs[ "paths" ][ "uploads" ] . "/" . $this::$configs[ "company" ] . "/$imageTable";
        if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );

        /**
         * Формирование пути к изображению
         */
        $imagePath = "$imagesDirPath/$imageName";


        /**
         * Сохранение изображения
         */

        $imagePathSource = fopen( $imagePath, "w" );
        stream_copy_to_stream( $imageSource, $imagePathSource );

        fclose( $imageSource );
        fclose( $imagePathSource );


        /**
         * Перевод изображения в формат WebP
         */
//        if ( $imageExtension !== "webp" )
//            if ( $this->imageToWepP( $imagePath ) ) $imagePath = "$imagesDirPath/$imageTitle.webp";

        return substr( $imagePath, strlen( $_SERVER[ "DOCUMENT_ROOT" ] ) );

    } // function. uploadBase64Image

    /**
     * Загрузка изображений по ссылке
     *
     * @param $imageUrl     string  Ссылка на изображение
     * @param $imageObject  string  Объект, к которому принадлежит изображение (clients, products, etc)
     * @param $imageTitle   string  Название изображения
     */
    public function uploadImageByUrl ( $imageUrl, $imageObject = "", $imageTitle = "" ) {

        /**
         * Получение информации об изображении
         */
        $imageDetail = pathinfo( $imageUrl );
        if ( !isset( $imageDetail[ "extension" ] ) || !$imageDetail[ "extension" ] ) return false;

        /**
         * Названия изображения по умолчанию
         */
        if ( !$imageTitle ) $imageTitle = date( "YmdHis" ) . rand( 10, 99 );


        /**
         * Получение пути к директории загрузок
         */
        $imagesDirPath = $_SERVER[ "DOCUMENT_ROOT" ] . "/uploads/" . $this::$configs[ "company" ];
        if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );

        /**
         * Получение пути к директории загрузок, для отдельного объекта
         */
        if ( $imageObject ) {

            $imagesDirPath .= "/$imageObject";
            if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );

        } // if. $imageObject

        /**
         * Получение пути к изображению на сервере
         */
        $imagePath = "$imagesDirPath/$imageTitle." . $imageDetail[ "extension" ];


        /**
         * Загрузка изображения
         */
        $uploadedImage = file_get_contents( $imageUrl );

        /**
         * Сохранение изображения на сервер
         */
        file_put_contents( $imagePath, $uploadedImage );

        /**
         * Перевод изображения в формат WebP
         */
//        if ( $imageDetail[ "extension" ] !== "webp" )
//            if ( $this->imageToWepP( $imagePath ) ) $imagePath = "$imagesDirPath/$imageTitle.webp";


        return substr( $imagePath, strlen( $_SERVER[ "DOCUMENT_ROOT" ] ) );

    } // function. uploadImageByUrl


    /**
     * Загрузка изображений из формы
     *
     * @param $rowId   integer  ID записи Объекта
     * @param $image   object   Изображение
     * @param $object  string   Объект
     */
    public function uploadImagesFromForm ( $rowId, $image = [], $object = "" ) {

        /**
         * Получение пути к директории загрузок
         */
        $imagesDirPath = $_SERVER[ "DOCUMENT_ROOT" ] . "/uploads/" . $this::$configs[ "company" ];
        if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );


        /**
         * Получение пути к директории загрузок, для объекта
         */

        if ( !$object ) $imagesDirPath .= "/" . $this->request->object;
        else $imagesDirPath .= "/$object";

        if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );


        /**
         * Получение пути к изображению на сервере
         */

        $hash = $rowId . random_bytes(10);
        $hash = md5( $hash );

        $imagePath = "$imagesDirPath/$hash";

        switch ( $image[ "type" ] ) {

            case "image/jpeg":
                $imagePath .= ".jpg";
                break;

            case "image/png":
                $imagePath .= ".png";
                break;

            case "image/webp":
                $imagePath .= ".webp";
                break;

            default:

                /**
                 * Очистка изображения
                 */
                unlink( $imagePath . ".webp" );

                return "";

        } // switch. $image[ "type" ]


        /**
         * Сохранение изображения на сервер
         */
        move_uploaded_file( $image[ "tmp_name" ], $imagePath );

        /**
         * Перевод изображения в формат WebP
         */
//        if ( $image[ "type" ] !== "webp" )
//            if ( $this->imageToWepP( $imagePath ) ) $imagePath = "$imagesDirPath/$rowId.webp";


        return substr( $imagePath, strpos( $imagePath, "/uploads" ) );

    } // function. uploadImagesFromForm


    /**
     * Загрузка файлов из формы
     *
     * @param $rowId   integer  ID записи Объекта
     * @param $file    object   Файл
     * @param $object  string   Объект
     */
    public function uploadFilesFromForm ( $rowId, $file = [], $object = "" ) {

        $file = $file[ 0 ];


        /**
         * Получение пути к директории загрузок
         */
        $filesDirPath = $_SERVER[ "DOCUMENT_ROOT" ] . "/uploads/" . $this::$configs[ "company" ];
        if ( !is_dir( $filesDirPath ) ) mkdir( $filesDirPath );


        /**
         * Получение пути к директории загрузок, для объекта
         */

        if ( !$object ) $filesDirPath .= "/" . $this->request->object;
        else $filesDirPath .= "/$object";

        if ( !is_dir( $filesDirPath ) ) mkdir( $filesDirPath );


        /**
         * Получение названия файла
         */
        $fileTitle = substr( $file[ "name" ], 0, strpos( $file[ "name" ], "." ) );


        /**
         * Получение пути к изображению на сервере
         */

        $filePath = "$filesDirPath/$fileTitle";

        switch ( $file[ "type" ] ) {

            case "image/jpeg":
                $filePath .= ".jpg";
                break;

            case "image/png":
                $filePath .= ".png";
                break;

            case "image/webp":
                $filePath .= ".webp";
                break;

            case "application/pdf":
                $filePath .= ".pdf";
                break;

            case "text/csv":
                $filePath .= ".csv";
                break;

            case "video/mp4":
            case "audio/mp4":
                $filePath .= ".mp4";
                break;

            case "audio/aac":
                $filePath .= ".aac";
                break;

            case "audio/mpeg":
                $filePath .= ".mp3";
                break;

            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                $filePath .= ".xlsx";
                break;

            default:

                return "";

        } // switch. $file[ "type" ]


        /**
         * Сохранение файла на сервер
         */
        move_uploaded_file( $file[ "tmp_name" ], $filePath );


        return substr( $filePath, strpos( $filePath, "/uploads" ) );

    } // function. uploadFilesFromForm


    /**
     * Множественная загрузка файлов
     *
     * @param $rowId   integer  ID записи Объекта
     * @param $files  object   Изображения
     * @param $object  object   Объект
     */
    public function uploadMultiplyFiles ( $rowId, $files = [], $object = "", $article = "" ) {

        $newfiles = [];

        /**
         * Получение пути к директории загрузок
         */
        $filesDirPath = $_SERVER[ "DOCUMENT_ROOT" ] . "/uploads/" . $this::$configs[ "company" ];
        if ( !is_dir( $filesDirPath ) ) mkdir( $filesDirPath );


        /**
         * Получение пути к директории загрузок, для объекта
         */

        if ( !$object ) $filesDirPath .= "/" . $this->request->object;
        else $filesDirPath .= "/$object";

        if ( !is_dir( $filesDirPath ) ) mkdir( $filesDirPath );


        /**
         * Получение пути к директории файлов на сервере
         */
        $filesDirPath = "$filesDirPath/$rowId";
        mkdir( $filesDirPath );

        if ( $files == null ) {

            $filesToRemove = glob( $filesDirPath );

            foreach( $filesToRemove as $file ){

                if(is_file($file)) {

                    unlink($file);

                }
            }

        }

        /**
         * Загрузка файлов
         */
        foreach ( $this->request->data->$article as $file ) {

            if ( is_string( $file ) ) {

                $newfiles[] = $file;

            } else {

                $newfiles[] = $file[ "name" ];

            }

        }

        foreach ( scandir($filesDirPath) as $oldFile ) {

            if ( !in_array( $oldFile, $newfiles ) ) {

                unlink($filesDirPath . "/" . $oldFile );

            }

        }

        foreach ( $files as $file ) {

            /**
             * Получение названия файла
             */
            $fileTitle = substr( $file[ "name" ], 0, strpos( $file[ "name" ], "." ) );


            /**
             * Получение пути к файлу на сервере
             */
            $filePath = "$filesDirPath/$fileTitle";


            /**
             * Сохранение файла на сервер
             */
            move_uploaded_file( $file[ "tmp_name" ], $filePath );

        } // foreach. $files


        return substr( $filesDirPath, strpos( $filesDirPath, "/uploads" ) );

    } // function. uploadMultiplyFiles



    /* Множественная загрузка изображений
     *
     * @param $rowId   integer  ID записи Объекта
     * @param $images  object   Изображения
     * @param $object  object   Объект
     */
    public function uploadMultiplyImages ( $rowId, $images = [], $object = "" ) {

        /**
         * Получение пути к директории загрузок
         */
        $imagesDirPath = $_SERVER[ "DOCUMENT_ROOT" ] . "/uploads/" . $this::$configs[ "company" ];
        if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );


        /**
         * Получение пути к директории загрузок, для объекта
         */

        if ( !$object ) $imagesDirPath .= "/" . $this->request->object;
        else $imagesDirPath .= "/$object";

        if ( !is_dir( $imagesDirPath ) ) mkdir( $imagesDirPath );


        /**
         * Получение пути к директории изображений на сервере
         */

        $currentFiles = [];
        $imagesDirPath = "$imagesDirPath/$rowId";

        foreach ( $images as $image )
            if ( gettype( $image ) === "string" ) $currentFiles[] = substr( $image, strpos( $image, "/uploads" ) );

        $this->clearDirMultiply( $imagesDirPath, $currentFiles );
        mkdir( $imagesDirPath );


        /**
         * Загрузка изображений
         */

        foreach ( $images as $imageKey => $image ) {

            /**
             * Получение пути к изображению на сервере
             */

            $isContinue = false;

            $hash = $rowId . random_bytes(10);
            $hash = md5( $hash );

            $imagePath = "$imagesDirPath/$hash";

            switch ( $image[ "type" ] ) {

                case "image/jpeg":
                    $imagePath .= ".jpg";
                    break;

                case "image/png":
                    $imagePath .= ".png";
                    break;

                case "image/webp":
                    $imagePath .= ".webp";
                    break;

                default:

                    /**
                     * Очистка изображения
                     */
                    unlink( $imagePath . ".webp" );

                    $isContinue = true;

            } // switch. $image[ "type" ]

            if ( $isContinue ) continue;


            /**
             * Сохранение изображения на сервер
             */
            move_uploaded_file( $image[ "tmp_name" ], $imagePath );

            /**
             * Перевод изображения в формат WebP
             */
            // if ( $image[ "type" ] !== "webp" ) $this->imageToWepP( $imagePath );

        } // foreach. $images


        return substr( $imagesDirPath, strpos( $imagesDirPath, "/uploads" ) );

    } // function. uploadMultiplyImages


    /**
     * Удаление директории с файлами
     *
     * @param $path          string  Путь к директории
     * @param $currentFiles  array   Файлы, которые не нужно чистить
     *
     * @return boolean
     */
    public function clearDirMultiply ( $path, $currentFiles ) {

        $files = array_diff( scandir( $path ), array( ".", ".." ) );


        foreach ( $files as $file ) {

            $filePath = substr( "$path/$file", strpos( "$path/$file", "/uploads" ) );

            if ( is_dir( "$path/$file" ) ) continue;
            elseif ( in_array( $filePath, $currentFiles ?? [] ) ) continue;
            else unlink( "$path/$file" );

        } // foreach. $files

        return true;

    } // function. clearDirMultiply


    /**
     * Проверка JWT авторизации
     *
     * @return boolean
     */
    private function validateJWT () {

        /**
         * Проверка передачи JWT кода
         */
        if ( !$this->request->jwt ) return false;


        try {

            /**
             * Декодирование JWT
             */
            $JWT_decoded = $this->JWT::decode( $this->request->jwt, $this::$configs[ "jwt_key" ], [ "HS256" ] );


            /**
             * Возвращение данных пользователя
             */
            if ( $JWT_decoded ) return $JWT_decoded;

            $this->returnResponse( "Пользователь не авторизован", 403 );

        } catch ( Exception $e ) {

            $this->returnResponse( "Ошибка авторизации", 403 );

        } // try


        return true;

    } // function. validateJWT


    /**
     * Определение является ли аккаунт публичным
     * @return bool
     */
    function isPublicAccount(): bool {

        /**
         * Получение роли
         */
        $roleDetails = $this->DB->from( "roles" )
            ->where( "id", $this::$userDetail->role_id )
            ->fetch();

        if ( !$roleDetails ) return false;
        return $roleDetails[ "article" ] === "public";

    } // function isPublicAccount(): bool


    /**
     * Проверка прав
     *
     * @param $permissions  array  Требуемые права
     *
     * @return boolean
     */
    public function validatePermissions ( $permissions, $use_available = false ) {

        /**
         * Проверка JWT авторизации
         */
        $this::$userDetail = $this->validateJWT();


        /**
         * Проверка требуемых доступов
         */

        if (
            isset( $this::$userDetail->role_id ) && $this::$userDetail->role_id == 1
        ) return true;

        $hasPermission = true;

        foreach ( $permissions as $permission ) {

            if ( !$this->hasPermisson( $permission ) ) {
                $hasPermission = false;
                continue;
            };
            if ( $use_available ) return true;

        } // foreach. $permissions


        return $hasPermission;

    } // function. validatePermissions



    public function hasPermisson( $permission ) {

        $rolePermission = $this->DB->from( "permissions" )
            ->leftJoin( "roles_permissions ON roles_permissions.permission_id = permissions.id" )
            ->select( null )->select( [ "permissions.id" ] )
            ->where( [
                "roles_permissions.role_id" => $this::$userDetail->role_id,
                "permissions.article" => $permission
            ] )
            ->fetch();

        return (bool) $rolePermission[ "id" ];
    }


    public function getCurrentUser () {

        return $this->validateJWT();

    } // function. getCurrentUser

    /**
     * Проверка подключения необходимых модулей
     *
     * @param $modules  array  Требуемые модули
     *
     * @return boolean
     */
    public function validateModules ( $modules ) {

        if ( !$modules ) return true;
        if ( !$this::$configs[ "settings" ][ "modules" ] ) return false;

        foreach ( $modules as $module )
            if ( !in_array( $module, $this::$configs[ "settings" ][ "modules" ] ?? [] ) ) return false;


        return true;

    } // function. validateModules


    /**
     * Формирование строк из переменных.
     * Позволяет собирать строки с использованием переменных
     *
     * @param $string     mixed   Строка
     * @param $rowDetail  object  Детальная информация о записи
     *
     * @return string
     */
    public function generatingStringFromVariables ( $string, $rowDetail ) {

        /**
         * Сформированная строка
         */
        $responseString = "";


        /**
         * Отмена обработки обычной строки
         */
        if ( gettype( $string ) === "string" ) return $string;
        if ( gettype( $string ) === "integer" ) return (int) $string;


        /**
         * Обработка схемы строки
         */

        foreach ( $string as $stringComponent ) {

            if ( $stringComponent[ 0 ] === ":" ) {

                /**
                 * Обработка переменной в строке
                 */

                /**
                 * Получение переменной в строке
                 */
                $stringVariable = substr( $stringComponent, 1 );


                /**
                 * Формирование строки
                 */
                if ( $rowDetail[ $stringVariable ] )
                    $responseString .= $rowDetail[ $stringVariable ];
                else $responseString .= "_";


                continue;

            } // if. $stringComponent[ 0 ] === ":"


            /**
             * Добавление простого текста в строку
             */
            $responseString .= $stringComponent;

        } // foreach. $string


        if ( ctype_digit( $responseString ) )
            $responseString = (int) $responseString;

        return $responseString;

    } // function. generatingStringFromVariables


    /**
     * Добавление лога
     *
     * @param $detail       array    Информация о логе
     * @param $requestData  object   Запрос
     *
     * @return boolean
     */
    public function addLog ( $detail,  $requestData = [] ) {

        /**
         * Заполнение системных св-в
         */
        $detail[ "ip" ] = $_SERVER[ "REMOTE_ADDR" ];


        /**
         * Заполнение данных о связанных Пользователях и Клиентах
         */

        $logDetail[ "users_id" ] = [];
        $logDetail[ "clients_id" ] = [];
        if ( empty( $requestData->user_id ) ) $requestData->user_id = 4;

        if ( !$requestData->is_ignore_current_user ) $logDetail[ "users_id" ][] = $this::$userDetail->id;
        if ( $detail[ "table_name" ] == "users" ) $logDetail[ "users_id" ][] = $detail[ "row_id" ];
        if ( $detail[ "table_name" ] == "clients" ) $logDetail[ "clients_id" ][] = $detail[ "row_id" ];
        if ( $requestData->user_id ) $logDetail[ "users_id" ][] = $requestData->user_id;
        if ( $requestData->client_id ) $logDetail[ "clients_id" ][] = $requestData->client_id;

        foreach ( $requestData->users_id as $userId ) $logDetail[ "users_id" ][] = $userId;
        foreach ( $requestData->clients_id as $clientId ) $logDetail[ "clients_id" ][] = $clientId;


        /**
         * Добавление лога
         */

        if ( is_array( $detail[ "row_id" ] ) ) {

            foreach ( $detail[ "row_id" ] as $rowId ) {

                $currentDetail = $detail;
                $currentDetail[ "row_id" ] = $rowId;
                $this->add_log_db( $currentDetail, $logDetail );

            }

        } else $this->add_log_db( $detail, $logDetail );


        return true;

    } // function. addLog
    

    function add_log_db ( $detail, $logDetail )
    {
        $insertedLogId = $this->DB->insertInto( "logs" )
            ->values( [
                "table_name" => $detail[ "table_name" ],
                "description" => mb_substr( $detail[ "description" ], 0, 200 ),
                "row_id" => $detail[ "row_id" ],
                "ip" => $detail[ "ip" ]
            ] )
            ->execute();

        if ( !$insertedLogId ) return false;
        foreach ( $logDetail[ "users_id" ] as $userId )
            $this->DB->insertInto( "logs_users" )
                ->values( [
                    "log_id" => $insertedLogId,
                    "user_id" => $userId ?? 4
                ] )
                ->execute();

        foreach ( $logDetail[ "clients_id" ] as $clientId )
            $this->DB->insertInto( "logs_clients" )
                ->values( [
                    "log_id" => $insertedLogId,
                    "client_id" => $clientId
                ] )
                ->execute();

        return $insertedLogId;
    }


    /**
     * Создание события.
     * Используется как аналог веб-соккетов.
     * В админке произойдет обновление указанного блока
     *
     * @param $blockArticle  string   Артикул блока, в котором произошло событие
     * @param $userId        integer  ID роли, которой предназначено событие
     *
     * @return boolean
     */
    public function addEvent ( $blockArticle, $userId = null ) {

        /**
         * Проверка обязательных параметров
         */
        if ( !$blockArticle ) return false;


        /**
         * Удаление старых событий
         */
        $this->DB->deleteFrom( "events" )
            ->where( [
                "table_name" => $blockArticle,
                "user_id" => $userId ?? 4
            ] )
            ->execute();


        $this->DB->insertInto( "events" )
            ->values( [
                "table_name" => $blockArticle,
                "user_id" => $userId ?? 4
            ] )
            ->execute();

        return true;

    } // function. addEvent


    /**
     * Добавление уведомления
     *
     * @param $type         string   Тип
     * @param $title        string   Название
     * @param $description  string   Описание
     * @param $status       string   Статус
     * @param $userId       integer  Пользователь
     * @param $href         string   Ссылка
     *
     * @return boolean
     */
    public function addNotification ( $type, $title, $description, $status = "info", $userId = null, $href = "" ) {

        /**
         * Получение детальной информации о типе уведомлений
         */

        $notificationTypesDetail = $this->DB->from( "notificationTypes" )
            ->where( [ "article" => $type ] )
            ->limit( 1 )
            ->fetch();

        if ( !$notificationTypesDetail ) return false;


        /**
         * Получение ролей, которые получат уведомление
         */
        $notificationTypes_roles = $this->DB->from( "roles_notificationTypes" )
            ->where( [ "notificationType_id" => $notificationTypesDetail[ "id" ] ] );


        /**
         * Добавление уведомлений
         */

        if ( $userId ) {

            /**
             * Персональное уведомление
             */
            $this->DB->insertInto( "notifications" )
                ->values( [
                    "title" => mb_substr( $title, 0, 75 ),
                    "description" => mb_substr( $description, 0, 255 ),
                    "status" => mb_substr( $status, 0, 15 ),
                    "user_id" => $userId ?? 4,
                    "href" => $href
                ] )
                ->execute();

            /**
             * Создание события
             */
            $this->addEvent( "notifications", $userId );

        } elseif ( !$notificationTypes_roles ) {

            /**
             * Общее уведомление
             */
            $this->DB->insertInto( "notifications" )
                ->values( [
                    "title" => mb_substr( $title, 0, 75 ),
                    "description" => mb_substr( $description, 0, 255 ),
                    "status" => mb_substr( $status, 0, 15 ),
                    "href" => $href
                ] )
                ->execute();

            /**
             * Создание события
             */
            $this->addEvent( "notifications" );

        } else {

            foreach ( $notificationTypes_roles as $notificationType_role ) {

                /**
                 * Получение пользователей роли
                 */
                $roleUsers = $this->DB->from( "users" )
                    ->where( [ "role_id" => $notificationType_role[ "role_id" ] ] );


                foreach ( $roleUsers as $roleUser ) {

                    /**
                     * Уведомление ответственного пользователя
                     */
                    $this->DB->insertInto( "notifications" )
                        ->values( [
                            "title" => mb_substr( $title, 0, 75 ),
                            "description" => mb_substr( $description, 0, 255 ),
                            "status" => mb_substr( $status, 0, 15 ),
                            "user_id" => $roleUser[ "id" ] ?? 4,
                            "href" => $href
                        ] )
                        ->execute();


                    /**
                     * Создание события
                     */
                    $this->addEvent( "notifications", $roleUser[ "id" ] );

                } // foreach. $roleUsers

            } // foreach. $notificationTypes_roles

        } // if. $userId


        return true;

    } // function. addNotification


    /**
     * Получение кэша высоконагруженных отчетов
     *
     * @param $reportArticle  string   Артикул отчета
     * @param $filters        array    Фильтры отчета
     *
     * @return array
     */
    public function getHardReportCache ( $reportArticle, $requestFilters = [] ) {

        if ( isset( $requestFilters->start_at ) ) $requestFilters->start_at .= " 00:00:00";

        $requestFilters = (array) $requestFilters;


        $reports = $this->DB->from( "hardReports" )
            ->where( "report_article", $reportArticle )
            ->orderBy( "created_at asc" );


        foreach ( $reports as $report ) {

            $isValid = true;
            $reportFiltersResult = [];


            $reportFilters = $this->DB->from( "hardReports_filters" )
                ->where( "report_id", $report[ "id" ] );

            foreach ( $reportFilters as $reportFilter )
                $reportFiltersResult[ $reportFilter[ "filter_article" ] ] = $reportFilter[ "filter_value" ];


            /**
             * Проверка соответствия отчета
             */
            foreach ( $requestFilters as $requestFilterArticle => $requestFilterValue )
                if ( $reportFiltersResult[ $requestFilterArticle ] != $requestFilterValue ) $isValid = false;


            if ( $isValid ) {

                /**
                 * Получение кэша
                 */

                $reportCache = [];

                $reportCacheRows = $this->DB->from( "hardReports_cache" )
                    ->where( "report_id", $report[ "id" ] );

                foreach ( $reportCacheRows as $reportCacheRow )
                    $reportCache[ $reportCacheRow[ "property_article" ] ] = $reportCacheRow[ "property_value" ];


                return [
                    "status" => $report[ "status" ],
                    "updated_at" => $report[ "created_at" ],
                    "data" => $reportCache
                ];

            } // if. $isValid

        } // foreach. $reports


        return [];

    } // function. getHighReportCache

    /**
     * Отправка запроса
     *
     * @param $body              array    Тело запроса
     * @param $api_url           string   URL запроса
     * @param $is_full_response  boolean  Полный ответ
     *
     * @return mixed
     */
    public function curlRequest ( $body = [], $api_url = "" ) {

        if ( !$api_url && $_SERVER[ "HTTP_HOST" ] ) $api_url = $_SERVER[ "HTTP_HOST" ];


        /**
         * Формирование заголовков запроса
         */
        $headers = [
            "Content-Type: application/json",
            "Timeout: 3"
        ];


        /**
         * Отправка запроса в API
         */

        $curl = curl_init( "https://$api_url" );

        curl_setopt_array( $curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "UTF-8",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode( $body ),
        ) );

        $response = json_decode( curl_exec( $curl ) );


        if ( !$response ) return false;
        else return $response;

    } // function. sendRequest

    /**
     * Отправка запроса в API
     *
     * @param $object            string   Объект запроса
     * @param $command           string   Команда запроса
     * @param $body              array    Тело запроса
     * @param $api_url           string   URL запроса
     * @param $is_full_response  boolean  Полный ответ
     *
     * @return mixed
     */
    public function sendRequest ( $object, $command, $body = [], $api_url = "", $is_full_response = false ) {

        if ( !$api_url && $_SERVER[ "HTTP_HOST" ] ) $api_url = $_SERVER[ "HTTP_HOST" ];


        /**
         * Формирование заголовков запроса
         */
        $headers = [
            "Content-Type: application/json",
            "Timeout: 3"
        ];


        /**
         * Формирование запроса
         */

        $data[ "jwt" ] = $this->request->jwt;
        $data[ "object" ] = $object;
        $data[ "command" ] = $command;
        $data[ "data" ] = $body;

        $data = json_encode( $data );


        /**
         * Отправка запроса в API
         */

        $curl = curl_init( "https://$api_url" );

        curl_setopt_array( $curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "UTF-8",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $data,
        ) );

        $response = json_decode( curl_exec( $curl ) );

        if ( !$response ) return false;

        if ( !$is_full_response ) return $response->data;
        else return $response;

    } // function. sendRequest

} // class. API

$API = new API;