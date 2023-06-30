<?php
	define("iC", true);
	require_once("conf/config.php");
	$version = 1;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title><?php echo htmlentities(NOMBRE_APP); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css" />
		<link rel="stylesheet" href="css/jquery/themes/vtc/ui.all.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/jquery/ui.jqgrid.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/jquery/ui.multiselect.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/touchspin.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/menu.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/jquery.alerts.css" type="text/css" media="screen" />
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic,300italic">
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700,400italic,300italic">
		<link rel="stylesheet" href="css/general.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/daterangepicker.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">

		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.js"></script>
        <script src="js/jquery-ui-1.8.2.custom.min.js"></script>
		<script src="js/jquery/jqGrid/i18n/grid.locale-es.js" type="text/javascript"></script>
		<script src="js/jquery.jqGrid.js"></script>
		<script src="js/jquery.steps.js"></script>
		<script src="js/sweetalert2.js"></script>
		<script type="text/javascript" src="js/jquery/ajaxfileupload.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.center.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.alerts2.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.autocomplete.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.tools.min.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.forms.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.hoverIntent.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.layout.min.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.mbMenu.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.metadata.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.uniform.min.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.validate.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.uploader.js"></script>

		<script type="text/javascript" src="js/jquery/jquery.maskMoney.js"></script>
		<script type="text/javascript" src="js/jquery/ui.datepicker-es.js"></script>
		<script type="text/javascript" src="js/moment.min.js"></script>
		<script type="text/javascript" src="js/es.js"></script>
		<!--<script type="text/javascript" src="js/hcharts.js"></script>-->

		<script type="text/javascript" src="js/jquery/jquery.daterangepicker.js"></script>
		<script type="text/javascript" src="js/jquery/zbootstrap-datetimepicker.min.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.blockUI.js"></script>
		<script type="text/javascript" src="js/adicional.js?<?php echo time(); ?>"></script>
		<script type="text/javascript" src="js/jquery/jquery.touchspin.js"></script>
		<script type="text/javascript" src="js/jquery/jquery.ui.touch-punch.min.js"></script>

		<script>
			setInterval(function() { $.post("index.php", { sync : "true"}, function(r) { }) },50000);
		</script>
	</head>
	<body class='background-theme'>
		<div id="aplicacion_web">
			<?php
				if (Aplicacion::getUser()->id <= 0)
					require_once(IU . "login.php");
				else
					require_once(IU . "validar_perfil.php");
			?>
		</div>
		<div class="tooltip"></div> 
		<div id='ventana' style='display:none;'>&nbsp;</div>
		<div id='ventana2' style='display:none;'>&nbsp;</div>
		<div id='ventana3' style='display:none;'>&nbsp;</div>
		<div id='ventana4' style='display:none;'>&nbsp;</div>
		<div id='ventana5' style='display:none;'>&nbsp;</div>
	</body>
</html>