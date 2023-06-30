<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../conf/config.php");
	Aplicacion::validarAcceso(10);
	
	$periodo = new Periodo(isset($_GET["periodo"]) ? $_GET["periodo"] : "");
	
	BD::changeInstancia("mysql");
	
	$fileType = 'Excel2007';
	$objPHPExcel = new PHPExcel();
	$fileName = dirname(__FILE__).'/perfil_taller.xlsx';
	$objReader = PHPExcel_IOFactory::createReader($fileType);
	$objPHPExcel = $objReader->load($fileName);
	$ws = $objPHPExcel->setActiveSheetIndex(0);

	list($anio, $mes) = array($periodo->getYear(), $periodo->getMonth());
	$r = BD::sql_query("select numero_orden, bodega, sucursal, vin, descripcion_modelo, modelo_ano, placa, combustible, kilometraje, tipo_vehiculo, colision_leve,
			colision_media, colision_fuerte, mecanica_especializada, mecanica_rapida, accesorios, garantia_gm, alistamiento_y_peritajes, retornos, internos, 2_razon_de_visita, fecha_hora_entrada,
			fecha_hora_salida, fecha_hora_factura_1, per_com_entrega, per_com_factura, fac_ocu_entrega, fac_ocu_factura, cheque, credito_concesionario, tarjeta_credito, tarjeta_debito, contado,
			nombres, apellidos, cedula_nit, telefono, mail, direccion, ciudad, rango_de_edad, fecha_cumpleanos, genero, repuestos_clientes, repuestos_aseguradoras, repuestos_garantias, repuestos_internos,
			mo_clientes, mo_aseguradoras, mo_garantias, mo_internos, aseguradora, linea_pedida, linea_atendida_100, fof, nombre_asesor, km_x_ano	
		FROM rp_perfil_taller INNER JOIN informe i ON informe_id=i.id
		WHERE i.anio=$anio and i.mes=$mes") or die(BD::getLastError());
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
	header('Content-Disposition: attachment;filename="perfil_taller.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
    unset($objPHPExcel);
	unset($writer);
?>