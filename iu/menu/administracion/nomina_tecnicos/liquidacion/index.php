<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php");
	Aplicacion::validarAcceso(10);

	if (isset($_POST["data"]) && is_array($_POST["data"])) {
		//BEGIN TRAN
		BD::changeInstancia("facts");
		if (!BD::sql_query("BEGIN TRAN")) die ("Error al iniciar la transacción");
		foreach($_POST["data"] as $dat) {
			$registro =  explode("@@@", $dat);
			if (count($registro) != 15) {
				BD::sql_query("ROLLBACK TRAN");
				die("<b>No se realizaron cambios:</b><br />La información recibida no es correcta");
			}
			$guardar = $registro[14] == "1" && $registro[13] == "0";
			$borrar = $registro[14] == "0" && $registro[13] != "0";
			
			if ($borrar) {
				$r = BD::eliminar("bsc_nompagolog", array(
					"id" => $registro[13],
					"fecha" => $registro[0],
					"tipo" => $registro[1],
					"numero" => $registro[2],
					"numero_orden" => $registro[3],
					"operario" => $registro[4],
					"serie" => $registro[5],
					"operacion" => $registro[6])
				);
				if ($r)
					continue;
				else {
					BD::sql_query("ROLLBACK TRAN");
					die("No fue posible quitar la siguiente información para pago:<br /><br /><table class='table table-hovered table-bordered'>"
						. "<tr><td style='width:170px;'><b>Factura:</b></td><td>" . $registro[1] . "-" .$registro[2] . "</td></tr>"
						. "<tr><td><b>#Orden de trabajo:</b> </td><td>" . $registro[3] . "</td></tr>"
						. "<tr><td><b>ID Técnico:</b> </td><td>" . $registro[4] . "</td></tr>"
						. "<tr><td><b>Liquidado:</b> </td><td>$" . Moneda::getMoneda($registro[12], 0) . "</td></tr>"
						. "</table>"
					);
				}
			}
			if ($guardar) {
				if ($registro[10] == "") {
					BD::sql_query("ROLLBACK TRAN");
					die("<b>No se realizaron cambios:</b><br />Verifique la forma de pago del técnico con identificación " . $registro[4]);
				}
				if ($registro[12] == "") {
					BD::sql_query("ROLLBACK TRAN");
					die("<b>No se registró ningún pago:</b><br />Verifique el valor de liquidación de la OT #" . $registro[3]);
				}

				$r = BD::adicionar("bsc_nompagolog", array(
					"fecha" => $registro[0],
					"tipo" => $registro[1],
					"numero" => $registro[2],
					"numero_orden" => $registro[3],
					"operario" => $registro[4],
					"serie" => $registro[5],
					"operacion" => $registro[6],
					"descripcion" => $registro[7],
					"total" => $registro[8],
					"tiempo" => $registro[9],
					"fpago_tipo" => $registro[10],
					"fpago_valor" => $registro[11],
					"liquidado" => round($registro[12])
				));
				if (!$r) {
					BD::sql_query("ROLLBACK TRAN");
					die("No fue posible guardar la siguiente información:<br /><br /><table class='table table-hovered table-bordered'>"
						. "<tr><td style='width:170px;'><b>Factura:</b></td><td>" . $registro[1] . "-" .$registro[2] . "</td></tr>"
						. "<tr><td><b>#Orden de trabajo:</b> </td><td>" . $registro[3] . "</td></tr>"
						. "<tr><td><b>ID Técnico:</b> </td><td>" . $registro[4] . "</td></tr>"
						. "<tr><td><b>Liquidado:</b> </td><td>$" . Moneda::getMoneda($registro[12], 0) . "</td></tr>"
						. "</table>"
					);
				}
			}
			
		}
		if (BD::sql_query("COMMIT TRAN"))
			die("ok");
		die("Error al guardar los datos de la transacción");
	}
