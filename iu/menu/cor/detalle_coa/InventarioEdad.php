<?php
	define("iC", true);
	require_once(dirname(__FILE__) . "/../../../../conf/config.php");
    Aplicacion::validarAcceso(10);

    $edad = isset($_GET["edad"]) ? $_GET["edad"] : die("Error en el parámetro edad");
    if (!in_array($edad, array('0M-12M', '12M-24M', '24M-MAS')))
        die("Parámetro edad incorrecto");

    $tipos_vehiculo = array("liviano", "pesados", "");
    $tipo_vehiculo = isset($_GET["tipo"]) ? $_GET["tipo"] : "";
    if (!in_array($tipo_vehiculo, $tipos_vehiculo)) die("Error al obtener el tipo de vehículo");

    $informe = Informe::getInstance();
    $descripcion = "Inventario $edad / GM";
?>
<div style='margin-bottom:5px; font-size:15px;'>
<b>Buscar:</b> <input onkeyup="buscarTextoTablaGeneral(this.value);" class="ui-corner-all ui-state-default" id='txt_buscar' type="text" name='' value='' style='padding:2px; font-weight: normal; width: 400px; font-size:15px;' placeholder="Escriba una parte del texto a buscar">
</div> 
<div style='overflow-y: scroll; max-height: 400px;'>
    <table class="table table-condensed table-hover table-bordered" style='font-size:12px;'>
        <thead>
            <tr class='ui-widget-header'>
                <td>Código</td>
                <td>Grupo</td>
                <td>Bod</td>
                <td>Descripción</td>
                <td>STOCK</td>
                <td>COSTO</td>
                <td>TOTAL</td>
                <td>Precio</td>
                <td>Calif.ABC</td>
                <td>UltEntrada</td>
                <td>UltSalida</td>
                <td>Edad</td>
            </tr>
        </thead>
        <tbody>
		<?php
			$x = 0;
            $colores = array("#efefef", "#cde1ff");
            $total = 0;
			foreach($informe->getInventariosData($edad, $tipo_vehiculo, true) as $data) {
                $total += $data["xtotal"];
                echo "<tr class='search-field' value='" . $data["codigo"] . " " . $data["descripcion"] . " " . $data["calificacion_abc"] . "'>";
                echo "<td>" . $data["codigo"] . "</td>";
                echo "<td>" . $data["nombre_grupo"] . "</td>";
                echo "<td>" . $data["bodega"] . "</td>";
                echo "<td>" . $data["descripcion"] . "</td>";
                echo "<td style='text-align:center;'>" . $data["stock"] . "</td>";
                echo "<td style='text-align:right;'>$" . Moneda::getMoneda($data["costo_promedio"], 0) . "</td>";
                echo "<td style='text-align:right;'><b>$" . Moneda::getMoneda($data["xtotal"], 0) . "</b></td>";
                echo "<td style='text-align:right;'>$" . Moneda::getMoneda($data["precio"], 0) . "</td>";
                echo "<td style='text-align:center;'>" . $data["calificacion_abc"] . "</td>";
                echo "<td>" . Fecha::getFechaCorta($data["fec_ultima_entrada"], "d-F-Y") . "</td>";
                echo "<td>" . Fecha::getFechaCorta($data["fec_ultima_salida"], "d-F-Y") . "</td>";
                echo "<td>" . $data["edad"] . "</td>";
                echo "</tr>";
            }
		?>
        </tbody>
        <tfoot>
            <tr class='ui-widget-header'>
                <td colspan=6 style='text-align:right;'><b>Total</b></td>
                <td style='text-align:right;'><b>$<?php echo Moneda::getMoneda($total, 0); ?></b></td>
                <td colspan=5></td>
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