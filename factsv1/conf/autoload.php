<?php
	defined("iC") or die("Utilice los enlaces de la aplicaciOn Web.");
	
	function autocarga($class_name) {
		if (file_exists(MN . str_replace("_", "/", $class_name) . '.php'))
	    	require_once MN . str_replace("_", "/", $class_name) . '.php';
		elseif (file_exists(BD . $class_name . '.class.php'))
	    	require_once BD . $class_name . '.class.php';
	    elseif (file_exists(MN . $class_name . '.class.php'))
	    	require_once MN . $class_name . '.class.php';
	    elseif (class_exists("Aplicacion")) {
	    	Aplicacion :: pararScript("Es posible que la clase '" . $class_name . "' no esté definida o no se encuentra implementada en el directorio correcto");
		}
		else
			die("Es posible que la clase '" . $class_name . "' no est? definida o no se encuentra implementada en el directorio correcto");
	};

	spl_autoload_register('autocarga');

?>