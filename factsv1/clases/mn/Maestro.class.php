<?php
	/*
	 * @author	Julio Cesar Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 */
	class Maestro extends Tabla {
		public $tabla = "rp_maestro";
		public $vista = "rp_maestro";

		private static $_data = array();

		public static function getSegmento($referencia) {
			self::loadDataMaestro();
			return isset(self::$_data[$referencia]["segmento"]) ? self::$_data[$referencia]["segmento"] : "";
		}

		public static function getTipoVehiculo($referencia, $linea) {
			self::loadDataMaestro();
			if (isset(self::$_data[$referencia]["tipo"])) 
				return self::$_data[$referencia]["tipo"];
			if (strpos(strtolower($linea), "nhr") !== false) return "pesados";
			if (strpos(strtolower($linea), "nkr") !== false) return "pesados";
			if (strpos(strtolower($linea), "nlr") !== false) return "pesados";
			if (strpos(strtolower($linea), "nnr") !== false) return "pesados";
			if (strpos(strtolower($linea), "nmr") !== false) return "pesados";
			if (strpos(strtolower($linea), "npr") !== false) return "pesados";
			if (strpos(strtolower($linea), "nqr") !== false) return "pesados";
			if (strpos(strtolower($linea), "ftr") !== false) return "pesados";
			if (strpos(strtolower($linea), "frr") !== false) return "pesados";
			if (strpos(strtolower($linea), "fsr") !== false) return "pesados";
			if (strpos(strtolower($linea), "fvz") !== false) return "pesados";
			return "liviano";
		}

		public static function loadDataMaestro() {
			if (count(self::$_data) == 0) {
				BD::changeInstancia("mysql");
				$r = BD::sql_query("SELECT material, segmento_parte, tipo_vehiculo FROM rp_maestro") or die("Error al consultar la tabla maestro " . BD::getLastError());
				while ($f = BD::obtenerRegistro($r)) {
					if (!isset(self::$_data[$f["material"]]))
						self::$_data[$f["material"]] = array("segmento" => $f["segmento_parte"], "tipo" => $f["tipo_vehiculo"]);
				}
			}
		}
	}
?>