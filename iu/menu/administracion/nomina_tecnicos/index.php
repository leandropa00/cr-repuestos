<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../conf/config.php");
	Aplicacion::validarAcceso(10);
?>
<div id="tabs-nomina-tecnicos">
    <ul>
        <li><a href="<?php echo DIR_WEB . "personal/index.php"; ?>">Personal</a></li>
        <li><a href="<?php echo DIR_WEB . "liquidacion/index.php"; ?>">Liquidación</a></li>
        <li><a href="<?php echo DIR_WEB . "resumen/index.php"; ?>">Resumen</a></li>
    </ul>
</div>
<script>
    $(document).ready(function() {
        $("#tabs-nomina-tecnicos").tabs({ selected: 0 }).css("border", "none");
    });
</script>