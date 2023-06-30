<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	
	$id = isset($_POST["identificacion"]) ? $_POST["identificacion"] : die("Error al obtener el ID");
	if (!preg_match("/^\d{1,}$/", $id)) die ("Error en el formato del documento");

	if (isset($_POST["tipo"]) && isset($_POST["valor"]) && isset($_POST["identificacion"])) {
		BD::changeInstancia("facts");
		$r = BD::adicionar("bsc_nomtipopago", array(
			"tipo" 				=> $_POST["tipo"],
			"identificacion" 	=> $_POST["identificacion"],
			"valor" 			=> str_replace(",", ".", $_POST["valor"]),
			"fecha" 			=> str_replace("-", "", $_POST["fecha"])
		));
		if ($r) die("ok");
		die("err");
	}
?>
<form method="post" name="formAddFormaPago" id="formAddFormaPago" action="<?php echo DIR_WEB . basename(__FILE__); ?>">
	<input type="hidden" name='identificacion' value="<?php echo $_POST["identificacion"]; ?>">
	<table cellpadding=3 border=0 width="100%">
		<tr>
			<td align=right><b>Identificación: </b></td>
			<td><input type=text value="<?php echo $id; ?>" style="width: 102px;" readonly></td>
		</tr>
		<tr>
			<td style="width:120px;" align=right><b>Forma de pago: </b></td>
			<td>
				<select id='tipo' name='tipo' style='width:150px;'>
					<option value=''>Seleccione...</option>
					<option value='1'>VALOR HORA</option>
					<option value='2'>PORCENTAJE</option>
				</select>
				<input type=text id="valor" maxlength=6 name="valor" style="width: 102px;">
			</td>
		</tr>
		<tr>
			<td style="width:120px;" align=right><b>Fecha aplica: </b></td>
			<td>
				<div class="input-append date form_datetime" id='dtpFecha'>
					<input style='width:80px;' type=text name='fecha' value=''>
					<span class="add-on"><i class="icon-calendar"></i></span>
					<span class="add-on"><i class="icon-remove"></i></span>
				</div>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><small>A partir de la fecha indicada se realizará el cálculo con la forma de pago asignada</small></td>
		</tr>
	</table>
</form>
<table width="100%">
	<tr>
		<td><hr size="1" style='border-color: #C0C0C0;'></td>
	</tr>
	<tr>
		<td align="right">
			<button class="btn btn-default" onclick='$("#ventana2").dialog("close"); $("#ventana").html("");'>
				<table>
    				<tr>
    					<td>Cancelar</td>
    				</tr>
    			</table>
			</button>
			<button id='btnAprobar' tarea_id=-1 class="btn btn-info" onclick='guardarFormaPago()'>
				<table>
    				<tr>
    					<td>Registrar</td>
    				</tr>
    			</table>
			</button>
		</td>
	</tr>
</table>
<script type="text/javascript">
	$(document).ready(function() {
		$("#ventana2").dialog("destroy");
		$("#ventana2").dialog({
			modal: true,
		    overlay: {
		        opacity: 0.4,
		        background: "black" 
			},
			title: "<i class='icon-white icon-edit' /> &nbsp; Asignar forma de pago",
			resizable: false,
			open : function() {
				var t = $(this).parent(), w = $(document);
				t.offset({
					top: 60,
					left: (w.width() / 2) - (t.width() / 2)
				});
			},
			width: 440,
	      	close : function () {
	      		$("#ventana2").html("");
				$("#ventana2").dialog("destroy");
	      	}
		});

		$("#dtpFecha").datetimepicker({
			language:  'es',
			autoclose: true,
			format: 'yyyy-mm-dd',
			todayHighlight : true,
			maxDate : '-1',
			minView : 2
		});

		$("#formAddFormaPago").validate({
			rules: {
				tipo : "required",
				valor : "required",
				fecha : "required"
			},
			messages: {
				tipo : "",
				valor : "",
				fecha : ""
			},
			submitHandler: function(e) {
				showMessage();
				$('#formAddFormaPago').ajaxSubmit({ success: function(resp) {
					hideMessage();
					if (/^ok$/.test(resp)) {
						mensajeTimer("¡Datos regitrados!", "Se ha actualizado la forma de pago correctamente.", "success", 2000);
						$("#ventana2").dialog("destroy");
						$("#lista").trigger("reloadGrid");
						$("#busca_nombre").focus();
					}
					else {
						mensajeTimer("Error", "No se pudo actualizar la forma de pago<br />" + resp, 'error');
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

	function guardarFormaPago() {
		if ($("#tipo").val() == 2) {//Si es porcentaje
			var val = parseFloat($("#valor").val().replace(",", "."));
			if (val > 1) {
				mensajeTimer("Error", "El porcentaje no puede exceder el 100%", "error", 1500);
				return;
			}
		}
		Swal.fire({ 
			title : "", 
			html : "¿Confirma?", 
			type : "question", 
			showCancelButton : true, 
			confirmButtonText: "Aceptar", 
			cancelButtonText: "Cancelar" 
		}).then(function (res) { 
			if (res.value) 
				$("#formAddFormaPago").submit(); 
		});
	}
</script>