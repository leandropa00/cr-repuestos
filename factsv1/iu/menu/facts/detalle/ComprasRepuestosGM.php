<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../../conf/config.php");
    Aplicacion::validarAcceso(10);
    
    $tipos = isset($_GET["tipo"]) ? explode(",", $_GET["tipo"]) : die("Error al recibir el tipo de documento");


    $informe = Informe::getInstance();
    $descripcion = "Compras repuestos GM";
?>
<div style='margin-bottom:5px; font-size:15px;'>
<b>Buscar:</b> <input onkeyup="buscarTextoTablaGeneral(this.value);" class="ui-corner-all ui-state-default" id='txt_buscar' type="text" name='' value='' style='padding:2px; font-weight: normal; width: 400px; font-size:15px;' placeholder="Escriba una parte del texto a buscar">
</div> 
<div style='overflow-y: scroll; max-height: 400px;'>
    <table class="table table-condensed table-hover table-bordered" style='font-size:12px;'>
        <thead>
            <tr class='ui-widget-header'>
                <td>Fecha</td>
                <td style='width:80px;'>#Fact</td>
                <td>NIT/CC</td>
                <td>Cliente</td>
                <td style='width:60px;'>#OT</td>
                <td>Referencia</td>
                <td>Línea</td>
                <td>Grupo</td>
                <td>Descripción</td>
                <td style='text-align:center;width:30px;'>Cant</td>
                <td style='width:60px;text-align:right;'>Vlr Unidad</td>
                <td style='width:60px;text-align:right;'>Descuentos</td>
                <td style='width:60px;text-align:right;'>TOTAL</td>
                <td style='width:60px;'>Devolución</td>
            </tr>
        </thead>
        <tbody>
		<?php
			$x = 0;
            $colores = array("#efefef", "#cde1ff");
            $total_descuentos_SD = $total_descuentos_CD = 0;
            $total_SD = $total_CD = 0;
			foreach($informe->getTotalComprasRepuestosData($tipos) as $data) {
                //$margen = round((1 - ($data["totalc"] / $data["total"])) * 100, 0);
                $class = "";
                $total_descuentos_CD += $data["descuentos"];
                $total_CD += $data["totalc"];
                if ($data["devolucion"] == 1)
                    $class = "error";
                else {
                    $total_descuentos_SD += $data["descuentos"];
                    $total_SD += $data["totalc"];
                }
                echo "<tr class='search-field $class' value='" . $data["tipo"] . "-" . $data["numero"] . " " . $data["nit_cliente"] . "-" . $data["cliente_nombre"] . " ". $data["numero_ot"] . " " . $data["fecha"] . $data["referencia"] . $data["linea"] . $data["descripcion"] . $data["nombre_grupo"] . "'>";
                echo "<td>" . $data["fecha"] . "</td>";
                echo "<td>" . $data["tipo"] . "-" . $data["numero"] . "</td>";
                echo "<td>" . $data["nit_cliente"] . "</td>";
                echo "<td>" . $data["cliente_nombre"] . "</td>";
                echo "<td>" . $data["numero_ot"] . "</td>";
                echo "<td>" . $data["referencia"] . "</td>";
                echo "<td>" . $data["linea"] . "</td>";
                echo "<td>" . $data["nombre_grupo"] . "</td>";
                echo "<td>" . $data["descripcion"] . "</td>";
                echo "<td style='text-align:center;'>" . $data["cantidad"] . "</td>";
                echo "<td style='text-align:right;'>$" . Moneda::getMoneda($data["valor_unitario"],0) . "</td>";
                echo "<td style='text-align:right;'>$" . Moneda::getMoneda($data["descuentos"],0) . "</td>";
                echo "<td style='text-align:right;'><b>$" . Moneda::getMoneda($data["totalc"],0) . "</b></td>";
                echo "<td><b>" . $data["devolucion_data"] . "</b></td>";
                //echo "<td style='text-align:right;'>$" . Moneda::getMoneda($data["totalc"],0) . "</td>";
                //echo "<td style='text-align:right;'>" . $margen . "%</td>";
                echo "</tr>";
            }
            $codigos = array(-1000);
			foreach($tipos as $td) {
				if ($td == 'CRCO') $codigos[] = 207;
				if ($td == 'CRO') $codigos[] = 208;
				if ($td == 'CROT') $codigos[] = 209;
			}
			$r = BD::sql_query("select sum(v.totalc) total FROM rp_ventasxasesor v 
				WHERE v.ubicacion_item_costo in (" . implode(",", $codigos) . ")  AND v.informe_id<>" . $informe->id . " and concat(v.tipo, v.numero) in (
					select concat(d.tipo_link,d.numero_link) from rp_ventasxasesor d where d.sw=2 and d.informe_id=" . $informe->id . ")") 
					or die(BD::getLastError());
			$devoluciones = ($f = BD::obtenerRegistro($r)) ? $f["total"] : 0;
		?>
            <tr class='info'>
                <td colspan=12 style='text-align:right;'><b>Total:</b></td>
                <td style='width:60px;text-align:right;'><b>$<?php echo Moneda::getMoneda($total_CD, 0); ?></b></td>
                <td style='width:60px;'></td>
            </tr>
            <tr class='error'>
                <td colspan=12 style='text-align:right;'><b>Devoluciones del mes actual:</b></td>
                <td style='width:60px;text-align:right;'><b>$<?php echo Moneda::getMoneda($total_SD - $total_CD, 0); ?></b></td>
                <td style='width:60px;'></td>
            </tr>
            <tr class='error'>
                <td colspan=12 style='text-align:right;'><b>Devoluciones de meses anteriores:</b></td>
                <td style='width:60px;text-align:right;'><b>$<?php echo Moneda::getMoneda(-$devoluciones, 0); ?></b></td>
                <td style='width:60px;'></td>
            </tr>
        </tbody>
        <tfoot>
            <tr class='ui-widget-header'>
                <td colspan=12 style='text-align:right;'><b>TOTAL - DEVOLUCIONES:</b></td>
                <td style='width:60px;text-align:right;'><b>$<?php echo Moneda::getMoneda($total_SD-$devoluciones, 0); ?></b></td>
                <td style='width:60px;'></td>
            </tr>
        </tfoot>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$("#ventana").dialog("destroy");
		$("#ventana").dialog({
			modal: true,
		    overlay: {
		        opacity: 0.4,
		        background: "black" 
			},
			title: "<i class='icon-white icon-list' /> &nbsp; <?php echo $descripcion; ?>",
			resizable: false,
			width: 1200,
			open : function() {
				var t = $(this).parent(), w = $(document);
				t.offset({
					top: 60,
					left: (w.width() / 2) - (t.width() / 2)
				});
			},
	      	close : function () {
	      		$("#ventana").html("");
				$("#ventana").dialog("destroy");
	      	}
		});
		
		$("#nombre").val("");
		$("#formEditar").css("font-size", "14px");
        hideMessage();
    });
    
    function buscarTextoTablaGeneral(texto) {
		$(".search-field").each(function(i, e) {
			item = $(e).attr("value").toLowerCase();
			$(e).css("display", (new RegExp(texto.toLowerCase())).test(item) ? "table-row" : "none");
		});
	}
</script>