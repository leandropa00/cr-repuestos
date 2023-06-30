<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	
	$periodo = new Periodo(isset($_GET["periodo"]) ? $_GET["periodo"] : "");
	
	BD::changeInstancia("mysql");
	
	$fileType = 'Excel2007';
	$objPHPExcel = new PHPExcel();
	$fileName = dirname(__FILE__).'/ventasxasesor.xlsx';
	$objReader = PHPExcel_IOFactory::createReader($fileType);
	$objPHPExcel = $objReader->load($fileName);
	$ws = $objPHPExcel->setActiveSheetIndex(0);

	function getClasificacion($id) {
		$clasificacion = array(
			1 => "CLIENTES",
			2 => "PROVEEDORES",
			3 => "CLIENTE / PROVEEDOR",
			4 => "EMPLEADO",
			5 => "ACCIONISTA",
			6 => "BANCARIA",
			7 => "OPERARIOS TALLER",
			8 => "ASEGURADORAS",
			9 => "CONCESIONARIO",
			10 => "CLIENTES PLAN FLOTAS",
			11 => "CLIENTES ACDELCO"
		);
		return isset($clasificacion[$id]) ? $clasificacion[$id] : "-";
	}
	
	list($anio, $mes) = array($periodo->getYear(), $periodo->getMonth());
	$r = BD::sql_query("SELECT v.ubicacion_item,v.ubicacion_item_costo, v.sw, v.tipo, v.numero, v.tipo_proveedor, v.nombre_grupo, v.vehiculo_tipo, c.tipo_cliente, 
			concat_ws('-', clasificacion, elt(c.clasificacion, 'CLIENTES', 'PROVEEDORES', 'CLIENTE / PROVEEDOR', 'EMPLEADO', 'ACCIONISTA', 'BANCARIA', 'OPERARIOS TALLER', 'ASEGURADORAS', 'CONCESIONARIO', 'CLIENTES PLAN FLOTAS', 'CLIENTES ADELCO')) clasificacion,
			elt(tipo_mecanica, 'especializada', 'rapida') tipo_mecanica,
			v.referencia, v.linea, v.descripcion, v.fecha, v.cantidad, v.valor_unitario, 
			v.vendedor, v.nombres, v.descuentos, v.total, v.totalc, v.numero_ot, 
			v.nit_cliente, c.nombre, 
			v.notas, v.devolucion_data, v.devolucion_fecha, v.tipo_link, v.numero_link
		FROM rp_ventasxasesor v INNER JOIN cliente_tipo c ON v.nit_cliente=c.identificacion
		WHERE year(fecha)=$anio and month(fecha)=$mes order by fecha") or die(BD::getLastError());

	$fila = 2;
	while ($f = BD::obtenerRegistro($r)) {
		$col = 0;
		foreach($f as $nom_columna => $data) {
			if ($col == 0) $data = isset(Informe::$item[$data]) ? Informe::$item[$data] : "";
			if ($col == 1) $data = isset(Informe::$item_costo[$data]) ? Informe::$item_costo[$data] : "";
			$ws->setCellValueByColumnAndRow($col++, $fila, utf8_encode($data));
		}
		$fila++;
	}

	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="ventasxasesor.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
    unset($objPHPExcel);
	unset($writer);
?>