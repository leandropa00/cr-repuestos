<?php
	defined("iC") or die("Error");
	if (is_dir(IU . Aplicacion::getPerfilID() . "/"))
		require_once(IU . Aplicacion::getPerfilID() . "/index.php");
?>