?>
<table style='margin-top:4px;'>
	<tr>
		<td valign="top" style="width: 250px;">
			<div style='padding: 4px;'>
				<table style="font-size:12px; color:black; background-color:#EFEFEF; border:1px solid #dedede;width:1082px;">
					<tr style="color:white;" class="ui-widget-header">
						<td colspan="7" style='padding: 4px;'><i class='icon-search icon-white'></i><b> FILTRAR</b></td>
					</tr>
					<tr>
						<td style="padding-left:12px;padding-top:12px;padding-bottom:12px; width:250px;">
							<b>Periodo:</b><br />
							<div id="reportrange" class="pull-center" style="background: transparent; cursor: pointer; padding: 5px 10px; right: auto; border: 1px solid rgba(255, 255, 255, 0.3); width: 100%">
							    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
							    <span></span> <b class="caret"></b>
							</div>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;">
							<b>Tipo:</b><br />
							<select id='busca_tipo' onchange='buscarTexto();' style='width:100px;'>
								<option value=''>TODOS</option>
								<option value='FL'>FL</option>
								<option value='FC'>FC</option>
								<option value='FG'>FG</option>
								<option value='FSC'>FSC</option>
								<option value='FT'>FT</option>
								<option value='FTA'>FTA</option>
								<option value='TI'>TI</option>
								<option value='DVTA'>DVTA</option>
								<option value='DVTO'>DVTO</option>
								<option value='DVTS'>DVTS</option>
								<option value='DVTC'>DVTC</option>
								<option value='DVTL'>DVTL</option>
							</option>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;">
							<b>Nro Factura:</b><br />
							<input id='busca_numero' onkeypress="return solo_numeros(event);" style='width:70px;' type=text>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;">
							<b>Nro OT:</b><br />
							<input id='busca_numero_orden' onkeypress="return solo_numeros(event);" style='width:70px;' type=text>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;">
							<b>CC/Nombre Técnico:</b><br />
							<input id='busca_nombres' placeholder="Escriba todo o parte del nombre" style='width:260px;' type=text>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;">
							<b>Otros Filtros:</b><br />
							<select id='otros_filtros' onchange='buscarTexto();' style="width:110px;">
								<option value=''>N/A</option>
								<option value='1'>Sin pago</option>
								<option value='2'>Pagados</option>
							</select>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;padding-right:12px;" valign="bottom">
							<button onclick="buscarTexto();" title="Buscar" class='btn btn-info'><i class='icon-search icon-white' /></i></button>
							<button onclick="limpiarBusqueda();" title="Limpiar formulario de búsqueda" class='btn btn-danger'><i class='icon-remove icon-white' /></i></button>
							<button onclick="descargarDetalleExcel();" title="Descargar detalle en excel" class='btn btn-success'><i class='icon-download-alt icon-white' /></i></button>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" style="padding:4px;">
			<button onclick='seleccionar(1);' class='btn btn-small btn-info'><i class='icon icon-white icon-check' /></button>
			<button onclick='seleccionar(0);' class='btn btn-small btn-danger'> Cancelar selección</button>
			<button onclick='procesarOpcion()' class='btn btn-small btn-success'>Aplicar cambios</button>
			<table id="lista" class="scroll" cellpadding="0" cellspacing="0"  style="font-size:12px; border-collapse: none;"></table>
			<div id="paginador" class="scroll" style="text-align:left; font-size:10px;"></div>
		</td>
	</tr>
