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
	<table class='table table-bordered table-condensed table-hover' style='margin-top:10px;width:700px;'>
		<thead>
			<tr class='ui-widget-header' style='font-size:18px;'>
				<th colspan=4 style="text-align:center;">COR - <?php echo $informe->getPeriodo()->format("F/Y"); ?><span style='position:relative;font-size:11px;float:right;text-decoration:underline;cursor:pointer;' onclick="toggleAcces();" id='ver_acces' estado=1>Ocultar SoloChevrolet</span></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class='tr_titulo' colspan=2>VENTAS DETAL LIVIANOS</td>
				<td style='text-align:center;' class='coa tr_titulo'>SoloChevrolet</td>
				<td style='text-align:center;' class='coa tr_titulo'>TOTAL</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('liviano', false, false), 0) ?></b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('liviano', false, true), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php 
					echo Moneda::getMoneda($informe->getTotalMostrador('liviano', false, false) + $informe->getTotalMostrador('liviano', false, true), 0); 
				?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSoloFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Colisión / Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorColision.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorColision('liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorColision('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Mantenimiento / Desgaste</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorMantenimientoDesgaste.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorMantenimientoDesgaste('liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostadorMantenimientoDesgaste('liviano'), false) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador (Otros) / Ventas Externas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorOtrosVentasExternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorOtrosVentasExternas('liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostadorOtrosVentasExternas('liviano', false). 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Solochevrolet</td>
				<td class='coa' style="text-align:right;">$0</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSolochevrolet.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('liviano'), false) ?></td>
			</tr>

			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('liviano', false, false), 0); ?></b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('liviano', false, true), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('liviano', false, false) + $informe->getTotalTallerMecanicaMantenimiento('liviano', false, true), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Rápida</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('liviano', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('liviano', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('liviano', false, false) + $informe->getTotalMecanicaRapida('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaRapidaFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('liviano', false, false) + $informe->getMecanicaRapidaFlotas('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaRapidaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('liviano', false, false) + $informe->getMecanicaRapidaUno('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Especializada</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('liviano', false, false), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('liviano', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('liviano', false, false) + $informe->getTotalMecanicaEspecializada('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaEspecializadaFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('liviano', false, false) + $informe->getMecanicaEspecializadaFlotas('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaEspecializadaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('liviano', false, false) + $informe->getMecanicaEspecializadaUno('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Colisión</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColision('liviano'), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColision('liviano', false), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionUno('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionUno('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionAseguradoras.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionAseguradoras('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionAseguradoras('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Garantías</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalGarantias.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalGarantias('liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalGarantias('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Internas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalInternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalInternas('liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInternas('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Alternos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('liviano', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('liviano', false, true), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('liviano', false, false) + $informe->getTotalAlternos('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTaller.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/AlternosTaller.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosTaller('liviano', false, false) + $informe->getAlternosTaller('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosColision.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosColision('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosColision('liviano', false, false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostrador.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/AlternosMostrador.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('liviano', false, false) + $informe->getAlternosMostrador('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Repuestos Flotas Otras Marcas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasOtrasMarcas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasOtrasMarcas.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('liviano', false, false) + $informe->getRepuestosFlotasOtrasMarcas('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Genuinos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('liviano', false, false)+$informe->getAccesoriosGenuinos('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Alternos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('liviano', false, false) + $informe->getAccesoriosAlternos('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Repuestos Flotas Chevrolet</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasChevrolet.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasChevrolet('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasChevrolet.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasChevrolet('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasChevrolet('liviano', false, false) + $informe->getRepuestosFlotasChevrolet('liviano', false, true), 0); ?></td>
			</tr>
			<tr style='background-color:#C0C0C0 !important'>
				<td style='font-weight:bold;'>Provisión (Repuestos No Facturados)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', false)+$informe->getInventariosEntregadoAServicio('liviano', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">N/A</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', false) + $informe->getInventariosEntregadoAServicio('liviano', true), 0); ?></td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Total ventas Detal LIVIANOS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano', false, false), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano', false, true), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano', false, false) + $informe->getTotalVentasDetal('liviano', false, true), 0); ?></td>
			</tr>

<!--- PESADOS --->
			<tr>
				<td class='tr_titulo' colspan=2>VENTAS DETAL PESADOS</td>
				<td style='text-align:center;' class='coa tr_titulo'>ACCES</td>
				<td style='text-align:center;' class='coa tr_titulo'>TOTAL</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('pesados', false, false), 0) ?></b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('pesados', false, true), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php 
					echo Moneda::getMoneda($informe->getTotalMostrador('pesados', false, false) + $informe->getTotalMostrador('pesados', false, true), 0); 
				?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSoloFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Colisión / Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorColision.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorColision('pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorColision('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Mantenimiento / Desgaste</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorMantenimientoDesgaste.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorMantenimientoDesgaste('pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostadorMantenimientoDesgaste('pesados'), false) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador (Otros) / Ventas Externas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorOtrosVentasExternas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostadorOtrosVentasExternas('pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostadorOtrosVentasExternas('pesados', false). 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Solochevrolet</td>
				<td class='coa' style="text-align:right;">$0</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSolochevrolet.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('pesados'), false) ?></td>
			</tr>


			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('pesados', false, false), 0); ?></b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('pesados', false, true), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('pesados', false, false) + $informe->getTotalTallerMecanicaMantenimiento('pesados', false, true), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Rápida</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('pesados', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('pesados', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapida('pesados', false, false) + $informe->getTotalMecanicaRapida('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaRapidaFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('pesados', false, false) + $informe->getMecanicaRapidaFlotas('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaRapidaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('pesados', false, false) + $informe->getMecanicaRapidaUno('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Especializada</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('pesados', false, false), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('pesados', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializada('pesados', false, false) + $informe->getTotalMecanicaEspecializada('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaEspecializadaFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('pesados', false, false) + $informe->getMecanicaEspecializadaFlotas('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/MecanicaEspecializadaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('pesados', false, false) + $informe->getMecanicaEspecializadaUno('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Colisión</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColision('pesados'), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColision('pesados', false), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionUno('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionUno('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionAseguradoras.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionAseguradoras('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionAseguradoras('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Garantías</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalGarantias.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalGarantias('pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalGarantias('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Internas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/TotalInternas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalInternas('pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInternas('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Alternos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('pesados', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('pesados', false, true), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternos('pesados', false, false) + $informe->getTotalAlternos('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTaller.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/AlternosTaller.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosTaller('pesados', false, false) + $informe->getAlternosTaller('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosColision.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosColision('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosColision('pesados', false, false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostrador.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/AlternosMostrador.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('pesados', false, false) + $informe->getAlternosMostrador('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Repuestos Flotas Otras Marcas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasOtrasMarcas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle_coa/RepuestosFlotasOtrasMarcas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('pesados', false, false) + $informe->getRepuestosFlotasOtrasMarcas('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Genuinos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('pesados', false, false)+$informe->getAccesoriosGenuinos('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Alternos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('pesados', false, false) + $informe->getAccesoriosAlternos('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Repuestos Flotas Chevrolet</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasChevrolet.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasChevrolet('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle_coa/RepuestosFlotasChevrolet.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasChevrolet('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasChevrolet('pesados', false, false) + $informe->getRepuestosFlotasChevrolet('pesados', false, true), 0); ?></td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Total ventas Detal PESADOS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados', false, false), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados', false, true), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados', false, false) + $informe->getTotalVentasDetal('pesados', false, true), 0); ?></td>
			</tr>
			<tr style='font-size:20px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">TOTAL VENTAS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados', false, false) + $informe->getTotalVentasDetal('liviano', false, false), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados', false, true) + $informe->getTotalVentasDetal('liviano', false, true), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('pesados', false, false) + $informe->getTotalVentasDetal('liviano', false, false) + $informe->getTotalVentasDetal('pesados', false, true) + $informe->getTotalVentasDetal('liviano', false, true), 0); ?></td>
			</tr>


			<!-- Costos de Venta -->
			<tr>
				<td colspan=2>-</td>
				<td class='coa'></td>
				<td class='coa'></td>
			<tr>
				<td style='font-weight:bold;'>Costo de ventas</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta(), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta('', true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta('', true) + $informe->getTotalCostosVenta(''), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasMostrador.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasMostrador(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/VentasMostrador.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasMostrador('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasMostrador('', true) + $informe->getVentasMostrador(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasTaller.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasTaller(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/VentasTaller.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasTaller('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasTaller('', true) + $informe->getVentasTaller(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasColision.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/VentasColision.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasColision('', true) + $informe->getVentasColision(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Garantías</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasGarantias.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/VentasGarantias.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasGarantias('', true) + $informe->getVentasGarantias(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Internos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasInternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/VentasInternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasInternos('', true) + $informe->getVentasInternos(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/VentasAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasAlternos('', true) + $informe->getVentasAlternos(''), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Total Compras Repuestos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT')), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), '', true), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), '', true) + $informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), ''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO')), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), '', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), '', true) + $informe->getTotalComprasRepuestos(array('CRCO'), ''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO')), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), '', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), '', true) + $informe->getTotalComprasRepuestos(array('CRO'), ''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT')), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), '', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), '', true) + $informe->getTotalComprasRepuestos(array('CROT'), ''), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Inventarios</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios(), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios('', true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios('', true) + $informe->getTotalInventarios(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Entregado a Servicio</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio(), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('', true) + $informe->getInventariosEntregadoAServicio(''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 0 a 12 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M'), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', '', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', '', true) + $informe->getInventarios('0M-12M', ''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 12 a 24 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M'), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', '', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', '', true) + $informe->getInventarios('12M-24M', ''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 24 o más meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS'), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', '', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', '', true) + $informe->getInventarios('24M-MAS', ''), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos(), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('', true) + $informe->getInventariosAlternos(''), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>% FOF</td>
				<td style="text-align:right;font-weight:bold;"><?php echo Moneda::getMoneda($informe->getTotalFOF(), 2) ?>%</td>
				<td class='coa' style="text-align:right;font-weight:bold;">-</td>
				<td class='coa' style="text-align:right;font-weight:bold;">-</td>
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

	function toggleAcces() {
		var estado = $("#ver_acces").attr("estado") == "1" ? "0" : "1";
		$(".coa").css("visibility", estado == "1" ? "visible" : "hidden");
		$("#ver_acces").html(estado == "1" ? "Ocultar SoloChevrolet" : "Mostrar SoloChevrolet").attr("estado", estado);
	};

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