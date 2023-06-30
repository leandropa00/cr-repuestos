<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../conf/config.php");
	Aplicacion::validarAcceso(5,10);
	$informe = Informe::getInstance();
	$informe->clearQuerys();
	$periodo = new Periodo(Informe::getLimitePeriodo());
	BD::changeInstancia("facts");

	if (isset($_POST["reload"])) 
		$informe->actualizarDatos();

	if (isset($_POST["periodo"])) {
		$periodo = new Periodo($_POST["periodo"]);
		if (!$informe->change($periodo->getYear(), $periodo->getMonth())) die ("Error al intentar consultar el periodo seleccionado");
	}
?>
<style>
	.tr_titulo {
		background-color: #dcb45b !important;
		font-weight:bold;
		color:black;
		font-size:16px;
	}
</style>
<div style="padding: 10px;">
	<b>Periodo de consulta: </b>
	<select id='seleccion_periodo' onchange="changeCombo()" style="cursor:pointer;">
		<?php
			$periodo = new Periodo(Informe::getLimitePeriodo());
			do {
				$selected = $informe->getPeriodo()->toString() == $periodo->toString() ? " selected='selected'" : "";
				echo "<option$selected value='" . $periodo->toString() . "'>" . $periodo->format("Y - F") . "</option>";
			} while ($periodo->previous() >= 201811);
		?>
	</select>
	<div class="btn-group">
		<button onclick="reload();" class='btn btn-primary'><i class='icon-white icon-refresh'></i> Recalcular</button> 
      	<!--<button class="btn dropdown-toggle btn-primary" data-toggle="dropdown">
        	<span class="caret"></span>
      	</button>
		<ul class="dropdown-menu">
	         <li><a tabindex="-1" href="#" onclick='descargarVentasPorAsesor()'><i class='icon icon-download'></i> &nbsp; Ventas por asesor</a></li>
	         <li><a tabindex="-1" href="#" onclick='descargarPerfilTaller()'><i class='icon icon-download'></i> &nbsp; Perfil Taller</a></li>
	         <li><a tabindex="-1" href="#" onclick='descargarUbicacionRepuestos()'><i class='icon icon-download'></i> &nbsp; Inventario</a></li>
		</ul>-->
    </div>
	Última actualización el <b><?php echo $informe->getFechaActualizacion(); ?></b>
	<table class='table table-bordered table-condensed table-hover' style='margin-top:10px;width:440px;'>
		<thead>
			<tr class='ui-widget-header' style='font-size:18px;'>
				<th colspan=2 style="text-align:center;">COA - <?php echo $informe->getPeriodo()->format("F/Y"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class='tr_titulo'>Ventas Accesorios</td>
				<td class='tr_titulo' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalVentasAcces(), 0) ?></b></td>
			</tr>
			<tr>
				<td style='font-weight:bold;padding-left:20px;'><b>Genuinos GM</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalAccesGM(), 0) ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Mostrador Accesorios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorAccesoriosGM.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorAccesGM(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Ventas Accesorios Vehiculos Nuevos</td>
				<td style="text-align:right;">-</td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Accesorios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/TallerAccesoriosGM.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getTallerAccesGM(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Chevystar</td>
				<td style="text-align:right;">-</td>
			</tr>
			<tr>
				<td style='font-weight:bold;padding-left:20px;'>Alternos Accesorios</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalAccesAlterno(), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Mostrador Accesorios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorAccesoriosAlterno.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorAccesAlterno(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Ventas Accesorios Vehiculos Nuevos</td>
				<td style="text-align:right;">-</td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Accesorios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/TallerAccesoriosAlterno.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getTallerAccesAlterno(), 0) ?></a></td>
			</tr>
			<tr>
				<td class='tr_titulo'>Costo de Ventas Accesorios</td>
				<td class='tr_titulo' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalCostoVentasAcces(), 0) ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo Genuinos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoVentaAccesGM.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentaAccesGM(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo Genuinos Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoVentaAccesAlterno.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentaAccesAlterno(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo Chevystar</td>
				<td style="text-align:right;">-</td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Utilidad Bruta</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasAcces() - $informe->getTotalCostoVentasAcces(), 0); ?></td>
			</tr>
			<!--<tr>
				<td style='font-weight:bold;'>Políticas Accesorios</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalGarantias.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda(0, 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Políticas Chevystar</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalInternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda(0, 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Utilidad bruta (P+OD)</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalInternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda(0, 0); ?></a></td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Margen Bruto Accesorios (P+OD) %</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda(0, 0); ?></td>
			</tr>
			-->
			<tr>
				<td colspan=2 class='tr_titulo'>Compra Accesorios</td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CompraAcces.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getCompraAccesGM(), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CompraAcces.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getCompraAccesAlternos(), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CompraAcces.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getCompraAccesOtros(), 0); ?></a></td>
			</tr>

			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Total Compras Accesorios</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalCompraAcces(), 0); ?></td>
			</tr>

			<tr>
				<td style='font-weight:bold;'>Lealtad Bruta accesorios</td>
				<td style="text-align:right;font-weight:bold;"><?php echo Moneda::getMoneda($informe->getLealtadBrutaAcces() * 100, 2); ?>%</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Lealtad Neta accesorios</td>
				<td style="text-align:right;font-weight:bold;"><?php echo Moneda::getMoneda($informe->getLealtadNetaAcces() * 100, 2); ?>%</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Días Hábiles al mes</td>
				<td style="text-align:right;font-weight:bold;">-</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Inventario Inicial accesorios</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getInventarioInicialAcces(), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Ajustes de inventario accesorios</td>
				<td style="text-align:right;font-weight:bold;">-</td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Inventario Final</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarioFinalAcces(), 0); ?></td>
			</tr>
			<tr>
				<td>Entregado a Servicio</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('', true), 0) ?></a></td>
			</tr>
			<tr>
				<td>Inventario 0 a 12 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', '', true), 0); ?></a></td>
			</tr>
			<tr>
				<td>Inventario 12 a 24 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', '', true), 0); ?></a></td>
			</tr>
			<tr>
				<td>Inventario 24 o más meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', '', true), 0); ?></a></td>
			</tr>
			<tr>
				<td>Inventario Accesorios GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosGM.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAcces('gm'), 0); ?></a></td>
			</tr>
			<tr>
				<td>Inventario Accesorios Alterno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAcces('alterno'), 0); ?></a></td>
			</tr>
			<tr>
				<td>% FOF</td>
				<td style="text-align:right;"><?php echo Moneda::getMoneda($informe->getTotalAccesFOF(), 2); ?>%</td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Rotación Bodega</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;"><?php echo Moneda::getMoneda($informe->getRotacionBodegaAcces(), 2); ?></td>
			</tr>
		</table>
	</tbody>
