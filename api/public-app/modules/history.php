<?php
	
	/**
	 * @file
	 * Управление историей
	 */
	
	
	
	class History {
		
		/**
		 * Модуль базы данных
		 */
		private $DB = null;
		
		
		
		function __construct() {
			
			$this->DB = new DB;
			
		} // function. __construct
		
		
		
		/**
		 * Добавление лога
		 *
		 * $table  str  Таблица
		 * $description  str  Описание
		 * $status  str  Статус
		 * $employee_id  int  Id сотрудника
		 * $client_id  int  Id клиента
		 * $row_id  int  Id записи
		 * $hospital_id  int  Id филиала
		 *
		 * return bool
		 */
		public function addLog (
			$table, $description, $status = "info", $employee_id = null, $client_id = null, $row_id = null, $hospital_id = null
		) {
			
			if ( !$employee_id ) $employee_id = "null";
			if ( !$client_id ) $client_id = "null";
			if ( !$row_id ) $row_id = "null";
			if ( !$hospital_id ) $hospital_id = "null";
			
			
			
			if ( !$this->DB->makeQuery(
				"INSERT INTO history ( status, table_name, description, employee_id, client_id, row_id, hospital_id, created_at ) 
				VALUES ( '$status', '$table', '$description', $employee_id, $client_id, $row_id, $hospital_id, now() )"
			) ) return mysqli_error( $this->DB->connection );
			
			return true;
			
		} // function. addLog

        /**
         * Добавление лога депозита
         */
        public function addDepositLog ( $type, $value, $client_id, $employee_id = null, $visit_id = null ) {

            if ( !$employee_id ) $employee_id = "null";
            if ( !$visit_id ) $visit_id = "null";



            if ( !$this->DB->makeQuery(
                "INSERT INTO deposit_log ( `type`, `value`, client_id, employee_id, datetime, visit_id ) 
				VALUES ( '$type', '$value', '$client_id', $employee_id, NOW(), $visit_id )"
            ) ) return false;

            return true;

        } // function. addDepositLog
		
		
		
		/**
		 * Вывод истории
		 *
		 * $table  str  Таблица
		 * $row_id  int  Id записи
		 * $employee_id  int  Id сотрудника
		 * $client_id  int  Id клиента
		 * $date_from  date  С какого числа осуществлять выборку
		 * $date_to  date  До какого числа осуществлять выборку
		 * $hospital_id  int  Id филиала
		 *
		 * return bool
		 */
		public function history_get (
			$table = null, $row_id = null, $employee_id = null, $client_id = null, $date_from = null, 
			$date_to = null, $hospital_id = null, $per_page = null, $page = null, $orderBy = null,
            $sort = null
		) {

            if ( !$per_page ) $per_page = 10;
            if ( !$page ) $page = 1;
            $page--;

            if ( ( $table == "employees" ) && $employee_id ) $table = "";


			
			/**
			 * Формирование запроса к БД
			 */
			
			$isFirstFilter = true;
			
			$query = "SELECT * FROM history";
			
			if ( $table || $row_id || $employee_id || $client_id || $date_from || $date_to || $hospital_id ) $query .= " WHERE";
			if ( $table ) {
				$query .= " table_name = '$table'";
				$isFirstFilter = false;
			}
			if ( $row_id ) {
				if ( !$isFirstFilter ) $query .= " AND";
				$query .= " row_id = '$row_id'";
				$isFirstFilter = false;
			}
			if ( $employee_id ) {
				if ( !$isFirstFilter ) $query .= " AND";
				$query .= " employee_id = '$employee_id'";
				$isFirstFilter = false;
			}
			if ( $client_id ) {
				if ( !$isFirstFilter ) $query .= " AND";
				$query .= " client_id = '$client_id'";
				$isFirstFilter = false;
			}
			if ( $date_from && $date_to ) {
				if ( !$isFirstFilter ) $query .= " AND";
				$query .= " date_format( created_at, '%Y-%m-%d' ) BETWEEN 
					date_format( '$date_from', '%Y-%m-%d' ) AND date_format( '$date_to', '%Y-%m-%d' )";
				$isFirstFilter = false;
			} else {
				if ( $date_from ) {
					if ( !$isFirstFilter ) $query .= " AND";
					$query .= " date_format( created_at, '%Y-%m-%d' ) >= date_format( '$date_from', '%Y-%m-%d' )";
					$isFirstFilter = false;
				}
				if ( $date_to ) {
					if ( !$isFirstFilter ) $query .= " AND";
					$query .= " date_format( created_at, '%Y-%m-%d' ) < date_format( '$date_to', '%Y-%m-%d' )";
					$isFirstFilter = false;
				}
			} // if. $date_from && $date_to
			if ( $hospital_id ) {
				if ( !$isFirstFilter ) $query .= " AND";
				$query .= " hospital_id = '$hospital_id'";
				$isFirstFilter = false;
			}

            /**
             * Получение кол-ва страниц
             */
            $queryPages = substr( $query, 9 );
            $queryPages = "SELECT COUNT( * ) " . $queryPages;
            $pagesCount = (int) mysqli_fetch_array( $this->DB->makeQuery( $queryPages ) )[ 0 ];

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }

            $query .= " LIMIT " . $page * $per_page . ", $per_page";
			
			
			
			/**
			 * Получение истории
			 */
			
			$history_return = [];
			
			$logs = $this->DB->makeQuery ( $query );
			
			foreach ( $logs as $log ) {

                $log[ "pages_count" ] = $pagesCount;
				$history_return[] = $log;
				
			} // foreach. $logs as $log
			
			
			
			return $history_return;
			
		} // function. history_get

		
	} // class. History
	
	
	
	$History = new History;