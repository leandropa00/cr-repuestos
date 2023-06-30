<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	
	$periodo = new Periodo(isset($_GET["periodo"]) ? $_GET["periodo"] : "");
	
	BD::changeInstancia("mysql");
	
	$fileType = 'Excel2007';
	$objPHPExcel = new PHPExcel();
	$fileName = dirname(__FILE__).'/ubicacion_repuestos.xlsx';
	$objReader = PHPExcel_IOFactory::createReader($fileType);
	$objPHPExcel = $objReader->load($fileName);
	$ws = $objPHPExcel->setActiveSheetIndex(0);

	list($anio, $mes) = array($periodo->getYear(), $periodo->getMonth());
	$r = BD::sql_query("SELECT * FROM informe WHERE anio=$anio AND mes=$mes");
	$f = BD::obtenerRegistro($r) or die("Error al cargar el informe");
	$informe = new Informe();
	$informe->load($f["id"]);

	$r = BD::sql_query("select codigo, nombre_grupo, tipo_proveedor, bodega, descripcion, stock, costo_promedio, stock*costo_promedio total, precio, ubicacion, calificacion_abc, stock_min, stock_max, fecha_creacion, fec_ultima_entrada, fec_ultima_salida, edad
		FROM rp_ubicacion_repuestos WHERE informe_id=" . $informe->id) or die(BD::getLastError());
	$fila = 2;
	while ($f = BD::obtenerRegistro($r)) {
		$col = 0;
		foreach($f as $nom_columna => $data)
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($data));
		$fila++;
	}

	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="inventario.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
    unset($objPHPExcel);
	unset($writer);
?>