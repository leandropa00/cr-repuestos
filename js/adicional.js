var app_cargando = "<div style='float:left'><img src='imagenes/cargando.gif' alt=''></div><div>&nbsp;<b>Cargando...</b></div>";
var SEPARADOR_MILES = ".";
function solo_numerosFloat(e) {
	tecla=(document.all) ? e.keyCode : e.which;
	return ((tecla >=48 && tecla <= 58) || tecla == 8 || tecla == 13 || tecla==0 || tecla==SEPARADOR_MILES.charCodeAt(0));
}

function limpiar(val) {
	return val.replace(/\$|\.|%/g , "");
}

function solo_numeros(e) {
	tecla=(document.all) ? e.keyCode : e.which;
	return ((tecla >=48 && tecla <= 58) || tecla == 8 || tecla == 13 || tecla==0);
}

function solo_letras(e) {
	tecla=(document.all) ? e.keyCode : e.which;
	return (!(tecla >=48 && tecla <= 58) || tecla == 8 || tecla == 13 || tecla==0);
}

function roundNumber(num, dec) {
	var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
	return result;
}

function menu_cerrarSesion() {
	Swal.fire({
		title : 'Cerrar sesión',
		text : '¿Confirma que desea cerrar la aplicación?',
		type : 'question',
		showCancelButton : true,
		confirmButtonText: 'Aceptar',
		cancelButtonText: 'Cancelar'
	}).then(function (res) {
		if (res.value) {
			showMessage();
			localStorage.clear();
			$.post("cerrar.php", function () {
				document.location.href = 'cerrar.php';
			});
		}
	});
}

function solo_pesos(e) {
	return solo_numeros(e);
}

function getValor(recibe) {
	var numero = "";
	var cadena = " " + recibe + "";
	var x = 0;
	for (x = 0; x < cadena.length; x++) {
		if (cadena.charAt(x) == '$' || cadena.charAt(x) == SEPARADOR_MILES || cadena.charAt(x) == ' ')
			continue;
		
		//ARREGLAR.. NO FUNCIONA CON INTERNET EXPLORER
		numero = numero + "" + cadena.charAt(x) + "";
	}
	return redondearNumero(numero, 0) ;
}

function redondearNumero(num, ndec) {
	var fact = Math.pow(10, ndec);
	return Math.round(num * fact) / fact;
}

function str_pad (input, pad_length, pad_string, pad_type) {
    var half = '',
        pad_to_go;
    var str_pad_repeater = function (s, len) {
        var collect = '', i;
        while (collect.length < len)
            collect += s;
        collect = collect.substr(0, len);
        return collect;
    };

    input += '';
    pad_string = pad_string !== undefined ? pad_string : ' ';
    if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH')
        pad_type = 'STR_PAD_RIGHT';
    if ((pad_to_go = pad_length - input.length) > 0) {
        if (pad_type == 'STR_PAD_LEFT')
            input = str_pad_repeater(pad_string, pad_to_go) + input;
        else if (pad_type == 'STR_PAD_RIGHT')
            input = input + str_pad_repeater(pad_string, pad_to_go);
        else if (pad_type == 'STR_PAD_BOTH') {
            half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
            input = half + input + half;
            input = input.substr(0, pad_length);
		}
    }
    return input;
}

function getPesos(numero) {
	numero  = getValor(numero);
	var x = 0;
	var cont = 0;
	var resultado = "";
	numero = "" + numero + "";
	for (x = numero.length - 1; x >= 0; x--) {
		if (cont % 3 == 0 && cont != 0)
			resultado = SEPARADOR_MILES  + resultado;
		resultado = numero.charAt(x) + resultado;
		cont++;
	}
	return "$" + resultado;
}


/* Date Format 1.2.2
 * (c) 2007-2008 Steven Levithan <stevenlevithan.com>
 * MIT license
 * Includes enhancements by Scott Trenda <scott.trenda.net> and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */
var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (val, len) {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};

	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && (typeof date == "string" || date instanceof String) && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date();
		if (isNaN(date)) throw new SyntaxError("invalid date");

		mask = String(dF.masks[mask] || mask || dF.masks["default"]);

		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}

		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};

		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
}();

// Some common format strings
dateFormat.masks = {
	"default":      "ddd mmm dd yyyy HH:MM:ss",
	shortDate:      "m/d/yy",
	mediumDate:     "mmm d, yyyy",
	longDate:       "mmmm d, yyyy",
	fullDate:       "dddd, mmmm d, yyyy",
	shortTime:      "h:MM TT",
	mediumTime:     "h:MM:ss TT",
	longTime:       "h:MM:ss TT Z",
	isoDate:        "yyyy-mm-dd",
	isoTime:        "HH:MM:ss",
	isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
	isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		"Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sáb",
		"Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"
	],
	monthNames: [
		"Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic",
		"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
	]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
	return dateFormat(this, mask, utc);
};

function showMessage() {
	$.blockUI({ 
		message: '<center><table style="color:black;margin-top:15px;margin-bottom:15px;"><tr><td width=50 valign=middle><img src="imagenes/ajax-loader2.gif" /></td><td style="font-size:18px;" valign="middle">Un momento por favor...</td></tr></table></center>',
		baseZ: 99999
	});
}

function hideMessage() {
	$.unblockUI();
}
$.blockUI.defaults.baseZ = 99999;
function str_pad (input, pad_length, pad_string, pad_type) {
    var half = '',
        pad_to_go;
    var str_pad_repeater = function (s, len) {
        var collect = '', i;
        while (collect.length < len)
            collect += s;
        collect = collect.substr(0, len);
        return collect;
    };

    input += '';
    pad_string = pad_string !== undefined ? pad_string : ' ';
    if (pad_type != 'STR_PAD_LEFT' && pad_type != 'STR_PAD_RIGHT' && pad_type != 'STR_PAD_BOTH')
        pad_type = 'STR_PAD_RIGHT';
    if ((pad_to_go = pad_length - input.length) > 0) {
        if (pad_type == 'STR_PAD_LEFT')
            input = str_pad_repeater(pad_string, pad_to_go) + input;
        else if (pad_type == 'STR_PAD_RIGHT')
            input = input + str_pad_repeater(pad_string, pad_to_go);
        else if (pad_type == 'STR_PAD_BOTH') {
            half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
            input = half + input + half;
            input = input.substr(0, pad_length);
		}
    }
    return input;
}

function mensaje(titulo, texto, tipo, callback) {
	callback = callback || function() {};
	Swal.fire({
		title : titulo,
		html : texto,
		type : tipo,
		confirmButtonText : 'Aceptar'
	}).then(callback);
}

function mensajeTimer(titulo, texto, tipo, mstimer, callback) {
	callback = callback || function() {};
	Swal.fire({
		title : titulo,
		html : texto,
		type : tipo,
		confirmButtonText : 'Aceptar',
		timer: mstimer
	}).then(callback);
}

function calcularEdad(div, fecha) { $("#" + div).html(moment().diff(fecha, 'years') + " años"); }

function scroll(x) {
	$("HTML, BODY").animate({ scrollTop: x }, 1000);
}


/*
var options = {
	enableHighAccuracy: true,
	timeout: 5000,
	maximumAge: 0
  };
  
function success(pos) {
var crd = pos.coords;

console.log('Your current position is:');
console.log('Latitude : ' + crd.latitude);
console.log('Longitude: ' + crd.longitude);
console.log('More or less ' + crd.accuracy + ' meters.');
};

function error(err) {
console.warn('ERROR(' + err.code + '): ' + err.message);
};

navigator.geolocation.getCurrentPosition(success, error, options);*/