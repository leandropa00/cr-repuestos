<?php
	/*
	 * @author	Julio Cesar Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 */
	class Moneda {
		
		private static $separador_decimales 	= ",";
		public static $separador_miles		= ".";
		private static $decimales = 2;
		
		public static function getMoneda($valor, $decimales = 2) {
			if ($valor == "") return "0";
			return number_format(doubleval($valor), $decimales, self::$separador_decimales, self::$separador_miles);
		}
	}
?>