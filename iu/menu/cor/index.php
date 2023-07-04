<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../conf/config.php");
	Aplicacion::validarAcceso(5,10);
	// define('MODO_DEBUG', true);
	$informe = Informe::getInstance();
	$informe->clearQuerys();
	$periodo = new Periodo(Informe::getLimitePeriodo());
	BD::changeInstancia("mysql");

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
	<table class='table table-bordered table-condensed table-hover' style='margin-top:10px;width:800px;'>
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
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('liviano', false, true), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('liviano', false, false) + $informe->getTotalMostrador('liviano', false, true), 0); ?></b></td>
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
				<td style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSolochevrolet.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('liviano'), false) ?></td>
			</tr>

			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('liviano', false, false), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('liviano', false, true), 0); ?></b></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotas.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('liviano', false, false) + $informe->getMecanicaRapidaFlotas('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('liviano', false, true), 0); ?></a></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotas.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('liviano', false, false) + $informe->getMecanicaEspecializadaFlotas('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('liviano', false, true), 0) ?></a></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTaller.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('liviano', false, true), 0); ?></a></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostrador.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('liviano', false, true), 0); ?></a></td>
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
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('liviano', false, false)+$informe->getAccesoriosGenuinos('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Alternos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('liviano', false, false) + $informe->getAccesoriosAlternos('liviano', false, true), 0); ?></td>
			</tr>
			<tr style='background-color:#C0C0C0 !important'>
				<td style='font-weight:bold;'>Provisión (Repuestos No Facturados)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ProvisionRepuestosNoFacturados.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', -1), 0) ?></a></td>
				<td class='coa' style="text-align:right;">N/A</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', false) + $informe->getInventariosEntregadoAServicio('liviano', true), 0); ?></td>
			</tr>
			<tr style='background-color:#C0C0C0 !important'>
				<td style='font-weight:bold;'>Repuestos Solo Flotas</td>
				<td style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano') + $informe->getMecanicaRapidaFlotas('liviano', false, false) + $informe->getMecanicaEspecializadaFlotas('liviano', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda(0 + $informe->getMecanicaRapidaFlotas('liviano', false, true) + $informe->getMecanicaEspecializadaFlotas('liviano', false, true), 0); ?></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano') + $informe->getMecanicaRapidaFlotas('liviano', false, false) + $informe->getMecanicaEspecializadaFlotas('liviano', false, false) + 0 +  + $informe->getMecanicaRapidaFlotas('liviano', false, true) + $informe->getMecanicaEspecializadaFlotas('liviano', false, true), 0); ?></td>
			</tr>
			<tr style='font-size:16px;'>
				<td style="background-color:#555;color:white;font-weight:bold;">Total ventas Detal LIVIANOS</td>
				<td style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano', false, false), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano', false, true), 0); ?></td>
				<td class='coa' style="background-color:#555;color:white;font-weight:bold;text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalVentasDetal('liviano', false, false) + $informe->getTotalVentasDetal('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td class='coa'></td>
				<td class='coa'></td>
			</tr>
