<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php");
	Aplicacion::validarAcceso(10);
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
						<td align="right" style="padding-top:12px; width:70px;"><b>Nombre:</b> &nbsp</td>
						<td style="padding-top:12px; "><input id='busca_nombre' style='padding:1px; width:146px;' type=text name='busca_nombre'></td>
					</tr>
					<tr>
						<td align="right"><b>Cédula:</b> &nbsp;</td>
						<td>
							<input id='busca_cedula' name="busca_cedula" style='padding:1px; width:146px;' type=text name=''>
						</td>
					</tr>
					<tr>
						<td align="right" colspan="2" style="padding:12px; padding-bottom:9px; border-bottom: 1px dashed black;">
							<button onclick="buscarTexto();" title="Buscar" class='btn btn-success'><i class='icon-search icon-white' /></i></button>
							<button onclick="limpiarBusqueda();" title="Limpiar formulario de búsqueda" class='btn btn-danger'><i class='icon-remove icon-white' /></i></button>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td valign="top" style="padding:4px;">
			<table id="lista_personal" class="scroll" cellpadding="0" cellspacing="0"  style="font-size:12px; border-collapse: none;"></table>
			<div id="paginador_personal" class="scroll" style="text-align:left; font-size:10px;"></div>
		</td>
	</tr>
</table>
<script type="text/javascript" charset="ISO-8859-1">
	$(document).ready(function() {
		$(".datetimepicker").remove();
		$("#lista_personal").jqGrid({
		    url : '<?php echo DIR_WEB; ?>lista.php',
		    datatype : "json",
		    colNames : ['<b>Cédula</b>', '<b>Nombre</b>','<b>Fecha Aplica</b>', '<b>Forma Pago</b>','<b>Valor</b>', '<b>Opc.</b>'],
		    colModel : [
		        { name:'nit', index:'nit', width:100, align: 'center' },
				{ name:'nombres', index:'nombres', width:280, align: 'left' },
		        { name:'fecha', index:'fecha', width:160, search: true, align : 'center' },
		        { name:'tipo', index:'tipo', width:90, align: 'center' },
		        { name:'valor', index:'valor', width:90, align: 'center' },
		        { name:'opciones', index:'opciones', width:55, align: 'center', search :false }
		    ],
		    pager: jQuery('#paginador_personal'),
			rowNum : 15,
		    rowList : [15, 20, 30, 50, 100],
		    imgpath : "css/jqGrid/steel/images",
		    sortname : 'fecha',
		    viewrecords : true,
		    mtype : 'POST',
			pagerpos: 'center',
		    sortorder : "desc",
			height: "auto",
		    caption: "<i class='icon-list icon-white' /> <b>PERSONAL TÉCNICO</b>",
			subGrid : true,
			subGridRowExpanded: function(subgrid_id, row_id) {
				var subgrid_table_id, pager_id;
				subgrid_table_id = subgrid_id + "_t"; 
				pager_id = "p_" + subgrid_table_id;
				$("#"+subgrid_id).html("<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>");
				$("#"+subgrid_table_id).jqGrid({
					url:"<?php echo DIR_WEB; ?>get_historico.php?id=" + row_id, 
					datatype: "json",
					colNames: ['Fecha registro', 'Fecha aplica', 'Forma de pago', 'Valor'], 
					colModel: [
						{ name:"fecha_registro", index:"fecha_registro",width:180,align:"center", sortable : false }, 
						{ name:"fecha", index:"fecha",width:120,align:"center", sortable : false }, 
						{ name:"tipo",index:"tipo",width:110, align: 'center', sortable : false },  
						{ name:"valor", index:"valor",width:110, align:"center", sortable : false }
					],
					rowNum : 10, 
					mtype: "POST",
					pager: pager_id, 
					imgpath: "css/jqGrid/steel/images", 
					sortname: 'fecha', 
					sortorder: "desc", 
					height: 'auto',
					viewrecords : true,
					caption: 'Histórico de cambios',
					rowList : [10, 20, 30, 50]
				});
			}
		});
		
		$(".ui-pg-input").css("width", "20px");
		$(".ui-pg-selbox").css("width", "50px");
		$("#gview_lista>div").removeClass("ui-corner-top");
		$("#gbox_lista").removeClass("ui-corner-all");
		$("#paginador_personal").removeClass("ui-corner-bottom");
		
		hideMessage();
		
		var hBuscar = null;
		$("#busca_nombre,#busca_cedula").keyup(function (e) {
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
		$("#busca_nombre,#busca_cedula").val("");
		buscarTexto();
	}
	
	function buscarTexto() {
		var busca_nombre = $("#busca_nombre").val();
		var busca_cedula = $("#busca_cedula").val();
		
		hBuscar = null;
		$("#lista_personal").setGridParam({
			url: "<?php echo DIR_WEB; ?>lista.php?noConvertir"
				+ "&nombre=" + busca_nombre
				+ "&cedula=" + busca_cedula,
			page: 1
		}).trigger("reloadGrid");
	}
</script>