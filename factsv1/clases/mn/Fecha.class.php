<?php
	/*
	 * @author	Julio Cesar Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 */
	class Fecha {
		
		private static $reemplazos =
			array(
				array("Jan", "Ene"),
				array("Aug", "Ago"),
				array("Dec", "Dic"),
				array("Apr", "Abr"),
				array("Eneuary", "Enero"),	//mod
				array("February", "Febrero"),
				array("March", "Marzo"),
				array("May", "Mayo"),
				array("June", "Junio"),
				array("July", "Julio"),
				array("Agoust", "Agosto"),	//mod
				array("September", "Septiembre"),
				array("October", "Octubre"),
				array("ember", "iembre")	//mod
			);
			
		public static function getFecha($fecha, $formato, $time = false) {
			if ($fecha == NULL)
				return "";
			$fret = "";
			if ($formato != "")
				$fret = @date($formato, $time ? $fecha : @strtotime($fecha));
			return self::reemplazar($fret);
		}
		
		public static function reemplazar($fecha) {
			$o = array();
			$r = array();
			foreach(self::$reemplazos as $reemplazo) {
				$o[] = $reemplazo[0];
				$r[] = $reemplazo[1];
			}
			return str_replace($o, $r, $fecha);
		}
		
		public static function getFechaCorta($fecha, $formato = "", $time = false) {
			if (strlen($formato) > 0 ) 
				$fecha = self::getFecha($fecha, $formato, $time);
			$arrayLargo = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
			$arrayCorto = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
			return str_replace($arrayLargo, $arrayCorto, $fecha);
		}
	}
?>