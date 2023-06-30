<?php
	/*
	 * @author	Julio Cesar Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 */
	class Tools {
		
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