<!--- PESADOS --->
			<tr>
				<td class='tr_titulo' colspan=2>VENTAS DETAL PESADOS</td>
				<td style='text-align:center;' class='coa tr_titulo'>Solochevrolet</td>
				<td style='text-align:center;' class='coa tr_titulo'>TOTAL</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('pesados', false, false), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalMostrador('pesados', false, true), 0) ?></b></td>
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
				<td style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorSolochevrolet.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSolochevrolet('pesados'), false) ?></td>
			</tr>

			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('pesados', false, false), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getTotalTallerMecanicaMantenimiento('pesados', false, true), 0); ?></b></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotas.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotas('pesados', false, false) + $informe->getMecanicaRapidaFlotas('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUno.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUno('pesados', false, true), 0); ?></a></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotas.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotas('pesados', false, false) + $informe->getMecanicaEspecializadaFlotas('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUno.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUno('pesados', false, true), 0) ?></a></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTaller.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTaller('pesados', false, true), 0); ?></a></td>
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
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostrador.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosMostrador('pesados', false, false) + $informe->getAlternosMostrador('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Repuestos Flotas Otras Marcas</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasOtrasMarcas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/RepuestosFlotasOtrasMarcas.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getRepuestosFlotasOtrasMarcas('pesados', false, false) + $informe->getRepuestosFlotasOtrasMarcas('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Genuinos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosGenuinos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosGenuinos('pesados', false, false)+$informe->getAccesoriosGenuinos('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Accesorios Alternos</td>
				<td style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><a onclick="verVentana('#ventana', 'detalle/AccesoriosAlternos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getAccesoriosAlternos('pesados', false, false) + $informe->getAccesoriosAlternos('pesados', false, true), 0); ?></td>
			</tr>
			<tr style='background-color:#C0C0C0 !important'>
				<td style='font-weight:bold;'>Provisión (Repuestos No Facturados)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ProvisionRepuestosNoFacturados.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('pesados', -1), 0) ?></a></td>
				<td class='coa' style="text-align:right;">N/A</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('pesados', false) + $informe->getInventariosEntregadoAServicio('pesados', true), 0); ?></td>
			</tr>
			<tr style='background-color:#C0C0C0 !important'>
				<td style='font-weight:bold;'>Repuestos Solo Flotas</td>
				<td style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados') + $informe->getMecanicaRapidaFlotas('pesados', false, false) + $informe->getMecanicaEspecializadaFlotas('pesados', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda(0 + $informe->getMecanicaRapidaFlotas('pesados', false, true) + $informe->getMecanicaEspecializadaFlotas('pesados', false, true), 0); ?></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados') + $informe->getMecanicaRapidaFlotas('pesados', false, false) + $informe->getMecanicaEspecializadaFlotas('pesados', false, false) + 0 +  + $informe->getMecanicaRapidaFlotas('pesados', false, true) + $informe->getMecanicaEspecializadaFlotas('pesados', false, true), 0); ?></td>
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
				<td colspan=2></td>
				<td class='coa'></td>
				<td class='coa'></td>
			</tr>

			<!-- COSTOS DE VENTA LIVIANOS -->
			<tr style='font-size:16px;'>
				<td colspan=4 style="background-color:#1f497d;color:white;font-weight:bold;">COSTOS DETAL LIVIANOS</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasMostrador('liviano', false, false), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasMostrador('liviano', false, true), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php 
					echo Moneda::getMoneda($informe->getVentasMostrador('liviano', false, false) + $informe->getVentasMostrador('liviano', false, true), 0); 
				?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorCostosSoloFlotas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloFlotas(['FA', 'FRD'], 'liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloFlotas(['FA', 'FRD'], 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Colisión / Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorCostosColision.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasColision(['FA', 'FRD'],'liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasColision(['FA', 'FRD'], 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Mantenimiento / Desgaste</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorMantenimientoDesgaste.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostosMostadorMantenimientoDesgaste([['FA', 'FRD']], 'liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostosMostadorMantenimientoDesgaste(['FA', 'FRD'], 'liviano'), false) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador (Otros) / Ventas Externas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorCostosOtrosVentasExternas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasExternas(['FA', 'FRD'], 'liviano'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasExternas(['FA', 'FRD'], 'liviano', false). 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Solochevrolet</td>
				<td style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorCostosSolochevrolet.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloChevrolet('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloChevrolet('liviano'), false) ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasTaller('liviano', false, false), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasTaller('liviano', false, true), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasTaller('liviano', false, false) + $informe->getVentasTaller('liviano', false, true), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Rápida</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapidaCostos('liviano', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapidaCostos('liviano', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapidaCostos('liviano', false, false) + $informe->getTotalMecanicaRapidaCostos('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotasCostos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotasCostos('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotasCostos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotasCostos('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotasCostos('liviano', false, false) + $informe->getMecanicaRapidaFlotasCostos('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUnoCostos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUnoCostos('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUnoCostos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUnoCostos('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUnoCostos('liviano', false, false) + $informe->getMecanicaRapidaUnoCostos('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Especializada</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializadaCostos('liviano', false, false), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializadaCostos('liviano', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializadaCostos('liviano', false, false) + $informe->getTotalMecanicaEspecializadaCostos('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotasCostos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotasCostos('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotasCostos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotasCostos('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotasCostos('liviano', false, false) + $informe->getMecanicaEspecializadaFlotasCostos('liviano', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUnoCostos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUnoCostos('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUnoCostos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUnoCostos('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUnoCostos('liviano', false, false) + $informe->getMecanicaEspecializadaUnoCostos('liviano', false, true), 0) ?></td>
			</tr>

			<tr>
				<td style='font-weight:bold;'>Colisión</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColisionCosto('liviano'), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColisionCosto('liviano', false), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionUnoCosto.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionUnoCosto('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionUnoCosto('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionAseguradorasCosto.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionAseguradorasCosto('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionAseguradorasCosto('liviano', false), 0); ?></td>
			</tr>
			<!-- <tr>
				<td style='padding-left:20px;'>Costo de venta Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasColision.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasColision.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasColision('liviano', false, false) + $informe->getVentasColision('liviano', false, true), 0); ?></td>
			</tr> -->
			<tr>
				<td style='padding-left:20px;'>Costo de venta Garantías</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasGarantias.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasGarantias.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasGarantias('liviano', false, false) + $informe->getVentasGarantias('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Internos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasInternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasInternos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasInternos('liviano', false, false) + $informe->getVentasInternos('liviano', false, true), 0); ?></td>
			</tr>
			<!-- <tr>
				<td style='padding-left:20px;'>Costo de venta Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasAlternos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasAlternos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasAlternos('liviano', false, false) + $informe->getVentasAlternos('liviano', false, true), 0); ?></td>
			</tr> -->
			<tr>
				<td style='font-weight:bold;'>Alternos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternosCosto('liviano', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternosCosto('liviano', false, true), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternosCosto('liviano', false, false) + $informe->getTotalAlternosCosto('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTallerCosto.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTallerCosto('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTallerCosto.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTallerCosto('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosTallerCosto('liviano', false, false) + $informe->getAlternosTallerCosto('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosColisionCosto.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosColisionCosto('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosColisionCosto('liviano', false, false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostradorCosto.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostradorCosto('liviano', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostradorCosto.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostradorCosto('liviano', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosMostradorCosto('liviano', false, false) + $informe->getAlternosMostradorCosto('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Repuestos Flotas Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostosRepuestosFlotasOtrasMarcas.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoRepuestosFlotasOtrasMarcas('liviano', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostosRepuestosFlotasOtrasMarcas.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoRepuestosFlotasOtrasMarcas('liviano', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoRepuestosFlotasOtrasMarcas('liviano', false, false) + $informe->getCostoRepuestosFlotasOtrasMarcas('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;background-color:#C0C0C0 !important'>Repuestos Flotas Chevrolet</td>
				<td style="text-align:right;background-color:#C0C0C0 !important">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano', false, 'totalc') + $informe->getMecanicaRapidaFlotas('liviano', false, false, 'totalc') + $informe->getMecanicaEspecializadaFlotas('liviano', false, false, 'totalc'), 0); ?></td>
				<td class='coa' style="text-align:right;background-color:#C0C0C0 !important">$<?php echo Moneda::getMoneda(0 + $informe->getMecanicaRapidaFlotas('liviano', false, true, 'totalc') + $informe->getMecanicaEspecializadaFlotas('liviano', false, true, 'totalc'), 0); ?></td>
				<td class='coa' style="text-align:right;background-color:#C0C0C0 !important">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('liviano', false, 'totalc') + $informe->getMecanicaRapidaFlotas('liviano', false, false, 'totalc') + $informe->getMecanicaEspecializadaFlotas('liviano', false, false, 'totalc') + 0 +  + $informe->getMecanicaRapidaFlotas('liviano', false, true, 'totalc') + $informe->getMecanicaEspecializadaFlotas('liviano', false, true, 'totalc'), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Accesorios Genuinos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosGenuinos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosGenuinos('liviano', true, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosGenuinos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosGenuinos('liviano', true, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosGenuinos('liviano', true, false)+$informe->getCostoAccesoriosGenuinos('liviano', true, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Accesorios Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosAlternos.php?tipo=liviano');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosAlternos('liviano', true, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosAlternos.php?tipo=liviano&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosAlternos('liviano', true, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosAlternos('liviano', true, false) + $informe->getCostoAccesoriosAlternos('liviano', true, true), 0); ?></td>
			</tr>
			
			<!-- COSTOS DE VENTA PESADOS -->
			<tr style='font-size:16px;'>
				<td colspan=4 style="background-color:#1f497d;color:white;font-weight:bold;">COSTOS DETAL PESADOS</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'><b>Mostrador</b></td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasMostrador('pesados', false, false), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasMostrador('pesados', false, true), 0) ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php 
					echo Moneda::getMoneda($informe->getVentasMostrador('pesados', false, false) + $informe->getVentasMostrador('pesados', false, true), 0); 
				?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorCostosSoloFlotas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloFlotas(['FA', 'FRD'], 'pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloFlotas(['FA', 'FRD'], 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Colisión / Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorCostosColision.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasColision(['FA', 'FRD'],'pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasColision(['FA', 'FRD'], 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Mantenimiento / Desgaste</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorMantenimientoDesgaste.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostosMostadorMantenimientoDesgaste([['FA', 'FRD']], 'pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostosMostadorMantenimientoDesgaste(['FA', 'FRD'], 'pesados'), false) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador (Otros) / Ventas Externas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostadorCostosOtrosVentasExternas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasExternas(['FA', 'FRD'], 'pesados'), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasExternas(['FA', 'FRD'], 'pesados', false). 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Mostrador Solochevrolet</td>
				<td style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MostradorCostosSolochevrolet.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloChevrolet('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoVentasSoloChevrolet('pesados'), false) ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Taller Mecánica y Mantenimiento</td>
				<td style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasTaller('pesados', false, false), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasTaller('pesados', false, true), 0); ?></b></td>
				<td class='coa' style="text-align:right;font-weight:bold;"><b>$<?php echo Moneda::getMoneda($informe->getVentasTaller('pesados', false, false) + $informe->getVentasTaller('pesados', false, true), 0); ?></b></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Rápida</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapidaCostos('pesados', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapidaCostos('pesados', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaRapidaCostos('pesados', false, false) + $informe->getTotalMecanicaRapidaCostos('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotasCostos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotasCostos('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaFlotasCostos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotasCostos('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaFlotasCostos('pesados', false, false) + $informe->getMecanicaRapidaFlotasCostos('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUnoCostos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUnoCostos('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaRapidaUnoCostos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUnoCostos('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaRapidaUnoCostos('pesados', false, false) + $informe->getMecanicaRapidaUnoCostos('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;font-weight:bold;'><b>Mecánica Especializada</b></td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializadaCostos('pesados', false, false), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializadaCostos('pesados', false, true), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalMecanicaEspecializadaCostos('pesados', false, false) + $informe->getTotalMecanicaEspecializadaCostos('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller solo flotas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotasCostos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotasCostos('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaFlotasCostos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotasCostos('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaFlotasCostos('pesados', false, false) + $informe->getMecanicaEspecializadaFlotasCostos('pesados', false, true), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:40px;'>Taller Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUnoCostos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUnoCostos('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/MecanicaEspecializadaUnoCostos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUnoCostos('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getMecanicaEspecializadaUnoCostos('pesados', false, false) + $informe->getMecanicaEspecializadaUnoCostos('pesados', false, true), 0) ?></td>
			</tr>

			<tr>
				<td style='font-weight:bold;'>Colisión</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColisionCosto('pesados'), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$0</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalColisionCosto('pesados', false), 0) ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Uno a Uno</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionUnoCosto.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionUnoCosto('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionUnoCosto('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Taller Colisión Aseguradoras</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ColisionAseguradorasCosto.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getColisionAseguradorasCosto('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getColisionAseguradorasCosto('pesados', false), 0); ?></td>
			</tr>
			<!-- <tr>
				<td style='padding-left:20px;'>Costo de venta Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasColision.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasColision.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasColision('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasColision('pesados', false, false) + $informe->getVentasColision('pesados', false, true), 0); ?></td>
			</tr> -->
			<tr>
				<td style='padding-left:20px;'>Costo de venta Garantías</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasGarantias.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasGarantias.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasGarantias('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasGarantias('pesados', false, false) + $informe->getVentasGarantias('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Internos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasInternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasInternos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasInternos('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasInternos('pesados', false, false) + $informe->getVentasInternos('pesados', false, true), 0); ?></td>
			</tr>
			<!-- <tr>
				<td style='padding-left:20px;'>Costo de venta Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasAlternos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/VentasAlternos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getVentasAlternos('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getVentasAlternos('pesados', false, false) + $informe->getVentasAlternos('pesados', false, true), 0); ?></td>
			</tr> -->
			<tr>
				<td style='font-weight:bold;'>Alternos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternosCosto('pesados', false, false), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternosCosto('pesados', false, true), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalAlternosCosto('pesados', false, false) + $informe->getTotalAlternosCosto('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Taller</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTallerCosto.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTallerCosto('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosTallerCosto.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosTallerCosto('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosTallerCosto('pesados', false, false) + $informe->getAlternosTallerCosto('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Colisión</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosColisionCosto.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosColisionCosto('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$0</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosColisionCosto('pesados', false, false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Alternos Mostrador</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostradorCosto.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostradorCosto('pesados', false, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/AlternosMostradorCosto.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getAlternosMostradorCosto('pesados', false, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getAlternosMostradorCosto('pesados', false, false) + $informe->getAlternosMostradorCosto('pesados', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Repuestos Flotas Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostosRepuestosFlotasOtrasMarcas.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoRepuestosFlotasOtrasMarcas('pesados', false, false), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostosRepuestosFlotasOtrasMarcas.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoRepuestosFlotasOtrasMarcas('pesados', false, true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoRepuestosFlotasOtrasMarcas('pesados', false, false) + $informe->getCostoRepuestosFlotasOtrasMarcas('liviano', false, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;background-color:#C0C0C0 !important'>Repuestos Flotas Chevrolet</td>
				<td style="text-align:right;background-color:#C0C0C0 !important">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados', false, 'totalc') + $informe->getMecanicaRapidaFlotas('pesados', false, false, 'totalc') + $informe->getMecanicaEspecializadaFlotas('pesados', false, false, 'totalc'), 0); ?></td>
				<td class='coa' style="text-align:right;background-color:#C0C0C0 !important">$<?php echo Moneda::getMoneda(0 + $informe->getMecanicaRapidaFlotas('pesados', false, true, 'totalc') + $informe->getMecanicaEspecializadaFlotas('pesados', false, true, 'totalc'), 0); ?></td>
				<td class='coa' style="text-align:right;background-color:#C0C0C0 !important">$<?php echo Moneda::getMoneda($informe->getMostradorSoloFlotas('pesados', false, 'totalc') + $informe->getMecanicaRapidaFlotas('pesados', false, false, 'totalc') + $informe->getMecanicaEspecializadaFlotas('pesados', false, false, 'totalc') + 0 +  + $informe->getMecanicaRapidaFlotas('pesados', false, true, 'totalc') + $informe->getMecanicaEspecializadaFlotas('pesados', false, true, 'totalc'), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Accesorios Genuinos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosGenuinos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosGenuinos('pesados', true, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosGenuinos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosGenuinos('pesados', true, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosGenuinos('pesados', true, false)+$informe->getCostoAccesoriosGenuinos('pesados', true, true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Costo de venta Accesorios Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosAlternos.php?tipo=pesados');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosAlternos('pesados', true, false), 0); ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/CostoAccesoriosAlternos.php?tipo=pesados&chevrolet');" href='#'>$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosAlternos('pesados', true, true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getCostoAccesoriosAlternos('pesados', true, false) + $informe->getCostoAccesoriosAlternos('pesados', true, true), 0); ?></td>
			</tr>

			<tr>
			<tr style='font-size:18px;color:white;'>
				<td style="background-color:#555;text-align:left;font-weight:bold;">TOTAL COSTOS</td>
				<td style="background-color:#555;text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta('pesados', false, false)+$informe->getTotalCostosVenta('liviano', false, false), 0); ?></td>
				<td class='coa' style="background-color:#555;text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta('pesados', false, true)+$informe->getTotalCostosVenta('liviano', false, true), 0); ?></td>
				<td class='coa' style="background-color:#555;text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalCostosVenta('pesados', false, false) + $informe->getTotalCostosVenta('pesados', false, true)+$informe->getTotalCostosVenta('liviano', false, false) + $informe->getTotalCostosVenta('liviano', false, true), 0); ?></td>
			</tr>
			<tr style='font-size:18px;color:black;'>
				<td style="background-color:#dcb45b;text-align:left;font-weight:bold;">MARGEN DE VENTAS</td>
				<?php
					$venta_total_1 = $informe->getTotalVentasDetal('pesados', false, false) + $informe->getTotalVentasDetal('liviano', false, false);
					$venta_total_2 = $informe->getTotalVentasDetal('pesados', false, true) + $informe->getTotalVentasDetal('liviano', false, true);
					$venta_total_3 = $informe->getTotalVentasDetal('pesados', false, false) + $informe->getTotalVentasDetal('liviano', false, false) + $informe->getTotalVentasDetal('pesados', false, true) + $informe->getTotalVentasDetal('liviano', false, true);
					
					$costo_total_1 = ($informe->getTotalCostosVenta('pesados', false, false) + $informe->getTotalCostosVenta('liviano', false, false));
					$costo_total_2 = ($informe->getTotalCostosVenta('pesados', false, true) + $informe->getTotalCostosVenta('liviano', false, true));
					$costo_total_3 = ($informe->getTotalCostosVenta('pesados', false, false) + $informe->getTotalCostosVenta('pesados', false, true)+$informe->getTotalCostosVenta('liviano', false, false) + $informe->getTotalCostosVenta('liviano', false, true));


					$margen_1 = $venta_total_1 <= 0 ? 0 : (($venta_total_1-$costo_total_1) / $venta_total_1) * 100;
					$margen_2 = $venta_total_2 <= 0 ? 0 : (($venta_total_2-$costo_total_2) / $venta_total_2) * 100;
					$margen_3 = $venta_total_3 <= 0 ? 0 : (($venta_total_3-$costo_total_3) / $venta_total_3) * 100;

				?>
				<td style="background-color:#dcb45b;text-align:center;font-weight:bold;"><?php echo Moneda::getMoneda($margen_1, 2); ?>%</td>
				<td class='coa' style="background-color:#dcb45b;text-align:center;font-weight:bold;"><?php echo Moneda::getMoneda($margen_2, 2); ?>%</td>
				<td class='coa' style="background-color:#dcb45b;text-align:center;font-weight:bold;"><?php echo Moneda::getMoneda($margen_3, 2); ?>%</td>
			</tr>
			<tr>
				<td colspan=2>-</td>
				<td class='coa'></td>
				<td class='coa'></td>
			</tr>
			<tr style='font-size:16px;'>
				<td colspan=4 style="background-color:#1f497d;color:white;font-weight:bold;">COMPRAS LIVIANOS</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Total Compras Repuestos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'liviano', -1, -6), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'liviano', -1, 6), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'liviano', -1), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'liviano', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'liviano', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'liviano', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'liviano', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosCROT(array('CROT'), 'liviano', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosCROT(array('CROT'), 'liviano', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosCROT(array('CROT'), 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros proveedores Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM_OM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosOtrasMarcas(array('CROT'), 'liviano', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM_OM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosOtrasMarcas(array('CROT'), 'liviano', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosOtrasMarcas(array('CROT'), 'liviano', false), 0); ?></td>
			</tr>
		
		<!-- COMPRAS ACCESORIOS -->
			<tr>
				<td style='padding-left:20px;'>Compras Accesorios repuestos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'liviano', true, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'liviano', true, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras Accesorios repuestos a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'liviano', true, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'liviano', true, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras Accesorios repuestos a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), 'liviano', true, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), 'liviano', true, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), 'liviano', true), 0); ?></td>
			</tr>

			<tr style='font-size:16px;'>
				<td colspan=4 style="background-color:#1f497d;color:white;font-weight:bold;">COMPRAS PESADOS</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Total Compras Repuestos</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'pesados', -1, -6), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'pesados', -1, 6), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'pesados', -1), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'pesados', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'pesados', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'pesados', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'pesados', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosCROT(array('CROT'), 'pesados', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosCROT(array('CROT'), 'pesados', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosCROT(array('CROT'), 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras repuestos a otros proveedores Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM_OM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosOtrasMarcas(array('CROT'), 'pesados', false, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM_OM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosOtrasMarcas(array('CROT'), 'pesados', false, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestosOtrasMarcas(array('CROT'), 'pesados', false), 0); ?></td>
			</tr>
		
		<!-- COMPRAS ACCESORIOS -->
			<tr>
				<td style='padding-left:20px;'>Compras Accesorios repuestos GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'pesados', true, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'pesados', true, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), 'pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras Accesorios repuestos a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'pesados', true, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'pesados', true, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), 'pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Compras Accesorios repuestos a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), 'pesados', true, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), 'pesados', true, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), 'pesados', true), 0); ?></td>
			</tr>
			<!--
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'pesados', -1, -6), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'pesados', -1, 6), 0); ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), 'pesados', -1), 0); ?></td>
				-->
			<tr style='font-size:18px;color:white;'>
				<td style="background-color:#555;text-align:left;font-weight:bold;">TOTAL COMPRAS</td>
				<td style="background-color:#555;text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), '', -1, -6), 0) ?></td>
				<td class='coa' style="background-color:#555;text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), '', -1, 6), 0); ?></td>
				<td class='coa' style="background-color:#555;text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO', 'CRO', 'CROT'), '', -1), 0); ?></td>
			</tr>

			<!-- RESUMEN -->
			<tr style='font-weight:bold;'>
				<td style='padding-left:20px;'>Compras GM</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), '', -1, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRCO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), '', -1, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRCO'), '', -1), 0); ?></td>
			</tr>
			<tr style='font-weight:bold;'>
				<td style='padding-left:20px;'>Compras a otros concesionarios</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), '', -1, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CRO');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), '', -1, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CRO'), '', -1), 0); ?></td>
			</tr>
			<tr style='font-weight:bold;'>
				<td style='padding-left:20px;'>Compras a otros proveedores</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), '', -1, -6), 0) ?></a></td>
				<td class='coa' style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle_coa/ComprasRepuestosGM.php?tipo=CROT');" href='#'>$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), '', -1, 6), 0) ?></a></td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getTotalComprasRepuestos(array('CROT'), '', -1), 0); ?></td>
			</tr>

			<tr>
				<td colspan=2>-</td>
				<td class='coa'></td>
				<td class='coa'></td>
			</tr>



			<tr style='font-size:16px;'>
				<td colspan=4 style="background-color:#1f497d;color:white;font-weight:bold;">INVENTARIO REPUESTOS LIVIANOS</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Inventarios</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios('liviano', false), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">-</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Entregado a Servicio</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 0 a 12 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 12 a 24 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 24 o más meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('liviano', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('liviano', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('liviano', false), 0); ?></td>
			</tr>

			<!-- ACCESORIOS -->
			<tr>
				<td style='padding-left:20px;'>Entregado a Servicio (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 0 a 12 meses (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'liviano', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 12 a 24 meses (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'liviano', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 24 o más meses (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'liviano', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('liviano', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos (Accesorios) Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('liviano', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('liviano', true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>% FOF</td>
				<td style="text-align:right;font-weight:bold;"><?php echo Moneda::getMoneda($informe->getTotalFOF('liviano', true), 2) ?>%</td>
				<td class='coa' style="text-align:right;font-weight:bold;">-</td>
				<td class='coa' style="text-align:right;font-weight:bold;">-</td>
			</tr>

			<tr style='font-size:16px;'>
				<td colspan=4 style="background-color:#1f497d;color:white;font-weight:bold;">INVENTARIO REPUESTOS PESADOS</td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>Inventarios</td>
				<td style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios('pesados', false), 0) ?></td>
				<td class='coa' style="text-align:right;font-weight:bold;">-</td>
				<td class='coa' style="text-align:right;font-weight:bold;">$<?php echo Moneda::getMoneda($informe->getTotalInventarios('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Entregado a Servicio</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('pesados', false), 0) ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 0 a 12 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 12 a 24 meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 24 o más meses</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('pesados', false), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('pesados', false), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('pesados', false), 0); ?></td>
			</tr>


			<!-- ACCESORIOS -->
			<tr>
				<td style='padding-left:20px;'>Entregado a Servicio (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosEntregadoAServicio.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('pesados', true), 0) ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosEntregadoAServicio('pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 0 a 12 meses (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=0M-12M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'pesados', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('0M-12M', 'pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 12 a 24 meses (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=12M-24M');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'pesados', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('12M-24M', 'pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario 24 o más meses (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventarioEdad.php?edad=24M-MAS');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'pesados', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventarios('24M-MAS', 'pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos (Accesorios)</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('pesados', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternos('pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='padding-left:20px;'>Inventario Alternos (Accesorios) Otras Marcas</td>
				<td style="text-align:right;"><a onclick="verVentana('#ventana', 'detalle/InventariosAlternos.php');" href='#'>$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('pesados', true), 0); ?></a></td>
				<td class='coa' style="text-align:right;">-</td>
				<td class='coa' style="text-align:right;">$<?php echo Moneda::getMoneda($informe->getInventariosAlternosOtrasMarcas('pesados', true), 0); ?></td>
			</tr>
			<tr>
				<td style='font-weight:bold;'>% FOF</td>
				<td style="text-align:right;font-weight:bold;"><?php echo Moneda::getMoneda($informe->getTotalFOF('pesados'), 2) ?>%</td>
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