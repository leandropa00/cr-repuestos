<?php
	/*
	 * @author	Julio Cesar Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 */
	class Misc {
		/**
		 * $fecha 	En formato YYYY-MM-DD
		 */
		public static function getEdad($fecha) {
			@list($ano,$mes,$dia) = explode("-",$fecha);
			$ano_diferencia  = date("Y") - intval($ano);
			$mes_diferencia = date("m") - intval($mes);
			$dia_diferencia   = date("d") - intval($dia);
			if ($dia_diferencia < 0 || $mes_diferencia < 0)
				$ano_diferencia--;
			if ($ano_diferencia > 1000) return "ERROR";
			return $ano_diferencia;
		}

		public static function getFechaArchivo($archivo) {
			if (file_exists($archivo)) {
				$fecha = filemtime($archivo);
				return Fecha::getFechaCorta(date("Y-m-d H:i:s", $fecha), "d/F/Y g:ia");
			}
			return "-";
		}
	
		public static function getPesoArchivo($archivo) {
			if (file_exists($archivo))
				return round(filesize($archivo) / 1024, 0) . "Kb";
			return "-";
		}
	}
?>