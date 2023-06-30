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
			17 => "LIVIANOS - Accesorios Genuinos",

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

		public static $item_coa = array(
			3000 => "Genuinos GM - Mostrador Accesorios",
			3001 => "Genuinos GM - Ventas Accesorios Vehiculos Nuevos",
			3002 => "Genuinos GM - Taller Accesorios",
			3003 => "Genuinos GM - Chevystar",
			3004 => "Alternos Accesorios - Mostrador Accesorios",
			3005 => "Alternos Accesorios - Ventas Accesorios Vehiculos Nuevos",
			3006 => "Alternos Accesorios - Taller Accesorios",

			3007 => "Costo de Ventas Accesorios - Costo Genuinos GM",
			3008 => "Costo de Ventas Accesorios - Costo Genuinos Alternos",
			3009 => "Costo de Ventas Accesorios - Costo Chevystar",

			3010 => "Compra Accesorios - Compras GM",
			3011 => "Compra Accesorios - Compras a otros concesionarios",
			3012 => "Compra Accesorios - Compras a otros proveedores"
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
		 * COA
		 **/
		public function getTotalVentasAcces() { return $this->getTotalAccesGM() + $this->getTotalAccesAlterno(); }

		public function getTotalAccesGM() { return  $this->getMostradorAccesGM() + $this->getTallerAccesGM(); }

		/** Genuinos GM - Mostrador Accesorios GM - 3000*/
		public function getMostradorAccesGM() { return $this->getQueryACCES('gm', array('FA', 'FRD', 'FS', 'FSC'), 3000); }
		public function getMostradorAccesGMData() { return $this->getQueryACCESData('gm', array('FA', 'FRD', 'FS', 'FSC')); }

		/** Genuinos GM - Taller Accesorios - 3002*/
		public function getTallerAccesGM() { return $this->getQueryACCES('gm', array('FT', 'FC', 'FL', 'FPC'), 3002); }
		public function getTallerAccesGMData() { return $this->getQueryACCESData('gm', array('FT', 'FC', 'FL', 'FPC')); }


		public function getTotalAccesAlterno() { return  $this->getMostradorAccesAlterno() + $this->getTallerAccesAlterno(); }
		/** Alternos Accesorios - Mostrador Accesorios Alternos - 3004*/
		public function getMostradorAccesAlterno() { return $this->getQueryACCES('alterno', array('FA', 'FRD', 'FS', 'FSC'), 3004); }
		public function getMostradorAccesAlternoData() { return $this->getQueryACCESData('alterno', array('FA', 'FRD', 'FS', 'FSC')); }

		/** Alternos Accesorios - Taller Accesorios - 3006*/
		public function getTallerAccesAlterno() { return $this->getQueryACCES('alterno', array('FT', 'FC', 'FL', 'FPC'), 3006); }
		public function getTallerAccesAlternoData() { return $this->getQueryACCESData('alterno', array('FT', 'FC', 'FL', 'FPC')); }

		
		public function getTotalCostoVentasAcces() { return $this->getCostoVentaAccesGM() + $this->getCostoVentaAccesAlterno(); }
		
		/** Costo de Ventas Accesorios - Costo Genuinos GM - 3007*/
		public function getCostoVentaAccesGM() { return $this->getQueryCostoACCES('gm', array('FA','FRD','FS','FSC','FC','FL','FT', 'FPC'), 3007); }
		public function getCostoVentaAccesGMData() { return $this->getQueryACCESData('gm', array('FA','FRD','FS','FSC','FC','FL','FT', 'FPC')); }

		/** Costo de Ventas Accesorios - Costo Genuinos Alternos - 3008*/
		public function getCostoVentaAccesAlterno() { return $this->getQueryCostoACCES('alterno', array('FA','FRD','FS','FSC','FC','FL','FT', 'FPC'), 3008); }
		public function getCostoVentaAccesAlternoData() { return $this->getQueryACCESData('alterno', array('FA','FRD','FS','FSC','FC','FL','FT', 'FPC')); }

		//Funciones de ayuda para COA
		public function getQueryACCES($param, $docs, $codigo_coa) {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $docs) . "') 
					AND v.tipo_proveedor='$param'
					and v.devolucion=0
					AND v.nombre_grupo in ('ACCES')
					ORDER BY v.fecha");
			
			$result = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_coa=$codigo_coa 
					AND v.nombre_grupo in ('ACCES') 
					AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$result -= $devoluciones;
			return $result;
		}

		public function getQueryACCESData($param, $docs) {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $docs) . "') 
					AND v.tipo_proveedor='$param'
					AND v.nombre_grupo in ('ACCES')
					ORDER BY v.fecha");
			
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		//Funciones de ayuda para COA
		public function getQueryCostoACCES($param, $docs, $codigo_coa) {
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $docs) . "') 
					AND v.tipo_proveedor='$param'
					and v.devolucion=0
					AND v.nombre_grupo in ('ACCES')
					ORDER BY v.fecha");
			
			$result = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_coa=$codigo_coa 
					AND v.nombre_grupo in ('ACCES') 
					AND v.informe_id<>" . $this->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$result -= $devoluciones;
			return $result;
		}


		//Compra Accesorios
		public function getTotalCompraAcces() { return $this->getCompraAccesGM() + $this->getCompraAccesAlternos() + $this->getCompraAccesOtros(); }

		//Compra Accesorios - Compras GM
		public function getCompraAccesGM() {
			return $this->getTotalComprasRepuestos(array('CRCO'), '', true);
		}

		//Compra Accesorios - Compras a otros concesionarios
		public function getCompraAccesAlternos() {
			return $this->getTotalComprasRepuestos(array('CRO'), '', true);
		}

		//Compra Accesorios - Compras a otros proveedores
		public function getCompraAccesOtros() {
			return $this->getTotalComprasRepuestos(array('CROT'), '', true);
		}

		public function getLealtadBrutaAcces() {
			if ($this->getTotalCompraAcces() > 0)
				return  ($this->getCompraAccesGM() + $this->getCompraAccesAlternos()) / $this->getTotalCompraAcces();
			return 0;
		}

		public function getLealtadNetaAcces() {
			if ($this->getTotalCompraAcces() > 0)
				return  $this->getCompraAccesGM() / $this->getTotalCompraAcces();
			return 0;
		}

		public function getInventariosAcces($param = '') {
			BD::changeInstancia("mysql");
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos 
				where  codigo NOT LIKE 'CL%' AND
					informe_id=" . $this->id . " 
					and bodega <> 99
					and nombre_grupo = 'ACCES'
					and tipo_proveedor='$param'";
			$r = self::queryMYSQL($query);
			return ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		}

		public function getInventariosAccesData($param = '') {
			$query_add = "";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select *, costo_promedio * stock xtotal
				from rp_ubicacion_repuestos 
				where  codigo NOT LIKE 'CL%' AND
					informe_id=" . $this->id . " 
					and bodega <> 99
					AND nombre_grupo = 'ACCES'
					and tipo_proveedor='$param'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
		
		public function getInventarioInicialAcces() {
			$inf = new Informe();
			$pp = new Periodo($this->getPeriodo()->toString());
			$pp->previous();
			$inf->change($pp->getYear(), $pp->getMonth());
			return $inf->getInventarioFinalAcces();
		}

		public function getInventarioFinalAcces() {
			return $this->getInventariosAcces('alterno') + $this->getInventariosAcces('gm');
		}

		public function getTotalAccesFOF() {
			$query_add = " AND accesorios=1";
			BD::changeInstancia("mysql");
			$query = "select avg(fof) total from rp_perfil_taller where informe_id=" . $this->id . " $query_add";
			$r = self::queryMYSQL($query) or die("Error en query: $query");
			$return = ($f = BD::obtenerRegistro($r)) ? round($f["total"], 2) : 0;
			return $return;
		}

		public function getRotacionBodegaAcces() {
			if ($this->getInventarioFinalAcces() > 0)
				return ($this->getTotalCostoVentasAcces() * 12) / $this->getInventarioFinalAcces();
			return 0;
		}

		/**
		 * MOSTRADOR
		 */
		public function getTotalMostrador($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			if ($chevrolet)
				return $this->getMostradorSolochevrolet($tipo_vehiculo, $ACCESS);
			return $this->getMostradorSoloFlotas($tipo_vehiculo, $ACCESS) 
				+ $this->getMostradorColision($tipo_vehiculo, $ACCESS) 
				+ $this->getMostadorMantenimientoDesgaste($tipo_vehiculo, $ACCESS) 
				+ $this->getMostadorOtrosVentasExternas($tipo_vehiculo, $ACCESS);
		}

		public function getMostradorSoloFlotas($tipo_vehiculo, $ACCESS = false, $campo = 'total') {
			BD::changeInstancia("mysql");
			$tipodocs = array('FA', 'FRD');
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.$campo) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.tipo_proveedor='gm'
					and v.devolucion=0
					AND c.clasificacion<>8
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='$tipo_vehiculo'");
			$result = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.$campo) total FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id<>" . $this->id . "
					AND v.tipo_proveedor='gm'
					AND c.clasificacion<>8
					and v.nombre_grupo $operador_access in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='$tipo_vehiculo'
					and concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 
						AND d.tipo_link in ('" . implode("','", $tipodocs) . "')  and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$result -= $devoluciones;
			return $result;
		}

		public function getMostradorSoloFlotasData($tipo_vehiculo, $ACCESS = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$tipodocs = array('FA', 'FRD');
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.tipo_proveedor='gm'
					AND c.clasificacion<>8
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND c.tipo_cliente='flota'
					AND vehiculo_tipo='$tipo_vehiculo' ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMostradorColision($tipo_vehiculo, $ACCESS = false) {//301785
			BD::changeInstancia("mysql");
			$tipodocs = array('FA', 'FRD');
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					and v.sw=1
					and v.devolucion=0
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 2 : 1002;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo 
					AND v.informe_id<>" . $this->id . " 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMostradorColisionData($tipo_vehiculo, $ACCESS = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$tipodocs = array('FA', 'FRD');
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v 
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						and v.sw=1
						AND v.tipo in ('" . implode("','", $tipodocs) . "') 
						AND v.nombre_grupo $operador_access in ('ACCES')
						AND v.tipo_proveedor='gm' 
						AND c.clasificacion=8
						AND vehiculo_tipo='$tipo_vehiculo' ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMostadorOtrosVentasExternas($tipo_vehiculo, $ACCESS = false) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FA', 'FRD')
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND v.sw=1
					and v.devolucion=0
					AND c.tipo_cliente='particular'
					AND v.vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 4 : 1004;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " 
					AND v.nombre_grupo $operador_access in ('ACCES')
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMostadorOtrosVentasExternasData($tipo_vehiculo, $ACCESS = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FA', 'FRD')  
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.tipo_proveedor='gm' 
					AND v.sw=1
					AND c.tipo_cliente='particular'
					AND v.vehiculo_tipo='$tipo_vehiculo' ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMostadorMantenimientoDesgaste($tipo_vehiculo, $ACCESS = false) {
			BD::changeInstancia("mysql");
			return 0;	//No se llena
		}

		public function getMostadorMantenimientoDesgasteData($tipo_vehiculo, $ACCESS = false) {
			BD::changeInstancia("mysql");
			return array();
		}

		public function getMostradorSolochevrolet($tipo_vehiculo, $ACCESS = false, $chevrolet = true) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('FS') 
					and v.devolucion=0
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			//Restar devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE
					v.informe_id<>" . $this->id . "   
					AND v.tipo_proveedor='gm'
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.vehiculo_tipo='$tipo_vehiculo'
					and concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 
						AND d.tipo_link in ('FS') and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMostradorSolochevroletData($tipo_vehiculo, $ACCESS = false, $chevrolet = true) {
			$result = array();
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion 
				WHERE v.informe_id=" . $this->id . " 
				AND v.tipo in ('FS') 
				and v.devolucion=0
				AND v.tipo_proveedor='gm' 
				AND v.nombre_grupo $operador_access in ('ACCES')
				AND v.vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
		/**-------------------------------------------------------------------*/


		/**
		 * Taller Mecánica y Mantenimiento
		 */

		public function getTotalTallerMecanicaMantenimiento($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getTotalMecanicaRapida($tipo_vehiculo, $ACCESS, $chevrolet) + $this->getTotalMecanicaEspecializada($tipo_vehiculo, $ACCESS, $chevrolet);
		}

		public function getTotalMecanicaRapida($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanicaRapidaFlotas($tipo_vehiculo, $ACCESS, $chevrolet)
				+  $this->getMecanicaRapidaUno($tipo_vehiculo, $ACCESS, $chevrolet);
		}

		public function getMecanica($tipo_vehiculo, $ACCESS, $chevrolet, $tipo_cliente, $tipo_mecanica, $campo = 'total') {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = ($chevrolet) ? array('FSC'): array('FT', 'FC', 'FPC');
			$r = self::queryMYSQL("select sum(v.$campo) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='$tipo_cliente'
					AND v.tipo_proveedor='gm'
					and v.devolucion=0
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.tipo_mecanica = $tipo_mecanica
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			// $codigo = $tipo_vehiculo == "liviano" ? 6 : 1006;
			$r = self::queryMYSQL("select sum(v.$campo) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion	
				WHERE 
					v.informe_id<>" . $this->id . " 
					AND c.tipo_cliente='$tipo_cliente'
					AND v.tipo_proveedor='gm'
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.tipo_mecanica = $tipo_mecanica
					AND v.vehiculo_tipo='$tipo_vehiculo'

					and concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) 
						FROM rp_ventasxasesor d WHERE 
							d.tipo_link in ('" . implode("', '", $documentos) . "') 
							and d.sw=2 
							and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getMecanicaData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false, $tipo_cliente, $tipo_mecanica) {
			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = ($chevrolet) ? array('FSC'): array('FT', 'FC', 'FPC');

			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND c.tipo_cliente='$tipo_cliente'
					AND v.tipo_proveedor='gm' 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.tipo_mecanica = $tipo_mecanica
					AND v.tipo in ('" . implode("', '", $documentos) . "')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getMecanicaRapidaFlotas($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false, $campo = 'total') {
			return $this->getMecanica($tipo_vehiculo, $ACCESS, $chevrolet, 'flota', 2, $campo);
		}

		public function getMecanicaRapidaFlotasData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanicaData($tipo_vehiculo, $ACCESS, $chevrolet, 'flota', 2);
		}
		
		public function getMecanicaRapidaUno($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanica($tipo_vehiculo, $ACCESS, $chevrolet, 'particular', 2);
		}

		public function getMecanicaRapidaUnoData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanicaData($tipo_vehiculo, $ACCESS, $chevrolet, 'particular', 2);
		}

	
	
		public function getTotalMecanicaEspecializada($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanicaEspecializadaFlotas($tipo_vehiculo, $ACCESS, $chevrolet)
				+  $this->getMecanicaEspecializadaUno($tipo_vehiculo, $ACCESS, $chevrolet);
		}


		public function getMecanicaEspecializadaFlotas($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false, $campo = 'total') {
			return $this->getMecanica($tipo_vehiculo, $ACCESS, $chevrolet, 'flota', 1, $campo);
		}
		public function getMecanicaEspecializadaFlotasData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanicaData($tipo_vehiculo, $ACCESS, $chevrolet, 'flota', 1);
		}
		
		public function getMecanicaEspecializadaUno($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanica($tipo_vehiculo, $ACCESS, $chevrolet, 'particular', 1);
		}
		public function getMecanicaEspecializadaUnoData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getMecanicaData($tipo_vehiculo, $ACCESS, $chevrolet, 'particular', 1);
		}


		/** 
		 * COLISION 
		*/
		public function getTotalColision($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getColisionUno($tipo_vehiculo, $ACCESS, $chevrolet) + $this->getColisionAseguradoras($tipo_vehiculo, $ACCESS, $chevrolet);
		}

		public function getColisionUno($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$documentos = $chevrolet ? array('XXXX') : array('FL');
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND v.devolucion=0
					AND c.clasificacion<>8
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 10 : 10010;
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item=$codigo AND v.informe_id<>" . $this->id . " 
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getColisionUnoData($tipo_vehiculo = 'liviano', $ACCESS = false) {
			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND c.clasificacion<>8
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getColisionAseguradoras($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$documentos = $chevrolet ? array('XXXX') : array('FL');
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND v.devolucion=0
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 11 : 10011;
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id<>" . $this->id . "
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getColisionAseguradorasData($tipo_vehiculo = 'liviano', $ACCESS = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('FL') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND c.clasificacion=8
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalGarantias($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			BD::changeInstancia("mysql");
			$documentos = $chevrolet ? array('XXXX') : array('FG', 'CR');
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND v.devolucion=0
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
				
			//Devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.total) total FROM rp_ventasxasesor v 
					WHERE v.informe_id<>" . $this->id . " 
						AND v.tipo_proveedor='gm' 
						AND v.nombre_grupo $operador_access in ('ACCES')
						AND vehiculo_tipo='$tipo_vehiculo'
					AND concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d 
						where d.sw=2 
							AND d.tipo_link in ('" . implode("', '", $documentos) . "') 
							and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getTotalGarantiasData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$documentos = $chevrolet ? array('XXXX') : array('FG', 'CR');
			$operador_access = $ACCESS ? "" : "NOT";

			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='gm' 
					AND v.tipo in ('" . implode("', '", $documentos) . "') 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
	
		public function getTotalInternas($tipo_vehiculo = 'liviano', $ACCESS = false) {
			BD::changeInstancia("mysql");
			return 0;
		}

		public function getTotalInternasData($tipo_vehiculo = 'liviano', $ACCESS = false) {
			BD::changeInstancia("mysql");
			return array();
		}

		public function getTotalAlternos($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getAlternosTaller($tipo_vehiculo, $ACCESS, $chevrolet) 
				+ $this->getAlternosColision($tipo_vehiculo, $ACCESS, $chevrolet) 
				+ $this->getAlternosMostrador($tipo_vehiculo, $ACCESS, $chevrolet);
		}
		
		public function getAlternosTaller($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('FSC') : array('FT', 'FC', 'FG', 'FPC');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("', '", $documentos) . "')
					AND v.devolucion=0
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion	
				WHERE 
					v.informe_id<>" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'
					AND concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) FROM rp_ventasxasesor d where d.sw=2 
						AND d.tipo_link in ('" . implode("', '", $documentos) . "') and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getAlternosTallerData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('FSC') : array('FT', 'FC', 'FG', 'FPC');
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("', '", $documentos) . "')
					AND v.devolucion=0
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getAlternosColision($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('XX') : array('FL');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("', '", $documentos) . "')
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.referencia LIKE '%*/'
					AND v.devolucion=0
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? 15 : 10015;
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id<>" . $this->id . " 
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.referencia LIKE '%*/'
					AND v.ubicacion_item=$codigo
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2
						AND d.tipo_link in ('" . implode("', '", $documentos) . "') and  d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getAlternosColisionData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('XX') : array('FL');
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("', '", $documentos) . "')
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getAlternosMostrador($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('FS') : array('FA', 'FRD');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("', '", $documentos) . "')
					AND v.devolucion=0
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion	
				WHERE 
					v.informe_id<>" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'
					AND concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) FROM rp_ventasxasesor d where d.sw=2 
						AND d.tipo_link in ('" . implode("', '", $documentos) . "') and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getAlternosMostradorData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			$documentos = $chevrolet ? array('FS') : array('FA', 'FRD');
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v 
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						AND v.tipo_proveedor='alterno' 
						AND v.tipo in ('" . implode("', '", $documentos) . "')
						AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
						AND v.nombre_grupo $operador_access in ('ACCES')
						AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}
		
		//17 - Accesorios Genuinos
		public function getAccesoriosGenuinos($tipo_vehiculo = 'liviano', $ACCES = "no aplica", $chevrolet = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $documentos) . "') 
					AND v.tipo_proveedor='gm'
					and v.devolucion=0
					AND vehiculo_tipo='$tipo_vehiculo'
					AND v.nombre_grupo in ('ACCES')");
			
			$result = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Restar devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.total) total 
					FROM rp_ventasxasesor v 
					WHERE 
						v.informe_id<>" . $this->id . "
						AND v.tipo in ('" . implode("','", $documentos) . "') 
						AND v.tipo_proveedor='gm'
						AND vehiculo_tipo='$tipo_vehiculo'
						AND v.nombre_grupo in ('ACCES')
						and concat(v.tipo, v.numero) in (
							select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $this->id . "
						)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$result -= $devoluciones;
			return $result;
		}

		

		public function getAccesoriosGenuinosData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC');
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v 
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						AND v.tipo in ('" . implode("','", $documentos) . "') 
						AND v.tipo_proveedor='gm'
						AND vehiculo_tipo='$tipo_vehiculo'
						AND v.nombre_grupo in ('ACCES')");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getAccesoriosAlternos($tipo_vehiculo = 'liviano', $ACCES = "no aplica", $chevrolet = false) {
			$result = array();
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC');
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo in ('" . implode("','", $documentos) . "') 
					AND v.tipo_proveedor='alterno'
					and v.devolucion=0
					AND vehiculo_tipo='$tipo_vehiculo'
					AND v.nombre_grupo in ('ACCES')
					ORDER BY v.fecha");
			
			$result = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id<>" . $this->id . " 
					 
					AND v.tipo_proveedor='alterno'
					AND vehiculo_tipo='$tipo_vehiculo'
					AND v.nombre_grupo in ('ACCES')
					and concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2
						AND d.tipo_link in ('" . implode("','", $documentos) . "') and d.informe_id=" . $this->id . "
					)");
						
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$result -= $devoluciones;
			return $result;
		}

		public function getAccesoriosAlternosData($tipo_vehiculo = 'liviano', $ACCES = "no aplica", $chevrolet = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC');
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						AND v.tipo in ('" . implode("','", $documentos) . "') 
						AND v.tipo_proveedor='alterno'
						AND vehiculo_tipo='$tipo_vehiculo'
						AND v.nombre_grupo in ('ACCES')
						ORDER BY v.fecha");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getRepuestosFlotasChevrolet($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FT', 'FC', 'FG', 'FPC',  'FL',  'FA', 'FRD');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("','", $documentos) . "') 
					AND v.devolucion=0
					AND c.tipo_cliente='flota'
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE v.informe_id<>" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.devolucion=0
					AND c.tipo_cliente='flota'
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					and concat(v.tipo, v.numero) in (select concat(d.tipo_link,d.numero_link) 
						FROM rp_ventasxasesor d 
						WHERE 
							d.tipo_link in ('" . implode("','", $documentos) . "') 
							and d.sw=2 
							and d.informe_id=" . $this->id . ")");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getRepuestosFlotasChevroletData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FT', 'FC', 'FG', 'FPC',  'FL',  'FA', 'FRD');
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						AND v.tipo_proveedor='alterno' 
						AND v.tipo in ('" . implode("','", $documentos) . "') 
						AND v.devolucion=0
						AND c.tipo_cliente='flota'
						AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
						AND v.nombre_grupo $operador_access in ('ACCES')
						AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getRepuestosFlotasOtrasMarcas($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "" : "NOT";
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FT', 'FC', 'FG', 'FPC',  'FL',  'FA', 'FRD');
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				WHERE v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND v.tipo in ('" . implode("','", $documentos). "')
					AND v.devolucion=0
					AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = $tipo_vehiculo == "liviano" ? "14,15,16" : "10014, 10015, 10016";
			$r = self::queryMYSQL("select sum(v.total) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion	
				WHERE 
					v.informe_id<>" . $this->id . " 
					AND v.tipo_proveedor='alterno' 
					AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)
					AND v.nombre_grupo $operador_access in ('ACCES')
					AND vehiculo_tipo='$tipo_vehiculo'
					AND concat(v.tipo, v.numero) in (
						select concat(d.tipo_link,d.numero_link) FROM rp_ventasxasesor d where d.sw=2 
						AND d.tipo_link in ('" . implode("','", $documentos). "') and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getRepuestosFlotasOtrasMarcasData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			$result = array();
			BD::changeInstancia("mysql");
			$documentos = $chevrolet ? array('FS', 'FSC') : array('FT', 'FC', 'FG', 'FPC',  'FL', 'FA', 'FRD');
			$operador_access = $ACCESS ? "" : "NOT";
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
					FROM rp_ventasxasesor v 
					INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
					WHERE v.informe_id=" . $this->id . " 
						AND v.tipo_proveedor='alterno' 
						AND v.tipo in ('" . implode("','", $documentos). "')
						AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)
						AND v.nombre_grupo $operador_access in ('ACCES')
						AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalVentasDetal($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return 
				$this->getTotalMostrador($tipo_vehiculo, $ACCESS, $chevrolet) 
				+ $this->getTotalTallerMecanicaMantenimiento($tipo_vehiculo, $ACCESS, $chevrolet) 
				+ $this->getTotalColision($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getTotalGarantias($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getTotalInternas($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getTotalAlternos($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getRepuestosFlotasOtrasMarcas($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getAccesoriosGenuinos($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getAccesoriosAlternos($tipo_vehiculo, $ACCESS, $chevrolet);
				//+ $this->getRepuestosFlotasChevrolet($tipo_vehiculo, $ACCESS, $chevrolet);
		}

		/**---------------------------------------------- */

		/**
		 * Costos de venta
		 */
		public function getTotalCostosVenta($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getVentasMostrador($tipo_vehiculo, $ACCESS, $chevrolet) 
				+ $this->getVentasTaller($tipo_vehiculo, $ACCESS, $chevrolet ) 
				+ $this->getVentasColision($tipo_vehiculo, $ACCESS, $chevrolet ) 
				+ $this->getVentasGarantias($tipo_vehiculo, $ACCESS, $chevrolet ) 
				+ $this->getVentasInternos($tipo_vehiculo, $ACCESS, $chevrolet ) 
				+ $this->getVentasAlternos($tipo_vehiculo, $ACCESS, $chevrolet)
				+ $this->getCostoAccesoriosGenuinos($tipo_vehiculo, true, $chevrolet)
				+ $this->getCostoAccesoriosAlternos($tipo_vehiculo, true, $chevrolet)
				+ $this->getCostoRepuestosFlotasOtrasMarcas($tipo_vehiculo, $ACCESS, $chevrolet);
		}

		public function getCostoVentas($documentos = array(), $tipo_vehiculo = '', $ACCESS = false, $tipo_proveedor = 'gm', $query_add = "") {
			$operador_access = $ACCESS ? "" : "NOT";
			if ($ACCESS !== -1) 			$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			if ($tipo_vehiculo != "") 	$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				WHERE 
					v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='$tipo_proveedor' 
					AND v.tipo in ('" . implode("','", $documentos) . "') 
					$query_add
					AND v.devolucion=0");
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;

			//Devoluciones de meses anteriores
			$codigo = 201;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.informe_id<>" . $this->id . "
					AND v.tipo_proveedor='$tipo_proveedor'
					$query_add
					and concat(v.tipo, v.numero) in ( 
						select concat(d.tipo_link,d.numero_link) 
						from rp_ventasxasesor d where 
							d.sw=2
							AND d.tipo_link in ('" . implode("','", $documentos) . "') 
							and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$return -= $devoluciones;
			return $return;
		}

		public function getCostoVentasData($documentos = array(), $tipo_vehiculo = '', $ACCESS = false, $tipo_proveedor = 'gm', $query_add = "") {
			$operador_access = $ACCESS ? "" : "NOT";
			if ($ACCESS !== -1) 			$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			if ($tipo_vehiculo != "") 	$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					AND v.tipo_proveedor='$tipo_proveedor' 
					AND v.tipo in ('" . implode("','", $documentos) . "')
					$query_add");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		//Costo de Venta Mostrador
		public function getVentasMostrador($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('FS') : array('FA','FRD'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		public function getVentasMostradorData($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('FS') : array('FA','FRD'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo de Venta Taller
		public function getVentasTaller($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('FSC', 'NDSR', 'NCSR') : array('FC', 'FT', 'NCDR', 'FPC', 'EXAJ'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		public function getVentasTallerData($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('FSC', 'NDSR', 'NCSR') : array('FC', 'FT', 'NCDR', 'FPC', 'EXAJ'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo de Venta Colision
		public function getVentasColision($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('x') : array('FL'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		public function getVentasColisionData($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('x') : array('FL'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo de Venta Garantías
		public function getVentasGarantias($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('x') : array('FG', 'CR'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		public function getVentasGarantiasData($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('x') : array('FG'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo de Venta Internos
		public function getVentasInternos($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('x') : array('TI'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		public function getVentasInternosData($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('x') : array('TI'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo de Venta Alternos
		public function getVentasAlternos($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('FS','FSC', 'NDSR', 'NCSR') : array('FA','FRD','FL','FT','FC', 'NCDR', 'FG', 'FPC'), $tipo_vehiculo, $ACCESS, 'alterno',
				"AND replace(v.referencia, '*/', '') in (select material from rp_maestro)");
		}

		public function getVentasAlternosData($tipo_vehiculo = '', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('FS','FSC', 'NDSR', 'NCSR') : array('FA','FRD','FL','FT','FC', 'NCDR', 'FG', 'FPC'), $tipo_vehiculo, $ACCESS, 'alterno',
				"AND replace(v.referencia, '*/', '') in (select material from rp_maestro)");
		}

		//Costo Repuestos Flotas Otras Marcas
		public function getCostoRepuestosFlotasOtrasMarcas($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('FS','FSC', 'NDSR', 'NCSR') : array('FA','FRD','FL','FT','FC', 'NCDR', 'FG', 'FPC'), $tipo_vehiculo, $ACCESS, 'alterno',
				"AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)");
		}

		public function getCostoRepuestosFlotasOtrasMarcasData($tipo_vehiculo = 'liviano', $ACCESS = false, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('FS','FSC', 'NDSR', 'NCSR') : array('FA','FRD','FL','FT','FC', 'NCDR', 'FG', 'FPC'), $tipo_vehiculo, $ACCESS, 'alterno',
				"AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)");
		}

		//Costo Accesorios Genuinos
		public function getCostoAccesoriosGenuinos($tipo_vehiculo = '', $ACCESS = true, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo Accesorios Genuinos
		public function getCostoAccesoriosGenuinosData($tipo_vehiculo = '', $ACCESS = true, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC'), $tipo_vehiculo, $ACCESS, 'gm');
		}

		//Costo Accesorios Alternos
		public function getCostoAccesoriosAlternos($tipo_vehiculo = '', $ACCESS = true, $chevrolet = false) {
			return $this->getCostoVentas($chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC'), $tipo_vehiculo, $ACCESS, 'alterno');
		}

		//Costo Accesorios Alternos
		public function getCostoAccesoriosAlternosData($tipo_vehiculo = '', $ACCESS = true, $chevrolet = false) {
			return $this->getCostoVentasData($chevrolet ? array('FS', 'FSC') : array('FA', 'FRD', 'FT', 'FC', 'FL', 'FPC'), $tipo_vehiculo, $ACCESS, 'alterno');
		}

		public function getTotalComprasRepuestosOtrasMarcas($tipodocs = array(), $tipo_vehiculo = '', $ACCESS = false, $bodega = '') {
			$query_add = "";
			$operador_access = $ACCESS ? "" : "NOT";
			
			if ($tipo_vehiculo != "")					$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			if ($bodega !== '' && $bodega < 0) 			$query_add .= " AND (v.bodega is NULL OR v.bodega<>'" . abs($bodega) . "') ";	//Si es negativo indica que es diferente de
			if ($bodega !== '' && $bodega >= 0) 		$query_add .= " AND v.bodega='$bodega' ";		//Positivo indica que solo esa bodega
			if ($ACCESS !== -1) 						$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)
					AND v.devolucion=0 
					AND v.sw=3");
			$total= ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE
					v.informe_id<>" . $this->id . "
					$query_add 
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) 
						from rp_ventasxasesor d 
						where d.sw=2 
						AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)
						AND d.tipo_link in ('" . implode("','", $tipodocs) . "') 
						and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$total -= $devoluciones;
			return $total;
		}

		public function getTotalComprasRepuestosOtrasMarcasData($tipodocs = array(), $tipo_vehiculo = '', $ACCESS = false, $bodega = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")	$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			if ($bodega != '' && $bodega < 0) 			$query_add .= " AND (v.bodega is NULL OR v.bodega<>'$bodega') ";	//Si es negativo indica que es diferente de
			if ($bodega != '' && $bodega >= 0) 			$query_add .= " AND v.bodega='$bodega' ";		//Positivo indica que solo esa bodega

			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			if ($ACCESS !== -1) 			$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					AND replace(v.referencia, '*/', '') not in (select material from rp_maestro)
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.devolucion=0 
					AND v.sw=3");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalComprasRepuestosCROT($tipodocs = array(), $tipo_vehiculo = '', $ACCESS = false, $bodega = '') {
			$query_add = "";
			$operador_access = $ACCESS ? "" : "NOT";
			
			if ($tipo_vehiculo != "")					$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			if ($bodega !== '' && $bodega < 0) 			$query_add .= " AND (v.bodega is NULL OR v.bodega<>'" . abs($bodega) . "') ";	//Si es negativo indica que es diferente de
			if ($bodega !== '' && $bodega >= 0) 		$query_add .= " AND v.bodega='$bodega' ";		//Positivo indica que solo esa bodega
			if ($ACCESS !== -1) 						$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.devolucion=0 
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.sw=3");
			$total= ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE
					v.informe_id<>" . $this->id . "
					$query_add 
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) 
						from rp_ventasxasesor d 
						where d.sw=2 
						AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
						AND d.tipo_link in ('" . implode("','", $tipodocs) . "') 
						and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$total -= $devoluciones;
			return $total;
		}

		public function getTotalComprasRepuestosCROTData($tipodocs = array(), $tipo_vehiculo = '', $ACCESS = false, $bodega = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")	$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			if ($bodega != '' && $bodega < 0) 			$query_add .= " AND (v.bodega is NULL OR v.bodega<>'$bodega') ";	//Si es negativo indica que es diferente de
			if ($bodega != '' && $bodega >= 0) 			$query_add .= " AND v.bodega='$bodega' ";		//Positivo indica que solo esa bodega

			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			if ($ACCESS !== -1) 			$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					$query_add
					AND replace(v.referencia, '*/', '') in (select material from rp_maestro)
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.devolucion=0 
					AND v.sw=3");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalComprasRepuestos($tipodocs = array(), $tipo_vehiculo = '', $ACCESS = false, $bodega = '') {
			$query_add = "";
			$operador_access = $ACCESS ? "" : "NOT";
			
			if ($tipo_vehiculo != "")					$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			if ($bodega !== '' && $bodega < 0) 			$query_add .= " AND (v.bodega is NULL OR v.bodega<>'" . abs($bodega) . "') ";	//Si es negativo indica que es diferente de
			if ($bodega !== '' && $bodega >= 0) 		$query_add .= " AND v.bodega='$bodega' ";		//Positivo indica que solo esa bodega
			if ($ACCESS !== -1) 						$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(v.totalc) total 
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.devolucion=0 
					AND v.sw=3");
			$total= ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$r = self::queryMYSQL("select sum(v.totalc) total FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE
					v.informe_id<>" . $this->id . "
					$query_add 
					and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) 
						from rp_ventasxasesor d 
						where d.sw=2 
						AND d.tipo_link in ('" . implode("','", $tipodocs) . "') 
						and d.informe_id=" . $this->id . "
					)");
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			$total -= $devoluciones;
			return $total;
		}

		public function getTotalComprasRepuestosData($tipodocs = array(), $tipo_vehiculo = '', $ACCESS = false, $bodega = '') {
			$query_add = "";
			if ($tipo_vehiculo != "")	$query_add .= " AND v.vehiculo_tipo='$tipo_vehiculo' ";
			if ($bodega != '' && $bodega < 0) 			$query_add .= " AND (v.bodega is NULL OR v.bodega<>'$bodega') ";	//Si es negativo indica que es diferente de
			if ($bodega != '' && $bodega >= 0) 			$query_add .= " AND v.bodega='$bodega' ";		//Positivo indica que solo esa bodega

			$result = array();
			$operador_access = $ACCESS ? "" : "NOT";
			if ($ACCESS !== -1) 			$query_add .= " AND v.nombre_grupo $operador_access in ('ACCES') ";
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select v.*, c.nombre cliente_nombre
				FROM rp_ventasxasesor v 
				INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				WHERE 
					v.informe_id=" . $this->id . " 
					$query_add
					AND v.tipo in ('" . implode("','", $tipodocs) . "') 
					AND v.devolucion=0 
					AND v.sw=3");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalInventarios($tipo_vehiculo = '', $ACCESS = false) {
			return $this->getInventariosEntregadoAServicio($tipo_vehiculo, $ACCESS)
				+ $this->getInventarios('0M-12M', $tipo_vehiculo, $ACCESS)
				+ $this->getInventarios('12M-24M', $tipo_vehiculo, $ACCESS)
				+ $this->getInventarios('24M-MAS', $tipo_vehiculo, $ACCESS)
				+ $this->getInventariosAlternos($tipo_vehiculo, $ACCESS)
				+ $this->getInventariosAlternosOtrasMarcas($tipo_vehiculo, $ACCESS);
		}

		public function getInventariosEntregadoAServicio($tipo_vehiculo = '', $ACCESS = false) {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "nombre_grupo is null or nombre_grupo <> 'ACCES'";
			if ($ACCESS === -1)
				$operador_access = "1=1";
			BD::changeInstancia("mysql");
			$query = "select sum(costo_promedio * stock) total
				FROM rp_ubicacion_repuestos 
				WHERE codigo NOT LIKE 'CL%' 
					AND codigo NOT LIKE '%*/'
					AND informe_id=" . $this->id . " 
					and ($operador_access)
				$query_add
				and bodega = 99";
			$r = self::queryMYSQL($query);
			$return = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
			return $return;
		}

		public function getInventariosEntregadoAServicioData($tipo_vehiculo = '', $ACCESS = false) {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";
			if ($ACCESS === -1)
				$operador_access = "1=1";
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select *, costo_promedio * stock xtotal
				from rp_ubicacion_repuestos 
				where 
					codigo NOT LIKE 'CL%' 
					AND codigo NOT LIKE '%*/'
					and bodega = 99
					AND informe_id=" . $this->id . " 
				$query_add
				and $operador_access
				and bodega =99");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getInventarios($edad, $tipo_vehiculo = '', $ACCESS = false) {
			BD::changeInstancia("mysql");
			$query_add = "and edad='" . Seguridad::escapeSQL($edad, 'mysql') . "'";
			if ($edad == "12M-24M")
				$query_add = "and (edad='" . Seguridad::escapeSQL($edad, 'mysql') . "' or edad ='N-A')";

			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";
			if ($ACCESS === -1) $operador_access = "1=1";
			
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos
				where  
					informe_id=" . $this->id . " 
					AND codigo NOT LIKE 'CL%' 
					and tipo_proveedor='gm'
					$query_add
					and $operador_access
					and bodega <> 99";
			$r = self::queryMYSQL($query);
			return ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		}

		public function getInventariosData($edad, $tipo_vehiculo = '', $ACCESS = false) {
			BD::changeInstancia("mysql");
			$query_add = "and edad='" . Seguridad::escapeSQL($edad, 'mysql') . "'";
			if ($edad == "12M-24M")
				$query_add = "and (edad='" . Seguridad::escapeSQL($edad, 'mysql') . "' or edad ='N-A')";

			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";			
			if ($ACCESS === -1) $operador_access = "1=1";

			$result = array();
			$query = "select *, (costo_promedio * stock) xtotal
				from rp_ubicacion_repuestos 
				where  
					informe_id=" . $this->id . " 
					AND codigo NOT LIKE 'CL%'
					and tipo_proveedor='gm'
					$query_add
					and $operador_access
					and bodega <> 99";
			$r = self::queryMYSQL($query);
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getInventariosAlternosOtrasMarcas($tipo_vehiculo = '', $ACCESS = false) {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";
			if ($ACCESS === -1)
				$operador_access = "1=1";
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos 
				where  codigo NOT LIKE 'CL%' AND
					informe_id=" . $this->id . " 
					and bodega <> 99
					AND replace(codigo, '*/', '') not in (select material from rp_maestro)
					$query_add
					and $operador_access
					and tipo_proveedor='alterno'";
			$r = self::queryMYSQL($query);
			return ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		}

		public function getInventariosAlternosOtrasMarcasData($tipo_vehiculo = '', $ACCESS = false) {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";
			$r = self::queryMYSQL("select *, costo_promedio * stock xtotal
				from rp_ubicacion_repuestos 
				where  codigo NOT LIKE 'CL%' AND
					informe_id=" . $this->id . " 
					and bodega <> 99
					AND replace(codigo, '*/', '') not in (select material from rp_maestro)
					and $operador_access
					$query_add
					and tipo_proveedor='alterno'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getInventariosAlternos($tipo_vehiculo = '', $ACCESS = false) {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";
			if ($ACCESS === -1)
				$operador_access = "1=1";
			$query = "select sum(costo_promedio * stock) total
				from rp_ubicacion_repuestos 
				where  codigo NOT LIKE 'CL%' AND
					informe_id=" . $this->id . " 
					and bodega <> 99
					AND replace(codigo, '*/', '') in (select material from rp_maestro)
					$query_add
					and $operador_access
					and tipo_proveedor='alterno'";
			$r = self::queryMYSQL($query);
			return ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		}

		public function getInventariosAlternosData($tipo_vehiculo = '', $ACCESS = false) {
			$query_add = "";
			if ($tipo_vehiculo != "")
				$query_add .= "AND vehiculo_tipo='$tipo_vehiculo'";
			$result = array();
			BD::changeInstancia("mysql");
			$operador_access = $ACCESS ? "nombre_grupo = 'ACCES'" : "(nombre_grupo is null or nombre_grupo <> 'ACCES')";
			$r = self::queryMYSQL("select *, costo_promedio * stock xtotal
				from rp_ubicacion_repuestos 
				where  codigo NOT LIKE 'CL%' AND
					informe_id=" . $this->id . " 
					and bodega <> 99
					AND replace(codigo, '*/', '') in (select material from rp_maestro)
					and $operador_access
					$query_add
					and tipo_proveedor='alterno'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalFOF($tipo_vehiculo = '', $ACCES = false) {
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
			//$xxperiodo->previous();	//04-Sept-2019: Nuevamente se comenta esta linea para que tome el periodo actual
			$xxanio = $xxperiodo->getYear();
			$xxmes = $xxperiodo->getMonth();
			$r = BD::sql_query("select * from vbsc_ubicacion_repuestos where ano=$xxanio and mes=$xxmes") or die("Error query");
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
				$f["vehiculo_tipo"] = Maestro::getTipoVehiculo($busca_codigo, $f["descripcion"]);
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
						$campos["vehiculo_tipo"] = Maestro::getTipoVehiculo(-1, $campos["descripcion_modelo"]);
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

			//insertar en tabla de garantias
			BD::changeInstancia("mysql");
			BD::eliminar("rp_garantias", array("informe_id" => $this->id));
			BD::changeInstancia("facts");
			$r = BD::sql_query("select v.tipo,v.numero as numero_fra,v.numero_orden,v.fec as fecha,v.operario,nombre_tecnico=(select nombres from terceros where nit=v.operario),
				v.serie,v.operacion,v.descripcion, v.tiempo,v.clase_operacion,v.porcen_dscto,v.valor
				from v_talldoclin v
				where v.tipo in ('FL','FC','FG','FSC','FT','FTA','TI','DVTA','DVTO','DVTS','DVTC','DVTL','FPC') 
				AND v.clase_operacion<>'R'
				AND year(v.fec)=$anio and month(v.fec)=$mes") or die("Error query");
			while ($fx = BD::obtenerRegistro($r)) {
				BD::changeInstancia("mysql");
				$campos = array("informe_id" => $this->id);
				foreach($fx as $campo => $valor) {
					$valor = str_replace(array("Nulo"), array(""), $valor);
					$campos[$campo] = $valor;
				}
				$fila = new Garantias($campos);
				if (!$fila->save()) {
					echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
					BD::eliminar("rp_garantias", array("informe_id" => $this->id));
					BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
					BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));
					die ("<br />Error copiando los datos de garantias");
				}
				if ($insertados++ % 150 == 0)
					BD::desconectar();
				
				BD::changeInstancia("facts");
			}

			//VENTAS POR ASESOR
			$insertados = 0;
			BD::changeInstancia("mysql");
			BD::eliminar("rp_ventasxasesor", array("informe_id" => $this->id));

			BD::changeInstancia("facts");
			$query = "SELECT
					tipo, numero, referencia, descripcion, concepto_1, fecha, cantidad, valor_unitario, vendedor, nombres, sw, isnull(descuentos, 0) descuentos, total, totalc, numero_ot, bodega,
					nit_cliente, linea, nombre_cliente, tipo_identificacion, nombre_grupo, nombre_subgrupo, notas, tipo_link, numero_link
				FROM
					ComprasyVentasRptosFacts
				WHERE
					sw in(1,2,3,11,12) AND	month(fecha)=$mes AND year(fecha)=$anio";
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
				if (in_array($f["tipo"], array("NDSR", "EXAJ")))
					$f["totalc"] = -$f["totalc"];
				$fila = new VentasPorAsesor($f);
				if (!$fila->save()) {
					echo "<b><font color=red>Error: " . BD::getLastError() . "</font></b>";
					echo "<pre>";
					print_r($fila);
					echo "</pre>";
					BD::eliminar("rp_perfil_taller", array("informe_id" => $this->id));
					BD::eliminar("rp_ubicacion_repuestos", array("informe_id" => $this->id));
					BD::eliminar("rp_ventasxasesor", array("informe_id" => $this->id));
					BD::eliminar("rp_garantias", array("informe_id" => $this->id));
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
				WHERE v.tipo in ('FS') 
					AND v.sw=1
					AND v.tipo_proveedor='gm' 
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
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo in ('FT', 'FC', 'FG', 'FPC','FSC')
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
					AND v.tipo in ('FA','FS','FRD')
					AND vehiculo_tipo='liviano'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item[$codigo]);
			}

			//17- LIVIANOS - Accesorios Genuinos
			$codigo = 17;
			$query = "update rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
				set ubicacion_item=$codigo
				WHERE v.tipo in ('FS', 'FSC', 'FA', 'FRD', 'FT', 'FC', 'FL', 'FPC') AND v.nombre_grupo = 'ACCES'";
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
				WHERE v.tipo in ('FS') 
					AND v.sw=1
					AND v.tipo_proveedor='gm' 
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
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo_mecanica = 2
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo_mecanica = 1
					AND v.tipo in ('FT', 'FC', 'FPC', 'FSC') 
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
					AND v.tipo in ('FT', 'FC', 'FG', 'FPC','FSC')
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
					AND v.tipo in ('FA','FS','FRD')
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
					AND v.sw=1 ";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//202 - Costo de venta Taller
			$codigo = 202;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='gm' 
					AND v.tipo in ('FC', 'FT', 'NCDR', 'FPC')
					AND v.sw IN (1,11) ";
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
					AND v.sw=1 ";
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
					AND v.sw=1 ";
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
					AND v.sw=1 ";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//206 - Costo de venta Alternos
			$codigo = 206;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo_proveedor='alterno' 
					AND v.tipo in ('FA','FS','FSC','FRD','FL','FT', 'FPC','FC','NCDR')
					";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//207 - Compras repuestos GM
			$codigo = 207;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo in ('CRCO') 
					AND v.devolucion=0 AND v.sw=3 ";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//208 - Compras repuestos a otros concesionarios
			$codigo = 208;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo in ('CRO') 
					AND v.sw=3 ";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}

			//209 - Compras repuestos a otros proveedores
			$codigo = 209;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_costo=$codigo
				WHERE v.tipo in ('CROT') 
					AND v.sw=3 ";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_costo[$codigo]);
			}



			/** COA */

			//3000 - Genuinos GM - Mostrador Accesorios
			$codigo = 3000;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_coa=$codigo
				WHERE 
					v.tipo in ('FA', 'FRD', 'FS', 'FSC')
					AND v.tipo_proveedor='gm'
					AND v.nombre_grupo in ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_coa[$codigo]);
			}

			//3002 - Genuinos GM - Taller Accesorios
			$codigo = 3002;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_coa=$codigo
				WHERE 
					v.tipo in ('FT', 'FC', 'FL', 'FPC')
					AND v.tipo_proveedor='gm'
					AND v.nombre_grupo in ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_coa[$codigo]);
			}

			//3004 - Alterno Accesorios - Mostrador Accesorios
			$codigo = 3004;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_coa=$codigo
				WHERE 
					v.tipo in ('FA', 'FRD', 'FS', 'FSC')
					AND v.tipo_proveedor='alterno'
					AND v.nombre_grupo in ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_coa[$codigo]);
			}

			//3006 - Alterno Accesorios - Taller Accesorios
			$codigo = 3006;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_coa=$codigo
				WHERE 
					v.tipo in ('FT', 'FC', 'FL', 'FPC')
					AND v.tipo_proveedor='alterno'
					AND v.nombre_grupo in ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_coa[$codigo]);
			}

			//3007 - Costo de Ventas Accesorios - Costo Genuinos GM
			$codigo = 3007;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_coa=$codigo
				WHERE 
					v.tipo in ('FA','FRD','FS','FSC','FC','FL','FT', 'FPC')
					AND v.tipo_proveedor='gm'
					AND v.nombre_grupo in ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_coa[$codigo]);
			}

			//3008 - Costo de Ventas Accesorios - Costo Genuinos Alternos
			$codigo = 3008;
			$query = "update rp_ventasxasesor v
				set ubicacion_item_coa=$codigo
				WHERE 
					v.tipo in ('FA','FRD','FS','FSC','FC','FL','FT', 'FPC')
					AND v.tipo_proveedor='alterno'
					AND v.nombre_grupo in ('ACCES')";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando el índice para los items de " . self::$item_coa[$codigo]);
			}

			//Update para clasificar inventario en pesados/livianos
			$query = "update rp_ubicacion_repuestos r INNER JOIN rp_maestro m 
					ON m.material=replace(replace(r.codigo, '*/', ''), '*$', '') and m.tipo_vehiculo='pesados'
					set r.vehiculo_tipo='pesados'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando rp_ubicacion_repuestos");
			}

			//Update para clasificar inventario en pesados/livianos
			$query = "UPDATE rp_perfil_taller SET vehiculo_tipo='pesados' WHERE 
				descripcion_modelo LIKE '%NKR%' OR 
				descripcion_modelo LIKE '%NHR%' OR 
				descripcion_modelo LIKE '%NLR%' OR 
				descripcion_modelo LIKE '%NNR%' OR 
				descripcion_modelo LIKE '%NMR%' OR 
				descripcion_modelo LIKE '%NPR%' OR 
				descripcion_modelo LIKE '%NQR%' OR 
				descripcion_modelo LIKE '%FTR%' OR 
				descripcion_modelo LIKE '%FRR%' OR 
				descripcion_modelo LIKE '%FSR%' OR 
				descripcion_modelo LIKE '%FVZ%'";
			if (!BD::sql_query($query)) {
				echo "<b><font color=red>" . BD::getLastError() . "</font></b>";
				die ("<br />Error actualizando rp_perfil_taller");
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
			$t = time();
			if (isset(self::$querys[md5($sql)])) {
				mysqli_data_seek(self::$querys[md5($sql)], 0);
				return self::$querys[md5($sql)];
			}
			self::$querys[md5($sql)] = BD::sql_query($sql) or die("ErrorQuery: " . BD::getLastError());
			$t2 = time();
			$dif =  ($t2 - $t);
			if ($dif > 10)
				echo "(" . $sql . " $dif ms)";
			return self::$querys[md5($sql)];
		}

		//Funciones para el Informe de COS
		public function getCantidadMecanicaRapida($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(*) cantidad
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND (mecanica_rapida=1 OR accesorios=1)
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaRapidaData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, tipo_vehiculo, mecanica_rapida, accesorios
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND (mecanica_rapida=1 OR accesorios=1)
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSMecanicaRapida($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(mo_clientes) valor
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND (mecanica_rapida=1 OR accesorios=1)
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadMecanicaEspecializada($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(*) cantidad
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND mecanica_especializada=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaEspecializadaData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, tipo_vehiculo, mecanica_especializada
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND mecanica_especializada=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSMecanicaEspecializada($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(mo_clientes) valor
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND mecanica_especializada=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadInternos($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(*) cantidad
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND internos=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaInternosData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, tipo_vehiculo, internos
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND internos=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSInternos($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(mo_internos) valor
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND internos=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadAlistamiento($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(*) cantidad
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND alistamiento_y_peritajes=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaAlistamientoData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, tipo_vehiculo, alistamiento_y_peritajes
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND alistamiento_y_peritajes=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSAlistamiento($tipo_vehiculo = 'liviano'){
			return 0;
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(mo) valor
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega<>5
					AND alistamiento_y_peritajes=1
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadGarantias($tipo_vehiculo ='liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(DISTINCT g.numero_orden) as cantidad
				FROM rp_garantias g 
				JOIN rp_perfil_taller pt ON g.numero_orden = pt.numero_orden
				WHERE g.informe_id = " . $this->id . "
					AND g.tipo='FG'
					AND pt.bodega<>5
					AND pt.vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaGarantiasData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select pt.numero_orden, pt.bodega, pt.sucursal, pt.vin, pt.descripcion_modelo, pt.modelo_ano, pt.placa, pt.tipo_vehiculo 
				FROM rp_garantias g 
				JOIN rp_perfil_taller pt ON g.numero_orden = pt.numero_orden
				WHERE g.informe_id = " . $this->id . "
					AND g.tipo='FG'
					AND pt.bodega<>5
					AND pt.vehiculo_tipo='$tipo_vehiculo'
				GROUP BY(g.numero_orden)");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSGarantias($tipo_vehiculo ='liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(DISTINCT g.numero_orden) as cantidad, sum(g.valor) valor
				FROM rp_garantias g 
				JOIN rp_perfil_taller pt ON g.numero_orden = pt.numero_orden
				WHERE g.informe_id = " . $this->id . "
					AND g.tipo='FG'
					AND pt.bodega<>5
					AND pt.vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadMecanica($tipo_vehiculo = 'liviano'){
			return $this->getCantidadMecanicaRapida($tipo_vehiculo)
				+ $this->getCantidadMecanicaEspecializada($tipo_vehiculo)
				+ $this->getCantidadInternos($tipo_vehiculo)
				+ $this->getCantidadAlistamiento($tipo_vehiculo)
				+ $this->getCantidadGarantias($tipo_vehiculo);
		}

		public function getTotalCOSMecanica($tipo_vehiculo = 'liviano'){
			return $this->getTotalCOSMecanicaRapida($tipo_vehiculo)
				+ $this->getTotalCOSMecanicaEspecializada($tipo_vehiculo)
				+ $this->getTotalCOSInternos($tipo_vehiculo)
				+ $this->getTotalCOSAlistamiento($tipo_vehiculo)
				+ $this->getTotalCOSGarantias($tipo_vehiculo);
		}

		public function getCantidadMecanicaUno($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(*) cantidad
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega=5
					AND aseguradora = '*'
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaUnoData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, tipo_vehiculo, aseguradora
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega=5
					AND aseguradora = '*'
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSMecanicaUno($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(mo_aseguradoras) valor 
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega=5
					AND aseguradora = '*'
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadMecanicaAseguradoras($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select COUNT(*) cantidad
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega=5
					AND aseguradora <> '*'
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['cantidad'] : 0;
		}

		public function getMecanicaAseguradoraData($tipo_vehiculo = 'liviano'){
			$result = array();
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, tipo_vehiculo, aseguradora
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega=5
					AND aseguradora <> '*'
					AND vehiculo_tipo='$tipo_vehiculo'");
			while ($f = BD::obtenerRegistro($r)) $result[] = $f;
			return $result;
		}

		public function getTotalCOSMecanicaAseguradoras($tipo_vehiculo = 'liviano'){
			BD::changeInstancia("mysql");
			$r = self::queryMYSQL("select sum(mo_aseguradoras) valor
				FROM rp_perfil_taller
				WHERE informe_id=" . $this->id . " 
					AND bodega=5
					AND aseguradora <> '*'
					AND vehiculo_tipo='$tipo_vehiculo'");
			return ($f = BD::obtenerRegistro($r)) ? $f['valor'] : 0;
		}

		public function getCantidadColision($tipo_vehiculo = 'liviano'){
			return $this->getCantidadMecanicaUno($tipo_vehiculo) 
			+ $this->getCantidadMecanicaAseguradoras($tipo_vehiculo);
		}

		public function getTotalCOSColision($tipo_vehiculo = 'liviano'){
			return $this->getTotalCOSMecanicaUno($tipo_vehiculo) 
			+ $this->getTotalCOSMecanicaAseguradoras($tipo_vehiculo);
		}


		public function getCantidadAtendidos($tipo_vehiculo = 'liviano'){
			return $this->getCantidadMecanica($tipo_vehiculo) + $this->getCantidadColision($tipo_vehiculo);
		}

		public function getTotalCOSAtendidos($tipo_vehiculo = 'liviano'){
			return $this->getTotalCOSMecanica($tipo_vehiculo) + $this->getTotalCOSColision($tipo_vehiculo);
		}

		public function getCantidadTallerMecanica(){
			return $this->getCantidadMecanicaRapida('liviano') + $this->getCantidadMecanicaRapida('pesados')
				+ $this->getCantidadMecanicaEspecializada('liviano') + $this->getCantidadMecanicaEspecializada('pesados')
				+ $this->getCantidadInternos('liviano') +$this->getCantidadInternos('pesados')
				+ $this->getCantidadAlistamiento('liviano') + $this->getCantidadAlistamiento('pesados')
				+ $this->getCantidadGarantias('liviano') + $this->getCantidadGarantias('pesados');
		}

		public function getTotalCOSTallerMecanica(){
			return $this->getTotalCOSMecanicaRapida('liviano') + $this->getTotalCOSMecanicaRapida('pesados')
				+ $this->getTotalCOSMecanicaEspecializada('liviano') + $this->getTotalCOSMecanicaEspecializada('pesados')
				+ $this->getTotalCOSInternos('liviano') +$this->getTotalCOSInternos('pesados')
				+ $this->getTotalCOSAlistamiento('liviano') + $this->getTotalCOSAlistamiento('pesados')
				+ $this->getTotalCOSGarantias('liviano') + $this->getTotalCOSGarantias('pesados');
		}

		public function getCantidadTallerColision(){
			return $this->getCantidadMecanicaUno('liviano') + $this->getCantidadMecanicaUno('pesados')
				+$this->getCantidadMecanicaAseguradoras('liviano')+ $this->getCantidadMecanicaAseguradoras('pesados');
		}

		public function getTotalCOSTallerColision(){
			return $this->getTotalCOSMecanicaUno('liviano') + $this->getTotalCOSMecanicaUno('pesados')
				+$this->getTotalCOSMecanicaAseguradoras('liviano')+ $this->getTotalCOSMecanicaAseguradoras('pesados');
		}

	}
