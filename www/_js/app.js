isJson = function (item) {
	item = typeof item !== "string" ? JSON.stringify(item) : item;

	try {
		item = JSON.parse(item);
	} catch (e) {
		return false;
	}

	if (typeof item === "object" && item !== null) {
		return true;
	}

	return false;
};

decode_base64 = function (s) {
	var e = {}, i, k, v = [], r = '', w = String.fromCharCode;
	var n = [[65, 91], [97, 123], [48, 58], [43, 44], [47, 48]];

	for (z in n) {
		for (i = n[z][0]; i < n[z][1]; i++) {
			v.push(w(i));
		}
	}
	for (i = 0; i < 64; i++) {
		e[v[i]] = i;
	}

	for (i = 0; i < s.length; i += 72) {
		var b = 0, c, x, l = 0, o = s.substring(i, i + 72);
		for (x = 0; x < o.length; x++) {
			c = e[o.charAt(x)];
			b = (b << 6) + c;
			l += 6;
			while (l >= 8) {
				r += w((b >>> (l -= 8)) % 256);
			}
		}
	}
	return r;
};

logger = function (l, err) {
	if (typeof l == "object")
		return logObj(l, err);

	if (!err)
		err = "inf";

	// msg = moment().format("YYYY-MM-DD HH:mm:ss") + "| " + l;
	msg = moment().format("HH:mm:ss") + "| " + l;
	if (err == "dbg") {
		console.debug(msg);
	}
	if (err == "inf") {
		console.log(msg);
	}
	if (err == "wrn") {
		console.warn(msg);
	}
	if (err == "err") {
		console.error(msg);
	}
};

logObj = function (msg, err) {
	if (!err)
		err = "inf";

	if (err == "dbg") {
		console.debug(msg);
	}
	if (err == "inf") {
		console.log(msg);
	}
	if (err == "wrn") {
		console.warn(msg);
	}
	if (err == "err") {
		console.error(msg);
	}
};

function number_format(number, decimals, dec_point, thousands_sep) {
	// Strip all characters but numerical ones.
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number, prec = !isFinite(+decimals) ? 0 : Math.abs(decimals), sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, dec = (typeof dec_point === 'undefined') ? '.' : dec_point, s = '', toFixedFix = function (n, prec) {
		var k = Math.pow(10, prec);
		return '' + Math.round(n * k) / k;
	};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

var toastTimeout = null;
function toast(text) {
	$("#snackbar").html(text);

	if (!$("#snackbar").hasClass("show")) {
		$("#snackbar").addClass("show");
	}

	// After 3 seconds, remove the show class from DIV
	if (toastTimeout === null) {
		toastTimeout = setTimeout(function () {
			if (toastTimeout) {
				$("#snackbar").removeClass("show");
				toastTimeout = null;
			}
		}, 3000);
	}
};
var app = angular.module("myApp", ["ngCookies"]);
$(document).ready(function () {
	// Switch main page into view
	setTimeout(function () {
		$("#page-loading").hide();
		$("#page-loaded").removeClass("d-none").show();
	}, 500);

	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
	var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl);
	});
	//toast("Application has loaded sucessfully!!");
});
