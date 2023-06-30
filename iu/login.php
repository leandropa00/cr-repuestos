<div class="login-outer">
	<div class="login-middle">
		<form id="formLogin" method="POST" class="form-signin" action="index.php">
			<table cellspacing="3" width="240" align="center" class="animated fadeIn">
				<tr>
					<td align="center"><img width="200" src="imagenes/logo_top.png" alt=""></td>
				</tr>
				<tr>
					<td align="center">
						<table class="ui-corner-all" style="margin-top:5px;margin-bottom:5px;">
								<td style='text-align:center;'>&nbsp;</td>
							</tr>
							<tr>
								<td>
									<div class="input-prepend" style='margin-bottom:10px;'>
										<span class="add-on"><i class="icon-user"></i></span>
										<input style='width:200px;' autocomplete="off" name='usuario' id='usuario' type="text" placeholder="Usuario" />
									</div>
								</td>
							</tr>
							<tr>
								<td align="right">
									<div class="input-prepend" style='margin-bottom:10px;'>
										<span class="add-on"><i class="icon-lock"></i></span>
										<input style='width:200px;' type="password" id='clave' name='clave' placeholder="Clave" />
									</div>
								</td>
							</tr>
							<tr>
								<td align=right style="padding-bottom:6px;">
									<input type=submit class='btn btn-large btn-block btn-primary' style='font-size: 14px; font-weight: bold;' name='login' id="btnLogin" value='Ingresar'>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		
		$("#usuario").focus();
		<?php
			if (isset($_POST["login"]) && isset($_POST["usuario"]) && $_POST["usuario"] != "")
				echo 'mensaje("Clave incorrecta", "Usuario o contraseña inválidos", "error");';
		?>
		
		$("#formLogin").submit(function() {
			if ($.trim($("#usuario").val()) == "") {
				mensaje("Datos incompletos", "Ingrese un nombre de <b>usuario</b> válido", "info");
				$("#usuario").focus();
				return false;
			}
			if ($.trim($("#clave").val()) == "") {
				mensaje("Datos incompletos", "El campo <b>clave</b> está vacío", "info");
				$("#clave").focus();
				return false;
			}
		});
		
		$("#mensajes").center({
			vertical:false
		});
	});
</script>