</div>
<script>
	$(document).ready(function() {
		<?php if (isset($_POST["reload"])) { ?>
		mensaje('Proceso finalizado', 'Se han generado correctamente los informes para el periodo seleccionado', 'success');
		<?php } ?>
	});

	function changeCombo() {
		var periodo = $("#seleccion_periodo").val();
		showMessage();
		$("#app_contenido").load("<?php echo DIR_WEB; ?>index.php", { periodo : periodo }, function() {
			hideMessage();
		});
	}

	function reload() {
		Swal.fire({
			title : 'Recalcular informes',
			html : 'Este proceso toma cerca de 8 minutos en procesar la información. <b>¿Desea continuar?</b>',
			type : 'question',
			showCancelButton : true,
			confirmButtonText: 'Aceptar',
			cancelButtonText: 'Cancelar'
		}).then(function (res) {
			if (res.value) {
				showMessage();
				$("#app_contenido").load("<?php echo DIR_WEB; ?>index.php", { reload : true }, function() {
					hideMessage();
				});
			}
		});
	}

	function verVentana(div, script) {
		showMessage();
		$(div).load("<?php echo DIR_WEB; ?>" + script);
	}

	function descargarVentasPorAsesor() {
		showMessage();
		var periodo = $("#seleccion_periodo").val();
		document.location.href='<?php echo DIR_WEB; ?>ventasxasesor.php?periodo=' + periodo;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 5000);
	}

	function descargarPerfilTaller() {
		showMessage();
		var periodo = $("#seleccion_periodo").val();
		document.location.href='<?php echo DIR_WEB; ?>perfil_taller.php?periodo=' + periodo;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 5000);
	}

	function descargarUbicacionRepuestos() {
		showMessage();
		var periodo = $("#seleccion_periodo").val();
		document.location.href='<?php echo DIR_WEB; ?>ubicacion_repuestos.php?periodo=' + periodo;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 5000);
	}
</script>