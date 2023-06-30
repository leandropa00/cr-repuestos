<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	
	$id = isset($_POST["id"]) ? intval($_POST["id"]) : die("Error al obtener el ID del usuario");
	
	$usu = new Usuario();
	if (!$usu->load($id)) die ("error al cargar la información del usuario");
	if (isset($_POST["perfil_id"]) && isset($_POST["usuario"]) && isset($_POST["clave"])) {
		if ($_POST["clave"] == "") {
			unset($_POST["clave"]);
			unset($_POST["clave2"]);
		} else {
			$_POST["clave"] = md5($_POST["clave"]);
			unset($_POST["clave2"]);
		}
		if ($usu->getCampo("fecha_ultimoacceso") == "")
			$usu->setCampo("fecha_ultimoacceso", "_NULL");
		die ($usu->update($_POST) ? "ok" : "err");
	}
?>
<form method="post" name="formEditar" id="formEditar" action="<?php echo DIR_WEB . basename(__FILE__); ?>">
	<input type="hidden" name='id' value="<?php echo $usu->id; ?>">
	<table cellpadding=3 border=0 width="100%">
		<tr>
			<td align=right><b>Identificación: </b></td>
			<td><input style="width:120px;" maxlength="15" type=text name='identificacion' id='identificacion' value='<?php echo $usu->getCampo("identificacion", true); ?>'></td>
		</tr>
		<tr>
			<td style="width:120px;" align=right><b>Nombre completo: </b></td>
			<td>
				<input style="width:121px;" maxlength="60" type=text name='nombre' id='nombre' value='<?php echo $usu->getCampo("nombre", true); ?>' placeholder="Nombre">
				<input style="width:121px;" maxlength="60" type=text name='apellidos' id='apellidos' value='<?php echo $usu->getCampo("apellidos", true); ?>' placeholder="Apellidos">
			</td>
		</tr>
		<tr>
			<td align=right><b>Correo: </b></td>
			<td><input maxlength="120" style="width:250px;" type=text name='correo' id='correo' value='<?php echo $usu->getCampo("correo", true); ?>'></td>
		</tr>
		<tr>
			<td align=right><b>Perfil: </b></td>
			<td>
				<select name='perfil_id' id='perfil_id'>
					<?php
						$p = new Perfil();
						$p->writeOptions($usu->getCampo("perfil_id"));
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=2>
				<hr size=1 border=1 bordercolor="#EFEFEF">
			</td>
		</tr>
		<tr>
			<td align=right style='width:100px;'><b>Usuario: </b></td>
			<td><input style="width:120px;" maxlength="30" type=text name='usuario' id='usuario' value='<?php echo $usu->getCampo("usuario", true); ?>'></td>
		</tr>
		<tr>
			<td align=right><b>Estado: </b></td>
			<td>
				<select name='estado' id='estado'>
					<option value=''>Seleccione...</option>
					<option <?php echo $usu->getCampo("estado") == "1" ? "selected='selected'" : "" ?> value='1'>ACTIVO</option>
					<option <?php echo $usu->getCampo("estado") == "0" ? "selected='selected'" : "" ?> value='0'>INACTIVO</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" style='font-size:12px; color:red;'><i><b>Nota:</b> Dejar el campo de clave vacío si desea conservar la clave actual</i></td>
		</tr>
		<tr>
			<td align=right><b>Clave: </b></td>
			<td><input autocomplete="off" style="width:120px;" type=password name='clave' id='clave' value=''></td>
		</tr>
		<tr>
			<td align=right><b>Repetir clave: </b></td>
			<td><input autocomplete="off" style="width:120px;" maxlength="100" style="width:250px;" type=password name='clave2' id='clave2' value=''></td>
		</tr>
	</table>
</form>
<script type="text/javascript">
	$(document).ready(function() {
		$("#ventana2").dialog("destroy");
		$("#ventana2").dialog({
			modal: true,
		    overlay: {
		        opacity: 0.4,
		        background: "black" 
			},
			title: "<i class='icon-white icon-edit' /> &nbsp; Editar usuario",
			resizable: false,
			open : function() {
				var t = $(this).parent(), w = $(document);
				t.offset({
					top: 60,
					left: (w.width() / 2) - (t.width() / 2)
				});
			},
			width: 440,
			buttons: {
				"Guardar": function() {
					Swal.fire({
						title : 'Confirme',
						text : '¿Desea guardar los cambios?',
						type : 'question',
						showCancelButton : true,
						confirmButtonText: 'Aceptar',
						cancelButtonText: 'Cancelar'
					}).then(function (res) {
						if (res.value) {
							$("#formEditar").submit();
						}
					});
              	},
               	"Cancelar": function() {
                    $("#ventana2").html("");
                    $("#ventana2").dialog("destroy");
                }
	        },
	      	close : function () {
	      		$("#ventana2").html("");
				$("#ventana2").dialog("destroy");
	      	}
		});
		$("#formEditar").validate({
			rules: {
				identificacion : {
					required : true,
					digits : true
				},
				nombre : "required",
				apellidos : "required",
				codigo : "required",
				correo : {
					email : true,
					required : true
				},
				perfil_id : "required",
				usuario : "required",
				estado : "required",
				clave2 : {
					equalTo : "#clave"
				}
			},
			messages: {
				identificacion : "",
				nombre : "",
				apellidos : "",
				codigo : "",
				correo : "",
				perfil_id : "",
				usuario : "",
				estado : "",
				clave2 : ""
			},
			submitHandler: function(e) {
				showMessage();
				$('#formEditar').ajaxSubmit({ success: function(resp) {
					hideMessage();
					if (/^ok$/.test(resp)) {
						mensaje("¡Datos actualizados!", "Se han actualizado los datos del usuario correctamente.", "success");
						$("#ventana2").dialog("destroy");
						$("#lista").trigger("reloadGrid");
						$("#busca_nombre").focus();
					}
					else {
						mensaje("Información", "Error al actualizar la información del usuario<br />" + resp, 'error');
					}
				}});
				return false;
			},
			success: function(label) {
				//label.html("&nbsp;").addClass("listo");
			}
		});
		$("#formEditar").css("font-size", "14px");
		hideMessage();
	});
</script>