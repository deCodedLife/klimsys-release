<?php
	
	/**
	 * @file
	 * Управление базой данных
	 */
	
	
	
	class DB {
		
		/**
		 * Коннектор базы данных
		 */
		public $connection;
		
		
		
		function __construct() {
			
			/**
			 * Подключение к базе данных
			 */
			
			$this->connection = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
//			returnResponse( DB_HOST . " " . DB_USER . " " . DB_PASSWORD . " " . DB_NAME );
			
			if ( !$this->connection ) returnResponse( "Can't connect to database", 500 );
			
			
			
			/**
			 * Настройка utf-8
			 */
			mysqli_query( $this->connection, "SET NAMES utf8" );
			
			return true;
			
		} // function. __construct
		
		
		
		/**
		 * Очистка строки от спецсимволов
		 *
		 * $str  str  Исходная строка
		 *
		 * return str
		 */
		private function clearString ( $str ) {
			
			return strip_tags( $str );
			
		} // function. clearString
		
		
		
		/**
		 * Создание запроса
		 *
		 * $query  str  Запрос к базе данных
		 *
		 * return mysqli_query
		 */
		public function makeQuery ( $query, $return_id = false ) {

			if ( !$return_id ) return mysqli_query( $this->connection, $this->clearString( $query ) );
			else {

				$response = mysqli_query( $this->connection, $query );
				if ( $response ) return $this->connection->insert_id;
				else return false;

			}

		} // function. makeQuery
		
		/**
		 * Создание запроса без обрезки спецсимволов
		 *
		 * $query  str  Запрос к базе данных
		 *
		 * return mysqli_query
		 */
		public function makeQueryWithoutClearString ( $query, $return_id = false ) {

			if ( !$return_id ) return mysqli_query( $this->connection, htmlspecialchars( $query ) );
			else {

				$response = mysqli_query( $this->connection, $query );
				if ( $response ) return $this->connection->insert_id;
				else return false;

			}

		} // function. makeQueryWithoutClearString
		
		
		
		/**
		 * Создание группы кастомных параметров
		 *
		 * $title  str  Название
		 *
		 * return int|bool
		 */
		public function makeCustomParamsGroup ( $title ) {
			
			/**
			 * Формирование запроса к БД
			 */
			$query = "INSERT INTO param_groups ( title ) VALUES ( '$title' )";
			
			if ( !$this->makeQuery ( $query ) ) return false;

			return mysqli_insert_id( $this->connection);
			
		} // function. makeCustomParamsGroup
		
		/**
		 * Редактирование группы кастомных параметров
		 *
		 * $id  int  Id
		 * $title  str  Название
		 *
		 * return bool
		 */
		public function changeCustomParamsGroup ( $id, $title ) {
			
			/**
			 * Формирование запроса к БД
			 */
			$query = "UPDATE param_groups SET title = '$title' WHERE id = '$id'";
			
			if ( !$this->makeQuery ( $query ) ) return false;
			return true;
			
		} // function. changeCustomParamsGroup
		
		/**
		 * Удаление группы кастомных параметров
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function removeCustomParamsGroup ( $id ) {
			
			/**
			 * Удаление связей текущей группы кастомных параметров
			 */
			if ( !$this->makeQuery ( "DELETE FROM `params-param_groups` WHERE param_group_id = '$id'" ) ) return false;
			
			/**
			 * Удаление группы кастомных параметров
			 */
			if ( !$this->makeQuery ( "DELETE FROM param_groups WHERE id = '$id'" ) ) return false;
			
			
			
			return true;
			
		} // function. removeCustomParamsGroup
		
		/**
		 * Вывод групп кастомных параметров
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function getCustomParamGroups ( $id = null, $orderBy = null, $sort = null ) {
			
			$customParamGroups_return = [];
			
			
			
			/**
			 * Получение групп кастомных параметров
			 */
			
			$query = "SELECT * FROM `param_groups`";
			if ( $id ) $query .= " WHERE id = '$id'";

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }
			
			$customParamGroups = $this->makeQuery ( $query );
			
			if ( !$customParamGroups ) return false;
			
			
			
			/**
			 * Формирование массива групп кастомных параметров
			 */
			foreach ( $customParamGroups as $customParamsGroup ) {
				
				$customParamGroups_return[] = [
					"id" => (int) $customParamsGroup[ "id" ],
					"title" => $customParamsGroup[ "title" ]
				];
				
			} // foreach. $customParamGroups as $customParamsGroup
			
			
			
			return $customParamGroups_return;
			
		} // function. getCustomParamGroups
		
		
		
		/**
		 * Создание кастомного параметра
		 *
		 * $title  str  Название
		 * $type  str  Тип
		 * $table  str  Таблица, к которой привязан параметр
		 * $groups  arr  Id групп
		 *
		 * return int|bool
		 */
		public function makeCustomParam ( $title, $type, $table, $groups = [] ) {
			
			/**
			 * Создание кастомного параметра
			 */
			if ( !$this->makeQuery (
				"INSERT INTO params ( title, type, table_name ) VALUES ( '$title', '$type', '$table' )"
			) ) return false;


            /**
             * Получение id созданного параметра
             */
            $paramId = mysqli_insert_id( $this->connection );


			/**
			 * Привязка кастомного параметра к группам
			 */
			if ( $groups ) {

				foreach ( $groups as $group ) {
					
					$this->makeQuery (
						"INSERT INTO `params-param_groups` ( param_id, param_group_id ) VALUES ( '$paramId', '$group' )"
					);
					
				} // foreach. $groups as $group
				
			} // if. $groups
			
			
			
			return $paramId;
			
		} // function. makeCustomParam
		
		/**
		 * Редактирование кастомного параметра
		 *
		 * $id  int  Id
		 * $value  mix  Значение
		 * $row_id  int  Id записи
		 *
		 * return bool
		 */
		public function changeCustomParam ( $id, $value, $row_id = null ) {
			
			if ( !$row_id ) $row_id = "null";
			
			
			
			/**
			 * Получение типа кастомного параметра
			 */
			$paramType = $this->makeQuery ( "SELECT type FROM params WHERE id = $id" );
			$paramType = mysqli_fetch_array( $paramType )[ 0 ];
			
			if ( !$paramType ) return false;
			
			
			/**
			 * Удаление прошлого значения кастомного параметра
			 */
			
			$deleteParamValue_query = "DELETE FROM `params_value_$paramType` WHERE param_id = '$id'";
			
			if ( $row_id == "null" ) {
				$deleteParamValue_query .= " AND row_id IS NULL";
			} else {
				$deleteParamValue_query .= " AND row_id = $row_id";
			}
			
			$this->makeQuery ( $deleteParamValue_query );
			
			
			/**
			 * Добавление значения кастомного параметра
			 */
			if ( !$this->makeQuery (
				"INSERT INTO `params_value_$paramType` ( param_id, row_id, value ) VALUES ( '$id', $row_id, '$value' )"
			) ) return false;
			
			
			
			return true;
			
		} // function. changeCustomParam
		
		/**
		 * Подключение кастомного параметра к группе
		 *
		 * $id  int  Id
		 * $group_id  int  Id группы
		 *
		 * return bool
		 */
		public function customParam_groupLogIn ( $id, $group_id ) {
			
			/**
			 * Проверка. Подключен ли уже кастомный параметр к группе
			 */
			$isCustomParamInGroup = $this->makeQuery (
				"SELECT COUNT(*) FROM `params-param_groups` WHERE param_id = '$id' AND param_group_id = '$group_id'"
			);
			$isCustomParamInGroup = mysqli_fetch_array( $isCustomParamInGroup )[ 0 ];
			
			if ( $isCustomParamInGroup > 0 ) return false;
			
			
			
			/**
			 * Формирование запроса к БД
			 */
			$query = "INSERT INTO `params-param_groups` ( param_id, param_group_id ) VALUES ( '$id', '$group_id' )";
			
			if ( !$this->makeQuery ( $query ) ) return false;
			return true;
			
		} // function. customParam_groupLogIn
		
		/**
		 * Исключение кастомного параметра из группы
		 *
		 * $id  int  Id
		 * $group_id  int  Id группы
		 *
		 * return bool
		 */
		public function customParam_groupLogOut ( $id, $group_id ) {
			
			/**
			 * Формирование запроса к БД
			 */
			$query = "DELETE FROM `params-param_groups` WHERE param_id = '$id' AND param_group_id = '$group_id' ";
			
			if ( !$this->makeQuery ( $query ) ) return false;
			return true;
			
		} // function. customParam_groupLogOut
		
		/**
		 * Удаление кастомного параметра
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function removeCustomParam ( $id ) {
			
			/**
			 * Проверка. Является ли удаляемый параметр системным
			 */
			$isParamSystem = $this->makeQuery ( "SELECT is_system FROM params WHERE id = $id" );
			$isParamSystem = mysqli_fetch_array( $isParamSystem )[ 0 ];
			
			if ( $isParamSystem == 1 ) return false;
			
			
			
			/**
			 * Удаление связей текущего кастомного параметра
			 */
			$this->makeQuery ( "DELETE FROM `params-param_groups` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_checkbox` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_datetime` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_file` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_float` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_int` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_select` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_text` WHERE param_id = '$id'" );
			$this->makeQuery ( "DELETE FROM `params_value_varchar` WHERE param_id = '$id'" );
			
			/**
			 * Удаление кастомного параметра
			 */
			if ( !$this->makeQuery ( "DELETE FROM params WHERE id = '$id'" ) ) return false;
			
			
			
			return true;
			
		} // function. removeCustomParam
		
		/**
		 * Вывод кастомных параметров
		 *
		 * $id  int  Id
		 * $table  str  Таблица
		 * $row_id  int  Id записи
		 * $group_id  int  Id группы
		 *
		 * return bool
		 */
		public function getCustomParams ( $id = null, $table = null, $row_id = null, $group_id = null, $orderBy = null,
                                          $sort = null ) {
			
			$customParams_return = [];
			
			
			
			/**
			 * Получение кастомных параметров
			 */
			
			$isFirtsFilter = true;
			
			$query = "SELECT * FROM `params`";
			
			if ( $id ) {
				
				$isFirtsFilter = false;
				$query .= " WHERE id = '$id'";
				
			} // if . $id
			
			if ( $table ) {
				
				if ( $isFirtsFilter ) {
					$query .= " WHERE table_name = '$table'";
				} else {
					$query .= " AND table_name = '$table'";
				}
				
			} // if. $table

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }
			
			$customParams = $this->makeQuery ( $query );
			
			if ( !$customParams ) return false;
			
			
			
			/**
			 * Формирование массива групп кастомных параметров
			 */
			foreach ( $customParams as $customParam ) {
				
				$customParam_obj = [
					"id"     => $customParam[ "id" ],
					"title"  => $customParam[ "title" ],
					"type"   => $customParam[ "type" ],
					"table"  => $customParam[ "table_name" ],
					"groups" => []
				];
				
				
				/**
				 * Получение значения кастомного параметра
				 */
				if ( ( $id || $table ) || $row_id ) {
					
					$customParamValue = $this->makeQuery (
						"SELECT value FROM params_value_" . $customParam[ "type" ] . 
						" WHERE param_id = '" . $customParam[ "id" ] . "' AND row_id = '$row_id' LIMIT 1"
					);
					$customParamValue = mysqli_fetch_array( $customParamValue )[ 0 ];
					
					$customParam_obj[ "value" ] = $customParamValue;
					
				} // if. $row_id
				
				
				/**
				 * Получение групп кастомного параметра
				 */
				
				$customParamGroupIds = $this->makeQuery (
					"SELECT param_group_id FROM `params-param_groups` WHERE param_id = '" . $customParam[ "id" ] . "'"
				);
				
				foreach ( $customParamGroupIds as $customParamGroupId ) {
					
					$customParamGroupId = $customParamGroupId[ "param_group_id" ];
					
					
					
					/**
					 * Получение детальной информации о группе кастомных параметров
					 */
					$customParamGroup = $this->makeQuery (
						"SELECT * FROM param_groups WHERE id = '$customParamGroupId'"
					);
					$customParamGroup = mysqli_fetch_array( $customParamGroup );
					
					
					
					$customParam_obj[ "groups" ][] = [
						"id" => (int) $customParamGroup[ "id" ],
						"title" => $customParamGroup[ "title" ]
					];
					
				} // foreach. $customParamGroupIds as $customParamGroupId
				
				
				
				$customParams_return[] = $customParam_obj;
				
			} // foreach. $customParamGroups as $customParamsGroup
			
			
			
			return $customParams_return;
			
		} // function. getCustomParams
		
	} // class. DB
	
	
	
	$DB = new DB;