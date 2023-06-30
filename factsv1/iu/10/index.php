<?php
	defined("iC") or die("Ingreso incorrecto");
	Aplicacion::validarAcceso(10);
?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".menuHorizontal").buildMenu({
			menuWidth : 180,
			iconPath : "imagenes/menu/",
			hasImages : true,
			fadeInTime : 100,
			fadeOutTime : 300,
			adjustLeft : 2,
			minZindex : "auto",
			adjustTop : 10,
			opacity : .95,
			shadow : false,
			shadowColor : "#ccc",
			openOnClick : true,
			closeOnMouseOut : false,
			closeAfter : 1000,
			submenuHoverIntent : 200
		});
		showMessage();
		$("#app_contenido").load("iu/menu/facts/index.php", function() {
			hideMessage();
		});
	});
	
	function action_menu(e) {
		$e = $(e);
		if ($e.attr("dest")) {
			showMessage();
			$(".rootVoice").removeClass("selected");
			if ($e.attr("ventana") != null)
				$("#ventana").load($e.attr("dest"), function () {
					hideMessage();
				});
			else
				$("#app_contenido").load($e.attr("dest"), function () {
					hideMessage();
				});
		}
	}
	
	function menu_procesar(e) {
		$e = $(e).find("a");
		action_menu($e);
	}	
</script>
<table border="0" cellspacing="0" cellpadding="0" align="center" style="width:1100px;" class="ui-corner-bottom animated fadeIn fast background-box">
	<tr style="background-color:#fff;" class="background-box">
		<td style='color:white; padding-left:6px; width:200px;' valign="middle">
			<a href='index.php'><img style='height: 70px; margin:5px;' src='imagenes/logo_top.png'></a>
		</td>
		<td>
			<div style='color:#312783;font-size:18px;'>Sistema de Informes</div>
		</td>
		<td valign="top" align="right">
			<div style='margin:10px; color:black;'><b><?php echo Aplicacion::getUser()->getNombreIniciales(); ?></b><br /><a style='color:black; text-decoration: none; font-size: 12px;' href='javascript:menu_cerrarSesion()'><i class="icon icon-off"></i> Cerrar sesión</a></div>
		</td>
	</tr>
	<tr>
		<td style="height:10px;" colspan="3">
			<div class="menuHorizontal">
				<table class="rootVoices" cellspacing='0' cellpadding='0' border='0' width="100%">
					<tr>
						<td class="rootVoice {menu: 'menu_micuenta'}" style='width:85px;'><i class='icon-white icon-user'></i> Mi cuenta</td>
						<td class="rootVoice {menu: 'menu_administracion'}" style='width:120px;'><i class='icon-white icon-cog'></i> Administración</td>
						<td class="rootVoice {menu: 'menu_consultas'}"  style='width:85px;'><i class='icon-white icon-search'></i> Consultas</td>
						<td align="right"></td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="3">
			<div id='app_contenido'>...</div>
		</td>
	</tr>
	<tr>
		<td valign="middle" align="center" colspan="3" height="55px;">
			<hr size="1">
			&copy; <?php echo date("Y"); ?>
		</td>
	</tr>
</table>
<?php require_once 'menu.php'; ?>