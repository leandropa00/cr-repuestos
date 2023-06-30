<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php"); 
	Aplicacion::validarAcceso(10);
	BD::changeInstancia("facts");
	
	$page = isset($_POST['page']) ? intval($_POST['page']) : die(""); 
	$limit = isset($_POST['rows']) ? intval($_POST["rows"]) : die("");
	$sidx = isset($_POST['sidx']) ? $_POST["sidx"] : 1; //VALIDAR
	$sord = isset($_POST['sord']) ? $_POST["sord"] : die("");
	
	$nit = isset($_GET["id"]) ? $_GET["id"] : "";
	if (!preg_match("/^\d{1,}$/", $nit))
		die("Error en el formato del documento de identificación");
	
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
	$campos = array("fecha", "tipo", "valor");
	$orden = array("asc", "desc");
	if (!in_array($sidx, $campos))
		die("ErrorCampos"); //Modificación de la petición: Posible intento de inyección de código SQL
	if (!in_array(strtolower($sord), $orden))
		die("ErrorOrden"); //Modificación de la petición: Posible intento de inyección de código SQL
	//-----------Fin validaciones---------------------------
	if (!$sidx)
		$sidx = 1;
	$count = 0;
	$result = BD::sql_query("select COUNT(*) AS count from bsc_nomtipopago where identificacion='$nit'");
	if ($row = BD::obtenerRegistro($result))
		$count = $row['count'];
	$total_pages =  ($count > 0) ? ceil($count/$limit) : 0;
	if ($page > $total_pages) 
		$page = $total_pages; 
	$start = $limit * $page - $limit;
	if ($start < 0)
		$start = 0;
	
	$query = "select * from bsc_nomtipopago where identificacion='$nit' order by fecha desc, id desc";
	$result = BD::sql_query_limit($query, $limit, $start) or die("Error en query");
	$responce = new ObjetoJSON();
	$responce->page = $page;
	$responce->total = $total_pages;
	$responce->records = $count;
	$i = 0;

	function getTipo($t) { return $t == 1 ? "VALOR HORA" : "PORCENTAJE"; }
	function getValor($t, $val) { return $t == 1 ? "$" . Moneda::getMoneda($val, 0) : ($val * 100) . "%"; }
	
	while ($row = BD::obtenerRegistro($result)) {
		$fecha_registro = ($row["fecha_registro"] != "") ? Fecha::getFechaCorta($row["fecha_registro"], "d/F/Y g:ia") : "-";
		$fecha = ($row["fecha"] != "") ? Fecha::getFechaCorta($row["fecha"], "d/F/Y") : "-";
		$tipo = ($row["tipo"] != "") ? getTipo($row["tipo"]) : "-";
		$valor = ($row["valor"] != "") ? getValor($row["tipo"], $row["valor"]) : "-";
		$responce->rows[$i]['id'] = $row["id"];
		$responce->rows[$i]['cell'] = array (
			utf8_encode($fecha_registro),
			utf8_encode($fecha),
			utf8_encode($tipo),
			utf8_encode($valor)
		);
		$i++;
	}
	echo json_encode($responce);
?>