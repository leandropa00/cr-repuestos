<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php"); 
	Aplicacion::validarAcceso(10);
	BD::changeInstancia("facts");
	
	$page = isset($_POST['page']) ? intval($_POST['page']) : die(""); 
	$limit = isset($_POST['rows']) ? intval($_POST["rows"]) : die("");
	$sidx = isset($_POST['sidx']) ? $_POST["sidx"] : 1; //VALIDAR
	$sord = isset($_POST['sord']) ? $_POST["sord"] : die("");
	
	$busca_nombre = isset($_GET["nombre"]) ? $_GET["nombre"] : "";
	$busca_cedula = isset($_GET["cedula"]) ? $_GET["cedula"] : "";
	
	$add_query = "";
	
	if ($busca_nombre != "")
		$add_query .= " AND nombres LIKE UPPER('%" . Seguridad::escapeSQL($busca_nombre, 'mssql_odbc') . "%')";
		
	if ($busca_cedula != "")
		$add_query .= " AND operario LIKE UPPER('%" . Seguridad::escapeSQL($busca_cedula, 'mssql_odbc') . "%')";
	
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
	$campos = array("nit", "nombres", "tipo", "valor", "fecha");
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
	$result = BD::sql_query("select COUNT(distinct v.operario) AS count
		from v_talldoclin v
		left join terceros ter on ter.nit=v.operario
		
		where v.tipo in ('FL','FC','FG','FSC','FT','FTA','TI','DVTA','DVTO','DVTS','DVTC','DVTL') 
		and v.fec > '20190801' and v.clase_operacion<>'R' $add_query");
	if ($row = BD::obtenerRegistro($result))
		$count = $row['count'];
	$total_pages =  ($count > 0) ? ceil($count/$limit) : 0;
	if ($page > $total_pages) 
		$page = $total_pages; 
	$start = $limit * $page - $limit;
	if ($start < 0)
		$start = 0;
	
	$query = "select v.operario nit, ter.nombres,
				(select top 1 fecha from bsc_nomtipopago bsc where identificacion=v.operario order by fecha desc, bsc.id desc) fecha,
				(select top 1 tipo from bsc_nomtipopago bsc where identificacion=v.operario order by fecha desc, bsc.id desc) tipo,
				(select top 1 valor from bsc_nomtipopago bsc where identificacion=v.operario order by fecha desc, bsc.id desc) valor
			from v_talldoclin v
			left join terceros ter on ter.nit=v.operario
			
			where v.tipo in ('FL','FC','FG','FSC','FT','FTA','TI','DVTA','DVTO','DVTS','DVTC','DVTL') 
			and v.fec > '20190801' and v.clase_operacion<>'R' $add_query
		group by v.operario, ter.nombres
		ORDER BY $sidx $sord";
	$result = BD::sql_query_limit($query, $limit, $start) or die("Error en query");
	$responce = new ObjetoJSON();
	$responce->page = $page;
	$responce->total = $total_pages;
	$responce->records = $count;
	$i = 0;

	function getTipo($t) { return $t == 1 ? "VALOR HORA" : "PORCENTAJE"; }
	function getValor($t, $val) { return $t == 1 ? "$" . Moneda::getMoneda($val, 0) : ($val * 100) . "%"; }
	
	while ($row = BD::obtenerRegistro($result)) {
		$fecha = ($row["fecha"] != "") ? Fecha::getFechaCorta($row["fecha"], "d/F/Y g:ia") : "-";
		$tipo = ($row["tipo"] != "") ? getTipo($row["tipo"]) : "-";
		$valor = ($row["valor"] != "") ? getValor($row["tipo"], $row["valor"]) : "-";
		$responce->rows[$i]['id'] = $row["nit"];
		$responce->rows[$i]['cell'] = array (
			utf8_encode($row["nit"]),
			utf8_encode($row["nombres"]),
			utf8_encode($fecha),
			utf8_encode($tipo),
			utf8_encode($valor),
			//utf8_encode("<button class='btn btn-small btn-success'>Editar</button>")
			utf8_encode("<img style='margin:3px;cursor:pointer;' onclick='editar(" . $row["nit"] . ")' src='imagenes/editar.png'>")
		);
		$i++;
	}
	echo json_encode($responce);
?>