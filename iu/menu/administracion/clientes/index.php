<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../conf/config.php");
	Aplicacion::validarAcceso(10);

	if (isset($_POST["cambiar"])) {
		$identificacion = $_POST["cambiar"];
//		define("MODO_DEBUG", true);
		BD::changeInstancia("mysql");
		die (BD::sql_query("UPDATE cliente_tipo SET tipo_cliente=if(tipo_cliente = 'flota', 'particular', 'flota') WHERE identificacion='" . Seguridad::escapeSQL($identificacion, 'mysql') ."'") ? "ok" : BD::getLastError());
	}
?>
<table style='margin-top:4px;'>
	<tr>
		<td valign="top" style="width: 250px;">
			<div style='padding: 4px;'>
				<table style="font-size:12px; color:black; background-color:#EFEFEF; width: 240px; border:1px solid #dedede;">
					<tr style="color:white;" class="ui-widget-header">
						<td colspan="2" style='padding: 4px;'><i class='icon-search icon-white'></i><b> CONSULTAS</b></td>
					</tr>
					<tr>
						<td align="right" style="padding-top:12px; width:70px;"><b>CC/NIT:</b> &nbsp</td>
						<td style="padding-top:12px; "><input id='busca_identificacion' style='padding:1px; width:146px;' type=text name='busca_identificacion'></td>
					</tr>
					<tr>
						<td align="right"><b>Nombre:</b> &nbsp;</td>
						<td>
							<input id='busca_nombre' name="busca_nombre" style='padding:1px; width:146px;' type=text name=''>
						</td>
					</tr>
					<tr>
						<td align="right"><b>Periodo:</b> &nbsp;</td>
						<td>
							<select style='padding:1px; height:25px; width:150px;' name='busca_informe' id='busca_informe'>
								<option value=''>Todos...</option>
								<?php
									BD::changeInstancia("mysql");
									$r = BD::sql_query("SELECT id, anio, mes from informe order by anio desc, mes desc");
									while ($f = BD::obtenerRegistro($r))
										echo "<option value='" . $f["id"] . "'>" . Fecha::getFechaCorta($f["anio"] . "-" . str_pad($f["mes"], 2, "0", STR_PAD_LEFT) . "-01", "F/Y") . "</option>";
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right"><b>Tipo:</b> &nbsp;</td>
						<td>
							<select style='padding:1px; height:25px; width:150px;' name='busca_tipo' id='busca_tipo'>
								<option value=''>Todos...</option>
								<option value='particular'>Particular</option>
								<option value='flota'>Flota</option>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right" colspan="2" style="padding:12px; padding-bottom:9px; border-bottom: 1px dashed black;">
							<button onclick="buscarTexto();" title="Buscar" class='btn btn-success'><i class='icon-search icon-white' /></i></button>
							<button onclick="limpiarBusqueda();" title="Limpiar formulario de búsqueda" class='btn btn-danger'><i class='icon-remove icon-white' /></i></button>
						</td>
					</tr>
					<tr>
						<td colspan=2 align="right" style="padding:12px; padding-top:9px;">
							<button title="Registrar Usuario" onclick="nuevo()" style="width:90px;" class='btn btn-success'><i class='icon-plus-sign icon-white' /></i> Nuevo</button>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td valign="top" style="padding:4px;">
			<table id="lista" class="scroll" cellpadding="0" cellspacing="0"  style="font-size:12px; border-collapse: none;"></table>
			<div id="paginador" class="scroll" style="text-align:left; font-size:10px;"></div>
		</td>
	</tr>
</table>
<script type="text/javascript" charset="ISO-8859-1">
	$(document).ready(function() {
		$(".datetimepicker").remove();
		$("#lista").jqGrid({
		    url : '<?php echo DIR_WEB; ?>lista.php',
		    datatype : "json",
		    colNames : ['<b>Periodo</b>', '<b>Identificación</b>', '<b>Nombre / Razón social</b>', '<b>Tipo</b>', '<b>Clasificación</b>'],
		    colModel : [
		        { name:'informe_id', index:'informe_id', width:100, align: 'center' },
		        { name:'identificacion', index:'identificacion', width:100, align: 'left' },
		        { name:'nombre', index:'nombre', width:333, align: 'left' },
		        { name:'tipo_cliente', index:'tipo_cliente', width:117, align: 'center', sortable : false },
		        { name:'clasificacion', index:'clasificacion', width:160, align: 'left' }
		    ],
		    pager: jQuery('#paginador'),
			rowNum : 10,
		    rowList : [3,10, 20, 30, 50],
		    imgpath : "css/jqGrid/steel/images",
		    sortname : 'nombre',
		    viewrecords : true,
		    mtype : 'POST',
			pagerpos: 'center',
		    sortorder : "asc",
			height: "auto",
		    caption: "<i class='icon-list icon-white' /> <b>CLIENTES</b>"
		});
		
		$(".ui-pg-input").css("width", "20px");
		$(".ui-pg-selbox").css("width", "50px");
		$("#gview_lista>div").removeClass("ui-corner-top");
		$("#gbox_lista").removeClass("ui-corner-all");
		$("#paginador").removeClass("ui-corner-bottom");
		
		hideMessage();
		
		$("#busca_tipo,#busca_informe").change(function() {
			buscarTexto();
		});
		
		var hBuscar = null;
		$("#busca_nombre,#busca_identificacion").keyup(function (e) {
			if (e.keyCode == 13) {
				buscarTexto();
				e.preventDefault();
			}
		});
		
		$("#formEditar").find("input,select").uniform();
	});
	
	function editar(id) {
		showMessage();
		$("#ventana2").load("<?php echo DIR_WEB; ?>editar.php", { id : id });
	}

	function limpiarBusqueda() {
		$("#busca_nombre,#busca_identificacion,#busca_tipo,#busca_informe").val("");
		buscarTexto();
	}
	
	function buscarTexto() {
		var busca_nombre = $("#busca_nombre").val();
		var busca_identificacion = $("#busca_identificacion").val();
		var busca_tipo = $("#busca_tipo").val();
		var busca_informe = $("#busca_informe").val();
		
		hBuscar = null;
		$("#lista").setGridParam({
			url: "<?php echo DIR_WEB; ?>lista.php?1=1"
				+ "&nombre=" + busca_nombre
				+ "&identificacion=" + busca_identificacion
				+ "&tipo=" + busca_tipo
				+ "&informe=" + busca_informe,
			page: 1
		}).trigger("reloadGrid");
	}

	function cambiarTipocliente(identificacion) {
		showMessage();
		$.post("<?php echo DIR_WEB; ?>index.php", { cambiar : identificacion }, function (resp) {
			if (!/^ok$/.test(resp))
				mensaje("Error", "No fue posible realizar el cambio<br />" + resp, "error");
			hideMessage();
			$("#lista").trigger("reloadGrid");
		});
	}
</script>