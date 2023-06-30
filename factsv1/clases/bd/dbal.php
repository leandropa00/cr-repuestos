<?php

/*
* Capa de abstraccion de la base de datos
* @package dbal
*/

defined("iC") or die("<script>alert('Ingreso incorrecto')</script>");

class dbal {
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_time = 0;
	var $num_queries = array();
	var $open_queries = array();

	var $curtime = 0;
	var $query_hold = '';
	var $html_hold = '';
	var $sql_report = '';
	
	var $persistency = false;
	var $user = '';
	var $server = '';
	var $dbname = '';

	// Se estable en verdadero si es disparado un error 
	var $sql_error_triggered = false;

	// Asegura la ultima consulta en sql error
	var $sql_error_sql = '';
	// Almacena la informacion del error - solo si sql_error_triggered esta establecido
	var $sql_error_returned = array();
	
	// Contador de transacciones
	var $transactions = 0;

	// Soporta multiples inserts?
	var $multi_insert = false;

	/**
	* Capa SQL actual
	*/
	var $sql_layer = '';

	/**
	* Comodines para emparejar cualquier (%) o exactamente un caracter (_) dentro de la expresion LIKE 
	*/
	var $any_char;
	var $one_char;

	/**
	* Constructor
	*/
	function __construct()
	{
		$this->num_queries = array(
			'cached'		=> 0,
			'normal'		=> 0,
			'total'			=> 0,
		);

		// Llena la capa SQL por defecto basado en la clase que esta siendo llamada.
		// Esto puede ser cambiado mas tarde por la capa especifica en si misma si es necesario.
		$this->sql_layer = substr(get_class($this), 5);

		// no cambiar esto
		$this->any_char = chr(0) . '%';
		$this->one_char = chr(0) . '_';
	}

	/**
	* returna el error o muestra el mensaje de error
	*/
	function sql_return_on_error($fail = false)
	{
		$this->sql_error_triggered = false;
		$this->sql_error_sql = '';

		$this->return_on_error = $fail;
	}

	/**
	* Retorna el numero de consultas SQL y las consultas usadas en cache
	*/
	function sql_num_queries($cached = false)
	{
		return ($cached) ? $this->num_queries['cached'] : $this->num_queries['normal'];
	}

	/**
	* Agrega al contador de consultas
	*/
	function sql_add_num_queries($cached = false)
	{
		$this->num_queries['cached'] += ($cached !== false) ? 1 : 0;
		$this->num_queries['normal'] += ($cached !== false) ? 0 : 1;
		$this->num_queries['total'] += 1;
	}

	/**
	* DBAL recoleccion de basura, cierra la conexion SQL
	*/
	function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		if ($this->transaction)
		{
			do
			{
				$this->sql_transaction('commit');
			}
			while ($this->transaction);
		}

		foreach ($this->open_queries as $query_id)
		{
			$this->sql_freeresult($query_id);
		}
		
