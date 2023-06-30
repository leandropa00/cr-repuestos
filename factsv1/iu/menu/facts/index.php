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
      	<button class="btn dropdown-toggle btn-primary" data-toggle="dropdown">
        	<span class="caret"></span>
      	</button>
		<ul class="dropdown-menu">
	         <li><a tabindex="-1" href="#" onclick='descargarVentasPorAsesor()'><i class='icon icon-download'></i> &nbsp; Ventas por asesor</a></li>
	         <li><a tabindex="-1" href="#" onclick='descargarPerfilTaller()'><i class='icon icon-download'></i> &nbsp; Perfil Taller</a></li>
	         <li><a tabindex="-1" href="#" onclick='descargarUbicacionRepuestos()'><i class='icon icon-download'></i> &nbsp; Inventario</a></li>
		</ul>
    </div>
	Última actualización el <b><?php echo $informe->getFechaActualizacion(); ?></b>
	<table class='table table-bordered table-condensed table-hover' style='margin-top:10px;width:440px;'>
		<thead>
			<tr class='ui-widget-header' style='font-size:18px;'>
				<th colspan=2 style="text-align:center;">SEDE A - <?php echo $informe->getPeriodo()->format("F/Y"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr><td class='tr_titulo' colspan=2>VENTAS DETAL LIVIANOS</td></tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('liviano'), 0) ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSoloFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Colisión / Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorColision.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorColision('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Mantenimiento / Desgaste</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorMantenimientoDesgaste.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorMantenimientoDesgaste('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador (Otros) / Ventas Externas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorOtrosVentasExternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorOtrosVentasExternas('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Solochevrolet</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSolochevrolet.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('liviano'), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Rápida</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('liviano'), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Especializada</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('liviano'), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Colisión</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColision('liviano'), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionUno('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionAseguradoras.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionAseguradoras('liviano'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Garantías</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalGarantias.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalGarantias('liviano'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Internas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalInternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalInternas('liviano'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Alternos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('liviano'), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTaller.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('liviano'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosColision.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosColision('liviano'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostrador.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('liviano'), 0); ?></a></td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Total ventas Detal LIVIANOS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano'), 0); ?></td>
			</tr>

<!--- PESADOS --->
			<tr><td class='tr_titulo' colspan=2>VENTAS DETAL PESADOS</td></tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('pesados'), 0) ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSoloFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Colisión / Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorColision.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorColision('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Mantenimiento / Desgaste</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorMantenimientoDesgaste.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorMantenimientoDesgaste('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador (Otros) / Ventas Externas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorOtrosVentasExternas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorOtrosVentasExternas('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Solochevrolet</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSolochevrolet.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('pesados'), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Rápida</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('pesados'), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Especializada</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('pesados'), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Colisión</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColision('pesados'), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionUno('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionAseguradoras.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionAseguradoras('pesados'), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Garantías</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalGarantias.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalGarantias('pesados'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Internas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalInternas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalInternas('pesados'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Alternos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('pesados'), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTaller.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('pesados'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosColision.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosColision('pesados'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostrador.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('pesados'), 0); ?></a></td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Total ventas Detal PESADOS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados'), 0); ?></td>
			</tr>
			<tr style='font-size:20px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">TOTAL VENTAS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados') + $informe->getTotalVentasDetal('liviano'), 0); ?></td>
			</tr>
			<tr><td colspan=2>-</td></tr>
			<tr>
				<td style='font-weight:bold;'>Costo de ventas</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta(), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasMostrador.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasMostrador(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasTaller.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasTaller(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasColision.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Garantías</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasGarantias.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Internos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasInternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Total Compras Repuestos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT')), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO')), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO')), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT')), 0) ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Inventarios</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios(), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Entregado a Servicio</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio(), 0) ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 0 a 12 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 12 a 24 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 24 o más meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS'), 0); ?></a></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos(), 0); ?></a></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>% FOF</td>
				<td style="text-align:right;font-weight:bold;"><?php echo Moneda::getMoneda($informe->getTotalFOF(), 2) ?>%</td>
			</tr>
		</tbody>
	</table>
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