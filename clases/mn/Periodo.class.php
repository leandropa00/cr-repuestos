<?php
	/**
	 * @author: Julio Cesar Garces Rios
	 * @email: juliogarcesrios@gmail.com
	 */
	class Periodo {
		private $text = "";
		
		function __construct($periodo) {
			if (!$this->setPeriodo($periodo))
				die("Formato incorrecto, se requiere formato: YYYYMM");
		}
		
		public function setPeriodo($periodo) {
			if ($this->isCorrect($periodo)) {
				$this->text = $periodo;
				return true;
			}
			$this->text = "";
			return false;
		}
		
		public function isCorrect($per = "") {
			return preg_match("/^\d{6}$/", $per == "" ? $this->toString() : $per);
		}
		
		public function toString() {
			return $this->text;
		}
		
		public function getYear() {
			return ($this->isCorrect())
				? substr($this->text, 0, 4)
				: "";
		}
		
		public function getMonth() {
			return ($this->isCorrect())
				? substr($this->text, 4, 2)
				: "";
		}
		
		public static function builder($periodo) {
			return new Periodo($periodo);
		}
		
		public function next() {
			if ($this->isCorrect()) {
				$anio = $this->getYear();
				$mes = $this->getMonth();
				if (++$mes > 12) {
					$anio++;
					$mes = 1;
				}
				$this->text = $anio . str_pad($mes, 2, "0", STR_PAD_LEFT);
			}
			return $this->text;
		}
		
		public function previous() {
			if ($this->isCorrect()) {
				$anio = $this->getYear();
				$mes = $this->getMonth();
				if (--$mes <= 0) {
					$anio--;
					$mes = 12;
				}
				$this->text = $anio . str_pad($mes, 2, "0", STR_PAD_LEFT);
			}
			return $this->text;
		}
		
		public function format($format) {
			return Fecha::getFecha($this->getYear() . "-" . $this->getMonth() . "-01", $format);
		}
		
		public function formatShort($format) {
			return Fecha::getFechaCorta(Fecha::getFecha($this->getYear() . "-" . $this->getMonth() . "-01", $format));
		}
	}
?>