		return $this->_sql_close();
	}

	/**
	* Construye la consulta LIMIT 
	* Realiza algunas validaciones aquí
	*/
	function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		if (empty($query))
		{
			return false;
		}

		// Nunca usa o compara un total negativo
		$total = ($total < 0) ? 0 : $total;
		$offset = ($offset < 0) ? 0 : $offset;

		return $this->_sql_query_limit($query, $total, $offset, $cache_ttl);
	}

	/**
	* Extrae todas las filas
	*/
	function sql_fetchrowset($query_id = false)
	{
		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($query_id !== false)
		{
			$result = array();
			while ($row = $this->sql_fetchrow($query_id))
			{
				$result[] = $row;
			}

			return $result;
		}
		
		return false;
	}

	/**
	* Extrae un campo
	* si rownum es falso, la fila actual es usada, sino apunta a la fila (zero-based)
	*/
	function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if ($query_id !== false)
		{
			if ($rownum !== false)
			{
				$this->sql_rowseek($rownum, $query_id);
			}

			if (!is_object($query_id) && isset($cache->sql_rowset[$query_id]))
			{
				return $cache->sql_fetchfield($query_id, $field);
			}

			$row = $this->sql_fetchrow($query_id);
			return (isset($row[$field])) ? $row[$field] : false;
		}

		return false;
	}

	/**
	* Ajusta correctamente la expresion LIKE para caracteres especiales
	* Algunos DBMS están manejando ellos de una manera diferente
	*
	* @param 	string 	$expression 	La expresion a utilizar. Cada comodin es escaped, excepto $this->any_char y $this->one_char
	* @return 	string 					Expresion LIKE incluyendo la palabra clave
	*/
	function sql_like_expression($expression)
	{
		$expression = str_replace(array('_', '%'), array("\_", "\%"), $expression);
		$expression = str_replace(array(chr(0) . "\_", chr(0) . "\%"), array('_', '%'), $expression);

		return $this->_sql_like_expression('LIKE \'' . $this->sql_escape($expression) . '\'');
	}

	/**
	* TransaccionSQL 
	* @acceso privado
	*/
	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				// Si estamos dentro de una transacciÃ³n no vamos a abrir otra, pero adjuntamos la actual para no perder los datos (previniendo auto commit)
				if ($this->transaction)
				{
					$this->transactions++;
					return true;
				}

				$result = $this->_sql_transaction('begin');

				if (!$result)
				{
					$this->sql_error();
				}

				$this->transaction = true;
			break;

			case 'commit':
				// Si habia una transaccion abierta previamente contamos el numero de transacciones internas
				if ($this->transaction && $this->transactions)
				{
					$this->transactions--;
					return true;
				}

				if (!$this->transaction)
				{
					return false;
				}

				$result = $this->_sql_transaction('commit');

				if (!$result)
				{
					$this->sql_error();
				}

				$this->transaction = false;
				$this->transactions = 0;
			break;

			case 'rollback':
				$result = $this->_sql_transaction('rollback');
				$this->transaction = false;
				$this->transactions = 0;
			break;

			default:
				$result = $this->_sql_transaction($status);
			break;
		}

		return $result;
	}

	/**
	* Construimos una sentencia SQL desde el array para las sentencias insert/update/select 
	*
	* Posibles valores de consulta: INSERT, INSERT_SELECT, MULTI_INSERT, UPDATE, SELECT
	*
	*/
	function sql_build_array($query, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = $values = array();

		if ($query == 'INSERT' || $query == 'INSERT_SELECT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_array($var) && is_string($var[0]))
				{
					// Este es usado para INSERT_SELECT(s)
					$values[] = $var[0];
				}
				else
				{
					$values[] = $this->_sql_validate_value($var);
				}
			}

			$query = ($query == 'INSERT') ? ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')' : ' (' . implode(', ', $fields) . ') SELECT ' . implode(', ', $values) . ' ';
		}
		else if ($query == 'MULTI_INSERT')
		{
			$ary = array();
			foreach ($assoc_ary as $id => $sql_ary)
			{
				// Si por accidente el array SQL es unidimensional construimos una sentencia INSERT normal
				if (!is_array($sql_ary))
				{
					return $this->sql_build_array('INSERT', $assoc_ary);
				}

				$values = array();
				foreach ($sql_ary as $key => $var)
				{
					$values[] = $this->_sql_validate_value($var);
				}
				$ary[] = '(' . implode(', ', $values) . ')';
			}

			$query = ' (' . implode(', ', array_keys($assoc_ary[0])) . ') VALUES ' . implode(', ', $ary);
		}
		else if ($query == 'UPDATE' || $query == 'SELECT')
		{
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				$values[] = "$key = " . $this->_sql_validate_value($var);
			}
			$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
	}

	/**
	* Construye la cadena de comparacion SQL IN o NOT IN
	* arrays para mejorar la velocidad de comparacion
	*
	* @access public
	* @param	string	$field				nombre de la columna SQL que debe ser comparada
	* @param	array	$array				array de valores que estan permitidos (IN) o no permitidos (NOT IN)
	* @param	bool	$negate				veradero para NOT IN (), falso para IN () (default)
	* @param	bool	$allow_empty_set	si es true, permite al $array estar vacio, esta funcion luego retornarÃ¡ 1=1 or 1=0.
	*/
	function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
	{
		if (!sizeof($array))
		{
			if (!$allow_empty_set)
			{
				// Imprime el seguimiento para ayudar a identificar la ubicacion del codigo problematico
				$this->sql_error('No values specified for SQL IN comparison');
			}
			else
			{
				// NOT IN () actualmente significa todo lo posible para usar una tautologia
				if ($negate)
				{
					return '1=1';
				}
				// IN () actualmente significa nada para usar una contradiccion
				else
				{
					return '1=0';
				}
			}
		}

		if (!is_array($array))
		{
			$array = array($array);
		}

		if (sizeof($array) == 1)
		{
			@reset($array);
			$var = current($array);

			return $field . ($negate ? ' <> ' : ' = ') . $this->_sql_validate_value($var);
		}
		else
		{
			return $field . ($negate ? ' NOT IN ' : ' IN ') . '(' . implode(', ', array_map(array($this, '_sql_validate_value'), $array)) . ')';
		}
	}

	/**
	* Corre mas de una sentencia INSERT
	*
	* @param string $table name de la table en la cual se correra el INSERT
	* @param array &$sql_ary array multi-dimensional que almacena los datos de la sentencia.
	*
	* @return bool false si no fueron ejecutadas las sentencias.
	* @acceso publico
	*/
	function sql_multi_insert($table, &$sql_ary)
	{
		if (!sizeof($sql_ary))
		{
			return false;
		}

		if ($this->multi_insert)
		{
			$this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('MULTI_INSERT', $sql_ary));
		}
		else
		{
			foreach ($sql_ary as $ary)
			{
				if (!is_array($ary))
				{
					return false;
				}

				$this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $ary));
			}
		}

		return true;
	}

	/**
	* Funcion para validar valores
	* @acceso privado
	*/
	function _sql_validate_value($var)
	{
		if (is_null($var))
		{
			return 'NULL';
		}
		else if (is_string($var))
		{
			return "'" . $this->sql_escape($var) . "'";
		}
		else
		{
			return (is_bool($var)) ? intval($var) : $var;
		}
	}

	/**
	* Construye la sentecia SQL desde el array para las sentencias SELECT y SELECT DISTINCT
	*
	* Posibles valores de consulta: SELECT, SELECT_DISTINCT
	*/
	function sql_build_query($query, $array)
	{
		$sql = '';
		switch ($query)
		{
			case 'SELECT':
			case 'SELECT_DISTINCT';

				$sql = str_replace('_', ' ', $query) . ' ' . $array['SELECT'] . ' FROM ';

				$table_array = array();
				foreach ($array['FROM'] as $table_name => $alias)
				{
					if (is_array($alias))
					{
						foreach ($alias as $multi_alias)
						{
							$table_array[] = $table_name . ' ' . $multi_alias;
						}
					}
					else
					{
						$table_array[] = $table_name . ' ' . $alias;
					}
				}

				$sql .= $this->_sql_custom_build('FROM', implode(', ', $table_array));

				if (!empty($array['LEFT_JOIN']))
				{
					foreach ($array['LEFT_JOIN'] as $join)
					{
						$sql .= ' LEFT JOIN ' . key($join['FROM']) . ' ' . current($join['FROM']) . ' ON (' . $join['ON'] . ')';
					}
				}

				if (!empty($array['WHERE']))
				{
					$sql .= ' WHERE ' . $this->_sql_custom_build('WHERE', $array['WHERE']);
				}

				if (!empty($array['GROUP_BY']))
				{
					$sql .= ' GROUP BY ' . $array['GROUP_BY'];
				}

				if (!empty($array['ORDER_BY']))
				{
					$sql .= ' ORDER BY ' . $array['ORDER_BY'];
				}

			break;
		}

		return $sql;
	}

	/**
	* muestra la pagina del error SQL
	*/
	function sql_error($sql = '')
	{
		global $auth, $user, $config;

		$this->sql_error_triggered = true;
		$this->sql_error_sql = $sql;

		$this->sql_error_returned = $this->_sql_error();

		if (!$this->return_on_error)
		{
			$message = 'SQL ERROR [ ' . $this->sql_layer . ' ]<br /><br />' . $this->sql_error_returned['message'] . ' [' . $this->sql_error_returned['code'] . ']';

			// Muestra el error SQL completo y la ruta a administradores unicamente
			if ((isset($auth) && $auth->acl_get('a_')) || defined('IN_INSTALL') || defined('DEBUG_EXTRA'))
			{
			 	$backtrace = get_backtrace();

				$message .= ($sql) ? '<br /><br />SQL<br /><br />' . htmlspecialchars($sql) : '';
				$message .= ($backtrace) ? '<br /><br />BACKTRACE<br />' . $backtrace : '';
				$message .= '<br />';
			}
			else
			{
				// Si el error ocurre iniciando la sesion necesitamos usar una cadena de lenguaje predefinido
				// Esto puede pasar si la conexion no puede ser establecida
				if (!isset($user->lang['SQL_ERROR_OCCURRED']))
				{
					$message .= '<br /><br />An sql error occurred while fetching this page. Please contact an administrator if this problem persists.';
				}
				else
				{
					if (!empty($config['board_contact']))
					{
						$message .= '<br /><br />' . sprintf($user->lang['SQL_ERROR_OCCURRED'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
					}
					else
					{
						$message .= '<br /><br />' . sprintf($user->lang['SQL_ERROR_OCCURRED'], '', '');
					}
				}
			}

			if ($this->transaction)
			{
				$this->sql_transaction('rollback');
			}

			if (strlen($message) > 1024)
			{
				// Necesitamos definir $msg_long_text aquÃ­ para eludir el paso de texto
				global $msg_long_text;
				$msg_long_text = $message;

				trigger_error(false, E_USER_ERROR);
			}

			//trigger_error($message, E_USER_ERROR);
		}

		if ($this->transaction)
		{
			$this->sql_transaction('rollback');
		}

		return $this->sql_error_returned;
	}

	/**
	* Explicar consultas
	*/
	function sql_report($mode, $query = '')
	{
		global $cache, $starttime, $phpbb_root_path, $user;

		if (empty($_REQUEST['explain']))
		{
			return false;
		}

		if (!$query && $this->query_hold != '')
		{
			$query = $this->query_hold;
		}

		switch ($mode)
		{
			case 'display':
				if (!empty($cache))
				{
					$cache->unload();
				}
				$this->sql_close();

				$mtime = explode(' ', microtime());
				$totaltime = $mtime[0] + $mtime[1] - $starttime;

				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
						<meta http-equiv="Content-Style-Type" content="text/css" />
						<meta http-equiv="imagetoolbar" content="no" />
						<title>SQL Report</title>
						<link href="' . $phpbb_root_path . 'adm/style/admin.css" rel="stylesheet" type="text/css" media="screen" />
					</head>
					<body id="errorpage">
					<div id="wrap">
						<div id="page-header">
							<a href="' . build_url('explain') . '">Return to previous page</a>
						</div>
						<div id="page-body">
							<div id="acp">
							<div class="panel">
								<span class="corners-top"><span></span></span>
								<div id="content">
									<h1>SQL Report</h1>
									<br />
									<p><b>Page generated in ' . round($totaltime, 4) . " seconds with {$this->num_queries['normal']} queries" . (($this->num_queries['cached']) ? " + {$this->num_queries['cached']} " . (($this->num_queries['cached'] == 1) ? 'query' : 'queries') . ' returning data from cache' : '') . '</b></p>

									<p>Time spent on ' . $this->sql_layer . ' queries: <b>' . round($this->sql_time, 5) . 's</b> | Time spent on PHP: <b>' . round($totaltime - $this->sql_time, 5) . 's</b></p>

									<br /><br />
									' . $this->sql_report . '
								</div>
								<span class="corners-bottom"><span></span></span>
							</div>
							</div>
						</div>
						<div id="page-footer">
							Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>
						</div>
					</div>
					</body>
					</html>';

				exit_handler();

			break;

			case 'stop':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$this->sql_report .= '

					<table cellspacing="1">
					<thead>
					<tr>
						<th>Query #' . $this->num_queries['total'] . '</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td class="row3"><textarea style="font-family:\'Courier New\',monospace;width:99%" rows="5" cols="10">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td>
					</tr>
					</tbody>
					</table>
					
					' . $this->html_hold . '

					<p style="text-align: center;">
				';

				if ($this->query_result)
				{
					if (preg_match('/^(UPDATE|DELETE|REPLACE)/', $query))
					{
						$this->sql_report .= 'Affected rows: <b>' . $this->sql_affectedrows($this->query_result) . '</b> | ';
					}
					$this->sql_report .= 'Before: ' . sprintf('%.5f', $this->curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed: <b>' . sprintf('%.5f', $endtime - $this->curtime) . 's</b>';
				}
				else
				{
					$error = $this->sql_error();
					$this->sql_report .= '<b style="color: red">FAILED</b> - ' . $this->sql_layer . ' Error ' . $error['code'] . ': ' . htmlspecialchars($error['message']);
				}

				$this->sql_report .= '</p><br /><br />';

				$this->sql_time += $endtime - $this->curtime;
			break;

			case 'start':
				$this->query_hold = $query;
				$this->html_hold = '';
			
				$this->_sql_report($mode, $query);

				$this->curtime = explode(' ', microtime());
				$this->curtime = $this->curtime[0] + $this->curtime[1];

			break;
			
			case 'add_select_row':

				$html_table = func_get_arg(2);
				$row = func_get_arg(3);
				
				if (!$html_table && sizeof($row))
				{
					$html_table = true;
					$this->html_hold .= '<table cellspacing="1"><tr>';
								
					foreach (array_keys($row) as $val)
					{
						$this->html_hold .= '<th>' . (($val) ? ucwords(str_replace('_', ' ', $val)) : '&nbsp;') . '</th>';
					}
					$this->html_hold .= '</tr>';
				}
				$this->html_hold .= '<tr>';

				$class = 'row1';
				foreach (array_values($row) as $val)
				{
					$class = ($class == 'row1') ? 'row2' : 'row1';
					$this->html_hold .= '<td class="' . $class . '">' . (($val) ? $val : '&nbsp;') . '</td>';
				}
				$this->html_hold .= '</tr>';
			
				return $html_table;

			break;

			case 'fromcache':

				$this->_sql_report($mode, $query);

			break;

			case 'record_fromcache':

				$endtime = func_get_arg(2);
				$splittime = func_get_arg(3);

				$time_cache = $endtime - $this->curtime;
				$time_db = $splittime - $endtime;
				$color = ($time_db > $time_cache) ? 'green' : 'red';

				$this->sql_report .= '<table cellspacing="1"><thead><tr><th>Query results obtained from the cache</th></tr></thead><tbody><tr>';
				$this->sql_report .= '<td class="row3"><textarea style="font-family:\'Courier New\',monospace;width:99%" rows="5" cols="10">' . preg_replace('/\t(AND|OR)(\W)/', "\$1\$2", htmlspecialchars(preg_replace('/[\s]*[\n\r\t]+[\n\r\s\t]*/', "\n", $query))) . '</textarea></td></tr></tbody></table>';
				$this->sql_report .= '<p style="text-align: center;">';
				$this->sql_report .= 'Before: ' . sprintf('%.5f', $this->curtime - $starttime) . 's | After: ' . sprintf('%.5f', $endtime - $starttime) . 's | Elapsed [cache]: <b style="color: ' . $color . '">' . sprintf('%.5f', ($time_cache)) . 's</b> | Elapsed [db]: <b>' . sprintf('%.5f', $time_db) . 's</b></p><br /><br />';

				$starttime += $time_db;

			break;

			default:
			
				$this->_sql_report($mode, $query);

			break;
		}

		return true;
	}
}

/**
* Esta variable almacena el nombre de la clase para usarla posteriormente
*/
$sql_db = (!empty($dbms)) ? 'dbal_' . basename($dbms) : 'dbal';

?>
