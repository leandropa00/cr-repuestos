<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php"); 
	Aplicacion::validarAcceso(10);
	BD::changeInstancia("facts");
	
	$fecha1 = isset($_GET["fecha1"]) ? $_GET["fecha1"] : "";
	$fecha2 = isset($_GET["fecha2"]) ? $_GET["fecha2"] : "";
	if ($fecha1 != "") {
		if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha1)) die ("Error en formato de fecha1");
		$fecha1 = str_replace("-", "", $fecha1);
	}
	if ($fecha2 != "") {
		if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha2)) die ("Error en formato de fecha2");
		$fecha2 = str_replace("-", "", $fecha2);
	}
	$add_query = "";
	if ($fecha1 != "" && $fecha2 != "") $add_query .= " AND v.fecha BETWEEN '$fecha1' AND '$fecha2' ";
	
	$query = "select v.operario, ter.nombres, v.fpago_tipo, v.fpago_valor, 
					sum(case when v.tipo LIKE 'D%' then 0 else v.total end) total_facturado, 
					sum(case when v.tipo LIKE 'D%' then 0 else v.tiempo end) tiempo, 
					sum(case when v.tipo LIKE 'D%' then 0 else v.liquidado end) devengado,
					sum(case when v.tipo LIKE 'D%' then v.liquidado end) devoluciones,
					sum(v.liquidado) total_pago
				FROM bsc_nompagolog v
				LEFT JOIN terceros ter on ter.nit=v.operario
				WHERE 1=1 $add_query
				GROUP BY v.operario, ter.nombres, v.fpago_tipo, v.fpago_valor
				ORDER BY v.fpago_tipo,ter.nombres";
	$result = BD::sql_query($query) or die("Error en query $query");

	function getTipo($t) { return $t == 1 ? "VALOR HORA" : "PORCENTAJE"; }
	function getValor($t, $val) { return $t == 1 ? "$" . Moneda::getMoneda($val, 0) : ($val * 100) . "%"; }
?>
<table class='table table-bordered table-hover table-striped'>
	<thead>
		<tr class='ui-widget-header'>
			<th>ID Técnico</th>
			<th>Nombre Técnico</th>
			<th>Forma Pago</th>
			<th style='text-align:center;'>Valor</th>
			<th style='text-align:right;'>Total Fact</th>
			<th style='text-align:center;'>Tiempo</th>
			<th style='text-align:right;'>Devengado</th>
			<th style='text-align:right;'>Devoluciones</th>
			<th style='text-align:right;'>Total Pago</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = BD::obtenerRegistro($result)) { ?>
		<tr>
			<td><?php echo $row["operario"]; ?></td>
			<td><?php echo $row["nombres"]; ?></td>
			<td><?php echo getTipo($row["fpago_tipo"]); ?></td>
			<td style='text-align:center;'><?php echo ($row["fpago_valor"] != "") ? getValor($row["fpago_tipo"], $row["fpago_valor"]) : "-"; ?></td>
			<td style='text-align:right;'><?php echo "$" . Moneda::getMoneda($row["total_facturado"], 0); ?></td>
			<td style='text-align:center;'><?php echo round($row["tiempo"], 3); ?></td>
			<td style='text-align:right;'><?php echo "$" . Moneda::getMoneda($row["devengado"], 0); ?></td>
			<td style='text-align:right;'><?php echo "$" . Moneda::getMoneda($row["devoluciones"], 0); ?></td>
			<td style='text-align:right;'><?php echo "$" . Moneda::getMoneda($row["total_pago"], 0); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>