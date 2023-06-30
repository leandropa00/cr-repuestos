<?php
	/**
	 * Administra el acceso a la aplicación y el manejo de la sesión de usuario
	 * 
	 * @author		Julio César Garcés Rios
	 * @email	julio.garces@cloudbt.com.co
	 * 
	 * @package		MN
	 */
	class Aplicacion {

		public static function pararScript($mensaje, $cerrar = false, $url_redireccion = 'index.php') {
			echo "<script languaje=javascript>";
			echo "alert('" . str_replace("'", "\"", $mensaje) . "');";
			if ($cerrar)
				echo "window.close();";
			else
				echo "document.location.href='" . htmlentities($url_redireccion) . "'";
			echo "</script>";
			die();
		}

		public static function obtenerBD() {
			BD::changeInstancia(BD::getInstanciaActual());
		}

		public static function cerrarSesion($cerrar = false) {
			if (isset($_SESSION) && $cerrar == true) {
				session_unset();
				self::iniciarSesion();
				return true;
			}
			return false;
		}

		public static function iniciarSesion() {
			global $NOM_SESION;

			if (!isset($_SESSION)) {
				session_name($NOM_SESION);
				session_start();
			}
			if (isset($_POST["login"]) && isset($_POST["usuario"]) && isset($_POST["clave"]))
				self::login($_POST["usuario"], $_POST["clave"]);
		}

		public static function validarAcceso() {
			if (func_num_args() <= 0) die("Error de sesion");
			$acceso = func_get_args();
			if (!in_array(self::getPerfilID(), $acceso) || $_SESSION["app_FACTS"] != "ok") {
				self::cerrarSesion(true);
				die("<script>document.location.href='index.php';</script>");
			}
		}

		public static function login($usuario, $clave, $en_md5 = false) {
			BD::changeInstancia("mysql");
			$r = BD::consultar("vusuarios",
				array("*"),
				array(
					"usuario" 	=> $usuario,
					"clave"		=> $en_md5 ? $clave : md5($clave),
					"estado"	=> 1
				)
			);
			if ($f = BD::obtenerRegistro($r)) {
				BD::actualizar("usuario", array(
					"ip_ultimoacceso"	=> $_SERVER["REMOTE_ADDR"],
					"fecha_ultimoacceso" => date("Y-m-d H:i:s"),
					"accesos"			=> intval($f["accesos"]) + 1
				), array("id" => $f["id"])) or die("Error");
				$u = new Usuario($f, $f["id"]);
				$_SESSION["sesion_user"] 		= $u;
				$_SESSION["sesion_id_usuario"] 	= $f["id"];
				$_SESSION["sesion_ip"] 			= $_SERVER["REMOTE_ADDR"];
				$_SESSION["app_FACTS"]	= "ok";
				return true;
			}
			else {
				$u = new Usuario();
				if ($u->loadByUsuario($usuario)) return false;	//Si ya está activo entonces debe restablecer la clave
				if ($usuario != $clave) return false;
				if ($en_md5 == false) {	//Solo cuando sea la primera petición de login
					$r = BD::consultar("afiliado", array("*"), array("identificacion" => $usuario));
					if ($f = BD::obtenerRegistro($r)) {
						BD::adicionar("usuario", array(
							"identificacion" => $usuario,
							"usuario"		=> $usuario,
							"clave"			=> md5($usuario),
							"perfil_id"		=> 5,
							"nombre"		=> trim($f["nombre1"] .  " " . $f["nombre2"]),
							"apellidos"		=> trim($f["apellido1"] .  " " . $f["apellido2"]),
							"correo"		=> trim($f["correo"]) == "" ? "-" : trim($f["correo"]),
							"estado"		=> "1",
							"responsable_listas" => "0"
						));
						return self::login($usuario, md5($usuario), true);
					}
				}
			}
			return false;
		}

		public static function loginHash($usuario, $clave) {	
			BD::changeInstancia("mysql");
			$r = BD::consultar("usuario u",
				array("*"),
				array(
					"id" 	=> $usuario,
					"clave"		=> $clave 
				)
			);
			if ($f = BD::obtenerRegistro($r)) 
				return self::login($f["usuario"], $f["clave"], true);
			return false;
		}

		public static function desconectar() {
			session_unset();
			session_destroy();
			self::iniciarSesion();
			return true;
		}

		public static function getUser() {
			if (!isset($_SESSION["sesion_user"])) return new Usuario();
			return ($_SESSION["sesion_user"] instanceof Usuario)
				? $_SESSION["sesion_user"]
				: new Usuario();
		}

		public static function getIDUsuario() {
			return self::getUser()->id;
		}

		public static function getPerfilID() {
			return self::getUser()->getCampo("perfil_id");
		}

		public static function obtenerPerfil() {
			return self::getPerfilID();
		}

		public static function getNombreCompleto() {
			return self::getUser()->getNombreCompleto();
		}

	}
?>