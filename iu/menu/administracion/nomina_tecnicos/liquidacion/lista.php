<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php"); 
	Aplicacion::validarAcceso(10);
	BD::changeInstancia("facts");
	
	$page = isset($_POST['page']) ? intval($_POST['page']) : die(""); 
	$limit = isset($_POST['rows']) ? intval($_POST["rows"]) : die("");
	$sidx = isset($_POST['sidx']) ? $_POST["sidx"] : 1; //VALIDAR
	$sord = isset($_POST['sord']) ? $_POST["sord"] : die("");
	
	$busca_tipo = isset($_GET["tipo"]) ? $_GET["tipo"] : "";
	$busca_otros_filtros = isset($_GET["otros_filtros"]) ? intval($_GET["otros_filtros"]) : "";
	$busca_numero = isset($_GET["numero"]) ? $_GET["numero"] : "";
	$busca_numero_orden = isset($_GET["numero_orden"]) ? $_GET["numero_orden"] : "";
	$busca_operario = isset($_GET["operario"]) ? $_GET["operario"] : "";
	$busca_nombres = isset($_GET["nombres"]) ? $_GET["nombres"] : "";
	/*$busca_serie = isset($_GET["serie"]) ? $_GET["serie"] : "";
	$busca_operacion = isset($_GET["operacion"]) ? $_GET["operacion"] : "";*/

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
	/*if ($busca_serie != "") $add_query .= " AND serie ='" . Seguridad::escapeSQL($busca_serie, 'mssql_odbc') . "'";
	if ($busca_operacion != "") $add_query .= " AND operacion ='" . Seguridad::escapeSQL($busca_operacion, 'mssql_odbc') . "'";*/
		
	
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
	$campos = array("tipo", "numero", "numero_orden", "operario", "nombres", "serie", "operacion", "descripcion", "fec", "tiempo", "valor", "descuento", "total", "liquidado");
	$orden = array("asc", "desc");
	if (!in_array($sidx, $campos))
		die("ErrorCampos"); //Modificación de la petición: Posible intento de inyección de código SQL
	if (!in_array(strtolower($sord), $orden))
		die("ErrorOrden"); //Modificación de la petición: Posible intento de inyección de código SQL
	//-----------Fin validaciones---------------------------
	if (!$sidx)
		$sidx = 1;
	$count = 0;
	$result = BD::sql_query("select COUNT(*) AS count from vbsc_nomtipopago v where porcen_apl>=50 $add_query");
	if ($row = BD::obtenerRegistro($result))
		$count = $row['count'];
	$total_pages =  ($count > 0) ? ceil($count/$limit) : 0;
	if ($page > $total_pages) 
		$page = $total_pages; 
	$start = $limit * $page - $limit;
	if ($start < 0)
		$start = 0;
	//define("MODO_DEBUG", true);
	$query = "select v.*, porcen_dscto descuento, 
					valor - (valor * porcen_dscto / 100) total,
						round((select top 1  (case 
							when tp.tipo=1 then tp.valor * v.tiempo
							when tp.tipo=2 then tp.valor * (v.valor - (v.valor * v.porcen_dscto / 100))
							else 0
						end) from bsc_nomtipopago tp where tp.identificacion=v.operario and tp.fecha <= v.fec order by tp.fecha desc, tp.id desc), 0) liquidado,
						(select docs.fecha from documentos docs where docs.tipo=v.tipo_dev and convert(varchar(10), docs.numero)=v.numero_dev) fecha_dev
			from vbsc_nomtipopago v where porcen_apl>=50 $add_query
		ORDER BY $sidx $sord";
	$result = BD::sql_query_limit($query, $limit, $start) or die("Error en query $query");
	$responce = new ObjetoJSON();
	$responce->page = $page;
	$responce->total = $total_pages;
	$responce->records = $count;
	$i = 0;

	function getTipo($t) { return $t == 1 ? "VALOR HORA" : "PORCENTAJE"; }
	function getValor($t, $val) { return $t == 1 ? "$" . Moneda::getMoneda($val, 0) : ($val * 100) . "%"; }

	$rdev = BD::sql_query("select v.tipo, v.numero, v.tipo_dev, v.numero_dev, v.numero_orden, month(v.fec) mes FROM vbsc_nomtipopago v WHERE sw=2 AND fec BETWEEN '$fecha1' AND '$fecha2'");
	$devoluciones = array();
	while ($fdev = BD::obtenerRegistro($rdev)) 
		$devoluciones[$fdev["tipo_dev"] . "-" . $fdev["numero_dev"]] = $fdev["tipo"] . "-" . $fdev["numero"];

	//print_r($devoluciones);

	while ($row = BD::obtenerRegistro($result)) {
		$fecha = Fecha::getFechaCorta($row["fec"], "d/F/Y");
		$valor = ($row["valor"] != "") ? getValor($row["tipo"], $row["valor"]) : "-";
		$descuento = ($row["descuento"] == 0) ? "-" : $descuento = Moneda::getMoneda($row["descuento"], 0) . "%";
		
		$forma_pago = " ";
		if ($row["fpago_tipo"] == 1) $forma_pago = "<small><b>Valor por hora: $" . Moneda::getMoneda($row["fpago_valor"], 0) . "</b></small>";
		if ($row["fpago_tipo"] == 2) $forma_pago = "<small><b>Porcentaje sobre total: " . Moneda::getMoneda($row["fpago_valor"] * 100, 0) . "%</b></small>";

		$factura = $row["tipo"] . "-" . $row["numero"];
		if (isset($devoluciones[$factura])) 
			$factura = "<div class='devolucion'>" . $row["tipo"] . "-" . $row["numero"] . "</div><small><b>" . $devoluciones[$factura] . "</b></small>";

		if ($row["sw"] == 2) {
			if (Fecha::getFechaCorta($row["fecha_dev"], "F") != Fecha::getFechaCorta($row["fec"], "F"))
				$factura = "<div class='devolucion'>" . $row["tipo"] . "-" . $row["numero"] . "</div><span style='color:black;'><small>" . $row["tipo_dev"] . "-" . $row["numero_dev"] . " " . Fecha::getFEchaCorta($row["fecha_dev"], "d/F") . "</small></span>";
			else
				$factura = "<div class='devolucion'>" . $row["tipo"] . "-" . $row["numero"] . "</div><span style='color:red;'><small>" . $row["tipo_dev"] . "-" . $row["numero_dev"] . " " . Fecha::getFEchaCorta($row["fecha_dev"], "d/F") . "</small></span>";
			$row["liquidado"] *= -1;
		}
		$checked = intval($row["pagado"]) > 0 ? "checked='checked'" : "";
		$responce->rows[$i]['cell'] = array (
			utf8_encode("<input class='seleccion-item' numid='" . $row["pagado"] . "' $checked value='" 
				. Fecha::getFecha($row["fec"], "Ymd") ."@@@" 
				. $row["tipo"] ."@@@" 
				. $row["numero"] ."@@@" 
				. $row["numero_orden"] ."@@@" 
				. $row["operario"] ."@@@" 
				. $row["serie"] ."@@@" 
				. $row["operacion"] ."@@@" 
				. $row["descripcion"] ."@@@" 
				. $row["total"] ."@@@" 
				. $row["tiempo"] ."@@@" 
				. $row["fpago_tipo"] ."@@@" 
				. $row["fpago_valor"] ."@@@" 
				. $row["liquidado"] . "@@@" . intval($row["pagado"])
				. "' type=checkbox>"),
			utf8_encode($factura),
			utf8_encode($row["numero_orden"]),
			utf8_encode($fecha),
			utf8_encode($row["operario"]),
			utf8_encode($row["nombres"] . "\n"  . $forma_pago),
			utf8_encode($row["descripcion"]),
			utf8_encode("$" . Moneda::getMoneda($row["valor"], 0)),
			utf8_encode($descuento),
			utf8_encode("$" . Moneda::getMoneda($row["total"], 0)),
			utf8_encode(round($row["tiempo"], 4)),
			utf8_encode("$" . Moneda::getMoneda($row["liquidado"], 0)),
		);
		$i++;
	}
	echo json_encode($responce);
?>