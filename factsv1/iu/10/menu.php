<?php
	defined("iC") or die("Ingreso incorrecto");
	Aplicacion::validarAcceso(10);
?>
<!-- menu Mi Cuenta -->
<div id="menu_micuenta" class="mbmenu">
	<a id='mn_cambio_clave' dest='iu/cambio_clave.php' class="{action: 'menu_procesar(this)', img: 'cambioclave.png'}">Cambio de clave</a>
	<a rel="separator"> </a>
	<a class="{action: 'menu_cerrarSesion()', img: 'cerrarsesion.png'}">Cerrar sesión</a>
</div>

<div id="menu_administracion" class="mbmenu">
	<a dest='iu/menu/administracion/usuarios/index.php' class="{action: 'menu_procesar(this)', img: 'usuarios.png'}">Usuarios del sistema</a>
	<a dest='iu/menu/administracion/clientes/index.php' class="{action: 'menu_procesar(this)', img: 'clientes.png'}">Clientes</a>
</div>
<div id="menu_consultas" class="mbmenu">
	<a dest='iu/menu/facts/index.php' class="{action: 'menu_procesar(this)', img: 'facts.png'}">Facts</a>
</div>