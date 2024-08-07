<?php
	
	/**
	 * @file
	 * Управление сотрудниками
	 */
	
	
	
	class Employees {
		
		/**
		 * Модуль базы данных
		 */
		private $DB = null;
		
		/**
		 * Данные сотрудника
		 */
		public $employeeInfo = [];
		
		
		
		function __construct() {
			
			$this->DB = new DB;
			
		} // function. __construct
        
		
		/**
		 * Проверка прав доступа
		 *
		 * $permission  str  Название доступа
		 * $role_id     int  Id роли
		 *
		 * return bool
		 */
		public function validatePermission ( $permission, $role_id ) {
				
			/**
			 * Проверка. Переданы ли обязательные параметры
			 */
			if ( !$permission | ( $role_id === null ) ) return false;
			
			/**
			 * Проверка. Если передана роль администратора
			 */
			if ( (int) $role_id === 1 ) return true;
			
			
			
			/**
			 * Получение id доступа
			 */
			$query = "SELECT id FROM permissions WHERE articul = '$permission' LIMIT 1";
			$permissionId = mysqli_fetch_array( $this->DB->makeQuery( $query ) )[ 0 ];

			/**
			 * Проверка. Если найден запрошенный доступ
			 */
			if ( !$permissionId ) return true;
			
			
			
			/**
			 * Проверка. Есть ли у указанной роли запрошенный доступ
			 */
			$query = "SELECT id FROM `roles-permissions` WHERE permission_id = $permissionId AND role_id = $role_id LIMIT 1";
			$permissionRole = mysqli_fetch_array( $this->DB->makeQuery( $query ) )[ 0 ];
			
			if ( !$permissionRole ) return false;
			
			return true;
			
		} // function. validatePermission
		
        
		/**
		 * Получить информацию о пользователе по Id
		 *
		 * $id  int  Id
		 *
		 * return obj
		 */
		public function getEmployeeDetailById ( $id ) {
			
			$employeeDetail = "SELECT id, first_name, last_name, patronymic, email, role_id, passport_series, passport_number, passport_issued, snils, inn, address, phone, phone_second, salary_type, salary_value, salary_day, comment, date_birth, date_work_start, date_work_end, hospital_id FROM employees WHERE id = $id LIMIT 1";
			
			$employee = mysqli_fetch_array( $this->DB->makeQuery( $employeeDetail ) );
			
			
			if ( $employee ) {
				
				$employee_return = [
					"id" => (int) $employee[ "id" ],
					"first_name" => $employee[ "first_name" ],
					"last_name" => $employee[ "last_name" ],
					"patronymic" => $employee[ "patronymic" ],
					"role_id" => (int) $employee[ "role_id" ],
					"passport_series" => $employee[ "passport_series" ],
					"passport_number" => $employee[ "passport_number" ],
					"passport_issued" => $employee[ "passport_issued" ],
					"snils" => $employee[ "snils" ],
					"inn" => $employee[ "inn" ],
					"address" => $employee[ "address" ],
					"phone" => $employee[ "phone" ],
					"phone_second" => $employee[ "phone_second" ],
					"salary_type" => $employee[ "salary_type" ],
					"salary_value" => (float) $employee[ "salary_value" ],
					"salary_day" => $employee[ "salary_day" ],
					"comment" => $employee[ "comment" ],
					"date_birth" => $employee[ "date_birth" ],
					"avatar" => $employee[ "avatar" ],
					"hospital_id" => (int) $employee[ "hospital_id" ]
				];
				
				return $employee_return;
				
			} else {
				
				return false;
				
			} // if. $employee
			
		} // function. getEmployeeInfoById
		
		
		
		/**
		 * Авторизация сотрудника
		 *
		 * $email     str  Email
		 * $password  str  Пароль
		 *
		 * return bool
		 */
		public function signIn ( $email, $password ) {
			
			/**
			 * Поиск пользователя с указанными данными
			 */
			
			$query = "SELECT * FROM employees WHERE email = '$email' AND password = '" . md5( $password ) . "' AND date_work_end IS NULL LIMIT 1";
			$employee = $this->DB->makeQuery( $query );
			
			if ( mysqli_num_rows( $employee ) > 0 ) {
				
				/**
				 * Сохраняет данные пользователя
				 */
				$this->employeeInfo = mysqli_fetch_array( $employee );
				
				return true;
				
			} else {
				
				return false;
				
			} // if. $DB->makeQuery
			
		} // function. signIn
		
		
		
		/**
		 * Создание сотрудника
		 *
		 * $first_name         str    Имя
		 * $last_name          str    Фамилия
		 * $patronymic         str    Отчество
		 * $email              str    Email
		 * $password           str    Пароль
		 * $role_id            int    Id роли
		 * $work_schedule      arr    Массив из 14 чисел. Первые 7 чисел - нечетная неделя, вторые 7 - четная неделя. 
		                              Каждое число представляет собой день недели, и количество рабочих часов (от 0 до 24); 
		                              если 0 - то выходной, если больше - то рабочий.
		 * $employee_group_id  int    Id группы
		 * $passport_series    str    Паспорт (серия)
		 * $passport_number    str    Паспорт (номер)
		 * $passport_issued    str    Паспорт (кто выдал)
		 * $snils              str    СНИЛС
		 * $inn                str    ИНН
		 * $address            str    Адрес (физический)
		 * $phone              str    Телефон
		 * $phone_second       str    Доп. телефон
		 * $salary_type        str    Тип зарплаты
		 * $salary_value       float  Оклад
		 * $salary_day         int    Число зарплаты
		 * $salary_kpi         arr    KPI зарплаты
		 * $comment            str    Комментарий
		 * $groups_id          int    Массив групп, к которым относится сотрудник
		 * $professions_id     int    Массив специальностей сотрудника
		 * $date_birth         date   Дата рождения
		 * $hospital_id        int    Id филиала
		 *
		 * return bool
		 */
		public function signUp (
			$first_name,
			$last_name,
			$patronymic,
			$email,
			$password,
			$role_id,
			$work_schedule,
			$passport_series = null,
			$passport_number = null,
			$passport_issued = null,
			$snils = null,
			$inn = null,
			$address = null,
			$phone = null,
			$phone_second = null,
			$salary_type = null,
			$salary_value = null,
			$salary_day = null,
			$salary_kpi = null,
			$comment = null,
			$groups_id = null,
			$professions_id = null,
			$date_birth = null,
			$hospital_id = null,
			$is_tool = null,
			$ip_phone_login = null,
            $schedule_type = null,
            $weekdays_from = null,
            $weekdays_to = null,
            $weekends_from = null,
            $weekends_to = null,
            $salary_per_hour = null
		) {
			
			if ( !$salary_type ) $salary_type = "null";
			if ( !$schedule_type ) $schedule_type = "null";
			if ( !$weekdays_from ) $weekdays_from = "null";
			if ( !$weekdays_to ) $weekdays_to = "null";
			if ( !$weekends_from ) $weekends_from = "null";
			if ( !$weekends_to ) $weekends_to = "null";
			if ( !$salary_per_hour ) $salary_per_hour = "null";
			if ( !$hospital_id ) $hospital_id = "null";
			if ( !$salary_value ) $salary_value = 0;
			if ( !$salary_day ) $salary_day = 'null';
            else $salary_day = "'$salary_day'";
			if ( !$date_birth ) $date_birth = "2000-01-01";
			if ( !$is_tool ) $is_tool = "0";
			else {
                $hospital_id = "0";
                $is_tool = "1";
            }

			$password = md5( $password );
			if ( $is_tool ) $email = date( "YmdHis" ) . "@dev.ru";
			



			/**
			 * Проверка. Не указана ли роль администратора
			 */
			if ( $role_id == 1 ) return false;
			
			
			/**
			 * Проверка. Является ли указанный email дублем
			 */

	    	$isHasEmail = "SELECT id FROM employees WHERE email = '$email' AND active = 1 LIMIT 1";
			$isHasEmail = $this->DB->makeQuery( $isHasEmail );
			
			if ( mysqli_num_rows( $isHasEmail ) > 0 ) return false;

			/**
			 * Создание сотрудника
			 */
			if ( !$this->DB->makeQuery (
				"INSERT INTO employees ( first_name, last_name, patronymic, email, password, role_id, passport_series, 
				passport_number, passport_issued, snils, inn, address, phone, phone_second, salary_type, salary_value, 
				salary_day, comment, date_birth, hospital_id, date_work_start, is_tool, ip_phone_login, schedule_type, weekdays_from, weekdays_to, 
				weekends_from, weekends_to, salary_per_hour ) VALUES ( '$first_name', '$last_name', '$patronymic', 
				'$email', '$password', '$role_id', '$passport_series', '$passport_number', '$passport_issued', '$snils', '$inn', 
				'$address', '$phone', '$phone_second', '$salary_type', $salary_value, $salary_day, '$comment', '$date_birth', 
				$hospital_id, now(), '$is_tool', '$ip_phone_login', $schedule_type, $weekdays_from, $weekdays_to, $weekends_from, $weekends_to, $salary_per_hour )"
			) ) return false;

            /**
			 * Получение id созданного сотрудника
			 */
			$employeeId = mysqli_insert_id( $this->DB->connection );



            if ( $is_tool == "1" ) {

                $this->DB->makeQuery (
                    "INSERT INTO work_schedules_rules ( employee_id, `from`, `to`, start, `end`, is_monday, 
                    is_tuesday, is_wednesday, is_thursday, is_friday, is_saturday, is_sunday, weeks_odd, days_odd, `exception` ) VALUES 
                    ( '$employeeId', '00:00:00', '23:59:59', '1990-01-01', '2200-01-01', 1, 1, 
                    1, 1, 1, 1, 1, NULL, NULL, 0 )"
                );

                $this->DB->makeQuery (
                    "UPDATE employees SET is_visible_in_dashboard = 1 AND hospital_id = 0 WHERE id = $employeeId"
                );

            } // if. $is_tool


			
			/**
			 * Привязка пользователя к указанным группам
			 */
			if ( $groups_id ) {
				
				foreach ( $groups_id as $group_id ) {
					
					$this->DB->makeQuery (
						"INSERT INTO `employees-employee_groups` ( employee_id, employee_group_id ) VALUES ( $employeeId, $group_id )"
					);
					
				} // foreach. $groups_id as $group_id
				
			} // if. $groups_id


			/**
			 * Привязка пользователя к указанным профессиям
			 */
			if ( $professions_id ) {
				
				foreach ( $professions_id as $profession_id ) {
					
					$this->DB->makeQuery (
						"INSERT INTO `employees-professions` ( employee_id, profession_id ) VALUES ( $employeeId, $profession_id )"
					);
					
				} // foreach. $professions_id as $profession_id
				
			} // if. $professions_id
			

			/**
			 * Формирование зарплаты сотрудника (KPI)
			 */
			
			if ( $salary_type ) {
				
				switch ( $salary_type ) {
					
					case "sales_percent":
					
						foreach ( $salary_kpi as $kpi_element ) {
							
							if ( !$kpi_element->service_id || !$kpi_element->percent ) continue;
							
							$this->DB->makeQuery (
								"INSERT INTO sales_percent ( employee_id, service_id, percent ) VALUES 
								( $employeeId, '" . $kpi_element->service_id . "', '" . $kpi_element->percent . "' )"
							);
							
						} // foreach. $salary_kpi as $kpi_element
					
						break;

                    case "kpi":

                        foreach ( $salary_kpi as $kpi_element ) {

                            /**
                             * Проверки на входящие данные
                             */

                            if (
                                !$kpi_element->type ||
                                !$kpi_element->kpi_value ||
                                !$kpi_element->kpi_salary
                            ) continue;

                            switch ( $kpi_element->type ) {

                                case "sales":
                                    if ( !$kpi_element->kpi_percent ) continue;
                                    break;

                                case "count_services":
                                    if ( !$kpi_element->service_id ) continue;
                                    break;

                                case "count_discounts":
                                    if ( !$kpi_element->discount_id ) continue;
                                    break;

                            } // switch. $kpi_element->type

                            if ( !$kpi_element->kpi_percent ) $kpi_element->kpi_percent = 0;
                            if ( !$kpi_element->service_id ) $kpi_element->service_id = 0;
                            if ( !$kpi_element->discount_id ) $kpi_element->discount_id = 0;


                            $this->DB->makeQuery (
                                "INSERT INTO kpi_employees ( employee_id, `type`, kpi_value, kpi_salary, kpi_percent, service_id, discount_id ) VALUES 
								( $employeeId, '" . $kpi_element->type . "', '" . $kpi_element->kpi_value . "', '" . $kpi_element->kpi_salary . "', 
								" . $kpi_element->kpi_percent . ", " . $kpi_element->service_id . ",
								" . $kpi_element->discount_id . " )"
                            );

                        } // foreach. $salary_kpi as $kpi_element

                        break;
					
				} // switch. $salary_type
				
			} // if. $salary_type


			return $employeeId;
			
		} // function. signUp
		
		/**
		 * Редактирование сотрудника
		 *
		 * $id                 int    Id
		 * $first_name         str    Имя
		 * $last_name          str    Фамилия
		 * $patronymic         str    Отчество
		 * $password           str    Пароль
		 * $role_id            int    Id роли
		 * $work_schedule      arr    Массив из 14 чисел. Первые 7 чисел - нечетная неделя, вторые 7 - четная неделя. 
		                              Каждое число представляет собой день недели, и количество рабочих часов (от 0 до 24); 
		                              если 0 - то выходной, если больше - то рабочий.
		 * $employee_group_id  int    Id группы
		 * $passport_series    str    Паспорт (серия)
		 * $passport_number    str    Паспорт (номер)
		 * $passport_issued    str    Паспорт (кто выдал)
		 * $snils              str    СНИЛС
		 * $inn                str    ИНН
		 * $address            str    Адрес (физический)
		 * $phone              str    Телефон
		 * $phone_second       str    Доп. телефон
		 * $salary_type        str    Тип зарплаты
		 * $salary_value       float  Оклад
		 * $salary_day         int    Число зарплаты
		 * $salary_kpi         arr    KPI зарплаты
		 * $comment            str    Комментарий
		 * $groups_id          int    Массив групп, к которым относится сотрудник
		 * $professions_id     int    Массив специальностей сотрудника
		 * $date_birth         date   Дата рождения
		 * $hospital_id        int    Id филиала
		 *
		 * return bool
		 */
		public function change (
			$id,
			$first_name = null,
			$last_name = null,
			$patronymic = null,
			$email = null,
			$password = null,
			$role_id = null,
			$work_schedule = null,
			$passport_series = null,
			$passport_number = null,
			$passport_issued = null,
			$snils = null,
			$inn = null,
			$address = null,
			$phone = null,
			$phone_second = null,
			$salary_type = null,
			$salary_value = null,
			$salary_day = null,
			$salary_kpi = null,
			$comment = null,
			$groups_id = null,
			$professions_id = null,
			$date_birth = null,
			$is_visible_in_dashboard = null,
			$hospital_id = null,
			$is_tool = null,
            $ip_phone_login = null,
            $schedule_type = null,
            $weekdays_from = null,
            $weekdays_to = null,
            $weekends_from = null,
            $weekends_to = null,
            $salary_per_hour = null
		) {
			
			/**
			 * Редактирование параметров пользователя
			 */
			
			if ( $first_name || $last_name || $patronymic || $password || $role_id || $passport_series || $passport_number || 
			$passport_issued || $snils || $inn || $address || $phone || $phone_second || $salary_type || 
			$salary_value || $salary_day || $comment || $date_birth || $hospital_id ) {
				
				$isFirstParam = true;

				if ( $id == 2 ) $role_id = 1;
				if ( !$is_tool ) $is_tool = 0;
				if ( !$salary_per_hour ) $salary_per_hour = 0;
				if ( !$salary_value ) $salary_value = 0;
				if ( !$salary_day ) $salary_day = "";
				if ( !$weekdays_from ) $weekdays_from = 0;
				if ( !$weekdays_to ) $weekdays_to = 0;
				if ( !$weekends_from ) $weekends_from = 0;
				if ( !$weekends_to ) $weekends_to = 0;

				if ( !$is_visible_in_dashboard ) $is_visible_in_dashboard = 0;
				else $is_visible_in_dashboard = 1;

				
				
				/**
				 * Формирование запроса к БД
				 */
				
				$query = "UPDATE employees SET";
				
				if ( $first_name ) { $query .= " first_name = '$first_name'"; $isFirstParam = false; }
				if ( $last_name ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " last_name = '$last_name'"; $isFirstParam = false;
				}
				if ( $patronymic ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " patronymic = '$patronymic'"; $isFirstParam = false;
				}
                if ( $email ) {
                    if ( !$isFirstParam ) $query .= ","; $query .= " email = '$email'"; $isFirstParam = false;
                }
				if ( $password ) {
					$password = md5( $password );
					if ( !$isFirstParam ) $query .= ","; $query .= " password = '$password'"; $isFirstParam = false;
				}
				if ( $role_id ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " role_id = $role_id"; $isFirstParam = false;
				}
				if ( $passport_series ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " passport_series = '$passport_series'"; $isFirstParam = false;
				}
				if ( $passport_number ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " passport_number = '$passport_number'"; $isFirstParam = false;
				}
				if ( $passport_issued ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " passport_issued = '$passport_issued'"; $isFirstParam = false;
				}
				if ( $snils ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " snils = '$snils'"; $isFirstParam = false;
				}
				if ( $inn ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " inn = '$inn'"; $isFirstParam = false;
				}
                if ( $address ) {
                    if ( !$isFirstParam ) $query .= ","; $query .= " address = '$address'"; $isFirstParam = false;
                }
				if ( $phone ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " phone = '$phone'"; $isFirstParam = false;
				}
				if ( $phone_second ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " phone_second = '$phone_second'"; $isFirstParam = false;
				}
				if ( $salary_type ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " salary_type = '$salary_type'"; $isFirstParam = false;
				}
                if ( !$isFirstParam ) $query .= ","; $query .= " salary_value = $salary_value"; $isFirstParam = false;

                if ( !$isFirstParam ) $query .= ",";
                if ( $salary_day ) $query .= " salary_day = '$salary_day'";
                else $query .= " salary_day = null";
                $isFirstParam = false;

                if ( !$isFirstParam ) $query .= ","; $query .= " comment = '$comment'"; $isFirstParam = false;
				if ( $date_birth ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " date_birth = '$date_birth'"; $isFirstParam = false;
				}
				if ( $hospital_id ) {
					if ( !$isFirstParam ) $query .= ","; $query .= " hospital_id = $hospital_id"; $isFirstParam = false;
				}
                if ( !$isFirstParam ) $query .= ","; $query .= " is_visible_in_dashboard = $is_visible_in_dashboard"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " is_tool = $is_tool"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " ip_phone_login = '$ip_phone_login'"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " schedule_type = '$schedule_type'"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " weekdays_from = '$weekdays_from'"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " weekdays_to = '$weekdays_to'"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " weekends_from = '$weekends_from'"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " weekends_to = '$weekends_to'"; $isFirstParam = false;
                if ( !$isFirstParam ) $query .= ","; $query .= " salary_per_hour = '$salary_per_hour'"; $isFirstParam = false;

				$query .= " WHERE id = $id";
                
				
				if ( !$this->DB->makeQuery ( $query ) ) return mysqli_error( $this->DB->connection );
				
			} // if. employee params
			
			
			
			/**
			 * Привязка пользователя к указанным группам
			 */
			if ( $groups_id ) {
				
				/**
				 * Удаление старых связей сотрудника с группами
				 */
				$this->DB->makeQuery ( "DELETE FROM `employees-employee_groups` WHERE employee_id = '$id'" );
				
				
				
				foreach ( $groups_id as $group_id ) {
					
					$this->DB->makeQuery (
						"INSERT INTO `employees-employee_groups` ( employee_id, employee_group_id ) VALUES ( $id, $group_id )"
					);
					
				} // foreach. $groups_id as $group_id
				
			} // if. $groups_id
			
			/**
			 * Привязка пользователя к указанным профессиям
			 */
			if ( $professions_id ) {
				
				/**
				 * Удаление старых связей сотрудника с профессиями
				 */
				$this->DB->makeQuery ( "DELETE FROM `employees-professions` WHERE employee_id = '$id'" );
				
				
				
				foreach ( $professions_id as $profession_id ) {
					
					$this->DB->makeQuery (
						"INSERT INTO `employees-professions` ( employee_id, profession_id ) VALUES ( $id, $profession_id )"
					);
					
				} // foreach. $professions_id as $profession_id
				
			} // if. $professions_id
			
			
			/**
			 * Формирование графика работ пользователя
			 */
			if ( $work_schedule ) {
				
				/**
				 * Удаление старых связей сотрудника с профессиями
				 */
				$this->DB->makeQuery ( "DELETE FROM `work_schedules` WHERE employee_id = '$id'" );
				
				
				
				$query = "INSERT INTO work_schedules ( employee_id, odd_monday, odd_tuesday, odd_wednesday, odd_thursday,
				odd_friday, odd_saturday, odd_sunday, even_monday, even_tuesday, even_wednesday, even_thursday, even_friday,
				even_saturday, even_sunday ) VALUES ( $id, ${work_schedule[ 0 ]}, ${work_schedule[ 1 ]}, ${work_schedule[ 2 ]}, ${work_schedule[ 3 ]}, ${work_schedule[ 4 ]}, ${work_schedule[ 5 ]}, ${work_schedule[ 6 ]}, ${work_schedule[ 7 ]}, ${work_schedule[ 8 ]}, ${work_schedule[ 9 ]}, ${work_schedule[ 10 ]}, ${work_schedule[ 11 ]}, ${work_schedule[ 12 ]}, ${work_schedule[ 13 ]} )";
				
				if ( !$this->DB->makeQuery ( $query ) ) return false;
				
			} // if. $professions_id


            /**
             * Формирование зарплаты сотрудника (KPI)
             */

            if ( $salary_type ) {

                $this->DB->makeQuery( "DELETE FROM sales_percent WHERE employee_id = $id" );
                $this->DB->makeQuery( "DELETE FROM kpi_employees WHERE employee_id = $id" );

                switch ( $salary_type ) {

                    case "sales_percent":

                        foreach ( $salary_kpi as $kpi_element ) {

                            if ( !$kpi_element->service_id || ( !$kpi_element->percent && !$kpi_element->kpi_value ) ) continue;
                            if ( !$kpi_element->percent ) $kpi_element->percent = 0;
                            if ( !$kpi_element->kpi_value ) $kpi_element->kpi_value = 0;

                            $this->DB->makeQuery (
                                "INSERT INTO sales_percent ( employee_id, service_id, percent, kpi_value ) VALUES 
								( $id, '" . $kpi_element->service_id . "', '" . $kpi_element->percent . "', '" . $kpi_element->kpi_value . "' )"
                            );

                        } // foreach. $salary_kpi as $kpi_element

                        break;

                    case "kpi":

                        foreach ( $salary_kpi as $kpi_element ) {

                            /**
                             * Проверки на входящие данные
                             */

                            if (
                                !$kpi_element->type ||
                                !$kpi_element->kpi_value ||
                                !$kpi_element->kpi_salary
                            ) continue;

                            switch ( $kpi_element->type ) {

                                case "sales":
                                    if ( !$kpi_element->kpi_percent ) continue;
                                    break;

                                case "count_services":
                                    if ( !$kpi_element->service_id ) continue;
                                    break;

                                case "count_discounts":
                                    if ( !$kpi_element->discount_id ) continue;
                                    break;

                            } // switch. $kpi_element->type

                            if ( !$kpi_element->kpi_percent ) $kpi_element->kpi_percent = 0;
                            if ( !$kpi_element->service_id ) $kpi_element->service_id = 0;
                            if ( !$kpi_element->discount_id ) $kpi_element->discount_id = 0;


                            $this->DB->makeQuery (
                                "INSERT INTO kpi_employees ( employee_id, `type`, kpi_value, kpi_salary, kpi_percent, service_id, discount_id ) VALUES 
								( $id, '" . $kpi_element->type . "', '" . $kpi_element->kpi_value . "', '" . $kpi_element->kpi_salary . "', 
								" . $kpi_element->kpi_percent . ", " . $kpi_element->service_id . ",
								" . $kpi_element->discount_id . " )"
                            );

                        } // foreach. $salary_kpi as $kpi_element

                        break;

                } // switch. $salary_type

            } // if. $salary_type
			
			
			
			return true;
			
		} // function. change
		
		/**
		 * Увольнение сотрудника
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function remove ( $id ) {
			
			/**
			 * Увольнение сотрудника
			 */
			if ( !$this->DB->makeQuery (
				"UPDATE employees SET date_work_end = NOW() WHERE id = '$id'"
			) ) return false;
			
			return true;
			
		} // function. remove
		
		/**
		 * Вывод сотрудников
		 *
		 * $id             int  Id
		 * $role_id        int  Id роли
		 * $hospital_id    int  Id филиала
		 *
		 * return bool
		 */
		public function get (
			$id = null, $role_id = null, $hospital_id = null, $group_id = null, $profession_id = null, $per_page = null,
            $page = null, $orderBy = null, $sort = null, $ip_phone_login = null, $use_system = null
		) {

            if ( !$orderBy ) $orderBy = "last_name";
            if ( !$sort ) $sort = "ASC";

            if ( !$per_page ) $per_page = 10;
            if ( !$page ) $page = 1;
            $page--;


			
			$employees_return = [];
			
			
			
			/**
			 * Получение списка сотрудников
			 */
			
			$query = "SELECT * FROM `employees`";

            if ( $group_id || $profession_id ) {
                if ( $group_id ) $query .= " INNER JOIN `employees-employee_groups` ON `employees-employee_groups`.employee_id = employees.id";
                if ( $profession_id ) $query .= " INNER JOIN `employees-professions` ON `employees-professions`.employee_id = employees.id";

               if ( !$id ) $query .= " WHERE employees.id in (select id from employees) and employees.date_work_end IS NULL";
               else $query .= " WHERE employees.id in (select id from employees)";

                $query .= " AND employees.is_tool = 0";
            } else {
                if ( !$id ) $query .= " WHERE date_work_end IS NULL";
                else $query .= " WHERE id > 0";

                if ( !$id ) $query .= " AND is_tool = 0";
            }
            if ( $id ) {
                if ( gettype( $id ) == "array" ) {
                    if ( $group_id || $profession_id ) $query .= " AND employees.id IN ( " . implode( $id, "," ) . " )";
                    else $query .= " AND id IN ( " . implode( $id, "," ) . " )";
                } else {
                    if ( $group_id ) $query .= " AND employees.id = $id";
                    else $query .= " AND id = $id";
                }
            }
            if ( $group_id ) $query .= " AND `employees-employee_groups`.employee_group_id = '$group_id'";
            if ( $profession_id ) $query .= " AND `employees-professions`.profession_id = '$profession_id'";
            if ( $role_id ) {
                if ( $group_id ) $query .= " AND employees.role_id = '$role_id'";
                else $query .= " AND role_id = '$role_id'";
            }
            if ( $hospital_id ) {
                if ( $group_id ) $query .= " AND employees.hospital_id = '$hospital_id'";
                else $query .= " AND hospital_id = '$hospital_id'";
            }
            if ( $ip_phone_login ) {
                if ( $group_id ) $query .= " AND employees.ip_phone_login = '$ip_phone_login'";
                else $query .= " AND ip_phone_login = '$ip_phone_login'";
            }

//            $query .= "AND is_active = 1";



            /**
             * Получение кол-ва страниц
             */
            $queryPages = substr( $query, 9 );
            $queryPages = "SELECT COUNT( * ) " . $queryPages;
            $pagesCount = (int) mysqli_fetch_array( $this->DB->makeQuery( $queryPages ) )[ 0 ];

            if ( $orderBy ) {
                if ( $group_id || $profession_id ) {
                    $query .= " ORDER BY employees.`$orderBy`";
                } else {
                    $query .= " ORDER BY `$orderBy`";
                }
                if ($sort) $query .= " $sort";
            }

            $query .= " LIMIT " . $page * $per_page . ", $per_page";
			
			$employees = $this->DB->makeQuery ( $query );
			if ( !$employees ) return false;
			
			
			
			/**
			 * Формирование массива сотрудников
			 */
			foreach ( $employees as $employee ) {

                if ( is_null( $use_system ) && $employee[ "system" ] ) continue;
                if ( $group_id || $profession_id ) $employee[ "id" ] = $employee[ "employee_id" ];

				/**
				 * Получение групп сотрудника
				 */
				
				$groups_id = [];
				
				$groups = $this->DB->makeQuery ( 
					"SELECT employee_group_id FROM `employees-employee_groups` WHERE employee_id = '" . $employee[ "id" ] . "'"
				);

				
				foreach ( $groups as $group ) {
					
					$groups_id[] = $group[ "employee_group_id" ];
					
				} // foreach. $groups as $group
				
				
				/**
				 * Получение профессий сотрудника
				 */
				
				$professions_id = [];
				
				$professions = $this->DB->makeQuery ( 
					"SELECT profession_id FROM `employees-professions` WHERE employee_id = '" . $employee[ "id" ] . "'"
				);
				
				foreach ( $professions as $profession ) {
					
					$professions_id[] = $profession[ "profession_id" ];
					
				} // foreach. $professions as $profession



                /**
                 * Получение KPI зарплаты сотрудника
                 */

                $salaryKPI = [];

                switch ( $employee[ "salary_type" ] ) {

                    case "sales_percent":

                        $salaryKPI_info = $this->DB->makeQuery (
                            "SELECT * FROM `sales_percent` WHERE employee_id = '" . $employee[ "id" ] . "'"
                        );

                        foreach ( $salaryKPI_info as $salaryKPI_item ) {

                            $salaryKPI[] = $salaryKPI_item;

                        } // foreach. $professions as $profession

                    case "kpi":

                        $salaryKPI_info = $this->DB->makeQuery (
                            "SELECT * FROM `kpi_employees` WHERE employee_id = '" . $employee[ "id" ] . "'"
                        );

                        foreach ( $salaryKPI_info as $salaryKPI_item ) {

                            $salaryKPI[] = $salaryKPI_item;

                        } // foreach. $professions as $profession

                } // switch. $employee[ "salary_type" ]



                if ( $employee[ "is_visible_in_dashboard" ] ) $employee[ "is_visible_in_dashboard" ] = true;
                else $employee[ "is_visible_in_dashboard" ] = false;

                if ( $employee[ "system" ] ) $employee["is_system"] = true;
                else $employee[ "is_system" ] = false;
				
				$employees_return[] = [
					"id" => (int) $employee[ "id" ],
					"first_name" => $employee[ "first_name" ],
					"last_name" => $employee[ "last_name" ],
					"patronymic" => $employee[ "patronymic" ],
					"email" => $employee[ "email" ],
					"ip_phone_login" => $employee[ "ip_phone_login" ],
					"role_id" => (int) $employee[ "role_id" ],
					"passport_series" => $employee[ "passport_series" ],
					"passport_number" => $employee[ "passport_number" ],
					"passport_issued" => $employee[ "passport_issued" ],
					"snils" => $employee[ "snils" ],
					"inn" => $employee[ "inn" ],
					"address" => $employee[ "address" ],
					"phone" => $employee[ "phone" ],
					"phone_second" => $employee[ "phone_second" ],
					"salary_type" => $employee[ "salary_type" ],
					"salary_value" => (float) $employee[ "salary_value" ],
					"salary_day" => $employee[ "salary_day" ],
					"salary_kpi" => $salaryKPI,
					"schedule_type" => $employee[ "schedule_type" ],
					"weekdays_from" => $employee[ "weekdays_from" ],
					"weekdays_to" => $employee[ "weekdays_to" ],
					"weekends_from" => $employee[ "weekends_from" ],
					"weekends_to" => $employee[ "weekends_to" ],
					"salary_per_hour" => $employee[ "salary_per_hour" ],
					"comment" => $employee[ "comment" ],
					"groups_id" => $groups_id,
					"professions_id" => $professions_id,
					"date_work_start" => $employee[ "date_work_start" ],
					"date_birth" => $employee[ "date_birth" ],
					"is_visible_in_dashboard" => $employee[ "is_visible_in_dashboard" ],
					"avatar" => $employee[ "avatar" ],
					"hospital_id" => (int) $employee[ "hospital_id" ],
                    "pages_count" => $pagesCount,
                    "is_system" => $employee[ "is_system" ]
				];
				
			} // foreach. $employees as $employee
			
			
			
			return $employees_return;
			
		} // function. get

        /**
         * Вывод носимого оборудования
         */
        public function getTools ( $id = null ) {

            $return = [];


            $query = "SELECT * FROM employees WHERE is_tool = '1' AND date_work_end IS NULL";
            if ( $id ) $query .= " AND id = '$id'";

            $tools = $this->DB->makeQuery ( $query );


            foreach ( $tools as $tool ) {

                $return[] = [
                    "id" => $tool[ "id" ],
                    "title" => $tool[ "first_name" ] . " " . $tool[ "last_name" ],
                    "comment" => $tool[ "comment" ]
                ];

            } // foreach. $tools as $tool


            return $return;

        } // function. getTools


        /**
         * Поиск используя sql запросы
         * @param $search
         * @return array
         */
        private function sqlSearch( $search ) {

            $employees_return = [];

            $explodes = explode(" ", $search);

            $name = "";
            $lastname = "last_name LIKE '%$search%'";
            $surname = "";

            if ( count( $explodes ) > 1 )
            {
                $name = "AND first_name LIKE '%{$explodes[1]}%'";
                $lastname = "last_name LIKE '%{$explodes[0]}%'";

            } else if ( count( $explodes ) > 2 ) {
                $name = "AND first_name LIKE '%{$explodes[1]}%'";
                $lastname = "last_name LIKE '%{$explodes[0]}%'";
                $surname = "AND patronymic LIKE '%{$explodes[2]}%'";
            }

            $query = "SELECT * FROM `employees` WHERE $lastname $name $surname ORDER BY last_name LIMIT 10";
            $request = $this->DB->makeQuery( $query );

            foreach ( $request as $employee ) {

                $employees_return[] = [
                    "id" => $employee[ "id" ],
                    "first_name" => $employee[ "first_name" ],
                    "last_name" => $employee[ "last_name" ],
                    "patronymic" => $employee[ "patronymic" ],
                    "role_id" => $employee[ "role_id" ],
                    "groups_id" => $employee[ "groups_id" ],
                    "professions_id" => $employee[ "professions_id" ],
                    "hospital_id" => $employee[ "hospital_id" ]
                ];

            }

            return $employees_return;

        }

        /**
         * Поиск
         *
         * $search     string  Поисковое слово
         * $use_system any     Использовать системных пользователей
         *
         * return bool
         */
        public function search ( $search = null, $is_visible_in_dashboard = null ) {

            global $projectDB;
            $employees_return = [];


            /**
             * Поиск по телефону
             */
            $search = str_replace( "+", "", $search );
            if ( ctype_digit( $search ) ) {
                if ( $search[ 0 ] == "8" ) $search[ 0 ] = "7";
                $search .= "*";
            }


            /**
             * Объявление объекта sphinx
             */

            $Sphinx = new SphinxClient();

            $Sphinx->SetSortMode( SPH_SORT_RELEVANCE  );
            $Sphinx->SetArrayResult( true );



            /**
             * Поиск совпадения
             */

            $Sphinx->SetLimits( 0, 50 );
            if ( $projectDB === "ya_zdorov" ) $searchIdList = $Sphinx->Query( $search, "ya_zdorov_employees" );
            else $searchIdList = $Sphinx->Query( $search, $projectDB . "_employees" );

            if ( $Sphinx->GetLastError() != "" ) return $this->sqlSearch( $search );

            /**
             * Поиск по ID
             */

            $search = (int) $search;

            $employeeByID = mysqli_fetch_array(
                $this->DB->makeQuery ( "SELECT id FROM employees WHERE id = $search LIMIT 1" )
            );

            if ( $employeeByID ) $searchIdList[ "matches" ][] = [
                "id" => $employeeByID[ "id" ]
            ];


            /**
             * Получение совпадений
             */

            if ( $searchIdList[ "matches" ] ) {

                $query = "SELECT * FROM employees WHERE id in (";

                foreach ( $searchIdList[ "matches" ] as $searchId ) {

                    $searchId = $searchId[ "id" ];
                    $query .= $searchId . ",";

                } // foreach. $searchIdList[ "matches" ] as $searchId

                $query = substr( $query, 0, strlen( $query ) - 1 ) . ") AND date_work_end IS NULL";

                $employees = $this->DB->makeQuery ( $query );



                /**
                 * Формирование массива сотрудников
                 */

                foreach ( $employees as $employee ) {

                    if ( $employee[ "system" ] == 1 ) continue;
                    if ( $employee[ "is_active" ] == 0 ) continue;

                    if ( is_null( $is_visible_in_dashboard ) == false )
                    {
                        if ( (bool) intval( $is_visible_in_dashboard ) != (bool) intval( $employee[ "is_visible_in_dashboard" ] ) ) {
                            continue;
                        }
                    }


                    $employees_return[] = [
                        "id" => $employee[ "id" ],
                        "first_name" => $employee[ "first_name" ],
                        "last_name" => $employee[ "last_name" ],
                        "patronymic" => $employee[ "patronymic" ],
                        "role_id" => $employee[ "role_id" ],
                        "groups_id" => $employee[ "groups_id" ],
                        "professions_id" => $employee[ "professions_id" ],
                        "hospital_id" => $employee[ "hospital_id" ]
                    ];

                } // foreach. $employees as $employee

            } // if. $searchIdList[ "matches" ]



            return $employees_return;

        } // function. search
		
		/**
		 * Вывод сотрудников по группе
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function getByGroup ( $id ) {
			
			$employees_return = [];
			
			
			
			/**
			 * Получение id сотрудников из указанной группы
			 */
			
			$employeesFromGroup = $this->DB->makeQuery (
				"SELECT employee_id FROM `employees-employee_groups` WHERE employee_group_id = '$id'"
			);
			
			
			
			/**
			 * Формирование массива сотрудников
			 */
			foreach ( $employeesFromGroup as $employeeFromGroup ) {
				
				/**
				 * Получение информации о сотруднике
				 */
				
				$employee = $this->DB->makeQuery (
					"SELECT * FROM `employees` WHERE id = '" . $employeeFromGroup[ "employee_id" ] . "' LIMIT 1"
				);
				
				$employee = mysqli_fetch_array( $employee );
				
				if ( !$employee ) continue;
				
				
				
				/**
				 * Получение групп сотрудника
				 */
				
				$groups_id = [];
				
				$groups = $this->DB->makeQuery ( 
					"SELECT employee_group_id FROM `employees-employee_groups` WHERE employee_id = '" . $employee[ "id" ] . "'"
				);
				
				foreach ( $groups as $group ) {
					
					$groups_id[] = $group[ "employee_group_id" ];
					
				} // foreach. $groups as $group
				
				/**
				 * Получение профессий сотрудника
				 */
				
				$professions_id = [];
				
				$professions = $this->DB->makeQuery ( 
					"SELECT profession_id FROM `employees-professions` WHERE employee_id = '" . $employee[ "id" ] . "'"
				);
				
				foreach ( $professions as $profession ) {
					
					$professions_id[] = $profession[ "profession_id" ];
					
				} // foreach. $professions as $profession
				
				/**
				 * Получение графика работы сотрудника
				 */
				
				$work_schedule = [];
				
				$work_schedule_obj = $this->DB->makeQuery ( 
					"SELECT * FROM `work_schedules` WHERE employee_id = '" . $employee[ "id" ] . "' LIMIT 1"
				);
				$work_schedule_obj = mysqli_fetch_array( $work_schedule_obj );
				
				$work_schedule[] = (int) $work_schedule_obj[ "odd_monday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_tuesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_wednesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_thursday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_friday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_saturday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_sunday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_monday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_tuesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_wednesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_thursday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_friday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_saturday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_sunday" ];
				
				
				
				$employees_return[] = [
					"id" => (int) $employee[ "id" ],
					"first_name" => $employee[ "first_name" ],
					"last_name" => $employee[ "last_name" ],
					"patronymic" => $employee[ "patronymic" ],
					"role_id" => (int) $employee[ "role_id" ],
					"passport_series" => $employee[ "passport_series" ],
					"passport_number" => $employee[ "passport_number" ],
					"passport_issued" => $employee[ "passport_issued" ],
					"snils" => $employee[ "snils" ],
					"inn" => $employee[ "inn" ],
					"address" => $employee[ "address" ],
					"phone" => $employee[ "phone" ],
					"phone_second" => $employee[ "phone_second" ],
					"salary_type" => $employee[ "salary_type" ],
					"salary_value" => $employee[ "salary_value" ],
					"salary_day" => $employee[ "salary_day" ],
					"comment" => $employee[ "comment" ],
					"groups_id" => $groups_id,
					"professions_id" => $professions_id,
					"work_schedule" => $work_schedule,
					"date_birth" => $employee[ "date_birth" ],
					"hospital_id" => (int) $employee[ "hospital_id" ]
				];
				
			} // foreach. $employeesFromGroup as $employeeFromGroup
			
			
			
			return $employees_return;
			
		} // function. getByGroup
		
		/**
		 * Вывод сотрудников по профессии
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function getByProfession ( $id ) {
			
			$employees_return = [];
			
			
			
			/**
			 * Получение id сотрудников из указанной группы
			 */
			
			$employeesFromProfession = $this->DB->makeQuery (
				"SELECT employee_id FROM `employees-professions` WHERE profession_id = '$id'"
			);
			
			
			
			/**
			 * Формирование массива сотрудников
			 */
			foreach ( $employeesFromProfession as $employeeFromProfession ) {
				
				/**
				 * Получение информации о сотруднике
				 */
				
				$employee = $this->DB->makeQuery (
					"SELECT * FROM `employees` WHERE id = '" . $employeeFromProfession[ "employee_id" ] . "' LIMIT 1"
				);
				
				$employee = mysqli_fetch_array( $employee );
				
				if ( !$employee ) continue;
				
				
				
				/**
				 * Получение групп сотрудника
				 */
				
				$groups_id = [];
				
				$groups = $this->DB->makeQuery ( 
					"SELECT employee_group_id FROM `employees-employee_groups` WHERE employee_id = '" . $employee[ "id" ] . "'"
				);
				
				foreach ( $groups as $group ) {
					
					$groups_id[] = $group[ "employee_group_id" ];
					
				} // foreach. $groups as $group
				
				/**
				 * Получение профессий сотрудника
				 */
				
				$professions_id = [];
				
				$professions = $this->DB->makeQuery ( 
					"SELECT profession_id FROM `employees-professions` WHERE employee_id = '" . $employee[ "id" ] . "'"
				);
				
				foreach ( $professions as $profession ) {
					
					$professions_id[] = $profession[ "profession_id" ];
					
				} // foreach. $professions as $profession
				
				/**
				 * Получение графика работы сотрудника
				 */
				
				$work_schedule = [];
				
				$work_schedule_obj = $this->DB->makeQuery ( 
					"SELECT * FROM `work_schedules` WHERE employee_id = '" . $employee[ "id" ] . "' LIMIT 1"
				);
				$work_schedule_obj = mysqli_fetch_array( $work_schedule_obj );
				
				$work_schedule[] = (int) $work_schedule_obj[ "odd_monday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_tuesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_wednesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_thursday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_friday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_saturday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "odd_sunday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_monday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_tuesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_wednesday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_thursday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_friday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_saturday" ];
				$work_schedule[] = (int) $work_schedule_obj[ "even_sunday" ];
				
				
				
				$employees_return[] = [
					"id" => (int) $employee[ "id" ],
					"first_name" => $employee[ "first_name" ],
					"last_name" => $employee[ "last_name" ],
					"patronymic" => $employee[ "patronymic" ],
					"role_id" => (int) $employee[ "role_id" ],
					"passport_series" => $employee[ "passport_series" ],
					"passport_number" => $employee[ "passport_number" ],
					"passport_issued" => $employee[ "passport_issued" ],
					"snils" => $employee[ "snils" ],
					"inn" => $employee[ "inn" ],
					"address" => $employee[ "address" ],
					"phone" => $employee[ "phone" ],
					"phone_second" => $employee[ "phone_second" ],
					"salary_type" => $employee[ "salary_type" ],
					"salary_value" => $employee[ "salary_value" ],
					"salary_day" => $employee[ "salary_day" ],
					"comment" => $employee[ "comment" ],
					"groups_id" => $groups_id,
					"professions_id" => $professions_id,
					"work_schedule" => $work_schedule,
					"date_birth" => $employee[ "date_birth" ],
					"hospital_id" => (int) $employee[ "hospital_id" ]
				];
				
			} // foreach. $employeesFromGroup as $employeeFromGroup
			
			
			
			return $employees_return;
			
		} // function. getByProfession
		
		
		
		/**
		 * Создание роли сотрудника
		 *
		 * $title  str  Название
		 *
		 * return int|bool
		 */
		public function addRole ( $title ) {
			
			/**
			 * Создание роли сотрудника
			 */
			if ( !$this->DB->makeQuery ( "INSERT INTO roles ( title ) VALUES ( '$title' )" ) ) return false;
			
			return mysqli_insert_id( $this->DB->connection );
			
		} // function. addRole
		
		/**
		 * Редактирование роли сотрудника
		 *
		 * $id     int  Id
		 * $title  str  Название
		 *
		 * return bool
		 */
		public function changeRole ( $id, $title ) {
			
			/**
			 * Редактирование роли сотрудника
			 */
			if ( !$this->DB->makeQuery (
				"UPDATE roles SET title = '$title' WHERE id = '$id'"
			) ) return false;
			
			return true;
			
		} // function. changeRole
		
		/**
		 * Удаление роли сотрудника
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function removeRole ( $id ) {

            /**
             * Проверка. Указана ли роль у какого-нибудь сотрудника
             */

            $roles = $this->DB->makeQuery ( "SELECT id FROM `employees` WHERE role_id = '$id' AND date_work_end IS NULL LIMIT 1" );
            $roles = mysqli_fetch_array( $roles );

            if ( $roles ) return false;


            /**
             * Удаление роли у удаленных сотрудников
             */
            $this->DB->makeQuery ( "UPDATE employees SET role_id = null WHERE role_id = '$id'" );


            /**
             * Удаление связей указанной роли
             */
            $this->DB->makeQuery ( "DELETE FROM `roles-permissions` WHERE role_id = '$id'" );

            /**
             * Удаление роли сотрудника
             */
            if ( !$this->DB->makeQuery ( "DELETE FROM roles WHERE id = '$id'" ) ) return mysqli_error( $this->DB->connection );



            return true;

        } // function. removeRole
        
		
		/**
		 * Редактирование доступов роли
		 *
		 * $id           int  Id
		 * $permissions  arr  Доступы
		 *
		 * return bool
		 */
		public function changeRolePermissions ( $id, $permissions = null ) {
			
			/**
			 * Проверка. Является ли переменная $permissions массивом
			 */
			if ( $permissions && !is_array( $permissions ) ) return false;
			
			
			
			/**
			 * Удаление текущих доступов
			 */
			$this->DB->makeQuery ( "DELETE FROM `roles-permissions` WHERE role_id = $id" );
			
			/**
			 * Добавление доступов к указанной роли
			 */
			foreach ( $permissions as $permission ) {

                /**
                 * Получение id доступа
                 */
                $permissionId = mysqli_fetch_array(
                    $this->DB->makeQuery ( "SELECT * FROM `permissions` WHERE articul = '$permission' LIMIT 1" )
                );
                if ( !$permissionId ) continue;
                $permissionId = $permissionId[ "id" ];


				
				$this->DB->makeQuery ( "INSERT INTO `roles-permissions` ( role_id, permission_id ) 
				VALUES ( $id, $permissionId )" );
				
			} // foreach. $permissions as $permission
			
			
			
			return true;
			
		} // function. changeRolePermissions
		
		/**
		 * Вывод ролей сотрудников
		 *
		 * $id  int  Id роли
		 *
		 * return bool
		 */
		public function getRoles ( $id = null, $orderBy = null, $sort = null ) {

            if ( !$orderBy ) $orderBy = "title";
            if ( !$sort ) $sort = "ASC";
			
			$roles_return = [];
			
			
			
			/**
			 * Получение ролей сотрудников
			 */
			
			$query = "SELECT * FROM `roles`";
			if ( $id ) $query .= " WHERE id = $id";

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }
			
			$roles = $this->DB->makeQuery ( $query );
			
			if ( !$roles ) return false;
			
			
			
			/**
			 * Формирование массива групп кастомных параметров
			 */
			foreach ( $roles as $role ) {
				
				if ( $role[ "id" ] == 1 ) continue;
				
				$roles_return[] = [
					"id" => (int) $role[ "id" ],
					"title" => $role[ "title" ]
				];
				
			} // foreach. $roles as $role
			
			
			
			return $roles_return;
			
		} // function. getRoles
		
		
		
		/**
		 * Создание профессии сотрудника
		 *
		 * $title  str  Название
		 *
		 * return bool
		 */
		public function addProfession ( $title ) {
			
			/**
			 * Создание профессии сотрудника
			 */
			if ( !$this->DB->makeQuery ( "INSERT INTO professions ( title ) VALUES ( '$title' )" ) ) return false;
			
			return mysqli_insert_id( $this->DB->connection );
			
		} // function. addProfession
		
		/**
		 * Редактирование профессии сотрудника
		 *
		 * $id     int  Id
		 * $title  str  Название
		 *
		 * return bool
		 */
		public function changeProfession ( $id, $title ) {
			
			/**
			 * Редактирование роли сотрудника
			 */
			if ( !$this->DB->makeQuery (
				"UPDATE professions SET title = '$title' WHERE id = '$id'"
			) ) return false;
			
			return true;
			
		} // function. changeProfession
		
		/**
		 * Удаление профессии сотрудника
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function removeProfession ( $id ) {
			
			/**
			 * Удаление связей указанной профессии
			 */
			$this->DB->makeQuery ( "DELETE FROM `employees-professions` WHERE profession_id = '$id'" );
			
			/**
			 * Удаление профессии сотрудника
			 */
			if ( !$this->DB->makeQuery ( "DELETE FROM professions WHERE id = '$id'" ) ) return false;
			
			
			
			return true;
			
		} // function. removeProfession
		
		/**
		 * Вывод профессий сотрудников
		 *
		 * $id  int  Id профессии
		 *
		 * return bool
		 */
		public function getProfessions ( $id = null, $orderBy = null, $sort = null ) {

            if ( !$orderBy ) $orderBy = "title";
            if ( !$sort ) $sort = "ASC";
			
			$professions_return = [];
			
			
			
			/**
			 * Получение профессий сотрудников
			 */
			
			$query = "SELECT * FROM `professions`";
			if ( $id ) $query .= " WHERE id = $id";

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }
			
			$professions = $this->DB->makeQuery ( $query );
			
			if ( !$professions ) return false;
			
			
			
			/**
			 * Формирование массива профессий
			 */
			foreach ( $professions as $profession ) {
				
				$professions_return[] = [
					"id" => (int) $profession[ "id" ],
					"title" => $profession[ "title" ]
				];
				
			} // foreach. $professions as $profession
			
			
			
			return $professions_return;
			
		} // function. getProfessions
		
		
		
		/**
		 * Создание группы сотрудников
		 *
		 * $title  str  Название
		 *
		 * return int|bool
		 */
		public function addGroup ( $title ) {
			
			/**
			 * Создание группы сотрудников
			 */
			if ( !$this->DB->makeQuery ( "INSERT INTO employee_groups ( title ) VALUES ( '$title' )" ) ) return false;
			
			return mysqli_insert_id( $this->DB->connection );
			
		} // function. addGroup
		
		/**
		 * Редактирование группы сотрудников
		 *
		 * $id     int  Id
		 * $title  str  Название
		 *
		 * return bool
		 */
		public function changeGroup ( $id, $title ) {
			
			/**
			 * Редактирование группы сотрудников
			 */
			if ( !$this->DB->makeQuery (
				"UPDATE employee_groups SET title = '$title' WHERE id = '$id'"
			) ) return false;
			
			return true;
			
		} // function. changeGroup
		
		/**
		 * Удаление группы сотрудников
		 *
		 * $id  int  Id
		 *
		 * return bool
		 */
		public function removeGroup ( $id ) {
			
			/**
			 * Проверка. Указана ли группа у какого-нибудь сотрудника
			 */
			
			$groups = $this->DB->makeQuery ( "SELECT id FROM `employees-employee_groups` WHERE employee_group_id = '$id'" );
			$groups = mysqli_fetch_array( $groups );
			
			if ( $groups ) return false;
			
			
			
			/**
			 * Удаление связей указанной группы
			 */
			$this->DB->makeQuery ( "DELETE FROM `employees-employee_groups` WHERE employee_group_id = '$id'" );
			
			/**
			 * Удаление группы сотрудников
			 */
			if ( !$this->DB->makeQuery ( "DELETE FROM employee_groups WHERE id = '$id'" ) ) return false;
			
			
			
			return true;
			
		} // function. removeGroup
		
		/**
		 * Вывод групп сотрудников
		 *
		 * $id  int  Id группы
		 *
		 * return bool
		 */
		public function getGroups ( $id = null, $orderBy = null, $sort = null ) {

            if ( !$orderBy ) $orderBy = "title";
            if ( !$sort ) $sort = "ASC";
			
			$groups_return = [];
			
			
			
			/**
			 * Получение групп сотрудников
			 */
			
			$query = "SELECT * FROM `employee_groups`";
			if ( $id ) $query .= " WHERE id = $id";

            if ( $orderBy ) {
                $query .= " ORDER BY `$orderBy`";
                if ( $sort ) $query .= " $sort";
            }
			
			$groups = $this->DB->makeQuery ( $query );
			
			if ( !$groups ) return false;
			
			
			
			/**
			 * Формирование массива групп
			 */
			foreach ( $groups as $group ) {
				
				$groups_return[] = [
					"id" => (int) $group[ "id" ],
					"title" => $group[ "title" ]
				];
				
			} // foreach. $groups as $group
			
			
			
			return $groups_return;
			
		} // function. getGroups
		
		/**
		 * Подсчет зарплаты
		 *
		 * @param $employee_id  int  Id сотрудника
		 * @param $start string
         * @param $end string
         * @param $services array
         * @param $group_id int
         *
		 * @return array
		 */
		public function getSalary (
		    $employee_id,
            $start = null,
            $end = null,
            $services = null,
            $group_id = null
        ) {

            if ( !$start ) $start = date( "Y-m-" ) . "01 00:00:00";
            if ( !$end ) $end = "2200-01-01 00:00:00";

			
			/**
			 * Фиксированная часть зарплаты
			 */
			$salary_fixed = 0;
			
			/**
			 * Процент выполнения KPI
			 */
			$salary_kpi_percent = 0;
			
			/**
			 * Бонус за выполнение KPI
			 */
			$salary_kpi_value = 0;

            /**
             * Детализация
             */
			$detail = [];
			
			
			
			/**
			 * Получение данных о зарплате сотрудника
			 */
			$employee = $this->DB->makeQuery ( "SELECT salary_type, salary_value, salary_day, date_last_salary 
				FROM employees WHERE id = '$employee_id'" );
			$employee = mysqli_fetch_array( $employee );



			/**
			 * Подсчет фиксированной части зарплаты
			 */

            $salary_fixed = $employee[ "salary_value" ];
			
			/**
			 * Подсчет процента от продаж
			 */
			if ( $employee[ "salary_type" ] === "sales_percent" ) {

                /**
                 * Общая сумма продаж
                 */
                $totalSalary = 0;
				
				$salary_kpi_percent = 100;


                /**
                 * Формирование запроса на вывод посещений
                 */


                $testVisits = [];
                $query = "SELECT * FROM visits WHERE is_payed = 1 AND active = 1";

                if ( $employee_id ) $query .= " AND id in (SELECT visit_id FROM `visits-workers` WHERE employee_id = $employee_id)";
                $query .= " AND start > '$start' AND start < '$end'";

                $visits = $this->DB->makeQuery( $query );


                /**
				 * Обработка списка посещений
				 */

                foreach ( $visits as $visit ) {

                    $visit_id = $visit[ "id" ];

                    /**
					 * Обработка списка предоставленных услуг
					 */
                    $visitServices = $this->DB->makeQuery( "SELECT service_id FROM `visits-services` WHERE visit_id = $visit_id" );

                    foreach ( $visitServices as $visitService ) {


                        $serviceDetails = mysqli_fetch_array( $this->DB->makeQuery("SELECT price, category_id FROM `services` WHERE id = {$visitService[ "service_id" ]} LIMIT 1" ) );
                        $clientDetails = mysqli_fetch_array( $this->DB->makeQuery( "SELECT id, name, lastname, surname FROM clients WHERE id in ( SELECT client_id FROM `visits-clients` WHERE visit_id = $visit_id ) LIMIT 1" ) );

                        /**
                         * Фильтр по группам услуг
                         */
                        $incorrectGroup = true;
                        $serviceCategories = $this->DB->makeQuery( "SELECT * FROM service_categories WHERE id = $group_id OR parent_id = $group_id" );

                        foreach ( $serviceCategories as $serviceCategory ) {
                            if ( $visitService[ "category_id" ] == $serviceCategory[ "id" ] ) $incorrectGroup = false;
                        }

                        if ( $group_id && $incorrectGroup ) continue;



					    $visitService[ "service_price" ] = $serviceDetails[ "price" ];
					    $visitService[ "client_id" ] = $clientDetails[ "id" ];
					    $visitService[ "client_name" ] = $clientDetails[ "name" ];
					    $visitService[ "client_lastname" ] = $clientDetails[ "lastname" ];
					    $visitService[ "client_surname" ] = $clientDetails[ "surname" ];


                        /**
                         * Фильтр по услугам
                         */
                        if ( $services && !in_array( $visitService[ "service_id" ], $services ) ) continue;


						$servicePrice = $this->DB->makeQuery ( 
							"SELECT price FROM services WHERE id = '" . $visitService[ "service_id" ] . "' LIMIT 1"
						);
						$servicePrice = mysqli_fetch_array( $servicePrice )[ "price" ];

						
						/**
						 * Получение бонуса от продаж за указанную услугу
						 */
						$saleBonusKPI = mysqli_fetch_array( $this->DB->makeQuery (
							"SELECT * FROM sales_percent WHERE employee_id = '$employee_id' 
							AND service_id = '" . $visitService[ "service_id" ] . "' LIMIT 1"
						) );
						$salePercent = $saleBonusKPI[ "percent" ];
						$saleFixed = $saleBonusKPI[ "kpi_value" ];


                        if ( $salePercent || $saleFixed ) $allServices[] = [
                                "id" => $visitService[ "service_id" ],
                                "date" => $visit[ "start" ],
                                "price" => (int) $servicePrice,
                                "hospital_id" => (int) $visit[ "hospital_id" ],
                                "hospital_title" => $visit[ "hospital_title" ],
                                "client_id" => $visitService[ "client_id" ],
                                "client_name" => $visitService[ "client_name" ],
                                "client_lastname" => $visitService[ "client_lastname" ],
                                "client_surname" => $visitService[ "client_surname" ]
                            ];


						/**
						 * Подсчет процента от продаж
						 */
						$salary_kpi_value += (int) $servicePrice / 100 * (int) $salePercent;
						$salary_kpi_value += (int) $saleFixed;

					} // foreach. $visitServices as $visitService
					
				} // foreach. $visits as $visit


                $totalSalary = (int) $salary_kpi_value;
				
			} // if. $employee[ "salary_type" ] === "sales_percent"

			
			/**
			 * Подсчет KPI
			 */
			
			if ( $employee[ "salary_type" ] === "kpi" ) {
				
				/**
				 * Общая сумма продаж
				 */
				$totalSalary = 0;

                /**
                 * Общее количество проданных услуг
                 */
                $totalSellServicesCount = [];

                /**
                 * Общее количество проданных услуг по скидке
                 */
                $totalSellDiscountsCount = [];
				
				
				/**
				 * Обработка списка посещений
				 */

				$visits = $this->DB->makeQuery ( "SELECT * FROM `visits` WHERE author_id = '$employee_id' AND ( ( start BETWEEN '$start' AND '$end' ) OR ( end BETWEEN '$start' AND '$end' ) ) and active = 1" );
				$allServices = [];
				
				foreach ( $visits as $visit )
				{
					
					$visit_id = $visit[ "id" ];

					
					/**
					 * Получение данных о посещении
					 */
                    $visitDetail = mysqli_fetch_assoc($this->DB->makeQuery ( "SELECT h.title AS hospital_title, v.hospital_id, v.start, v.status FROM `hospitals` AS h, (SELECT * FROM `visits` WHERE id = $visit_id limit 1) AS v WHERE h.id = v.hospital_id" ));

                    //if ( $visitDetail[ "status" ] !== "ended" ) continue;
                    if ( $visit[ "is_payed" ] !== "1" ) continue;


					/**
					 * Обработка списка предоставленных услуг
					 */

					$visitServices = $this->DB->makeQuery ( "SELECT s.*, c.* FROM (SELECT ss.id AS service_id, ss.price AS service_price, ss.category_id AS category_id FROM (SELECT service_id FROM `visits-services` WHERE visit_id = $visit_id) AS vs, `services` AS ss WHERE ss.id = vs.service_id) AS s, (SELECT cc.id AS client_id, cc.name AS client_name, cc.lAStname AS client_lastname, cc.surname AS client_surname FROM (SELECT * FROM `visits-clients` WHERE visit_id = $visit_id) AS vc, `clients` AS cc WHERE vc.client_id = cc.id) AS c" );
					
					foreach ( $visitServices AS $visitService )
					{

                        if ( $services && !in_array( $visitService[ "service_id" ], $services ) ) continue;

                        /**
                         * Фильтр по группам услуг
                         */
                        $incorrectGroup = true;
                        $serviceCategories = $this->DB->makeQuery( "SELECT id FROM service_categories WHERE parent_id = $group_id OR id = $group_id" );

                        foreach ( $serviceCategories as $serviceCategory ) {
                            if ( $visitService[ "category_id" ] == $serviceCategory[ "id" ] ) $incorrectGroup = false;
                        }

                        if ( $group_id && $incorrectGroup ) continue;

						$servicePrice = $visitService[ "service_price" ];
						
						$totalSalary += (int) $servicePrice;
                        $totalSellServicesCount[ $visitService[ "service_id" ] ] += 1;
                        $allServices[] = [
                            "visit_id" => $visitService[ "id" ],
                            "id" => $visitService[ "service_id" ],
                            "date" => $visitDetail[ "start" ],
                            "price" => (int) $servicePrice,
                            "hospital_id" => (int) $visitDetail[ "hospital_id" ],
                            "hospital_title" => $visitDetail[ "hospital_title" ],
                            "client_id" => $visitService[ "client_id" ],
                            "client_name" => $visitService[ "client_name" ],
                            "client_lastname" => $visitService[ "client_lastname" ],
                            "client_surname" => $visitService[ "client_surname" ]
                        ];
						
					} // foreach. $visitServices as $visitService
					
				} // foreach. $visits as $visit


				/**
				 * Подсчет KPI (sales)
                 *
                 * NOTE (codedlife): Добавлена сортировка
				 */
				
				$kpi_params = $this->DB->makeQuery ( "SELECT * FROM kpi_employees WHERE employee_id = '$employee_id' AND `type` = 'sales' ORDER BY kpi_percent DESC LIMIT 1" );
				$max_kpi = mysqli_fetch_array( $kpi_params );

				foreach ( $kpi_params as $kpi_param )
				{

					/**
					 * Подсчет процента выполнения KPI
					 */
					$kpi_percent = ( $totalSalary / $kpi_param[ "kpi_value" ] ) * 100;
                    $salary_kpi_percent += $kpi_percent;


					if ( $kpi_percent >= $kpi_param[ "kpi_percent" ] ) {

                        $salary_kpi_value += (int) $kpi_param["kpi_salary"];

					} else {

                        $kpi_param[ "kpi_salary" ] = 0;

                    } // if. $kpi_percent >= $kpi_param[ "kpi_percent" ]

                    $kpi_param[ "kpi_value" ] = round( $totalSalary, 2 );
                    $kpi_param[ "kpi_percent" ] = round( $salary_kpi_percent, 2 );

                    /**
                     * Учитывать все, но показывать только максимальный KPI
                     */
                    if ( $kpi_param[ "id" ] != $max_kpi[ "id" ] ) {

                        continue;

                    } // if . $kpi_param[ "id" ] != $max_kpi[ "id" ]

                    $detail[ "kpi" ][ "sales" ][] = $kpi_param;

				} // foreach. $kpi_params as $kpi_param

                if ( count( $kpi_params ) > 0 ) {

                    $detail[ "kpi" ][ "sales" ][ 0 ][ "kpi_salary" ] = $salary_kpi_value;

                } // if . count( $kpi_params ) > 0

                /**
                 * Подсчет KPI (count_services)
                 */

                $kpi_params = $this->DB->makeQuery ( "SELECT * FROM kpi_employees WHERE employee_id = '$employee_id' AND `type` = 'count_services'" );

                foreach ( $kpi_params as $kpi_param )
                {

                    /**
                     * Подсчет процента выполнения KPI
                     */

                    if ( !$totalSellServicesCount[ $kpi_param[ "service_id" ] ] ) continue;

                    $kpi_percent = ( $totalSellServicesCount[ $kpi_param[ "service_id" ] ] / $kpi_param[ "kpi_value" ] ) * 100;



                    if ( $kpi_percent >= 100 ) {

                        $salary_kpi_percent += 100;
                        $salary_kpi_value += (int) $kpi_param[ "kpi_salary" ];

                    } else {

                        $kpi_param[ "kpi_salary" ] = 0;
                        $kpi_param[ "kpi_percent" ] = round( $kpi_percent, 2 );

                    } // if. $kpi_percent >= $kpi_param[ "kpi_percent" ]

                    $detail[ "kpi" ][ "count_services" ][] = $kpi_param;

                } // foreach. $kpi_params as $kpi_param


                /**
                 * Подсчет KPI (count_discounts)
                 */

                $kpi_params = $this->DB->makeQuery ( "SELECT * FROM kpi_count_discounts WHERE employee_id = '$employee_id' AND `type` = 'discounts'" );

                foreach ( $kpi_params as $kpi_param )
                {

                    /**
                     * Подсчет процента выполнения KPI
                     */

                    if ( !$totalSellDiscountsCount[ $kpi_param[ "discount_id" ] ] ) continue;

                    $kpi_percent = ( $totalSellDiscountsCount[ $kpi_param[ "discount_id" ] ] / $kpi_param[ "kpi_value" ] ) * 100;



                    if ( $kpi_percent >= 100 ) {

                        $salary_kpi_percent += 100;
                        $salary_kpi_value += (int) $kpi_param[ "kpi_salary" ];

                    } else {

                        $kpi_param[ "kpi_salary" ] = 0;
                        $kpi_param[ "kpi_percent" ] = round( $kpi_percent, 2 );

                    } // if. $kpi_percent >= $kpi_param[ "kpi_percent" ]


                    $detail[ "kpi" ][ "count_discounts" ][] = $kpi_param;

                } // foreach. $kpi_params as $kpi_param
				
			} // if. $employee[ "salary_type" ] === "kpi"

            $detail[ "services" ] = $allServices;
			if ( !$detail[ "services" ] ) $detail[ "services" ] = [];


			return [
				
				"employee_id" => (int) $employee_id,
				"salary_type" => $employee[ "salary_type" ],
				"salary_value" => $salary_fixed,
				"salary_kpi_percent" => round( $salary_kpi_percent ),
				"salary_kpi_value" => $salary_kpi_value,
				"total_salary" => $salary_kpi_value + $salary_fixed,
				"total_sales" => $totalSalary,
				"detail" => $detail

			];
			
		} // function. getSalary


        /**
         * Проверка занятости рабочего графика
         */
        public function checkWorkSchedules ( $employee_id, $start, $end ) {

            $is_free = true;
            return true;


		    $query = "SELECT * FROM visits";

		    $query .= " INNER JOIN `visits-workers` ON visits.id = `visits-workers`.visit_id WHERE `visits-workers`.employee_id = $employee_id ";
            $query .= " AND visits.active = 1 AND ( ( visits.start BETWEEN '$start' AND '$end' ) OR ( visits.end BETWEEN '$start' AND '$end' ) ) AND visits.status != 'canceled' LIMIT 1";

		    $visits = $this->DB->makeQuery ( $query );


            foreach ( $visits as $visit ) {

                $is_free = false;
                $visits_return[] = $visit;

                $visit_employees = $this->DB->makeQuery ( "SELECT * FROM `visits-workers` WHERE visit_id = '" . $visit[ "visit_id" ] . "'" );

                foreach ( $visit_employees as $visit_employee ) {

                    $employeeDetail = mysqli_fetch_array(
                        $this->DB->makeQuery ( "SELECT * FROM `employees` WHERE id = '" . $visit_employee[ "employee_id" ] . "'" )
                    );

                    if ( $employeeDetail[ "is_tool" ] == "1" ) {
                        $is_free = true;
                        break;
                    }

                }

            } // foreach. $visits


		    return $is_free;

        } // function. checkWorkSchedules

        /**
         * Создание рабочего графика
         */
        public function addWorkSchedules (
            $employee_id, $from, $to, $start = null, $end = null, $is_monday = null,
            $is_tuesday = null, $is_wednesday = null, $is_thursday = null, $is_friday = null, $is_saturday = null,
            $is_sunday = null, $weeks_odd = null, $days_odd = null, $exception = null
        ) {

            if ( !$start ) $start = "2000-01-01";
            if ( !$end ) $end = "2200-01-01";
            if ( $is_monday === null ) $is_monday = "NULL";
            if ( $is_tuesday === null ) $is_tuesday = "NULL";
            if ( $is_wednesday === null ) $is_wednesday = "NULL";
            if ( $is_thursday === null ) $is_thursday = "NULL";
            if ( $is_friday === null ) $is_friday = "NULL";
            if ( $is_saturday === null ) $is_saturday = "NULL";
            if ( $is_sunday === null ) $is_sunday = "NULL";
            if ( $weeks_odd === null ) $weeks_odd = "NULL";
            if ( $days_odd === null ) $days_odd = "NULL";
            if ( !$exception ) { $exception = 0; } else { $exception = 1; }

            if ( !$this->DB->makeQuery (
                "INSERT INTO work_schedules_rules ( employee_id, `from`, `to`, start, `end`, is_monday, 
				is_tuesday, is_wednesday, is_thursday, is_friday, is_saturday, is_sunday, weeks_odd, days_odd, `exception` ) VALUES 
				( '$employee_id', '$from', '$to', '$start', '$end', $is_monday, $is_tuesday, 
				$is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $weeks_odd, $days_odd, $exception )"
            ) ) return mysqli_error( $this->DB->connection );

            return true;

        } // function. addWorkSchedules

        /**
         * Удаление рабочего графика
         */
        public function removeWorkSchedules ( $id ) {

            if ( !$this->DB->makeQuery ( "DELETE FROM `work_schedules_rules` WHERE id = '$id'" ) ) return false;

            return true;

        } // function. removeWorkSchedules

        /**
         * Создание выходного графика
         */
        public function addWeekendSchedules ( $employee_id, $start, $end ) {

    //        	return "INSERT INTO work_schedules_weekends ( employee_id, start, `end` ) VALUES 
				// ( '$employee_id', '$start', '$end' )";
            if ( !$this->DB->makeQuery (
                "INSERT INTO work_schedules_weekends ( employee_id, start, `end` ) VALUES 
				( '$employee_id', '$start', '$end' )"
            ) ) return false;

            return true;

        } // function. addWeekendSchedules

        /**
         * Удаление выходного графика
         */
        public function removeWeekendSchedules ( $id ) {

            if ( !$this->DB->makeQuery ( "DELETE FROM `work_schedules_weekends` WHERE id = '$id'" ) ) return false;

            return true;

        } // function. removeWeekendSchedules

        /**
         * Вывод схемы рабочего графика
         *
         * @return array
         */
        public function getWorkScheduleScheme ( $id ) {

            return mysqli_fetch_array(
                $this->DB->makeQuery ( "SELECT * FROM `work_schedules_rules` WHERE id = '$id'" )
            );

        } // function. getWorkScheduleScheme

        /**
         * Вывод рабочего графика
         *
         * @param integer $employee_id ID сотрудника
         * @param string $yearMonth Год и месяц выборки
         * @param integer $serviceId ID услуги (нужно для учета свободного времени сотрудника)
         *
         * @return array
         */
        public function getWorkSchedule ( $employee_id, $yearMonth, $serviceId = 0, $is_test = false ) {

            /**
             * Рабочий график за месяц
             */
            $workMonthSchedule = [];

            /**
             * Получение рабочих графиков
             */
            $workSchedules = $this->DB->makeQuery ( "SELECT * FROM work_schedules_rules WHERE employee_id = '$employee_id'" );

            /**
             * Получение выходных графиков
             */
            $weekendSchedules = $this->DB->makeQuery ( "SELECT * FROM work_schedules_weekends WHERE employee_id = '$employee_id'" );

            $yearMonth_year = (int) substr( $yearMonth, 0, 4 );
            $yearMonth_month = (int) substr( $yearMonth, 5, 7 );


            /**
             * Обработка рабочих графиков
             */
            foreach ( $workSchedules as $workSchedule ) {

                /**
                 * Проверка актуальности рабочего график для текущего месяца
                 */

                if ( $workSchedule[ "start" ] || $workSchedule[ "end" ] ) {

                    $workScheduleStart = $workSchedule[ "start" ];
                    $workScheduleEnd = $workSchedule[ "end" ];

                    if ( !$workScheduleStart ) $workScheduleStart = "1990-01-01";
                    if ( !$workScheduleEnd ) $workScheduleEnd = "2200-01-01";

                    $workScheduleStart = substr( $workScheduleStart, 0, 7 );
                    $workScheduleEnd = substr( $workScheduleEnd, 0, 7 );


                    if (
                        ( $workScheduleStart > $yearMonth ) ||
                        ( $workScheduleEnd < $yearMonth )
                    ) continue;

                } // if. $workSchedule[ "start" ] || $workSchedule[ "end" ]


                /**
                 * Обработка дней графика
                 */

                for ( $dayNumber = 1, $max = cal_days_in_month(
                    CAL_GREGORIAN, $yearMonth_month, $yearMonth_year
                ); $dayNumber <= $max; $dayNumber++ ) {

                    $isContinue = false;
                    $timesVariants = [];

                    $dayOfWeek = date( "w", strtotime(
                        $yearMonth . "-" . sprintf( '%02d', $dayNumber )
                    ) );

                    $weekNumber = date( "W", strtotime(
                        $yearMonth . "-" . sprintf( '%02d', $dayNumber )
                    ) );

                    /**
                     * Проверка. Актуален ли рабочий график для этого дня
                     * День месяца
                     */
                    if (
                        $workSchedule[ "start" ] &&
                        ( $workSchedule[ "start" ] > $yearMonth . "-" . sprintf( '%02d', $dayNumber ) )
                    ) $isContinue = true;
                    if (
                        $workSchedule[ "end" ] &&
                        ( $workSchedule[ "end" ] < $yearMonth . "-" . sprintf( '%02d', $dayNumber ) )
                    ) $isContinue = true;

                    /**
                     * Проверка. Актуален ли рабочий график для этого дня
                     * День недели
                     */
                    switch ( $dayOfWeek ) {

                        case "1":
                            if ( !$workSchedule[ "is_monday" ] && ( $workSchedule[ "is_monday" ] !== null ) ) $isContinue = true;
                            break;
                        case "2":
                            if ( !$workSchedule[ "is_tuesday" ] && ( $workSchedule[ "is_tuesday" ] !== null ) ) $isContinue = true;
                            break;
                        case "3":
                            if ( !$workSchedule[ "is_wednesday" ] && ( $workSchedule[ "is_wednesday" ] !== null ) ) $isContinue = true;
                            break;
                        case "4":
                            if ( !$workSchedule[ "is_thursday" ] && ( $workSchedule[ "is_thursday" ] !== null ) ) $isContinue = true;
                            break;
                        case "5":
                            if ( !$workSchedule[ "is_friday" ] && ( $workSchedule[ "is_friday" ] !== null ) ) $isContinue = true;
                            break;
                        case "6":
                            if ( !$workSchedule[ "is_saturday" ] && ( $workSchedule[ "is_saturday" ] !== null ) ) $isContinue = true;
                            break;
                        case "0":
                            if ( !$workSchedule[ "is_sunday" ] && ( $workSchedule[ "is_sunday" ] !== null ) ) $isContinue = true;
                            break;

                    } // switch. $dayOfWeek

                    /**
                     * Проверка. Актуален ли рабочий график для этого дня
                     * Четность недели
                     */
                    if ( $workSchedule[ "weeks_odd" ] !== null ) {

                        $isWeekOdd = false;
                        if ( (int) $weekNumber %2 == 0 ) $isWeekOdd = true;

                        if ( ( $workSchedule[ "weeks_odd" ] == 0 ) && ( $isWeekOdd ) ) $isContinue = true;
                        if ( ( $workSchedule[ "weeks_odd" ] == 1 ) && ( !$isWeekOdd ) ) $isContinue = true;

                    } // if. $workSchedule[ "weeks_odd" ] != null

                    /**
                     * Проверка. Актуален ли рабочий график для этого дня
                     * Четность дня
                     */
                    if ( $workSchedule[ "days_odd" ] !== null ) {

                        $isDayOdd = true;
                        if ( ( (int) $dayNumber + 1 ) %2 == 0 ) $isDayOdd = false;

                        if ( ( $workSchedule[ "days_odd" ] == "0" ) && ( $isDayOdd ) ) $isContinue = true;
                        if ( ( $workSchedule[ "days_odd" ] == "1" ) && ( !$isDayOdd ) ) $isContinue = true;

                    } // if. $workSchedule[ "days_odd" ] != null

                    if ( $isContinue ) continue;


                    /**
                     * Проверка и вывод свободного времени специалиста
                     */

                    if ( $serviceId ) {

                        /**
                         * Автоматический подсчет окончания посещения
                         */

                        $servicesWorkTimeInMinutes = 0;

                        $service = mysqli_fetch_array (
                            $this->DB->makeQuery( "SELECT worktime FROM services WHERE id = " . $serviceId . " LIMIT 1" )
                        );


                        /**
                         * Время выполнения услуги для отдельного специалиста
                         */
                        $employeeWorktime = mysqli_fetch_array (
                            $this->DB->makeQuery( "SELECT * FROM `employee-worktime_service` WHERE service_id = $serviceId AND employee_id = $employee_id LIMIT 1" )
                        );


                        if ( $employeeWorktime[ "worktime" ] )
                            $servicesWorkTimeInMinutes += (int) $employeeWorktime[ "worktime" ];
                        else $servicesWorkTimeInMinutes += (int) $service[ "worktime" ];


                        /**
                         * Определение вариантов времени записи
                         */

                        $workScheduleTimestampFrom = strtotime( "1970-01-01 " . $workSchedule[ "from" ] );
                        $workScheduleTimestampTo = strtotime( "1970-01-01 " . $workSchedule[ "to" ] );
                        $workScheduleTimestampTo -= $servicesWorkTimeInMinutes * 60;

                        $currentTimestamp = $workScheduleTimestampFrom;


                        $timesVariantsIncrement = 0;

                        while ( $workScheduleTimestampTo > $currentTimestamp ) {

                            /**
                             * Шаг в 2 минуты
                             */
                            $currentTimestamp += 1200;


                            if ( $is_test ) {

                                $timesVariants[] = [
                                    $yearMonth . "-" . sprintf( '%02d', $dayNumber ) . " " . date( "H:i:s", $currentTimestamp - 1200 ),
                                    $yearMonth . "-" . sprintf( '%02d', $dayNumber ) . " " . date( "H:i:s", $currentTimestamp  - 1200 + ( $servicesWorkTimeInMinutes * 60 ) ),
                                    $this->validateFreeTime(
                                        $yearMonth . "-" . sprintf( '%02d', $dayNumber ) . " " . date( "H:i:s", $currentTimestamp - 1200 ),
                                        $yearMonth . "-" . sprintf( '%02d', $dayNumber ) . " " . date( "H:i:s", $currentTimestamp  - 1200 + ( $servicesWorkTimeInMinutes * 60 ) ),
                                        $employee_id,
                                        true
                                    )
                                ];

                                continue;

                            }


                            if ( !$this->validateFreeTime(
                                $yearMonth . "-" . sprintf( '%02d', $dayNumber ) . " " . date( "H:i:s", $currentTimestamp - 1200 ),
                                $yearMonth . "-" . sprintf( '%02d', $dayNumber ) . " " . date( "H:i:s", $currentTimestamp  - 1200 + ( $servicesWorkTimeInMinutes * 60 ) ),
                                $employee_id
                            ) ) {

                                if (
                                    ( $yearMonth . "-" . sprintf( '%02d', $dayNumber ) == date( "Y-m-d" ) ) &&
                                    ( date("H:i:s", $currentTimestamp - 1200 ) <= date( "H:i:s" ) )
                                ) continue;

                                $timesVariantsIncrement++;
                                $timesVariants[] = date("H:i:s", $currentTimestamp - 1200 );

                            }


                            /**
                             * Ограничение на кол-во временных отрезков
                             */
                            if ( $timesVariantsIncrement > 2 ) break;

                        } // while. $workScheduleTimestampTo >= $currentTimestamp

                    } // if. $serviceId



                    $workMonthSchedule[ $dayNumber ][] = [
                        "id" => $workSchedule[ "id" ],
                        "type" => "work",
                        "from" => $workSchedule[ "from" ],
                        "to" => $workSchedule[ "to" ],
                        "time" => $timesVariants,
                        "exception" => $workSchedule[ "exception" ]
                    ];

                } // for. cal_days_in_month( CAL_GREGORIAN, $yearMonth_month, $yearMonth_year )

            } // foreach. $workSchedules


            /**
             * Обработка выходных графиков
             */

            foreach ( $weekendSchedules as $weekendSchedule ) {

                /**
                 * Проверка. Актуален ли выходной график для этого месяца
                 */

                $weekendScheduleStart = substr( $weekendSchedule[ "start" ], 0, 7 );
                $weekendScheduleEnd = substr( $weekendSchedule[ "end" ], 0, 7 );

                if (
                    ( $weekendScheduleStart > $yearMonth ) ||
                    ( $weekendScheduleEnd < $yearMonth )
                ) continue;


                for (
                    $dayNumber = 1,
                    $max = cal_days_in_month(
                        CAL_GREGORIAN, $yearMonth_month, $yearMonth_year
                    );
                    $dayNumber <= $max; $dayNumber++
                ) {

                    $dayOfMonth = $yearMonth . "-" . sprintf( '%02d', $dayNumber );


                    if (
                        ( $dayOfMonth < $weekendSchedule[ "start" ] ) ||
                        ( $dayOfMonth > $weekendSchedule[ "end" ] )
                    ) continue;


                    $workMonthSchedule[ $dayNumber ] = [ [
                        "id" => $weekendSchedule[ "id" ],
                        "type" => "weekend"
                    ] ];

                } // foreach. $weekendSchedules as $weekendSchedule

            } // for. cal_days_in_month( CAL_GREGORIAN, $yearMonth_month, $yearMonth_year )


            /**
             * Перетирание рабочих графиков
             */

            foreach ( $workMonthSchedule as $workMonthScheduleDayKey => $workMonthScheduleDayValue ) {

                $isExceptions = false;
                $workKeys = [];


                foreach ( $workMonthScheduleDayValue as $workMonthScheduleDataKey => $workMonthScheduleDataValue ) {

                    if ( $workMonthScheduleDataValue[ "exception" ] ) $isExceptions = true;
                    if ( $workMonthScheduleDataValue[ "type" ] == "work" ) $workKeys[] = $workMonthScheduleDataKey;

                } // foreach. $workMonthScheduleDayValue


                if ( ( count( $workKeys ) > 1 ) && $isExceptions ) {

                    $workSchedules = [];


                    foreach ( $workMonthScheduleDayValue as $workMonthScheduleDataKey => $workMonthScheduleDataValue ) {

                        if ( $workMonthScheduleDataValue[ "exception" ] ) $workSchedules[] = $workMonthSchedule[ $workMonthScheduleDayKey ][ $workMonthScheduleDataKey ];
                        unset( $workMonthSchedule[ $workMonthScheduleDayKey ][ $workMonthScheduleDataKey ] );

                    } // foreach. $workMonthScheduleDayValue


                    foreach ( $workSchedules as $workScheduleKey => $workScheduleValue )
                        $workMonthSchedule[ $workMonthScheduleDayKey ][ $workScheduleKey ] = (array) $workScheduleValue;

                } // if. ( count( $workKeys ) > 1 ) && $isExceptions

            } // foreach. $workMonthScheduleZ


            ksort( $workMonthSchedule );

            return $workMonthSchedule;

        } // function. getWorkSchedule

        /**
         * Проверка свободности времени
         *
         * $start			datetime	Запланированное начало
         * $end			    datetime	Запланированный конец
         * $employees_id	array[int]	Массив исполнителей, которые привязаны к посещению
         *
         * return bool|string
         */
        public function validateFreeTime ( $start, $end, $employee_id, $is_test = false ) {

            if ( !$start ) $start = "1990-01-01 00:00:00";
            if ( !$end ) $end = "2200-01-01 00:00:00";


            /**
             * Получение списка посещений в данный период
             */

            $query = "SELECT * FROM visits ";
            $query .= " INNER JOIN `visits-workers` ON visits.id = `visits-workers`.visit_id WHERE `visits-workers`.employee_id = $employee_id ";
            $query .= " AND visits.active = 1 AND visits.status != 'canceled' AND ( (visits.start >= '{$start}' AND visits.start < '{$end}') OR (visits.end > '{$start}' AND visits.end < '{$end}') OR (visits.start < '{$start}' AND visits.end >= '{$end}') OR (visits.start > '{$start}' AND visits.end < '{$end}') ) LIMIT 1";


            $visits = mysqli_fetch_array( $this->DB->makeQuery( $query ) );
            if ( $visits ) return $visits[ "visit_id" ];


            return false;

        } // function. validateFreeTime



        /**
         *  Получение списка рабочих дней
         *
         * @param   $start  string  Дата начала
         *
         * @return  array
         */
        public function getDaysInSchedule($start ) {

            $lastMonthDay = new DateTime('last day of ' . $start);

            $weekdays = 0;
            $weekends = 0;

            $saturdayCount = 0;
            $sundayCount = 0;

            for ( $day = 1; $day <= (int) $lastMonthDay->format("d"); $day++ ) {
                $weekDayIndex = date(
                    "w",
                    mktime(
                        0,
                        0,
                        0,
                        (int) $lastMonthDay->format("m"),
                        $day,
                        (int) $lastMonthDay->format("Y")
                    ));

                if ( $weekDayIndex > 0 && $weekDayIndex < 6 ) $weekdays++;
                else {

                    $weekends++;

                    if ( $weekDayIndex == 0 ) $sundayCount++;
                    else $saturdayCount++;

                }
            }

            return [
                "weekdays" => $weekdays,
                "weekends" => $weekends,

                "saturdayCount" => $saturdayCount,
                "sundayCount" => $sundayCount
            ];

        } // public function getDaysInSchedule( $start )

        /**
         * Вычисляет количесво рабочих часов в месяце
         *
         * @param $employee_id  int     ID сотрудника
         * @param $start        string  Дата начала
         * @param $end          string  Дата окончания
         *
         * @return int
         */
        public function getHoursBySchedule( $employee_id, $start ) {

            $totalHours = 0;
            $employeeDetails = mysqli_fetch_array(
                $this->DB->makeQuery( "SELECT * FROM `employees` WHERE id = $employee_id LIMIT 1" )
            );

            $weekdaysHours = ( int ) $employeeDetails[ "weekdays_to" ] - ( int ) $employeeDetails[ "weekdays_from" ];
            $weekendsHours = ( int ) $employeeDetails[ "weekends_to" ] - ( int ) $employeeDetails[ "weekends_from" ];

            if ( !$weekdaysHours && !$weekdaysHours ) return 0;
            $days = $this->getDaysInSchedule( $start );



            /**
             * Учет ставки
             */
            switch ( $employeeDetails[ "schedule_type" ] ) {

                case "2/2":

                    // * Для клиники "Я Здоров"
                    $weekends = $days[ "saturdayCount" ];
                    $totalHours = ( $weekdaysHours * $days[ "weekdays" ] ) + ( $weekendsHours * $weekends );

                    $totalHours /= 2;
                    break;

                case "5/2":
                    $totalHours = $weekdaysHours * $days[ "weekdays" ];
                    break;

                case "6/1":
                    $weekends = $days[ "saturdayCount" ];
                    $totalHours = ( $weekdaysHours * $days[ "weekdays" ] ) + ( $weekendsHours * $weekends );
                    break;

            } // switch . $employeeDetails[ "schedule_type" ]

            return $totalHours;
        } // public function getWorkedHours(



        /**
         * Расчет разницы во времени
         *
         * @param $start string Начало
         * @param $end   string Конец
         *
         * @return float|int
         */
        private function timeDifference( $start, $end )
        {
        	$start = new DateTime( $start );
			$end = new DateTime( $end );

			$interval = $start->diff($end);
			$totalTime = 0;

			foreach ( $interval as $key => $value ) {

				switch ( $key ) {

					case 'd':
						if( $interval->$key != 0 ) {
						    $totalTime = $totalTime + 24 * $interval->$key;
						}
					    break;

					case 'h':
						$totalTime = $totalTime + $interval->$key;
					    break;

					case 'i':
						$totalTime = $totalTime + ( $interval->$key ) / 60;
					    break;

					case 's':
						$totalTime = $totalTime + ( $interval->$key / 3600 );
					    break;

				} // switch . $key

			} // foreach . $interval as $key => $value

			return $totalTime;
        } // private function timeDifference( $start, $end )



        /**
         * Вычисляет фактическое количество отработанных часов
         *
         * @param $employee_id int    ID сотрудника
         * @param $start       string Начало отсчета
         * @param $end         string Конец отсчета
         *
         * @return float|int
         */
        public function getActualHoursWorked( $employee_id, $start, $end ) {

            $totalTime = 0;

            $firstMonthDay = new DateTime( "first day of " . $start ); //объект из переданной с клиента даты
            $worked = $this->getWorkSchedule( $employee_id, $firstMonthDay->format('Y-m') );

            $endDate = DateTime::createFromFormat( 'Y-m-d', $end );



            foreach ( $worked as $key => $value ) {

                if ( intval( $key ) > intval( $endDate->format('d') ) ) continue;

                foreach ( $value as $day ) {

                    if( $day[ "type" ] == "work" ) {

                        $totalTime += $this->timeDifference( $day[ "from" ], $day[ "to" ] );

                    } // if . $dates[ "type" ] == "work"

                } // foreach . $value as $dates

                $firstMonthDay->add(new DateInterval('P1D'));

            } // foreach . $worked as $key => $value

            return $totalTime;

        } // public function getActualHoursWorked( $employee_id, $start )

        /**
         * Расчет оклада сотрудника за месяц
         *
         * @param $employee_id int    ID сотрудника
         * @param $start       string Дата начала расчета
         * @param $end         string Дата окончания расчета
         *
         * @return float|int
         */
        public function getSalaryByPeriod( $employee_id, $start, $end ) {

            /**
             * Получение информации о сотруднике
             */
            $employeeDetails = array_shift( array_values( $this->get( $employee_id ) ) );
            if ( !$employeeDetails[ "id" ] ) return 0;



            /**
             * Получение общего и фактического времени работы сотрудника
             */
            $totalHours = $this->getHoursBySchedule( $employee_id, $start );
            $hoursWorked = $this->getActualHoursWorked( $employee_id, $start, $end );


            /**
             * Расчет оклада за не полностью отработанный месяц
             */
            if ( $totalHours != $hoursWorked ) {

                $salaryPerHour = $employeeDetails[ "salary_value" ] / $totalHours;

                if ( $employeeDetails[ "salary_per_hour" ] ) {

                    $salaryPerHour = intval( $employeeDetails[ "salary_per_hour" ] );

                }

                return $hoursWorked * $salaryPerHour;

            } // if . $totalHours != $hoursWorked

            return $employeeDetails[ "salary_value" ];

        } // public function getSalaryByPeriod( $employee_id, $start )


        /**
         * Изменить аватарку сотрудника
         *
         * @param $id
         * @param $filename
         *
         * @return bool
         */
        public function changeAvatar ( $id, $filename ) {

            /*
             * Определение пути к файлу
             */
            $filePath = str_replace( PATH_ROOT, "", $filename );

            /**
             * Определение домена проекта
             */
            $filePath = "https://" . $_SERVER[ "HTTP_HOST" ] . "$filePath";


            /**
             * Редактирование аватара сотрудника
             */

            $result = $this->DB->makeQuery (
                "UPDATE employees SET avatar = '$filePath' WHERE id = $id"
            );

            if ( !$result ) return false;

            return true;

        } // function. changeAvatar
		
	} // class. Employees
	
	
	
	$Employees = new Employees;