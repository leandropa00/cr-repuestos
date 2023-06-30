<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../conf/config.php"); 
	Aplicacion::validarAcceso(10, 7, 6);
	BD::changeInstancia("mysql");
	
	$page = isset($_POST['page']) ? intval($_POST['page']) : die(""); 
	$limit = isset($_POST['rows']) ? intval($_POST["rows"]) : die("");
	$sidx = isset($_POST['sidx']) ? $_POST["sidx"] : 1; //VALIDAR
	$sord = isset($_POST['sord']) ? $_POST["sord"] : die("");
	
	$busca_nombre = isset($_GET["nombre"]) ? $_GET["nombre"] : "";
	$busca_identificacion = isset($_GET["identificacion"]) ? $_GET["identificacion"] : "";
	$busca_tipo = isset($_GET["tipo"]) ? $_GET["tipo"] : "";
	if (!in_array($busca_tipo, array("", "particular", "flota"))) die("Error en el filtro tipo");
	$busca_informe = isset($_GET["informe"]) ? intval($_GET["informe"]) : "";
	
	$add_query = "";
	
	if ($busca_nombre != "") 			$add_query .= " AND nombre LIKE UPPER('%" . Seguridad::escapeSQL($busca_nombre) . "%')";
	if ($busca_identificacion != "") 	$add_query .= " AND identificacion LIKE '%" . Seguridad::escapeSQL($busca_identificacion) . "%'";
	if ($busca_tipo != "") 				$add_query .= " AND tipo_cliente LIKE UPPER('%" . Seguridad::escapeSQL($busca_tipo) . "%')";
	if ($busca_informe > 0) 			$add_query .= " AND informe_id = $busca_informe";
		
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
	$campos = array("identificacion", "nombre", "informe_id", "tipo_cliente", "clasificacion");
	$orden = array("asc", "desc");
	if (!in_array($sidx, $campos))
		die("ErrorCampos"); //Modificación de la petición: Posible intento de inyección de código SQL
	if (!in_array(strtolower($sord), $orden))
		die("ErrorOrden"); //Modificación de la petición: Posible intento de inyección de código SQL
	//-----------Fin validaciones---------------------------
	if (!$sidx)
		$sidx = 1;
	$count = 0;
	$add_query .= " ";
	$result = BD::sql_query("select COUNT(*) AS count FROM cliente_tipo WHERE 1=1 $add_query");
	if ($row = BD::obtenerRegistro($result))
		$count = $row['count'];
	$total_pages =  ($count > 0) ? ceil($count/$limit) : 0;
	if ($page > $total_pages) 
		$page = $total_pages; 
	$start = $limit * $page - $limit;
	if ($start < 0)
		$start = 0;
	
	$query = "select i.anio, i.mes, c.* FROM cliente_tipo c INNER JOIN informe i ON c.informe_id=i.id WHERE 1=1 $add_query ORDER BY $sidx $sord";
	$result = BD::sql_query_limit($query, $limit, $start) or die("Error en query");
	$responce = new ObjetoJSON();
	$responce->page = $page;
	$responce->total = $total_pages;
	$responce->records = $count;
	$i = 0;

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
	
	while ($row = BD::obtenerRegistro($result)) {
		$color = $row["tipo_cliente"] == "particular" ? "darkblue" : "darkred";
		$responce->rows[$i]['id']   = $row["identificacion"];
		$responce->rows[$i]['cell'] = array (
			utf8_encode(Fecha::getFechaCorta($row["anio"] . "-" . str_pad($row["mes"], 2, "0", STR_PAD_LEFT) . "-01", "F/Y")),
			utf8_encode(htmlentities($row["identificacion"], ENT_QUOTES, "iso8859-1")),
			utf8_encode(htmlentities($row["nombre"], ENT_QUOTES, "iso8859-1")),
			utf8_encode("<div ondblclick=\"cambiarTipocliente('" . $row["identificacion"] . "')\" class='noselect' style='cursor:pointer; color:$color;'>" . $row["tipo_cliente"] . "</div>"),
			utf8_encode(getClasificacion($row["clasificacion"]))
		);
		$i++;
	}
	echo json_encode($responce);
?>