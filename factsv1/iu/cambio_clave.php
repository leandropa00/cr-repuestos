<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../conf/config.php"); 
	$cuentaUsuario = new Usuario();
	if (!$cuentaUsuario->load(Aplicacion::getIDUsuario()))
		Aplicacion::cerrarSesion();
	
	if (isset($_POST["cr"]) && isset($_POST["cn"]) && isset($_POST["ca"])) {
		$clave_nueva = $_POST["cn"];
		$clave_repite = $_POST["cr"];
		$clave_actual = $_POST["ca"];
		
		if ($cuentaUsuario->getCampo("fecha_ultimoacceso") == "")
			$cuentaUsuario->setCampo("fecha_ultimoacceso", "_NULL");
		if ($cuentaUsuario->getCampo("ip_ultimoacceso") == "")
			$cuentaUsuario->setCampo("ip_ultimoacceso", "_NULL");
		
		if ($clave_nueva != $clave_repite)
			die("clave_repite_mal");
		
		if ($cuentaUsuario->getClave() != md5($clave_actual))
			die("clave_actual_invalida");
		
		if (strlen(trim($clave_nueva)) < 6)
			die("error_tam_clave");
		
		if ($clave_nueva != $clave_repite)
			die("clave_repite_mal");
		$cuentaUsuario->setClave($clave_nueva);
		if ($cuentaUsuario->update())
			die("ok");
		
		die("error_desconocido");
	}
?>
<div style='padding:10px;'>
	<div style='float:left'><img src='imagenes/mi_cuenta.png'></div><div><b>&nbsp;Cambiar mi clave de acceso</b></div>
	<hr size=1 color="#C0C0C0"><br />
	<table cellspacing=3 style="margin-top:10px;" align=center width=570>
		<tr>
			<td width=40 style='padding:10px;'><img src='imagenes/importante.png'></td>
			<td valign=middle>
				<table>
					<tr>
						<td>
							<b>1. </b>Recuerde que su clave es de <b>uso personal</b> e <b>intransferible</b>.<br />
							<b>2. </b>Utilice una <b>clave difícil de adivinar</b>. Le sugerimos combinar letras y números.<br />
							<b>3. </b>Cambie su clave periódicamente.<br />
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<table style='' width="400" id='formCambioClave' class="ui-corner-all" align="center">
		<tr>
			<td colspan="3" style="height:20px;">&nbsp;</td>
		</tr>
		<tr>
			<td rowspan="5" style="height:20px;">&nbsp;</td>
		</tr>
		<tr>
			<td align="right">Confirme su clave actual: </td>
			<td align="left"><input class="ui-corner-all ui-state-default" type=password id='clave_actual' name='clave_actual' style='width:160px;'></td>
		</tr>
		<tr>
			<td colspan=2>&nbsp;</td>
		</tr>
		<tr>
			<td align="right">Nueva clave de acceso: </td>
			<td align="left"><input type=password id='clave_nueva' style='width:160px;' name='clave_nueva'></td>
		</tr>
		<tr>
			<td align="right">Repita su nueva clave: </td>
			<td align="left"><input type=password id='clave_repite' style='width:160px;' name='clave_repite'></td>
		</tr>
		<tr>
			<td colspan=2 align="right">&nbsp;</td>
			<td align="left" style="padding-top:5px;"><input class='btn btn-success' type=button id='clave_cambiar' name='clave_cambiar' style='cursor:pointer; width:174px;' value='Cambiar mi clave'></td>
		</tr>
		<tr>
			<td colspan="3" style="height:20px;"><div id='clave_carga' /></td>
		</tr>
	</table>
</div>
<script type="text/javascript" charset="ISO-8859-1">
	$(document).ready(function() {
		
		//Asignamos las clases
		$("#formCambioClave").find(":input[type=text]")
			.bind('focus blur',
				function(e) {
					e.type == 'focus' ? $(this).addClass("ui-state-hover") : $(this).removeClass("ui-state-hover");
				}
			)
			.addClass("ui-state-default ui-corner-all");
			
		//Se establece el foco en la caja de texto del password actual
		$("[name=clave_actual]").focus();
		
		$("#clave_cambiar").click(function() {
			if ($.trim($("#clave_actual").val()).length == 0) {
				mensaje("Clave incorrecta", "Debes escribir la clave actual", "error");
				$("#clave_actual").focus();
				return;
			}
			if ($.trim($("#clave_nueva").val()).length == 0) {
				mensaje("Clave incorrecta", "Debes escribir la nueva actual", "error");
				$("#clave_nueva").focus();
				return;
			}
			if ($("#clave_repite").val() != $("#clave_nueva").val()) {
				mensaje("Repite la nueva clave", "La confirmación de tu nueva clave no coincide", "error");
				$("#clave_repite").focus();
				return;
			}
			showMessage();
			$.post("iu/cambio_clave.php", { ca : $("#clave_actual").val(), cn : $("#clave_nueva").val(), cr : $("#clave_repite").val() }, function(data) {
				hideMessage();
				switch(data) {
					case "clave_actual_invalida":
						mensaje("Clave actual incorrecta", "La clave actual no es v&aacute;lida</b>", "error");
						break;
					case "error_tam_clave":
						mensaje("Tamaño incorrecto", "La clave debe contener <b>m&iacute;nimo 6 caracteres</b>", "error");
						break;
					case "ok":
						mensaje("¡Cambio existoso!", "La clave se ha actualizado correctamente", "success");
						$("#app_contenido").load("iu/cambio_clave.php");
						break;
					case "clave_repite_mal":
						mensaje("Clave incorrecta", "La clave de confirmaci&oacute;n no coincide con la nueva", "error");
						break;
					case "error_desconocido":
					default:
						mensaje("Error desconocido", "Informe de este error al departamento de sistemas", "error");
						break;
				}
			});
		});
	})
</script>