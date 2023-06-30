<?php
		
	/**
	 * Permite administrar el acceso a la base de datos para realizar operaciones
	 * de tipo SELECT, INSERT, UPDATE y DELETE, además de tratar los resultados
	 * de las consultas realizadas.
	 * 
	 * @author Julio César Garcés Rios [garcez@linuxmail.org]
	 * 
	 * @package 	BD
	 */
	class BD {
		
		public static $bd;
		private static $instancia_actual = "mysql";
		
		public static function getInstanciaActual() {
			return self::$instancia_actual;
		}

		public static function getConID() {
			if (isset(self::$bd[self::$instancia_actual]["con"]))
				return self::$bd[self::$instancia_actual]["con"]->db_connect_id;
			return null;
		}
		
		public static function changeInstancia($instancia) {
			global $_CONFIG;
			if (isset(self::$bd[$instancia]["con"])) {
				self::$instancia_actual = $instancia;
				return true;
			}
			if (isset($_CONFIG["BD"][$instancia])) {
				self::$instancia_actual = $instancia;
				list($dbms, $bd_user, $bd_pass, $bd_host, $bd_base) = $_CONFIG["BD"][$instancia];
				if (preg_match("/^\w{2,}$/", $dbms) && file_exists(BD . $dbms . ".php"))
					require_once(BD . $dbms . ".php");
				else
					self :: pararScript("Verifique que está implementada la clase para acceso a la BD " . htmlentities($dbms));
				//Verificar la conexión a otros gestores de base de datos
				//Verificar que solo se realice una conexión a la BD, porque podrían estarse realizando conexiones en cada changeInstancia
				if ($dbms == "mysql")
					self :: $bd[$instancia]["con"] = new dbal_mysql();
				if ($dbms == "oracle")
					self :: $bd[$instancia]["con"] = new dbal_oracle();
				if ($dbms == "mssqlnative")
					self :: $bd[$instancia]["con"] = new dbal_mssqlnative();
				if ($dbms == "mssql_odbc")
					self :: $bd[$instancia]["con"] = new dbal_mssql_odbc();
				self :: $bd[$instancia]["dbms"] = $dbms;
				self :: $bd[$instancia]["con"]->sql_connect($bd_host, $bd_user, $bd_pass, $bd_base);
				return true;
			}
			return false;
		}
		
		/**
		 * Realiza una consulta a la BD
		 * 
		 * @param 	string	$tabla		Tabla que será consultada
		 * @param 	array	$campos		Array que contiene los campos que serán seleccionados en la consulta.
		 * @param 	array	$condicion	Array que contiene los indices como campos y los valores como condiciÃ³n de cada campo.
		 * @return	resource			En caso de éxito hace referencia al resultado de la consulta, de lo contrario retorna FALSE.  
		 */
		public static function consultar($tabla, $campos, $condicion = array(), $adicional = "") {
			if (!isset(self::$bd[self::$instancia_actual]))
				die("Instancia no definida");
			if (!is_array($campos) || count($campos) == 0)
				return false;
			$query = "SELECT " . join(",", $campos) . " FROM " . $tabla;
			if (count($condicion) > 0) {
				$array_condicion = array();
				foreach($condicion as $campo => $dato) {
					if (substr($campo, 0, 1) == "@")
						$array_condicion[] = str_replace("@", "", $campo) ." = " . $dato;
					else
						$array_condicion[] = $campo . " = '" . Seguridad :: escapeSQL($dato, self::$bd[self::$instancia_actual]["dbms"]) . "'";
				}
				$query .= " WHERE " . join(" AND ", $array_condicion);
			}
			$query .= " " . $adicional;
			$modo_debug = self::modoDEBUG();
			self::depurar($modo_debug, $query);
			return self::$bd[self::$instancia_actual]["con"]->sql_query($query, 0, $modo_debug);
		}
		
		public static function numFilas($resource) {
			return self::$bd[self::$instancia_actual]["con"]->sql_numrows($resource);
		}
		
		/**
		 * Obtiene la siguiente fila dado un recurso devuelto por una consulta
		 * 
		 * @access public
		 * 
		 * @param 	resource	$recurso	Parámetro opcional, es el Identificador del recurso que hace referencia al resultado de una consulta.		
		 * @return 	array					Matriz que corresponde a la sentencia extraida, o false si no quedan más filas.
		 */
		public static function obtenerRegistro($recurso = false) {
			return self::$bd[self::$instancia_actual]["con"]->sql_fetchrow($recurso);
		}
		
		public static function sql_query($query) {
			$modo_debug = self :: modoDEBUG();
			self :: depurar($modo_debug, $query);
			return self :: $bd[self::$instancia_actual]["con"] -> sql_query($query, 0, $modo_debug);
		}
		
		public static function getInsertID() {
			return self :: $bd[self::$instancia_actual]["con"] -> sql_nextid();
		}
		
		/**
		 * Adiciona un registro con información a una tabla
		 * 
		 * @param	string	$tabla			
		 * @param 	array	$array_datos	Array de valores, los índices corresponden a los campos y su contenido al valor
		 * @return 	bool					Respuesta positiva o negativa (true/false) de la consulta insert.
		 */
		public static function adicionar($tabla, $array_datos) {
			$campos = array();
			if (!is_array($array_datos) || count($array_datos) == 0)
				Aplicacion :: pararScript("No hay nada que adicionar en ".htmlentities($tabla));
			foreach($array_datos as $campo => $dato) {
				if ($array_datos[$campo] === "") {
					unset($array_datos[$campo]);
					continue;
				}
				$array_datos[$campo] = Seguridad :: escapeSQL($array_datos[$campo]);
				$campos[] = $campo;
			}
			$query = "INSERT INTO ".$tabla." (".join(",", $campos).") VALUES('".join("', '", $array_datos)."')";
			$modo_debug = self :: modoDEBUG();
			self :: depurar($modo_debug, $query);
			
			return self :: $bd[self::$instancia_actual]["con"] -> sql_query($query, 0, $modo_debug);
		}

		/**
		 * Desconecta de la BD para liberar memoria
		 */
		public static function desconectar() {
			self :: $bd[self::$instancia_actual]["con"] -> _sql_close();
			unset(self :: $bd[self::$instancia_actual]["con"]);
			return true;
		}

		/**
		 * Retorna la descripción del último error generado
		 */
		public static function getLastError() {
			return self :: $bd[self::$instancia_actual]["con"] -> get_last_error();
		}
		
		/**
		 * Realiza el proceso de actualización de la información contenida en la base de datos.
		 * 
		 * @access 	public
		 * 
		 * @param 	string	$tabla			Tabla que será actualizada			
		 * @param 	array	$array_datos	Array de nuevos valores, los índices corresponden a los campos y su contenido al nuevo valor
		 * @param 	array	$condicion		Array que contiene los indices como campos y los valores como condición de cada campo.
		 * @return 	bool					Respuesta positiva o negativa (true/false) de la consulta update.
		 */
		public static function actualizar($tabla, $array_datos, $condicion) {
			$actualizaciones = array();
			
			if (!is_array($array_datos) || count($array_datos) == 0)
				Aplicacion :: pararScript("No hay nada que actualizar en " . htmlentities($tabla));
			foreach($array_datos as $campo => $valor) {
				if ($array_datos[$campo] == "")
					$actualizaciones[] = $campo. "= ''";
				else if ($array_datos[$campo] == "_NULL")
					$actualizaciones[] = $campo. "= NULL ";
				else
					$actualizaciones[] = $campo. "='" . Seguridad :: escapeSQL($valor) . "'";
			}
			
			if (count($actualizaciones) == 0)
				return true;
				
			$query = "UPDATE $tabla SET ".join(",", $actualizaciones);
			if (count($condicion) > 0) {
				$array_condicion = array();
				foreach($condicion as $campo => $dato)
					$array_condicion[] = $campo . " = '" . Seguridad :: escapeSQL($dato) . "'";
				$query .= " WHERE " . join(" AND ", $array_condicion);
			}
			$modo_debug = self :: modoDEBUG();
			self :: depurar($modo_debug, $query);
			return self :: $bd[self::$instancia_actual]["con"] -> sql_query($query, 0, $modo_debug);
		}
		
		/**
		 * Elimina un registro de la tabla PUC que cumpla con una condición espcificada en el parámetro
		 * 
		 * @access	public
		 * 
		 * @param 	string	$tabla		Tabla de la cual se eliminará la información	
		 * @param 	array 	$condicion 	Array que contiene los indices como campos y los valores como condición de cada campo.
		 * @return 	bool				Respuesta positiva o negativa (true/false) que se produce al ejecutar al consulta
		 */
		public static function eliminar($tabla, $condicion) {
			$query = "DELETE FROM $tabla";
			if (count($condicion) > 0) {
				$array_condicion = array();
				foreach($condicion as $campo => $dato)
					$array_condicion[] = $campo . " = '" . Seguridad :: escapeSQL($dato) . "'";
				$query .= " WHERE " . join(" AND ", $array_condicion);
			}
			$modo_debug = self :: modoDEBUG();
			self :: depurar($modo_debug, $query);
			
			return self :: $bd[self::$instancia_actual]["con"] -> sql_query($query, 0, $modo_debug);
		}
		
		public static function sql_query_limit($query, $offset, $start) {
			$modo_debug = self :: modoDEBUG();
			self :: depurar($modo_debug, $query);
			return self :: $bd[self::$instancia_actual]["con"] -> _sql_query_limit($query, $offset, $start);
		}
		
//Métodos privados		
		/**
		 * Imprime información con respecto a la consulta realizada para depurar la aplicación
		 * 
		 * @access 	private
		 * 
		 * @param 	bool 	$modo		true/false que indica si se debe depurar o no
		 * @param 	string 	$query		Consulta que se mostrará en caso de que se desee depurar.
		 */
		private static function depurar($modo, $query) {
			if ($modo === true)
				echo "<br><font color=red>" . htmlentities($query, ENT_QUOTES, "ISO8859-1") . "</font><br>";
		}
		
		/**
		 * Comprueba si la aplicación está funcionando en modo de depuración o no.
		 * 
		 * @access 	private
		 * 
		 * @return 	bool	Modo de depuración == true, de lo contrario false
		 */
		private static function modoDEBUG() {
			return defined("MODO_DEBUG") ? MODO_DEBUG : false;
		}
	
	}
?>