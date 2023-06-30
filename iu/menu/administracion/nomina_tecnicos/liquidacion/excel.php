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
	
	$busca_tipo = isset($_GET["tipo"]) ? $_GET["tipo"] : "";
	$busca_otros_filtros = isset($_GET["otros_filtros"]) ? intval($_GET["otros_filtros"]) : "";
	$busca_numero = isset($_GET["numero"]) ? $_GET["numero"] : "";
	$busca_numero_orden = isset($_GET["numero_orden"]) ? $_GET["numero_orden"] : "";
	$busca_operario = isset($_GET["operario"]) ? $_GET["operario"] : "";
	$busca_nombres = isset($_GET["nombres"]) ? $_GET["nombres"] : "";

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
	if ($fecha1 != "" && $fecha2 != "") $add_query .= " AND fec BETWEEN '$fecha1' AND '$fecha2' ";
	if ($busca_tipo != "") $add_query .= " AND tipo ='" . Seguridad::escapeSQL($busca_tipo, 'mssql_odbc') . "'";
	if ($busca_otros_filtros == 1) $add_query .= " AND pagado is null";
	if ($busca_otros_filtros == 2) $add_query .= " AND pagado  > 0";
	if ($busca_numero != "") $add_query .= " AND numero ='" . Seguridad::escapeSQL($busca_numero, 'mssql_odbc') . "'";
	if ($busca_numero_orden != "") $add_query .= " AND numero_orden ='" . Seguridad::escapeSQL($busca_numero_orden, 'mssql_odbc') . "'";
	if ($busca_operario != "") $add_query .= " AND operario ='" . Seguridad::escapeSQL($busca_operario, 'mssql_odbc') . "'";
	if ($busca_nombres != "") $add_query .= " AND 
		(nombres  LIKE '%" . Seguridad::escapeSQL($busca_nombres, 'mssql_odbc') . "%'
		 OR convert(varchar(18), operario)  LIKE '%" . Seguridad::escapeSQL($busca_nombres, 'mssql_odbc') . "%')
		";
	
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
	$campos = array("tipo", "numero", "numero_orden", "operario", "nombres", "serie", "operacion", "descripcion", "fec", "tiempo", "valor", "descuento", "total", "liquidado");
	$query = "select v.*, porcen_dscto descuento, 
					valor - (valor * porcen_dscto / 100) total,
						round((select top 1  (case 
							when tp.tipo=1 then tp.valor * v.tiempo
							when tp.tipo=2 then tp.valor * (v.valor - (v.valor * v.porcen_dscto / 100))
							else 0
						end) from bsc_nomtipopago tp where tp.identificacion=v.operario and tp.fecha <= v.fec order by tp.fecha desc, tp.id desc), 0) liquidado,
						(select docs.fecha from documentos docs where docs.tipo=v.tipo_dev and convert(varchar(10), docs.numero)=v.numero_dev) fecha_dev
			from vbsc_nomtipopago v where porcen_apl>=50 $add_query
			ORDER BY nombres";
	$result = BD::sql_query($query) or die("Error en query");

	function getTipo($t) { return $t == 1 ? "VALOR HORA" : "PORCENTAJE"; }
	function getValor($t, $val) { return $t == 1 ? $val : ($val * 100) . "%"; }

	$rdev = BD::sql_query("select v.tipo, v.numero, v.tipo_dev, v.numero_dev, v.numero_orden, month(v.fec) mes FROM vbsc_nomtipopago v WHERE sw=2 AND fec BETWEEN '$fecha1' AND '$fecha2'");
	$devoluciones = array();
	while ($fdev = BD::obtenerRegistro($rdev)) 
		$devoluciones[$fdev["tipo_dev"] . "-" . $fdev["numero_dev"]] = $fdev["tipo"] . "-" . $fdev["numero"];

	
	//filtros
	$sbusca_otros_filtros = "N/A";
	if ($busca_otros_filtros == 1)
		$sbusca_otros_filtros = "Sin pago";
	if ($busca_otros_filtros == 2)
		$sbusca_otros_filtros = "Pagados";
	$ws->setCellValueByColumnAndRow(2, 2, utf8_encode($fecha1 . " hasta " . $fecha2));
	$ws->setCellValueByColumnAndRow(2, 3, utf8_encode($busca_tipo));
	$ws->setCellValueByColumnAndRow(2, 4, utf8_encode($busca_numero));
	$ws->setCellValueByColumnAndRow(2, 5, utf8_encode($busca_numero_orden));
	$ws->setCellValueByColumnAndRow(2, 6, utf8_encode($busca_nombres));
	$ws->setCellValueByColumnAndRow(2, 7, utf8_encode($sbusca_otros_filtros));
	$ws->setCellValueByColumnAndRow(0, 8, utf8_encode("Reporte generado el día " . Fecha::getFecha(date("Y-m-d H:i:s"), "d/F/Y g:ia")));

	$fila = 10;
	while ($row = BD::obtenerRegistro($result)) {
		$fecha = new DateTime(Fecha::getFecha($row["fec"], "Y-m-d"));;
		$descuento = $row["descuento"] . "%";
		$factura = $row["tipo"] . "-" . $row["numero"];
		
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

		$operador = 1;
		$sdev = "";
		$sfechadev = "";
		if ($row["sw"] == 2) {
			$operador = -1;
			$sdev = $row["tipo_dev"] . "-" . $row["numero_dev"];
			$sfechadev = Fecha::getFecha($row["fecha_dev"], "Y-m-d");
		}


		$col = 0;
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["tipo"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["numero"]));
		if (isset($devoluciones[$factura])) {
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($devoluciones[$factura]));
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode(""));
		}
		else {
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($sdev));
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($sfechadev));
		}

		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["numero_orden"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, PHPExcel_Shared_Date::PHPToExcel($fecha));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["operario"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["nombres"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($forma_pago));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($forma_valor));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["descripcion"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["serie"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["operacion"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["valor"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($descuento));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["total"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["tiempo"]));
		$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($row["liquidado"] * $operador));

		$fila++;
	}

	$ws->getStyle("A10:R" . ($fila - 1))->applyFromArray(
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
	header('Content-Disposition: attachment;filename="liquidacion.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
    unset($objPHPExcel);
	unset($writer);
?>