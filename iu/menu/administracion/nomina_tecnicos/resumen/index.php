<?php
	define("iC", true);
	require_once (dirname(__FILE__) . "/../../../../../conf/config.php");
	Aplicacion::validarAcceso(10);
?>
<table style='margin-top:4px;width:100%'>
	<tr>
		<td valign="top" style="width: 250px;">
			<div style='padding: 4px;'>
				<table style="font-size:12px; color:black; background-color:#EFEFEF; border:1px solid #dedede;">
					<tr style="color:white;" class="ui-widget-header">
						<td colspan="7" style='padding: 4px;'><i class='icon-search icon-white'></i><b> FILTRAR</b></td>
					</tr>
					<tr>
						<td style="padding-left:12px;padding-top:12px;padding-bottom:12px; width:250px;">
							<b>Periodo:</b><br />
							<div id="reportrange2" class="pull-center" style="background: transparent; cursor: pointer; padding: 5px 10px; right: auto; border: 1px solid rgba(255, 255, 255, 0.3); width: 100%">
							    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
							    <span></span> <b class="caret"></b>
							</div>
						</td>
						<td style="padding-top:12px;padding-bottom:12px;padding-right:12px;" valign="bottom">
							<button onclick="descargarResumenExcel();" title="Descargar en formato Excel" class='btn btn-success'><i class='icon-download-alt icon-white' /></i></button>
						</td>
					</tr>
					<!--<tr>
						<td align="right" colspan="2" style="padding:12px; padding-bottom:9px; border-bottom: 1px dashed black;">

						</td>
					</tr>-->
				</table>
			</div>
		</td>
	</tr>
	<tr>
		<td valign="top" style="padding:4px;">
			<div id='data_resumen'></div>
		</td>
	</tr>
</table>
<script type="text/javascript" charset="ISO-8859-1">
	$(document).ready(function() {
		$(".datetimepicker").remove();
		hideMessage();
	});
	
	var informe_ini = moment().startOf('month');
	var informe_fin = moment().endOf('month');
	
	function cb2(start, end) {
    	informe_ini = start;
    	informe_fin = end;
    	
        $('#reportrange2 span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        
        f1 = start.format('YYYY-MM-DD');
		f2 = end.format('YYYY-MM-DD');
		showMessage();
		$("#data_resumen").load("<?php echo DIR_WEB; ?>get_resumen.php?noConvertir&fecha1=" + f1 + "&fecha2=" + f2, function () {
			hideMessage();
		});
	}
	
	var elmes = 'mes';
    $('#reportrange2').daterangepicker({
        startDate: informe_ini,
        endDate: informe_fin,
        ranges: {
           'Este mes': [moment().startOf('month'), moment().endOf('month')],
           'El mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
           'Hace 2 meses' : [moment().subtract(2, 'month').startOf('month'), moment().subtract(2, 'month').endOf('month')],
           'Hace 3 meses': [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')],
           'Hace 4 meses': [moment().subtract(4, 'month').startOf('month'), moment().subtract(4, 'month').endOf('month')],
           'Hace 5 meses': [moment().subtract(5, 'month').startOf('month'), moment().subtract(5, 'month').endOf('month')],
           'Hace 6 meses': [moment().subtract(6, 'month').startOf('month'), moment().subtract(6, 'month').endOf('month')]
        }
    }, cb2);
    
	cb2(informe_ini, informe_fin);
	$(".caret").css("vertical-align", "middle");

	function descargarResumenExcel() {
    	
        $('#reportrange span').html(informe_ini.format('MMMM D, YYYY') + ' - ' + informe_fin.format('MMMM D, YYYY'));
        
        f1 = informe_ini.format('YYYY-MM-DD');
		f2 = informe_fin.format('YYYY-MM-DD');
		
		showMessage();
		document.location.href="<?php echo DIR_WEB; ?>excel.php?noConvertir"
				+ "&fecha1=" + f1
				+ "&fecha2=" + f2;
		setTimeout(function () {
			$.post("index.php", { sync : "true"}, function(r) { hideMessage(); })
		}, 2000);
	}
</script>