<?php
define("iC", true);
require_once(dirname(__FILE__) . "/../../../conf/config.php");
Aplicacion::validarAcceso(5, 10);
$informe = Informe::getInstance();
$informe->clearQuerys();
$periodo = new Periodo(Informe::getLimitePeriodo());
BD::changeInstancia("facts");

if (isset($_POST["reload"]))
    $informe->actualizarDatos();

if (isset($_POST["periodo"])) {
    $periodo = new Periodo($_POST["periodo"]);
    if (!$informe->change($periodo->getYear(), $periodo->getMonth())) die("Error al intentar consultar el periodo seleccionado");
}

?>
<style>
    .tr_titulo {
        background-color: #dcb45b !important;
        font-weight: bold;
        color: black;
        font-size: 16px;
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
	         <li><a tabindex="-1" href="#" onclick='descargarPerfilTaller()'><i class='icon icon-download'></i> &nbsp; Perfil Taller</a></li>
	         <li><a tabindex="-1" href="#" onclick='descargarFacturacionTallerManoObra()'><i class='icon icon-download'></i> &nbsp; Facturación Taller Mano de Obra</a></li>
		</ul>
    </div>
    Última actualización el <b><?php echo $informe->getFechaActualizacion(); ?></b>
    <table class='table table-bordered table-condensed table-hover' style='margin-top:10px;width:700px;'>
        <thead>
            <tr class='ui-widget-header' style='font-size: 18px'>
                <th colspan="2" style='text-align:center;'>COS - <?php echo $informe->getPeriodo()->format('F/Y') ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class='tr_titulo'>SEDE</td>
                <td class='tr_titulo' style='text-align:center;'>MES DE FACTS</td>
            </tr>
            <tr>
                <td colspan="2" class='tr_titulo'>ORDENES DE TRABAJO VEHÍCULOS LIVIANOS</td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Mecánica Rápida</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_rapida.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadMecanicaRapida('liviano') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Mecánica Especializada</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_especializada.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadMecanicaEspecializada('liviano') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Internos</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_internos.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadInternos('liviano') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Alistamiento</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_alistamiento.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadAlistamiento('liviano') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Garantías</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_garantias.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadGarantias('liviano') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Terceros Mecánica (TOT)</td>
                <td style="text-align: right;">0</td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Entradas Taller de Mecánica vehículos LIVIANOS</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadMecanica('liviano') ?></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Uno a Uno</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_uno.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadMecanicaUno('liviano') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Aseguradoras</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_aseguradoras.php?tipo=liviano');" href="#"><?php echo $informe->getCantidadMecanicaAseguradoras('liviano') ?></a></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Entradas Taller de Colisión vehículos LIVIANOS</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadColision('liviano') ?></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Total Vehículos LIVIANOS Atendidos</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadAtendidos('liviano') ?></td>
            </tr>
            <tr>
                <td colspan="2" class='tr_titulo'>ORDENES DE TRABAJO VEHÍCULOS PESADOS</td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Mecánica Rápida</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_rapida.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadMecanicaRapida('pesados') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Mecánica Especializada</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_especializada.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadMecanicaEspecializada('pesados') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Internos</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecaniza_internos.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadInternos('pesados') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Alistamiento</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_alistamiento.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadAlistamiento('pesados') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Garantías</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_garantias.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadGarantias('pesados') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Terceros Mecánica (TOT)</td>
                <td style="text-align: right;">0</td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Entradas Taller de Mecánica vehículos PESADOS</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadMecanica('pesados') ?></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Uno a Uno</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_uno.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadMecanicaUno('pesados') ?></a></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Aseguradoras</td>
                <td style="text-align: right;"><a onclick="verVentana('#ventana', 'detalle/mecanica_aseguradoras.php?tipo=pesados');" href="#"><?php echo $informe->getCantidadMecanicaAseguradoras('pesados') ?></a></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Entradas Taller de Colisión vehículos PESADOS</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadColision('pesados') ?></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Total Vehículos PESADOS Atendidos</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadAtendidos('pesados') ?></td>
            </tr>
            <tr>
                <td colspan="2" class='tr_titulo'>RESUMEN DE ÓRDENES DE TRABAJO TOTALES</td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Mecánica Rápida</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadMecanicaRapida('liviano') + $informe->getCantidadMecanicaRapida('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Mecánica Especializada</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadMecanicaEspecializada('liviano') + $informe->getCantidadMecanicaEspecializada('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Internos</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadInternos('liviano') + $informe->getCantidadInternos('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Alistamiento</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadAlistamiento('liviano') + $informe->getCantidadAlistamiento('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Garantías</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadGarantias('liviano') + $informe->getCantidadGarantias('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Terceros Mecánica (TOT)</td>
                <td style="background-color: #555; color: white; text-align: right;">0</td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Entradas Taller de Mecánica</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadMecanica('liviano') + $informe->getCantidadMecanica('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Uno a Uno</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadMecanicaUno('liviano') + $informe->getCantidadMecanicaUno('pesados') ?></td>
            </tr>
            <tr>
                <td style='background-color: #555; color: white; padding-left: 20px;'>Aseguradoras</td>
                <td style="background-color: #555; color: white; text-align: right;"><?php echo $informe->getCantidadMecanicaAseguradoras('liviano') + $informe->getCantidadMecanicaAseguradoras('pesados') ?></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Entradas Taller de Colisión</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadColision('liviano') + $informe->getCantidadColision('pesados') ?></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Total Vehículos Atendidos</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right'><?php echo $informe->getCantidadAtendidos('liviano') + $informe->getCantidadAtendidos('pesados') ?></td>
            </tr>
        </tbody>
    </table>
    <br>
    <table>
        <tbody>
            <tr>
                <td colspan="2" class='tr_titulo'>FACTURACION MANO DE OBRA LIVIANOS</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Mecánica Rápida</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaRapida('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaRapida('liviano') / 1000), 2) ?></a>
                </td>
                <td colspan="2">
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Mecánica Especializada</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaEspecializada('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaEspecializada('liviano') / 1000), 2) ?></a>
                </td>
                <td colspan="2">
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Colisión Uno a Uno</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaUno('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaUno('liviano') / 1000), 2) ?></a>
                </td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Colisión Aseguradoras</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaAseguradoras('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaAseguradoras('liviano') / 1000), 2) ?></a>
                </td>
                <td colspan="2">
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Internos</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSInternos('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSInternos('liviano') / 1000), 2) ?></a>
                </td>
                <td colspan="2">
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Alistamiento</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSAlistamiento('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSAlistamiento('liviano') / 1000), 2) ?></a>
                </td>
                <td colspan="2">
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Garantías</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSGarantias('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSGarantias('liviano') / 1000), 2) ?></a>
                </td>
                <td colspan="2">
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Terceros Mecánica (TOT)</td>
                <td style="text-align: right;" title='$0'>$0</td>
                <td colspan="2">
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Facturación Total de MO por servicios prestados a vehículos LIVIANOS</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right' title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSAtendidos('liviano', 'valor'), 2) ?>">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSAtendidos('liviano', 'valor') / 1000), 2) ?></td>
                <td colspan="2">
            </tr>
            <tr>
                <td colspan="2" class='tr_titulo'>FACTURACION MANO DE OBRA PESADOS</td>
                <td class='tr_titulo'>% VENTA</td>
                <td class='tr_titulo'> COSTO DE VENTA PESADOS</td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Mecánica Rápida</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaRapida('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaRapida('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaRapida('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Mecánica Especializada</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaEspecializada('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaEspecializada('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaEspecializada('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Colisión Uno a Uno</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaUno('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaUno('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaUno('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Colisión Aseguradoras</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaAseguradoras('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSMecanicaAseguradoras('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaAseguradoras('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Internos</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSInternos('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSInternos('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSInternos('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Alistamiento</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSAlistamiento('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSAlistamiento('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSAlistamiento('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Garantías</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSGarantias('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSGarantias('pesados') / 1000), 2) ?></a></td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSGarantias('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Terceros Mecánica (TOT)</td>
                <td style="text-align: right;" title='$0'>$0</td>
                <td style="text-align: right">0%</td>
                <td style="text-align: right"></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Facturación Total de MO por servicios prestados a vehículos PESADOS</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right' title="<?php echo "$ " . Moneda::getMoneda($informe->getTotalCOSAtendidos('pesados'), 2) ?>">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSAtendidos('pesados') / 1000), 2) ?></td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSAtendidos('pesados') * 100) / $informe->getTotalCOSAtendidos('pesados'), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td class='tr_titulo'>RESUMEN DE FACTURACIÓN MANO DE OBRA TOTAL</td>
                <td class='tr_titulo'>VENTA</td>
                <td class='tr_titulo'>% VENTA</td>
                <td></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Mecánica Rápida</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaRapida('pesados') + $informe->getTotalCOSMecanicaRapida('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round(($informe->getTotalCOSMecanicaRapida('pesados') + $informe->getTotalCOSMecanicaRapida('liviano')) / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaRapida('pesados') + $informe->getTotalCOSMecanicaRapida('liviano')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . '%' ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Mecánica Especializada</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaEspecializada('pesados') + $informe->getTotalCOSMecanicaEspecializada('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round(($informe->getTotalCOSMecanicaEspecializada('pesados') + $informe->getTotalCOSMecanicaEspecializada('liviano')) / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaEspecializada('pesados') + $informe->getTotalCOSMecanicaEspecializada('liviano')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . '%' ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Colisión Uno a Uno</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaUno('pesados') + $informe->getTotalCOSMecanicaUno('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round(($informe->getTotalCOSMecanicaUno('pesados') + $informe->getTotalCOSMecanicaUno('pesados')) / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaUno('pesados') + $informe->getTotalCOSMecanicaUno('pesados')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . '%' ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Colisión Aseguradoras</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSMecanicaAseguradoras('pesados'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round(($informe->getTotalCOSMecanicaAseguradoras('pesados') + $informe->getTotalCOSMecanicaAseguradoras('liviano')) / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSMecanicaAseguradoras('pesados') + $informe->getTotalCOSMecanicaAseguradoras('liviano')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . '%' ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Internos</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSInternos('pesados')+$informe->getTotalCOSInternos('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round(($informe->getTotalCOSInternos('pesados')+$informe->getTotalCOSInternos('liviano')) / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSInternos('pesados')+$informe->getTotalCOSInternos('liviano')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Alistamiento</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSAlistamiento('pesados')+$informe->getTotalCOSAlistamiento('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round($informe->getTotalCOSAlistamiento('pesados') / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSAlistamiento('pesados')+$informe->getTotalCOSAlistamiento('liviano')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Garantías</td>
                <td style="text-align: right;" title="<?php echo "$" . Moneda::getMoneda($informe->getTotalCOSGarantias('pesados')+$informe->getTotalCOSGarantias('liviano'), 2) ?>">
                    <a onclick="" href="#">$<?php echo Moneda::getMoneda(round(($informe->getTotalCOSGarantias('pesados')+$informe->getTotalCOSGarantias('liviano')) / 1000), 2) ?></a>
                </td>
                <td style="text-align: right"><?php echo round(($informe->getTotalCOSGarantias('pesados')+$informe->getTotalCOSGarantias('liviano')) * 100 / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . "%" ?></td>
                <td style="text-align: right"></td>
            </tr>
            <tr>
                <td style='padding-left: 20px;'>Facturación MO Terceros Mecánica (TOT)</td>
                <td style="text-align: right;" title='$0'>$0</td>
                <td style="text-align: right">0%</td>
                <td style="text-align: right"></td>
            </tr>
            <tr style='font-size: 16px;'>
                <td style='background-color: #555; color: white; font-weight: bold;'>Facturación Total MO</td>
                <td style='background-color: #555; color: white; font-weight: bold; text-align: right' title="<?php echo "$ " . Moneda::getMoneda($informe->getTotalCOSAtendidos('pesados')+$informe->getTotalCOSAtendidos('liviano'), 2) ?>">
                    $<?php echo Moneda::getMoneda(round(($informe->getTotalCOSAtendidos('pesados')+$informe->getTotalCOSAtendidos('liviano')) / 1000), 2) ?></td>
                <td style="text-align: right"><?php echo round((($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')) * 100) / ($informe->getTotalCOSAtendidos('pesados') + $informe->getTotalCOSAtendidos('liviano')), 2) . "%" ?></td>
                <td style="text-align: right"></td>
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
        $("#app_contenido").load("<?php echo DIR_WEB; ?>index.php", {
            periodo: periodo
        }, function() {
            hideMessage();
        });
    }

    function reload() {
        Swal.fire({
            title: 'Recalcular informes',
            html: 'Este proceso toma cerca de 8 minutos en procesar la información. <b>¿Desea continuar?</b>',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar'
        }).then(function(res) {
            if (res.value) {
                showMessage();
                $("#app_contenido").load("<?php echo DIR_WEB; ?>index.php", {
                    reload: true
                }, function() {
                    hideMessage();
                });
            }
        });
    }

    function descargarPerfilTaller() {
		showMessage();
		var periodo = $("#seleccion_periodo").val();
		document.location.href='<?php echo DIR_WEB; ?>../cor/perfil_taller.php?periodo=' + periodo;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 5000);
	}

    function descargarFacturacionTallerManoObra() {
		showMessage();
		var periodo = $("#seleccion_periodo").val();
		document.location.href='<?php echo DIR_WEB; ?>facturaciontallermanoobra.php?periodo=' + periodo;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 5000);
	}

    function verVentana(div, script) {
		showMessage();
		$(div).load("<?php echo DIR_WEB; ?>" + script);
	}
</script>