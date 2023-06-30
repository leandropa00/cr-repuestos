
<?php
define("iC", true);
require_once(dirname(__FILE__) . "/../../../../conf/config.php");
Aplicacion::validarAcceso(10);

$tipos = array("liviano", "pesados");
$tipo_vehiculo = isset($_GET["tipo"]) ? $_GET["tipo"] : "";
if (!in_array($tipo_vehiculo, $tipos)) die("Error al obtener el tipo de vehículo");

$informe = Informe::getInstance();
$descripcion = "Alistamiento y Peritaje: " . (($tipo_vehiculo == "liviano") ? "LIVIANOS" : "PESADOS");
?>
<div style='margin-bottom:5px; font-size:15px;'>
    <b>Buscar:</b> <input onkeyup="buscarTextoTablaGeneral(this.value);" class="ui-corner-all ui-state-default" id='txt_buscar' type="text" name='' value='' style='padding:2px; font-weight: normal; width: 400px; font-size:15px;' placeholder="Escriba una parte del texto a buscar">
</div>
<div style='overflow-y: scroll; max-height: 400px;'>
    <table class="table table-condensed table-hover table-bordered" style='font-size:12px;'>
        <thead>
            <tr class='ui-widget-header'>
                <td>Numero Orden</td>
                <td>Bodega</td>
                <td>Sucursal</td>
                <td>VIN</td>
                <td>Descripción Modelo</td>
                <td>Modelo Año</td>
                <td>Placa</td>
                <td>Tipo Vehiculo</td>
                <td>Alistamiento y Peritaje</td>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($informe->getMecanicaAlistamientoData($tipo_vehiculo) as $data) {
                    echo "<tr class='search-field' value='" . $data["numero_orden"] . "-" . $data["bodega"] . "-" . $data["sucursal"] . "-" . $data["vin"] . "-". $data["descripcion_modelo"] . "-" . $data["modelo_ano"] . "-" . $data["placa"] ."-". $data["tipo_vehiculo"] . "'>";
                    echo "<td>" . $data["numero_orden"] . "</td>";
                    echo "<td>" . $data["bodega"] . "</td>";
                    echo "<td>" . $data["sucursal"] . "</td>";
                    echo "<td>" . $data["vin"] . "</td>";
                    echo "<td>" . $data["descripcion_modelo"] . "</td>";
                    echo "<td>" . $data["modelo_ano"] . "</td>";
                    echo "<td>" . $data["placa"] . "</td>";
                    echo "<td>" . $data["tipo_vehiculo"] . "</td>";
                    echo "<td>" . $data["alistamiento_y_peritajes"] . "</td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<script type='text/javascript'>
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
            open: function() {
                var t = $(this).parent(),
                    w = $(document);
                t.offset({
                    top: 60,
                    left: (w.width() / 2) - (t.width() / 2)
                });
            },
            close: function() {
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