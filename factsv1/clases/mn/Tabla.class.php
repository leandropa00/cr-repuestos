<?php
	/**
	 * @author		Julio Cesar Garces [julio.garces@cloudbt.com.co]
	 * @package 	MN
	 */
	class Tabla {
		public $campos = array();
		public $id;
		
		public $tabla = "";
		public $campos_tabla = array();
		public $campos_vista = array();
		
		public $vista = "";
		
		function __construct($campos = array(), $id = -1) {
			BD::changeInstancia("mysql");
			$this->campos_tabla = array();
			if (!isset(CamposTabla::$campos[$this->tabla])) {
				$r = BD::sql_query("DESCRIBE " . $this->tabla);
				if (!$r) die("La tabla '" . htmlentities($this->tabla) . "' no es válida");
				while ($f = BD::obtenerRegistro($r))
					$this->campos_tabla[] = $f["Field"];
				CamposTabla::$campos[$this->tabla] = $this->campos_tabla;
				CamposTabla::$vista[$this->tabla] = $this->campos_tabla;
				if ($this->vista != $this->tabla) {
					$this->campos_vista = array();
					$r = BD::sql_query("DESCRIBE " . $this->vista);
					if (!$r) die("La tabla '" . htmlentities($this->vista) . "' no es válida");
					while ($f = BD::obtenerRegistro($r))
						$this->campos_vista[] = $f["Field"];
				}
				CamposTabla::$vista[$this->tabla] = $this->campos_vista;
			}
			$this->campos_tabla = CamposTabla::$campos[$this->tabla];
			$this->campos_vista = CamposTabla::$vista[$this->tabla];
			
			$this->id = $id;
			foreach($campos as $campo => $valor)
				if (in_array($campo, $this->campos_tabla) || in_array($campo, $this->campos_vista))
					$this->campos[$campo] = $valor;
		}
		
 		public function setCampo($campo, $valor, $forzar = false) {
 			if ($forzar == true) {
				$this->campos[$campo] = $valor;
				return true;
			}
 			if ($campo == "id") return false;
 			if (in_array($campo, $this->campos_tabla) || $forzar) {
 				$this->campos[$campo] = $valor;
				return true;
			}
			return false;
 		}
		
		public function getCampo($campo, $htmlentities = false) {
			$valor = isset($this->campos[$campo]) ? $this->campos[$campo] : "";
			if ($htmlentities) return htmlentities($valor, ENT_QUOTES, "iso8859-1");
			return $valor;
		}
		
		public function load($id) {
			$r = BD::consultar($this->vista, array("*"), array("id" => $id));
			if ($f = BD::obtenerRegistro($r)) {
				$this->loadThis($f);
				$this->id = $id;
				return true;
			}
			return false;
		}

		public function loadThis($f) {
			$this->campos = $f;
			if (isset($this->campos["id"])) {
				$this->id = $f["id"];
				unset($this->campos["id"]);
				return true;
			}
			return false;
		}
		
		public function save($save_id = false) {
			$nuevos_valores = $this->campos;
			foreach($nuevos_valores as $campo => $valor)
				if (!in_array($campo, $this->campos_tabla))
					unset($nuevos_valores[$campo]);
			if (isset($nuevos_valores['id']))
				unset($nuevos_valores['id']);
			if (!($save_id === false)) {
				$nuevos_valores['id'] = $save_id;
				$this->id = $save_id;
			}
			$r = BD::adicionar($this->tabla, $nuevos_valores);
			if ($r && $save_id === false) {
				$this->id = BD::getInsertID();
				$this->setCampo("id", $this->id);
			}
			return $r;
		}
		
		public function update() {
			
			if (func_num_args() == 1) {
				$post = func_get_arg(0);
				if (!is_array($post)) return false;
				foreach($post as $campo => $valor)
					$this->setCampo($campo, $valor);
				return $this->update();
			}
			
			$nuevos_valores = $this->campos;
			foreach($nuevos_valores as $campo => $valor)
				if (!in_array($campo, $this->campos_tabla))
					unset($nuevos_valores[$campo]);
			if (isset($nuevos_valores[0]))
				unset($nuevos_valores[0]);
			return BD::actualizar(
				$this->tabla,
				$nuevos_valores,
				array("id" => $this->id)
			);
		}
		
		public function delete() {
			if ($this->id <= 0) return false;
			return BD::eliminar($this->tabla, array("id" => $this->id));
		}
		
		public function writeOptions($default = -1, $campos = array("id", "nombre"), $condiciones = array(), $condicion_adicional = "") {
			if (count($campos) != 2) return false;
			$r = BD::consultar($this->vista, $campos, $condiciones, $condicion_adicional);
			while ($f = BD::obtenerRegistro($r)) {
				if ($default == $f[$campos[0]])
					echo "<option selected='selected' value='" . $f[$campos[0]] . "'>" . $f[$campos[1]] . "</option>";
				else
					echo "<option value='" . $f[$campos[0]] . "'>" . $f[$campos[1]] . "</option>";
			}
		}
	}
?>
