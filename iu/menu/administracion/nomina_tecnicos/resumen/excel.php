<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php"); 
	Aplicacion::validarAcceso(10);
	BD::changeInstancia("facts");

	$fileType = 'Excel2007';
	$objPHPExcel = new PHPExcel();
	$fileName = dirname(__FILE__).'/excel.xlsx';
	$objReader = PHPExcel_IOFactory::createReader($fileType);
	$objPHPExcel = $objReader->load($fileName);
	$ws = $objPHPExcel->setActiveSheetIndex(0);
	
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
	if ($fecha1 != "" && $fecha2 != "") $add_query .= " AND fecha BETWEEN '$fecha1' AND '$fecha2' ";
	
	
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
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
	function getValor($t, $val) { return $t == 1 ? $val : ($val * 100) . "%"; }

	$rdev = BD::sql_query("select v.tipo, v.numero, v.tipo_dev, v.numero_dev, v.numero_orden, month(v.fec) mes FROM vbsc_nomtipopago v WHERE sw=2 AND fec BETWEEN '$fecha1' AND '$fecha2'");
	$devoluciones = array();
	while ($fdev = BD::obtenerRegistro($rdev)) 
		$devoluciones[$fdev["tipo_dev"] . "-" . $fdev["numero_dev"]] = $fdev["tipo"] . "-" . $fdev["numero"];

	
	//filtros
	$ws->setCellValueByColumnAndRow(1, 2, utf8_encode($fecha1 . " hasta " . $fecha2));
	$ws->setCellValueByColumnAndRow(0, 3, utf8_encode("Reporte generado el día " . Fecha::getFecha(date("Y-m-d H:i:s"), "d/F/Y g:ia")));

	$fila = 5;
	while ($row = BD::obtenerRegistro($result)) {
		$col = 0;
		$forma_pago = " ";
		$forma_valor = " ";
		if ($row["fpago_tipo"] == 1) {
			$forma_pago = "VALOR HORA";
			$forma_valor = $row["fpago_valor"];
		}
		if ($row["fpago_tipo"] == 2) {
			$forma_pago = "PORCENTAJE";
			$forma_valor = ($row["fpago_valor"] * 100) . "%";
		}

		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["operario"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["nombres"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($forma_pago));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($forma_valor));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["total_facturado"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["tiempo"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["devengado"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["devoluciones"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["total_pago"]));

		$fila++;
	}

	$ws->getStyle("A5:I" . ($fila - 1))->applyFromArray(
		array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			)
		)
	);

	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="resumen.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
    unset($objPHPExcel);
	unset($writer);
?>