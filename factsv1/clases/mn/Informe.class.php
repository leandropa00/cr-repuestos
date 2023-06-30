<?php
	/*
	 * @author	Julio Cesar Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 */
	class Informe extends Tabla {
		public $tabla = "informe";
		public $vista = "informe";
		const SESSION_CLASS = "informe";
		private static $instance = null;
		private static $querys = array();
		private static $devoluciones = array();
		public static $item = array(
			1 => "LIVIANOS - Mostrador - solo flotas",
			2 => "LIVIANOS - Mostrador - Colisión / Aseguradoras",
			3 => "LIVIANOS - Mostrador - Mantenimiento / Desgaste",
			4 => "LIVIANOS - Mostrador - (Otros) / Ventas Externas",
			5 => "LIVIANOS - Mostrador - Solochevrolet",
			
			6 => "LIVIANOS - Mecánica Rápida - Taller solo flotas",
			7 => "LIVIANOS - Mecánica Rápida - Taller Uno a Uno",
			8 => "LIVIANOS - Mecánica Especializada - Taller solo flotas",
			9 => "LIVIANOS - Mecánica Especializada - Taller Uno a Uno",

			10 => "LIVIANOS - Taller Colisión Uno a Uno",
			11 => "LIVIANOS - Taller Colisión Aseguradoras",

			12 => "LIVIANOS - Garantías",
			13 => "LIVIANOS - Internas",

			14 => "LIVIANOS - Alternos Taller",
			15 => "LIVIANOS - Alternos Colisión",
			16 => "LIVIANOS - Alternos Mostrador",

			1001 => "PESADOS - Mostrador - solo flotas",
			1002 => "PESADOS - Mostrador - Colisión / Aseguradoras",
			1003 => "PESADOS - Mostrador - Mantenimiento / Desgaste",
			1004 => "PESADOS - Mostrador - (Otros) / Ventas Externas",
			1005 => "PESADOS - Mostrador - Solochevrolet",
			
			1006 => "PESADOS - Mecánica Rápida - Taller solo flotas",
			1007 => "PESADOS - Mecánica Rápida - Taller Uno a Uno",
			1008 => "PESADOS - Mecánica Especializada - Taller solo flotas",
			1009 => "PESADOS - Mecánica Especializada - Taller Uno a Uno",

			10010 => "PESADOS - Taller Colisión Uno a Uno",
			10011 => "PESADOS - Taller Colisión Aseguradoras",

			10012 => "PESADOS - Garantías",
			10013 => "PESADOS - Internas",

			10014 => "PESADOS - Alternos Taller",
			10015 => "PESADOS - Alternos Colisión",
			10016 => "PESADOS - Alternos Mostrador"
		);

		public static $item_costo = array(
			201 => "Costo de venta Mostrador",
			202 => "Costo de venta Taller",
			203 => "Costo de venta Colisión",
			204 => "Costo de venta Garantías",
			205 => "Costo de venta Internos",
			206 => "Costo de venta Alternos",

			207 => "Compras repuestos GM",
			208 => "Compras repuestos a otros concesionarios",
			209 => "Compras repuestos a otros proveedores"
		);


		public static function &getInstance() {
			if (isset($_SESSION[self::SESSION_CLASS]) && $_SESSION[self::SESSION_CLASS] instanceof Informe) {
				self::$instance = &$_SESSION[self::SESSION_CLASS];
			}
			else {
				$_SESSION[self::SESSION_CLASS] = new Informe();
				self::$instance = &$_SESSION[self::SESSION_CLASS];
				$periodo = new Periodo(Informe::getLimitePeriodo());
				self::$instance->change($periodo->getYear(), $periodo->getMonth());
			}
			return self::$instance;
		}

		public function change($anio, $mes) {
			BD::changeInstancia("mysql");
			$vars = array("anio" => $anio, "mes" => $mes);
			$r = BD::consultar($this->tabla, array("*"), $vars);
			if ($f = BD::obtenerRegistro($r))
				return $this->loadThis($f);
			
			//Si no lo encuentro
			$vars["fecha"] = date("Y-m-d H:i:s");
			$informe = new Informe($vars);
			if ($informe->save()) {
				if ($this->load($informe->id))
					return $this->actualizarDatos();
			}
		}

		public function getPeriodo() {
			return new Periodo($this->getCampo("anio") . str_pad($this->getCampo("mes"), 2, "0", STR_PAD_LEFT));
		}

		public function getFechaActualizacion($formato  ="d/F/Y g:ia") {
			return Fecha::getFechaCorta($this->getCampo("fecha"), $formato);
		}


		/**
		 * MOSTRADOR
		 */
		public function getTotalMostrador($tipo_vehiculo = 'liviano') {
			return $this->getMostradorSoloFlotas($tipo_vehiculo) 
					+ $this->getMostradorColision($tipo_vehiculo) 
					+ $this->getMostadorMantenimientoDesgaste($tipo_vehiculo) 
					+ $this->getMostadorOtrosVentasExternas($tipo_vehiculo) 
					+ $this->getMostradorSolochevrolet($tipo_vehiculo);
		}

		public function getMostradorSoloFlotas($tipo_vehiculo) {
			BD::changeInstancia("mysql");
			$tipodocs = array('FA', 'FRD');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.tipo_proveedor='gm'
					and v.devolucion=0
					AND c.clasificacion<>8
					AND v.nombre_grupo not in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='$tipo_vehiculo'");
			$result = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 1 : 1001;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$result -= $devoluciones;
			return $result;
		}

		public function getMostradorSoloFlotasData($tipo_vehiculo) {
			$result = array();
			BD::changeInstancia("mysql");
			$tipodocs = array('FA', 'FRD');
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.tipo_proveedor='gm'
					AND c.clasificacion<>8
					AND v.nombre_grupo not in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='$tipo_vehiculo' ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMostradorColision($tipo_vehiculo) {//301785
			BD::changeInstancia("mysql");
			$tipodocs = array('FA','FRD');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					and v.sw=1
					and v.devolucion=0
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.tipo_proveedor='gm' 
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 2 : 1002;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMostradorColisionData($tipo_vehiculo) {
			$result = array();
			BD::changeInstancia("mysql");
			$tipodocs = array('FA','FRD');
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v 
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						and v.sw=1
						AND v.tipo in ('" . implode("','", $tipodocs) . "') 
						AND v.tipo_proveedor='gm' 
						AND c.clasificacion=8
						AND vehiculo_tipo='$tipo_vehiculo' ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMostadorOtrosVentasExternas($tipo_vehiculo) {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FA', 'FRD')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND v.sw=1
					and v.devolucion=0
					AND c.tipo_cliente='particular'
					AND v.vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 4 : 1004;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMostadorOtrosVentasExternasData($tipo_vehiculo) {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FA', 'FRD')  
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND v.sw=1
					AND c.tipo_cliente='particular'
					AND v.vehiculo_tipo='$tipo_vehiculo' ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMostadorMantenimientoDesgaste($tipo_vehiculo) {
			BD::changeInstancia("mysql");
			return 0;	//No se llena
		}

		public function getMostadorMantenimientoDesgasteData($tipo_vehiculo) {
			BD::changeInstancia("mysql");
			return array();
		}

		public function getMostradorSolochevrolet($tipo_vehiculo) {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FS', 'FSC') 
					AND v.sw=1
					and v.devolucion=0
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 5 : 1005;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMostradorSolochevroletData($tipo_vehiculo) {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FS', 'FSC') 
					AND v.sw=1
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
		/**-------------------------------------------------------------------*/


		/**
		 * Taller Mecánica y Mantenimiento
		 */

		public function getTotalTallerMecanicaMantenimiento($tipo_vehiculo = 'liviano') {
			return $this->getTotalMecanicaRapida($tipo_vehiculo) + $this->getTotalMecanicaEspecializada($tipo_vehiculo);
		}

		public function getTotalMecanicaRapida($tipo_vehiculo = 'liviano') {
			return $this->getMecanicaRapidaFlotas($tipo_vehiculo)
				+  $this->getMecanicaRapidaUno($tipo_vehiculo);
		}

		public function getMecanicaRapidaFlotas($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					and v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 6 : 1006;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMecanicaRapidaFlotasData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
		
		public function getMecanicaRapidaUno($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			$codigo = $tipo_vehiculo == "liviano" ? 7 : 1007;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMecanicaRapidaUnoData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalMecanicaEspecializada($tipo_vehiculo = 'liviano') {
			return $this->getMecanicaEspecializadaFlotas($tipo_vehiculo)
				+  $this->getMecanicaEspecializadaUno($tipo_vehiculo);
		}

		public function getMecanicaEspecializadaFlotas($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.devolucion=0
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 8 : 1008;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMecanicaEspecializadaFlotasData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
		
		public function getMecanicaEspecializadaUno($tipo_vehiculo = 'liviano') {
			if (isset($return)) return $return;
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND v.devolucion=0
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 9 : 1009;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMecanicaEspecializadaUnoData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}


		/** 
		 * COLISION 
		*/
		public function getTotalColision($tipo_vehiculo = 'liviano') {
			return $this->getColisionUno($tipo_vehiculo) + $this->getColisionAseguradoras($tipo_vehiculo);
		}

		public function getColisionUno($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo not IN ('ACCES')
					AND v.devolucion=0
					AND c.clasificacion<>8
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 10 : 10010;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getColisionUnoData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo not IN ('ACCES')
					AND c.clasificacion<>8
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getColisionAseguradoras($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 11 : 10011;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getColisionAseguradorasData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalGarantias($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FG')
					AND v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
				
			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 12 : 10012;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getTotalGarantiasData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FG')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
	
		public function getTotalInternas($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			return 0;
		}

		public function getTotalInternasData($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			return array();
		}

		public function getTotalAlternos($tipo_vehiculo = 'liviano') {
			return $this->getAlternosTaller($tipo_vehiculo) + $this->getAlternosColision($tipo_vehiculo) + $this->getAlternosMostrador($tipo_vehiculo);
		}
		
		public function getAlternosTaller($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('FT', 'FC', 'FG')
					AND v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 14 : 10014;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getAlternosTallerData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('FT', 'FC', 'FG')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getAlternosColision($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('FL')
					AND v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 15 : 10015;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getAlternosColisionData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('FL')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getAlternosMostrador($tipo_vehiculo = 'liviano') {
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('FA','FS','FRD','FSC')
					AND v.devolucion=0
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 16 : 10016;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getAlternosMostradorData($tipo_vehiculo = 'liviano') {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v 
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						AND v.tipo_proveedor='alterno' 
						AND v.tipo in ('FA','FS','FRD','FSC')
						AND v.nombre_grupo NOT IN ('ACCES')
						AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalVentasDetal($tipo_vehiculo = 'liviano') {
			return $this->getTotalMostrador($tipo_vehiculo) 
				+ $this->getTotalTallerMecanicaMantenimiento($tipo_vehiculo) 
				+ $this->getTotalColision($tipo_vehiculo)
				+ $this->getTotalGarantias($tipo_vehiculo)
				+ $this->getTotalInternas($tipo_vehiculo)
				+ $this->getTotalAlternos($tipo_vehiculo);
		}


		/**---------------------------------------------- */

		/**
		 * Costos de venta
		 */
		public function getTotalCostosVenta($tipo_vehiculo = '') {
			return $this->getVentasMostrador($tipo_vehiculo) + $this->getVentasTaller($tipo_vehiculo) + $this->getVentasColision($tipo_vehiculo) + $this->getVentasGarantias($tipo_vehiculo) + $this->getVentasInternos($tipo_vehiculo) + $this->getVentasAlternos($tipo_vehiculo);
		}

		public function getVentasMostrador($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FA','FSC','FS','FRD')
					$query_add
					AND v.devolucion=0 AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 201;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo=$codigo 
					$query_add
					AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getVentasMostradorData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FA','FSC','FS','FRD')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		//Costo de venta Taller
		public function getVentasTaller($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FC', 'FT', 'NCDR')
					AND v.devolucion=0 AND v.nombre_grupo NOT IN ('ACCES')");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 202;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo=$codigo AND v.informe_id<>" . $this->id . " $query_add and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getVentasTallerData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FC', 'FT', 'NCDR')
					AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getVentasColision($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FL')
					AND v.devolucion=0 AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 203;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo=$codigo AND v.informe_id<>" . $this->id . " $query_add and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getVentasColisionData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FL')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getVentasGarantias($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FG')
					AND v.devolucion=0 AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 204;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo=$codigo AND v.informe_id<>" . $this->id . " $query_add and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getVentasGarantiasData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('FG')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getVentasInternos($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('TI')
					AND v.devolucion=0 AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 205;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo=$codigo AND v.informe_id<>" . $this->id . " $query_add and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getVentasInternosData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					$query_add
					AND v.tipo in ('TI')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		//Costo de venta Alternos
		public function getVentasAlternos($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					$query_add
					AND v.tipo in ('FA','FS','FSC','FRD','FL','FT','FC', 'NCDR')
					AND v.devolucion=0 AND v.nombre_grupo NOT IN ('ACCES')");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 206;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo=$codigo AND v.informe_id<>" . $this->id . " $query_add and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getVentasAlternosData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					$query_add
					AND v.tipo in ('FA','FS','FSC','FRD','FL','FT','FC', 'NCDR')
					AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}


		public function getTotalComprasRepuestos($tipodocs = array(),$tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.devolucion=0 AND v.sw=3 AND v.nombre_grupo NOT IN ('ACCES')");
			$total= ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			//Devoluciones de meses anteriores
			$codigos = array(-1000);
			foreach($tipodocs as $td) {
				if ($td == 'CRCO') $codigos[] = 207;
				if ($td == 'CRO') $codigos[] = 208;
				if ($td == 'CROT') $codigos[] = 209;
			}
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo in (" . implode(",", $codigos) . ") AND v.informe_id<>" . $this->id . " $query_add and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$total -= $devoluciones;
			return $total;
		}

		public function getTotalComprasRepuestosData($tipodocs = array(), $tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND v.vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.sw=3 AND v.nombre_grupo NOT IN ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalInventarios($tipo_vehiculo = '') {
			return $this->getInventariosEntregadoAServicio($tipo_vehiculo)
				+ $this->getInventarios('0M-12M', $tipo_vehiculo)
				+ $this->getInventarios('12M-24M', $tipo_vehiculo)
				+ $this->getInventarios('24M-MAS', $tipo_vehiculo)
				+ $this->getInventariosAlternos($tipo_vehiculo);
		}

		public function getInventariosEntregadoAServicio($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos where informe_id=" . $this->id . " 
				and (nombre_grupo is null or nombre_grupo <> 'ACCES')
				$query_add
				and bodega = 99";
			$r = self::queryMYSQL($query);
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			return $return;
		}

		public function getInventariosEntregadoAServicioData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select *, costo_promedio * stock xtotal
				from rp_ubicacion_repuestos where informe_id=" . $this->id . " 
				$query_add
				and (nombre_grupo is null or nombre_grupo <> 'ACCES')
				and bodega =99");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getInventarios($edad, $tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos 
				where informe_id=" . $this->id . " 
					and tipo_proveedor='gm'
					$query_add
					and (nombre_grupo is null or nombre_grupo <> 'ACCES')
					and edad='" . Seguridad::escapeSQL($edad, 'mysql') . "'
					and bodega <> 99";
			$r = self::queryMYSQL($query);
			return ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		}

		public function getInventariosData($edad, $tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$result = array();
			$query = "select *, (costo_promedio * stock) xtotal
				from rp_ubicacion_repuestos 
				where informe_id=" . $this->id . " 
					and tipo_proveedor='gm'
					$query_add
					and (nombre_grupo is null or nombre_grupo <> 'ACCES')
					and edad='" . Seguridad::escapeSQL($edad, 'mysql') . "'
					and bodega <> 99";
			$r = self::queryMYSQL($query);
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getInventariosAlternos($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos 
				where informe_id=" . $this->id . " 
					$query_add
					and (nombre_grupo is null or nombre_grupo <> 'ACCES')
					and tipo_proveedor='alterno'";
			$r = self::queryMYSQL($query);
			return ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		}

		public function getInventariosAlternosData($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select *, costo_promedio * stock xtotal
				from rp_ubicacion_repuestos where informe_id=" . $this->id . " 
					and (nombre_grupo is null or nombre_grupo <> 'ACCES')
					$query_add
					and tipo_proveedor='alterno'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalFOF($tipo_vehiculo = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
				
			BD::changeInstancia("mysql");
			$query = "select avg(fof) total from rp_perfil_taller where informe_id=" . $this->id . " $query_add";
			$r = self::queryMYSQL($query) or die("Error en query: $query");
			$return = ($f = BD::obtenerRegistro($r)) ? round($f["total"], 2) : 0;
			return $return;
		}

		/*-------------------*/

		public function clearQuerys() {
			$this->MostradorSoloFlotas = array();
			$this->MostradorColision = array();
			$this->mostrador_mantenimiento = array();
			$this->MostadorOtrosVentasExternas = array();
			$this->MostradorSolochevrolet = array();
			
			$this->MecanicaRapidaFlotas = array();
			$this->MecanicaRapidaUno = array();
			$this->MecanicaEspecializadaFlotas = array();
			$this->MecanicaEspecializadaUno = array();

			$this->ColisionUno = array();
			$this->ColisionAseguradoras = array();
			$this->TotalGarantias = array();
			$this->AlternosTaller = array();
			$this->AlternosColision = array();
			$this->AlternosMostrador = array();

			$this->VentasMostrador = array();
			$this->VentasTaller = array();
			$this->VentasColision = array();
			$this->VentasGarantias = array();
			$this->VentasInternos = array();
			$this->VentasAlternos = array();
			$this->TotalComprasRepuestos = array();
			
			$this->InventariosEntregadoAServicio = array();
			$this->getTotalFOF = array();
		}


		public function actualizarDatos() {
			set_time_limit(1800);
			$anio = $this->getCampo("anio");
			$mes = $this->getCampo("mes");

			$this->clearQuerys();

			//Cargo las devoluciones
			BD::changeInstancia("facts");
			$r = BD::sql_query("SELECT tipo, numero, fec, codigo, cantidad, valor_unitario, nit, tipo_link, numero_link
				FROM documentos_lin
				WHERE sw=2 AND year(fec)= $anio and month(fec)=$mes") or die("Error query");
			while ($f = BD::obtenerRegistro($r))
				Informe::$devoluciones[$f["tipo_link"] . $f["numero_link"]] = $f;
			
			//UBICACIÓN REPUESTOS
			BD::changeInstancia("mysql");
			BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));

			BD::changeInstancia("facts");
			$insertados = 0;
			$xxperiodo = new Periodo($anio . str_pad($mes, 2, "0", STR_PAD_LEFT));
			$xxperiodo->previous();
			$xxanio = $xxperiodo->getYear();
			$xxmes = $xxperiodo->getMonth();
			$r = BD::sql_query("select * from ubicacion_repuestos where ano=$xxanio and mes=$xxmes") or die("Error query");
			while ($f = BD::obtenerRegistro($r)) {
				$f["informe_id"] = $this->id;
				foreach($f as $xcampo => $xvalue)
					$f[strtolower($xcampo)] = $xvalue;
				if ($f["precio"] == "") unset($f["precio"]);
				if ($f["fecha_creacion"] == "") unset($f["fecha_creacion"]);
				if ($f["fec_ultima_entrada"] == "") unset($f["fec_ultima_entrada"]);
				if ($f["fec_ultima_salida"] == "") unset($f["fec_ultima_salida"]);
				$f["nombre_grupo"] = $f["clase"] == "01" ? "ACCES" : $f["grupo"];
				$tipo_proveedor = "gm";
				if (preg_match("/\*\//", $f["codigo"]) || preg_match("/^[M|m]\d/", $f["codigo"]))
					$tipo_proveedor = "alterno";
				$f["tipo_proveedor"] = $tipo_proveedor;
				$busca_codigo = $f["codigo"];
				if ($f["tipo_proveedor"] == "gm")
					$busca_codigo = str_replace("*$", "", $busca_codigo);
				if (strlen($f["nombre_grupo"]) < 4)
					$f["nombre_grupo"] = Maestro::getSegmento($busca_codigo);

				BD::changeInstancia("mysql");
				$fila = new UbicacionRepuesto($f);
				if (!$fila->save()) {
					echo "<b><font color=red>Error: " . BD::getLastError() . "</font></b>";
					echo "<pre>";
					print_r($fila);
					echo "</pre>";
					BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));
					BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
					BD::eliminar("rp_ventasxasesor", array("informe_id" => $this->id));
					die ("<br />Error copiando los datos del informe ventas por asesor");
				}
				if ($insertados++ % 200 == 0)
					BD::desconectar();
				
				BD::changeInstancia("facts");
			}
			BD::changeInstancia("mysql");
			$fecha_corte = $this->getPeriodo()->format('Y-m-t');
			$query = "update rp_ubicacion_repuestos set edad=case 
			when fec_ultima_salida is null and fec_ultima_entrada is null and fecha_creacion is null then 'N-A' 
			when PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$fecha_corte'), EXTRACT(YEAR_MONTH FROM ifnull(fec_ultima_salida, ifnull(fec_ultima_entrada, fecha_creacion)))) < 12 then '0M-12M' 
			when PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$fecha_corte'), EXTRACT(YEAR_MONTH FROM ifnull(fec_ultima_salida, ifnull(fec_ultima_entrada, fecha_creacion)))) < 24 then '12M-24M'
			else '24M-MAS' end WHERE informe_id=" . $this->id;
			if (!BD::sql_query($query)) {
					echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
					BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
					BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));
					die ("<br />Error actualizando la edad del inventario:<br />" . $query);
			}

			//PERFIL TALLER
			BD::changeInstancia("facts");
			$insertados = 0;
			$r = BD::sql_query("select * from perfil_cliente_comandos where tipo=1 and instruccion is not null order by id asc") or die("Error query");
			while ($f = BD::obtenerRegistro($r)) {
				$query = $f["instruccion"];
				if (strpos($query, "@mes") === FALSE) {
					if (!BD::sql_query($query)) {
						if (strpos($query, "Drop") === FALSE)
							die("Error en query " . $f["id"] . ": $query");
					}
				}
				else {
					//Hacemos query y guardamos en mysql
					BD::changeInstancia("mysql");
					BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
					BD::changeInstancia("facts");
					$query = "select d.numero_orden,e.bodega,e.sucursal,e.vin, 'descripcion_modelo'=g.Descripción_Modelo,f.Modelo_año,f.placa,g.combustible  ,'Kilometraje'=h.Kilometraje_max  ,'Tipo_vehiculo'=case when e.tipo_vehiculo  is null or e.tipo_vehiculo= '' or e.tipo_vehiculo= '0' then 'Otro' else e.tipo_vehiculo end   ,i.Colision_leve,i.Colision_Media,i.Colision_Fuerte,i.Mecanica_especializada,i.Mecanica_rapida,i.Accesorios  ,i.[Garantia_GM(incluye_Extendidas)],i.Alistamiento_y_Peritajes,i.Retornos,i.Internos,i.[2_razon_de_visita]  ,f.Fecha_hora_Entrada,f.Fecha_hora_Salida,d.Fecha_hora_Factura_1,j.Per_Com_entrega,j.Per_Com_factura,j.Fac_Ocu_entrega  ,j.Fac_Ocu_factura,q.Cheque,'Credito_concesionario'=case when (q.Tarjeta_credito+q.tarjeta_Debito+q.Contado+q.cheque)>0 then 0 else 1 end  ,q.Tarjeta_credito,q.tarjeta_Debito,q.Contado,r.Nombres,r.Apellidos,r.Cedula_nit,r.Telefono,r.Mail,r.Direccion,r.Ciudad,r.Rango_de_Edad,r.Fecha_cumpleaños,r.Genero  ,s.repuestos_clientes,s.repuestos_aseguradoras,s.repuestos_garantias,s.repuestos_internos,t.MO_clientes,t.MO_aseguradoras  ,t.MO_garantias,t.MO_internos,t.aseguradora,b.linea_pedida,b.linea_atendida_100,b.FOF  ,j.Nombre_Asesor,h.km_x_ano  from #GEGP_PC_primera_factura d  join #GEGP_PC_servicio e on d.numero_orden=e.numero_orden   join #GEGP_PC_vehiculo f on e.numero_orden=f.numero_orden and e.id=f.id  join #GEGP_PC_modelos g on e.numero_orden=g.numero_orden and e.id=f.id  join #GEGP_PC_Km_x_ano h on f.codigo = h.codigo  join #GEGP_PC_razones i on e.numero_orden=i.numero_orden and e.id=i.id  join #GEGP_PC_permanencia j on e.numero_orden=j.numero_orden and e.id=j.id  join #GEGP_forma_pago_agrupa q on e.numero_orden=q.numero_orden   join #GEGP_PC_tercero r on e.numero_orden=r.numero_orden and e.id=r.id  Left join #GEGP_PC_repuestos s on e.numero_orden=s.numero_orden and e.id=s.id  Left join #GEGP_PC_MO t on e.numero_orden=t.numero_orden and e.id=t.id   left join #GEGP_PC_FOF b on e.numero_orden=b.numero_orden and e.id=b.id   where year(d.fecha_factura)=$anio and month(d.fecha_factura)=$mes";
					$rx = BD::sql_query($query) or die("Error en query $query");
					while ($fx = BD::obtenerRegistro($rx)) {
						$campos = array("informe_id" => $this->id);
						foreach($fx as $campo => $valor) {
							$campo = strtolower(str_replace(array("ñ", "(incluye_Extendidas)"), array("n", ""), $campo));
							$valor = str_replace(array("Nulo"), array(""), $valor);
							$campos[$campo] = $valor;
						}
						BD::changeInstancia("mysql");
						$fila = new PerfilTaller($campos);
						if (!$fila->save()) {
							echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
							BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
							BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));
							die ("<br />Error copiando los datos del perfil taller");
						}
						if ($insertados++ % 150 == 0)
							BD::desconectar();
						
						BD::changeInstancia("facts");
					}
				}
			}

			//VENTAS POR ASESOR
			$insertados = 0;
			BD::changeInstancia("mysql");
			BD::eliminar("rp_ventasxasesor", array("informe_id" => $this->id));

			BD::changeInstancia("facts");
			$query = "SELECT
					tipo, numero, referencia, descripcion, concepto_1, fecha, cantidad, valor_unitario, vendedor, nombres, sw, isnull(descuentos, 0) descuentos, total, totalc, numero_ot, 
					nit_cliente, linea, nombre_cliente, tipo_identificacion, nombre_grupo, nombre_subgrupo, notas, tipo_link, numero_link
				FROM
					ComprasyVentasRptosFacts
				WHERE
					sw in(1,2,3,11) AND	month(fecha)=$mes AND year(fecha)=$anio";
			$r = BD::sql_query($query) or die("Error query $query");
			while ($f = BD::obtenerRegistro($r)) {
				//guardamos en mysql
				$f["informe_id"] = $this->id;
				$tipo_proveedor = "gm";
				if (preg_match("/\*\//", $f["referencia"]) || preg_match("/^[M|m]\d/", $f["referencia"]))
					$tipo_proveedor = "alterno";
				if (isset(Informe::$devoluciones[$f["tipo"] . $f["numero"]])) {
					$f["devolucion"] = "1";
					$f["devolucion_data"] = Informe::$devoluciones[$f["tipo"] . $f["numero"]]["tipo"] ."-". Informe::$devoluciones[$f["tipo"] . $f["numero"]]["numero"];
					$f["devolucion_fecha"] = Fecha::getFecha(Informe::$devoluciones[$f["tipo"] . $f["numero"]]["fec"], "Y-m-d");
				}

				$f["tipo_proveedor"] = $tipo_proveedor;
				BD::changeInstancia("mysql");
				BD::adicionar("cliente_tipo", array("informe_id" => $this->id, "clasificacion" => "" . $f["concepto_1"] . "", "identificacion" => $f["nit_cliente"], "nombre" => $f["nombre_cliente"], "tipo_cliente" => $f["tipo_identificacion"] == "N" ? "flota" : "particular"));
				$f["vehiculo_tipo"] = ($f["nombre_subgrupo"] == "02") ? "pesados" : Maestro::getTipoVehiculo($f["referencia"], $f["linea"]);
				
				if ($f["linea"] == 'ACCESORIOS')
					$f["nombre_grupo"] = 'ACCES';
				if ($f["numero_link"] == '') unset($f["numero_link"]);
				$fila = new VentasPorAsesor($f);
				if (!$fila->save()) {
					echo "<b><font color=red>Error: " . BD::getLastError() . "</font></b>";
					echo "<pre>";
					print_r($fila);
					echo "</pre>";
					BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
					BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));
					BD::eliminar("rp_ventasxasesor", array("informe_id" => $this->id));
					die ("<br />Error copiando los datos del informe ventas por asesor");
				}
				if ($insertados++ % 200 == 0)
					BD::desconectar();
				
				BD::changeInstancia("facts");
			}
			
			BD::desconectar();

			//Actualización mecánica rápida y mecánica especializada
			BD::changeInstancia("mysql");
			$query = "update rp_ventasxasesor set tipo_mecanica=1 where informe_id=" . $this->id . " and numero_ot in (select numero_orden from rp_perfil_taller where mecanica_especializada=1)";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando tipo mecanica especializada:<br />" . $query);
			}

			$query = "update rp_ventasxasesor set tipo_mecanica=2 where informe_id=" . $this->id . " and numero_ot in (select numero_orden from rp_perfil_taller where mecanica_especializada<>1)";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando tipo mecanica rapida:<br />" . $query);
			}

		//Marcación de los registros con el ID del item que ocupa en la tabla final
			//1 - LIVIANOS - Mostrador solo flotas
			$codigo = 1;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FA', 'FRD') 
					AND v.tipo_proveedor='gm'
					AND c.clasificacion<>8
					AND v.nombre_grupo not in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//2 - LIVIANOS - Mostrador Colisión / Aseguradoras
			$codigo = 2;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FA', 'FRD') 
					and v.sw=1
					AND v.tipo_proveedor='gm' 
					AND c.clasificacion=8
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//4 - LIVIANOS - Mostrador (Otros) / Ventas Externas
			$codigo = 4;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FA', 'FRD')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND v.sw=1
					AND c.tipo_cliente='particular'
					AND v.vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//5 - LIVIANOS - Mostrador Solochevrolet
			$codigo = 5;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FS', 'FSC') 
					AND v.sw=1
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//6 - LIVIANOS - Mecánica Rápida - Taller solo flotas
			$codigo = 6;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//7 - LIVIANOS - Mecánica Rápida - Taller Uno a Uno
			$codigo = 7;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//8 - LIVIANOS - Mecánica Especilizada - Taller solo flotas
			$codigo = 8;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//9 - LIVIANOS - Mecánica Especilizada - Taller Uno a Uno
			$codigo = 9;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10 - LIVIANOS - Taller Colisión Uno a Uno
			$codigo = 10;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo not IN ('ACCES')
					AND c.clasificacion<>8
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//11 - LIVIANOS - Taller Colisión Aseguradoras
			$codigo = 11;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//12 - LIVIANOS - Garantías
			$codigo = 12;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FG')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//14 - LIVIANOS - Alternos Taller
			$codigo = 14;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FT', 'FC', 'FG')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//15- LIVIANOS - Alternos Colisión
			$codigo = 15;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FL')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//16- LIVIANOS - Alternos Mostrador
			$codigo = 16;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FA','FS','FRD','FSC')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}


			//1001 - PESADOS - Mostrador solo flotas
			$codigo = 1001;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FA', 'FRD') 
					AND v.tipo_proveedor='gm'
					AND c.clasificacion<>8
					AND v.nombre_grupo not in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1002 - PESADOS - Mostrador Colisión / Aseguradoras
			$codigo = 1002;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FA', 'FRD') 
					and v.sw=1
					AND v.tipo_proveedor='gm' 
					AND c.clasificacion=8
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1004 - PESADOS - Mostrador (Otros) / Ventas Externas
			$codigo = 1004;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FA', 'FRD')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND v.sw=1
					AND c.tipo_cliente='particular'
					AND v.vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1005 - PESADOS - Mostrador Solochevrolet
			$codigo = 1005;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FS', 'FSC') 
					AND v.sw=1
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1006 - PESADOS - Mecánica Rápida - Taller solo flotas
			$codigo = 1006;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1007 - PESADOS - Mecánica Rápida - Taller Uno a Uno
			$codigo = 1007;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1008 - PESADOS - Mecánica Especilizada - Taller solo flotas
			$codigo = 1008;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='flota'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//1009 - PESADOS - Mecánica Especilizada - Taller Uno a Uno
			$codigo = 1009;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE c.tipo_cliente='particular'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC') 
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10010 - PESADOS - Taller Colisión Uno a Uno
			$codigo = 10010;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo not IN ('ACCES')
					AND c.clasificacion<>8
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10011 - PESADOS - Taller Colisión Aseguradoras
			$codigo = 10011;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo NOT IN ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10012 - PESADOS - Garantías
			$codigo = 10012;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FG')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10014 - PESADOS - Alternos Taller
			$codigo = 10014;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FT', 'FC', 'FG')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10015 - PESADOS - Alternos Colisión
			$codigo = 10015;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FL')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//10016 - PESADOS - Alternos Mostrador
			$codigo = 10016;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FA','FS','FRD','FSC')
					AND v.nombre_grupo NOT IN ('ACCES')
					AND vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//201 - Costo de ventas Mostrador
			$codigo = 201;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FA','FSC','FS','FRD')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//202 - Costo de venta Taller
			$codigo = 202;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FC', 'FT', 'NCDR')
					AND v.sw IN (1,11) AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//203 - Costo de venta Colisión
			$codigo = 203;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FL')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//204 - Costo de venta Garantías
			$codigo = 204;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FG')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//205 - Costo de venta Internos
			$codigo = 205;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('TI')
					AND v.sw=1 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//206 - Costo de venta Alternos
			$codigo = 206;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FA','FS','FSC','FRD','FL','FT','FC','NCDR')
					AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//207 - Compras repuestos GM
			$codigo = 207;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo in ('CRCO') 
					AND v.devolucion=0 AND v.sw=3 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//208 - Compras repuestos a otros concesionarios
			$codigo = 208;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo in ('CRO') 
					AND v.sw=3 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//209 - Compras repuestos a otros proveedores
			$codigo = 209;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo in ('CROT') 
					AND v.sw=3 AND v.nombre_grupo NOT IN ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			$this->setCampo("fecha", date("Y-m-d H:i:s"));
			$this->update();
			return true;
		}

		public static function getLimitePeriodo() {
			$xp = new Periodo(date("Ym"));
			//$xp->previous();	//Un mes menos del actual
			return $xp->toString();
		}

		public static function queryMYSQL($sql) {
			BD::changeInstancia("mysql");
			if (isset(self::$querys[md5($sql)])) {
				mysqli_data_seek(self::$querys[md5($sql)], 0);
				return self::$querys[md5($sql)];
			}
			self::$querys[md5($sql)] = BD::sql_query($sql) or die("ErrorQuery: " . BD::getLastError());
			return self::$querys[md5($sql)];
		}
	}
?>