</table>
<script type="text/javascript" charset="ISO-8859-1">
	$(document).ready(function() {
		$(".datetimepicker").remove();
		$("#lista").jqGrid({
		    url : '<?php echo DIR_WEB; ?>lista.php?fecha1=<?php echo date("Y-m-01"); ?>&fecha2=<?php echo date("Y-m-t"); ?>',
		    datatype : "json",
		    colNames : ["*", '<b>Doc Número</b>','<b>Nro OT</b>', '<b>Fecha</b>','<b>ID Técnico</b>', '<b>Nombre Técnico</b>', '<b>Descripción</b>', '<b>Valor</b>', '<b>Desc</b>','<b>Total</b>','<b>Tiempo</b>', '<b>Liquidado</b>'],
		    colModel : [
		        { name:'seleccion', index:'seleccion', width:25, align: 'center', sortable : false },
		        { name:'numero', index:'numero', width:100, align: 'center' },
		        { name:'numero_orden', index:'numero_orden', width:60, align: 'center' },
		        { name:'fec', index:'fec', width:75, align: 'center' },
				{ name:'operario', index:'operario', width:80, align: 'left' },
		        { name:'nombres', index:'nombres', width:200, search: true, align : 'left' },
		        { name:'descripcion', index:'descripcion', width:160, align: 'left' },
		        { name:'valor', index:'valor', width:70, align: 'right' },
		        { name:'descuento', index:'descuento', width:43, align: 'center' },
		        { name:'total', index:'total', width:70, align: 'right' },
		        { name:'tiempo', index:'tiempo', width:70, align: 'center' },
		        { name:'liquidado', index:'liquidado', width:70, align: 'right' }
		    ],
		    pager: jQuery('#paginador'),
			rowNum : 15,
		    rowList : [15, 20, 30, 50, 100],
		    imgpath : "css/jqGrid/steel/images",
		    sortname : 'numero_orden',
		    viewrecords : true,
		    mtype : 'POST',
			pagerpos: 'center',
		    sortorder : "desc",
			height: "auto",
			caption: "<i class='icon-list icon-white' /> <b>FACTURAS</b>",
			loadComplete : function () {
				$(".devolucion").parent().parent().css("color", "#990000");
				hideMessage();
			}
		});
		
		$(".ui-pg-input").css("width", "20px");
		$(".ui-pg-selbox").css("width", "50px");
		$("#gview_lista>div").removeClass("ui-corner-top");
		$("#gbox_lista").removeClass("ui-corner-all");
		$("#paginador").removeClass("ui-corner-bottom");
		
		hideMessage();
		
		var hBuscar = null;
		$("#busca_numero,#busca_numero_orden,#busca_operario,#busca_nombres,#busca_serie,#busca_operacion").keyup(function (e) {
			if (e.keyCode == 13) {
				buscarTexto();
				return;
			}
		});
		
		$("#formEditar").find("input,select").uniform();
	});
	
	function editar(id) {
		showMessage();
		$("#ventana2").load("<?php echo DIR_WEB; ?>editar.php", { identificacion : id });
	}
	
	function limpiarBusqueda() {
		$("#busca_tipo,#otros_filtros,#busca_numero,#busca_numero_orden,#busca_operario,#busca_nombres,#busca_serie,#busca_operacion").val("");
		buscarTexto();
	}
	
	var informe_ini = moment().startOf('month');
	var informe_fin = moment().endOf('month');

	function buscarTexto() {
		cb(informe_ini, informe_fin);
	}

	function descargarDetalleExcel() {
    	
        $('#reportrange span').html(informe_ini.format('MMMM D, YYYY') + ' - ' + informe_fin.format('MMMM D, YYYY'));
        
        f1 = informe_ini.format('YYYY-MM-DD');
		f2 = informe_fin.format('YYYY-MM-DD');
		
		var busca_tipo = $("#busca_tipo").val();
		var busca_otros_filtros = $("#otros_filtros").val();
		var busca_numero = $("#busca_numero").val();
		var busca_numero_orden = $("#busca_numero_orden").val();
		var busca_nombres = $("#busca_nombres").val();

		showMessage();
		document.location.href="<?php echo DIR_WEB; ?>excel.php?noConvertir"
				+ "&tipo=" + busca_tipo
				+ "&numero=" + busca_numero
				+ "&numero_orden=" + busca_numero_orden
				+ "&otros_filtros=" + busca_otros_filtros
				+ "&nombres=" + busca_nombres
				+ "&fecha1=" + f1
				+ "&fecha2=" + f2;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 2000);
	}
	
	function cb(start, end) {
    	informe_ini = start;
    	informe_fin = end;
    	
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        
        f1 = start.format('YYYY-MM-DD');
		f2 = end.format('YYYY-MM-DD');
		
		var busca_tipo = $("#busca_tipo").val();
		var busca_otros_filtros = $("#otros_filtros").val();
		var busca_numero = $("#busca_numero").val();
		var busca_numero_orden = $("#busca_numero_orden").val();
		//var busca_operario = $("#busca_operario").val();
		var busca_nombres = $("#busca_nombres").val();
		/*var busca_serie = $("#busca_serie").val();
		var busca_operacion = $("#busca_operacion").val();*/
		
        $("#lista").setGridParam({
			url: "<?php echo DIR_WEB; ?>lista.php?noConvertir"
				+ "&tipo=" + busca_tipo
				+ "&numero=" + busca_numero
				+ "&numero_orden=" + busca_numero_orden
				+ "&otros_filtros=" + busca_otros_filtros
				//+ "&operario=" + busca_operario
				+ "&nombres=" + busca_nombres
				/*+ "&serie=" + busca_serie
				+ "&operacion=" + busca_operacion*/
				+ "&fecha1=" + f1
				+ "&fecha2=" + f2,
			page: 1
		}).trigger("reloadGrid");
    }
	var elmes = 'mes';
    $('#reportrange').daterangepicker({
        startDate: informe_ini,
        endDate: informe_fin,
        ranges: {
           'Este mes': [moment().startOf('month'), moment().endOf('month')],
           'El mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'Hace 2 meses' : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
           'Hace 3 meses': [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
           'Hace 4 meses': [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
           'Hace 5 meses': [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')],
           'Hace 6 meses': [moment().subtract(6, 'month').startOf('month'), moment().subtract(6, 'month').endOf('month')]
        }
    }, cb);
    
	cb(informe_ini, informe_fin);
	$(".caret").css("vertical-align", "middle");

	function procesarOpcion(v) {
		Swal.fire({
			title : 'Marcar/Desmarcar pagos',
			text : '¿Confirma que desea realizar los cambios?',
			type : 'question',
			showCancelButton : true,
			confirmButtonText: 'Aceptar',
			cancelButtonText: 'Cancelar'
		}).then(function (res) {
			if (res.value) {
				showMessage();
				data = [];
				$(".seleccion-item").each(function(i) { data.push($(this).val() + ($(this).is(":checked") ? "@@@1" : "@@@0")); });
				$.post("<?php echo DIR_WEB; ?>index.php", { data : data }, function (result) {
					if (/^ok$/.test(result)) {
						mensajeTimer("Información", "Todos los pagos se actualizaron correctamente", "success", 2000);
						$("#lista").trigger("reloadGrid");
					}
					else
						mensaje("Error", result, "error");
					hideMessage();
				});
			}
		});
	}

	function seleccionar(v) {
		if (v == 1)
			$(".seleccion-item").attr("checked", "checked");
		else {
			showMessage();
			$("#lista").trigger("reloadGrid");
		}
	}
</script>