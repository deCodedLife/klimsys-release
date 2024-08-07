<?php
	
	/**
	 * @file
	 * Управление штрафами
	 */
	
	
	
	class Fines {
		
		/**
		 * Модуль базы данных
		 */
		private $DB = null;
		
		
		
		function __construct() {
			
			$this->DB = new DB;
			
		} // function. __construct

		/**
		 * Создание штрафа
		 *
		 * Параметры функции
		 * *$employee_id	int		Id сотрудника
		 * *$amount			float	Cумма
		 * $comment			str		Комментарий
		 *
		 * return int|bool
		 */
		public function addFine ( 
			$author_id,
			$employee_id,
			$amount,
			$comment = null
		) {

			/**
			* Проверка входных данных
			*/

			if ( !$comment ) $comment = "null";
			
			/**
			 * Формирование и отправка запроса на создание штрафа
			 */

            $query = "INSERT INTO fine ( employee_id, author_id, amount, comment, created_at ) VALUES ( $employee_id, $author_id, $amount, '$comment', NOW() )";
            if ( !$this->DB->makeQuery ( $query ) ) return false;
			
			return mysqli_insert_id( $this->DB->connection );
			
		} // function addFine

		/**
		 * Редактирование штрафа
		 *
		 * Параметры функции
		 * *$id			int		Id фтрафа
		 * $employee_id	int		Id сотрудника
		 * $amount		float	Cумма
		 * $comment		str		Комментарий
		 *
		 * return bool
		 */
		public function changeFine ( 
            $id, 
            $employee_id = null, 
            $amount = null, 
            $comment = null 
        ) {
			
			/**
			 * Проверка переменных
			 */
			
			if (
				!$employee_id &&
				!$amount &&
				!$comment
			) return true;
            
			/**
			 * Формирование и отправка запроса на редактирование услуги
			 */

			$isFirstArg = false;
				
			$query = "UPDATE fine SET";
				
			if ( $employee_id ) {

				if ( $isFirstArg ) $query .= ",";
				$query .= " employee_id = $employee_id";
				$isFirstArg = true;
            
			}
			if ( $amount ) {

				if ( $isFirstArg) $query .= ",";
				$query .= " amount = $amount";
				$isFirstArg = true;
            
            }
			if ( $comment ) {

				if ( $isFirstArg ) $query .= ",";
				$query .= " comment = '$comment'";
				$isFirstArg = true;

            }
            
			/**
			 * Отправка запроса к базе данных на редактирование штрафа
			 */
				
			$query .= " WHERE id = $id";
			if ( !$this->DB->makeQuery ( $query ) ) return false;
			
			return true;
			
		} // function. changeFine
        
        /**
		 * Вывод штрафов
		 *
		 * Параметры функции
		 * $id         int Id штрафа
		 * $employee_id int Id сотрудника
		 *
		 * return bool
		 */
		public function getFines ( $id = null, $employee_id = null, $orderBy = null, $sort = null ) {

			$fines_return = [];
            
			/**
			 * Формирование и отправка запроса на получения списка штрафов
			 */
			
			$query = "SELECT * FROM fine WHERE id != 0";
			if ( $id ) $query .= " AND id = $id";
			if ( $employee_id ) $query .= " AND employee_id = $employee_id";

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }

			/**
			 * Получение списка штрафов
			 */
			
			$fines = $this->DB->makeQuery ( $query );
			if ( !$fines ) return false;
			
			/**
			 * Формирование массива штрафов
			 */

			foreach ( $fines as $fine ) {
				
				$fines_return[] = [
					"id" => (int) $fine[ "id" ],
					"employee_id" => (int) $fine[ "employee_id" ],
					"author_id" => (int) $fine[ "author_id" ],
					"amount" => $fine[ "amount" ],
					"comment" => $fine[ "comment" ],
					"created_at" => $fine[ "created_at" ]
				];
				
			} // foreach. $fines as $fine
			
			return $fines_return;

		} // function getFines

		/**
		 * Удаление штрафа
		 *
		 * Параметры функции
		 * *$id int Id фтрафа
		 *
		 * return bool
		 */
		public function removeFine ( $id ) {
			
			/**
			 * Формирование и отправка запроса на удаление штрафа
			 */

			$query = "DELETE FROM fine WHERE id = $id";
			if ( !$this->DB->makeQuery ( $query ) ) return false;
			
			return true;
			
		} // function removeFine
        

	} // class Fines
	
	$Fines = new Fines;
        
?>