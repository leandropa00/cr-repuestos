<?php
		
	/** 
	 * Clase con métodos estáticos que mejoran las condiciones de seguridad 
	 * de la aplicación Web.
	 * 
	 * @author Julio César Garcés Rios [garcez@linuxmail.org]
	 * 
	 * @package BD
	 */
	class Seguridad {
		
		/**
		 * Filtra las variables que son utilizadas en las consultas
		 * de acuerdo al motor de base de datos utilizado
		 * 
		 * @access 	public
		 * 
		 * @param 	string	$var	Variable sin filtrar
		 * @return 	string			Variable filtrada de acuerdo al motor de base de datos
		 */
		public static function escapeSQL($var, $dbms = "") {
			if ($dbms == "")
				$dbms = "mysql";
			switch($dbms) {
				case "mysql" :
					if (function_exists("mysqli_real_escape_string"))
						return mysqli_real_escape_string(BD::$bd[BD::getInstanciaActual()]["con"]->db_connect_id, get_magic_quotes_gpc() ? stripslashes($var) : $var);
					return addslashes(BD::$bd[BD::getInstanciaActual()]["con"]->db_connect_id, get_magic_quotes_gpc() ? stripslashes($var) : $var);
					break;
				case "oracle" :
					return str_replace("'", "''", $var);
					break;
				case "mssqlnative" :
				case "mssql_odbc" :
					return str_replace(array("'", "%"), array("''", ""), $var);
					break;
				default :
					Aplicacion :: pararScript("No se han definido validaciones para las variables de las consultas en " . self :: $dbms, true);
					break;
			}
		}
	}
?>