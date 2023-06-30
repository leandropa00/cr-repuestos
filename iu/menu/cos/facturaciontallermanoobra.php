<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	
	$periodo = new Periodo(isset($_GET["periodo"]) ? $_GET["periodo"] : "");
	
	BD::changeInstancia("mysql");
	
	$fileType = 'Excel2007';
	$objPHPExcel = new PHPExcel();
	$fileName = dirname(__FILE__).'/facturacion_talleres_mano_obra.xlsx';
	$objReader = PHPExcel_IOFactory::createReader($fileType);
	$objPHPExcel = $objReader->load($fileName);
	$ws = $objPHPExcel->setActiveSheetIndex(0);

	list($anio, $mes) = array($periodo->getYear(), $periodo->getMonth());
    $r = BD::sql_query("select g.tipo, g.numero_fra, g.numero_orden, g.fecha, g.operario,g.nombre_tecnico,g.serie,g.operacion, g.descripcion, g.tiempo, g.clase_operacion, g.porcen_dscto, g.valor
        FROM informe i INNER JOIN rp_garantias g ON i.id=g.informe_id 
		WHERE i.anio=$anio and i.mes=$mes 
		ORDER BY g.tipo,g.numero_fra") or die(BD::getLastError());
	$titulos = false;
	$fila = 2;
	while ($f = BD::obtenerRegistro($r)) {
		$col = 0;
		foreach($f as $nom_columna => $data)
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($data));
		$fila++;
	}

	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="facturacion_talleres_mano_obra.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
    unset($objPHPExcel);
	unset($writer);
?>