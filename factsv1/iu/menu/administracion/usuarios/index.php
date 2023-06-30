<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	if (isset($_POST["delete"])) {
		$reg = new Usuario();
		$reg->load($_POST["delete"]) or die("Error al cargar la información");
		die ($reg->delete() ? "ok" : "Hay información relacionada con este usuario. No se puede borrar la información"); 
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
						<td align="right" style="padding-top:12px; width:70px;"><b>Nombre:</b> &nbsp</td>
						<td style="padding-top:12px; "><input id='busca_nombre' style='padding:1px; width:146px;' type=text name='busca_nombre'></td>
					</tr>
					<tr>
						<td align="right"><b>Usuario:</b> &nbsp;</td>
						<td>
							<input id='busca_usuario' name="busca_usuario" style='padding:1px; width:146px;' type=text name=''>
						</td>
					</tr>
					<tr>
						<td align="right"><b>Perfil:</b> &nbsp;</td>
						<td>
							<select style='padding:1px; height:25px; width:150px;' name='busca_perfil' id='busca_perfil'>
								<option value=''>Todos...</option>
								<?php
									$perfil = new Perfil();
									$perfil->writeOptions();
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td align="right"><b>Estado:</b> &nbsp;</td>
						<td>
							<select style='padding:1px; height:25px; width:150px;' name='busca_estado' id='busca_estado'>
								<option value=''>Todos...</option>
								<option value='1'>ACTIVO</option>
								<option value='0'>INACTIVO</option>
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
		    colNames : ['<b>Estado</b>', '<b>Usuario</b>','<b>Perfil</b>', '<b>Nombre Completo</b>','<b>E-mail</b>', '<b>Último acceso</b>', '<b>Opc.</b>'],
		    colModel : [
		        { name:'estado', index:'estado', width:80, align: 'center' },
				{ name:'usuario', index:'usuario', width:90, align: 'left' },
				{ name:'perfil_nombre', index:'perfil_nombre', width:90, align: 'left' },
		        { name:'nombres', index:'nombres', width:170, search: true, align : 'left' },
		        { name:'correo', index:'correo', width:170, align: 'left' },
		        { name:'fecha_ultimoacceso', index:'fecha_ultimoacceso', width:140, align: 'center' },
		        { name:'opciones', index:'opciones', width:60, align: 'center', search :false }
		    ],
		    pager: jQuery('#paginador'),
			rowNum : 20,
		    rowList : [10, 20, 30, 50, 100],
		    imgpath : "css/jqGrid/steel/images",
		    sortname : 'fecha_ultimoacceso',
		    viewrecords : true,
		    mtype : 'POST',
			pagerpos: 'center',
		    sortorder : "desc",
			height: "auto",
		    caption: "<i class='icon-list icon-white' /> <b>USUARIOS DEL SISTEMA</b>"
		});
		
		$(".ui-pg-input").css("width", "20px");
		$(".ui-pg-selbox").css("width", "50px");
		$("#gview_lista>div").removeClass("ui-corner-top");
		$("#gbox_lista").removeClass("ui-corner-all");
		$("#paginador").removeClass("ui-corner-bottom");
		
		hideMessage();
		
		$("#busca_estado,#busca_perfil").change(function() {
			buscarTexto();
		});
		
		var hBuscar = null;
		$("#busca_nombre,#busca_usuario").keyup(function (e) {
			if (e.keyCode == 13) {
				buscarTexto();
				return;
			}
		});
		
		$("#formEditar").find("input,select").uniform();
	});
	
	function editar(id) {
		showMessage();
		$("#ventana2").load("<?php echo DIR_WEB; ?>editar.php", { id : id });
	}
	
	function nuevo() {
		showMessage();
		$("#ventana2").load("<?php echo DIR_WEB; ?>nuevo.php");
	}
	<?php if (isset($_GET["nuevo"])) { ?>
	nuevo();
	<?php } ?>
	function limpiarBusqueda() {
		$("#busca_nombre,#busca_usuario,#busca_perfil,#busca_estado").val("");
		buscarTexto();
	}
	
	function eliminar(id) {
		Swal.fire({
			title : 'Confirme',
			text : '¿Desea borrar el usuario?',
			type : 'question',
			showCancelButton : true,
			confirmButtonText: 'Aceptar',
			cancelButtonText: 'Cancelar'
		}).then(function (res) {
			if (res.value) {
				showMessage();
				$.post("<?php echo DIR_WEB; ?>index.php", { delete : id }, function(res) {
					hideMessage();
					if (!/^ok$/.test(res))
						mensaje("Error", "No ha sido posible borrar el registro del usuario debido a que existe información relacionada con este usuario<br />" + resp, 'error');
					$("#lista").trigger("reloadGrid");
				});
			}
		});
	}
	
	function buscarTexto() {
		var busca_nombre = $("#busca_nombre").val();
		var busca_login = $("#busca_usuario").val();
		var busca_perfil = $("#busca_perfil").val();
		var busca_estado = $("#busca_estado").val();
		
		hBuscar = null;
		$("#lista").setGridParam({
			url: "<?php echo DIR_WEB; ?>lista.php?1=1"
				+ "&nombre=" + busca_nombre
				+ "&login=" + busca_login
				+ "&perfil=" + busca_perfil
				+ "&estado=" + busca_estado,
			page: 1
		}).trigger("reloadGrid");
	}
</script>