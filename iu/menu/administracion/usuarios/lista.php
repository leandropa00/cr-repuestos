<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../conf/config.php"); 
	//Aplicacion::validarAcceso(10);
	BD::changeInstancia("mysql");
	
	$page = isset($_POST['page']) ? intval($_POST['page']) : die(""); 
	$limit = isset($_POST['rows']) ? intval($_POST["rows"]) : die("");
	$sidx = isset($_POST['sidx']) ? $_POST["sidx"] : 1; //VALIDAR
	$sord = isset($_POST['sord']) ? $_POST["sord"] : die("");
	
	$busca_estado = (isset($_GET["estado"]) && $_GET["estado"] != "") ? intval($_GET["estado"]) : -1;
	$busca_nombre = isset($_GET["nombre"]) ? $_GET["nombre"] : "";
	$busca_login = isset($_GET["login"]) ? $_GET["login"] : "";
	$busca_perfil = (isset($_GET["perfil"]) && $_GET["perfil"] != "") ? intval($_GET["perfil"]) : -1;
	
	$add_query = "";
	
	if ($busca_nombre != "")
		$add_query .= " AND v.nombres LIKE UPPER('%" . Seguridad::escapeSQL($busca_nombre) . "%')";
		
	if ($busca_login != "")
		$add_query .= " AND v.usuario LIKE UPPER('%" . Seguridad::escapeSQL($busca_login) . "%')";
	
	if ($busca_perfil >= 0)
		$add_query .= " AND v.perfil_id = '" . Seguridad::escapeSQL($busca_perfil) . "'";
	
	if ($busca_estado >= 0)
		$add_query .= " AND v.estado = '" . Seguridad::escapeSQL($busca_estado) . "'";
	
	/**
	 * Validaciones para evitar inyección de código SQL 
	*/
	$campos = array("id", "usuario", "nombres", "perfil_nombre", "email", "estado", "fecha_ultimoacceso");
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
	$result = BD::sql_query("select COUNT(*) AS count FROM vusuarios WHERE 1=1 $add_query");
	if ($row = BD::obtenerRegistro($result))
		$count = $row['count'];
	$total_pages =  ($count > 0) ? ceil($count/$limit) : 0;
	if ($page > $total_pages) 
		$page = $total_pages; 
	$start = $limit * $page - $limit;
	if ($start < 0)
		$start = 0;
	$order_by = "ESTADO DESC,";
	if ($sidx == "estado")
		$order_by = "";
	
	$query = "select v.*
		FROM vusuarios v
		WHERE 1=1 $add_query
		GROUP BY v.id
		ORDER BY $order_by $sidx $sord";
	$result = BD::sql_query_limit($query, $limit, $start) or die("Error en query");
	$responce = new ObjetoJSON();
	$responce->page = $page;
	$responce->total = $total_pages;
	$responce->records = $count;
	$i = 0;
	
	while ($row = BD::obtenerRegistro($result)) {
		if ($row["fecha_ultimoacceso"] == "0000-00-00 00:00:00")
			$fecha_uacceso = "";
		else
			$fecha_uacceso = Fecha::getFechaCorta($row["fecha_ultimoacceso"], "d/F/Y g:ia");
		if (Fecha::getFecha($row["fecha_ultimoacceso"], "Y-m-d") == date("Y-m-d"))
			$fecha_uacceso = "<span style='color:green;font-weight:bold;'>Hoy a las " . Fecha::getFechaCorta($row["fecha_ultimoacceso"], "g:ia") . "</span>";
		$responce->rows[$i]['id']   = $row["id"];
		$estado = ($row["estado"] == 1)
				? "<span style='width:85%;' class='label label-success'>ACTIVO</span>" 
				: "<span style='width:85%;' class='label label-default'>INACTIVO</span>";
		$btnBorrar = "<img style='margin:3px;cursor:pointer;'  onclick='eliminar(" . $row["id"] . ")' src='imagenes/eliminar.png'>";
		if ($row["id"] == Aplicacion::getIDUsuario())
			$btnBorrar = "<img title='Este usuario no se puede borrar desde la aplicación' style='margin:3px;cursor:pointer;' src='imagenes/informacion.png'>";
		$ultimo_acceso = ($fecha_uacceso == "") ? "" : $fecha_uacceso;
		$responce->rows[$i]['cell'] = array (
			utf8_encode($estado),
			utf8_encode($row["usuario"]),
			utf8_encode($row["perfil_nombre"]),
			utf8_encode($row["nombres"]),
			utf8_encode($row["correo"]),
			utf8_encode($ultimo_acceso),
			utf8_encode("<img style='margin:3px;cursor:pointer;' onclick='editar(" . $row["id"] . ")' src='imagenes/editar.png'>$btnBorrar")
		);
		$i++;
	}
	echo json_encode($responce);
?>