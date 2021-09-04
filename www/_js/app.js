$(document).ready(function() {
	// Switch main page into view
	$("#page-loading").hide();
	$("#page-loaded").show();
	//toast("Application has loaded sucessfully!!");
});

/*************************************************************************************************
 *
 */
/*
function formatMm(mm) {
	var l = "mm";
	if (mm >= 10) {
		mm = mm / 10;
		l = "cm";
	}
	if (mm >= 100) {
		mm = mm / 100;
		l = "m";
	}
	if (mm >= 1000) {
		mm = mm / 1000;
		l = "km";
	}
	return number_format(mm, 1) + l;
};
*/
isJson = function(item) {
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

function decode_base64(s) {
	var e = {}, i, k, v = [], r = '', w = String.fromCharCode;
	var n = [ [ 65, 91 ], [ 97, 123 ], [ 48, 58 ], [ 43, 44 ], [ 47, 48 ] ];

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

function xxlogger(str) {
};
logger = function(l, err) {
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

logObj = function(msg, err) {
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

/*
function colorLuminance(hex, lum) {

	// validate hex string
	hex = String(hex).replace(/[^0-9a-f]/gi, "");
	if (hex.length < 6) {
		hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
	}
	lum = lum || 0;

	// convert to decimal and change luminosity
	var rgb = "#", c, i;
	for (i = 0; i < 3; i++) {
		c = parseInt(hex.substr(i * 2, 2), 16);
		c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
		rgb += ("00" + c).substr(c.length);
	}
	//console.log("colorLuminance(" + hex + ", " + lum + "): returning '" + rgb + "'");

	return rgb;
};
*/
function number_format(number, decimals, dec_point, thousands_sep) {
	// Strip all characters but numerical ones.
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number, prec = !isFinite(+decimals) ? 0 : Math.abs(decimals), sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep, dec = (typeof dec_point === 'undefined') ? '.' : dec_point, s = '', toFixedFix = function(n, prec) {
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

/*
Array.prototype.random = function() {
	return this[Math.round((Math.random() * (this.length - 1)))];
};
*/
/***************
 * genKey()
 *
 * Generates an arbitary key that consists of the alpha-numeric and special character sets, but with confusing characters like';1', 'i', and 'l' removed
 *
 * Key is a charater string that defines the charaters and can consist of:
 * 	u - Upper case
 * 	l - lower case
 * 	n - number
 *  s - special character
 *  x - any of the above
 *
 *  for example genKey('unlllaaa') would produce 'E5ncyCgt'
 */
/*
function genKey(key) {
	var uc = [ 'A', 'B', 'C', 'E', 'F', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'T', 'W', 'Y', 'Z' ];
	var lc = [ 'a', 'b', 'd', 'e', 'g', 'h', 'k', 'n', 'p', 'q', 'r', 's', 't', 'x', 'y', 'z' ];
	var nc = [ '2', '3', '4', '5', '6', '7', '8', '9' ];
	var sc = [ '=', '-', '.', '_', '@' ];
	var an = [].concat(uc).concat(lc).concat(nc);
	var ny = [].concat(sc).concat(an);
	return key.replace(/[xlunas]/g, function(c) {
		return (c === 'u' ? uc.random() : (c === 'l' ? lc.random() : (c === 'n' ? nc.random() : (c === 's' ? sc.random() : (c === 'a' ? an.random() : ny.random())))));
	});
}
*/
var toastTimeout = null;
function toast(text) {
	xxlogger("updating toast text");
	$("#snackbar").html(text);

	if (!$("#snackbar").hasClass("show")) {
		xxlogger("showing toast");
		$("#snackbar").addClass("show");
	}

	// After 3 seconds, remove the show class from DIV
	if (toastTimeout === null) {
		toastTimeout = setTimeout(function() {
			if (toastTimeout) {
				xxlogger("Clearing toast");
				$("#snackbar").removeClass("show");
				toastTimeout = null;
			}
		}, 3000);
	}
};
/*
function navigateToUrl(url) {
	var f = document.createElement("FORM");
	f.action = url;

	var indexQM = url.indexOf("?");
	if (indexQM >= 0) {
		// the URL has parameters => convert them to hidden form inputs
		var params = url.substring(indexQM + 1).split("&");
		for (var i = 0; i < params.length; i++) {
			var keyValuePair = params[i].split("=");
			var input = document.createElement("INPUT");
			input.type = "hidden";
			input.name = keyValuePair[0];
			input.value = keyValuePair[1];
			f.appendChild(input);
		}
	}

	document.body.appendChild(f);
	f.submit();
}

function appguid() { // Public Domain/MIT
	var d = new Date().getTime();
	if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
		d += performance.now(); //use high-precision timer if available
	}
	return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
		var r = (d + Math.random() * 16) % 16 | 0;
		d = Math.floor(d / 16);
		return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
	});
};

function applog(str) {
	console.log(str);
	$("pre#debug-container").html($("pre#debug-container").html() + str + "\n");
};

function galog(str) {
	console.log(str);
};

function deg2rad(deg) {
	return deg * (Math.PI / 180);
};

function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
	var R = 6371; // Radius of the earth in km
	var dLat = deg2rad(lat2 - lat1); // deg2rad below
	var dLon = deg2rad(lon2 - lon1);
	var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	var d = R * c; // Distance in km
	return d;
};

function parseVcard(input) {
	var Re1 = /^(version|fn|title|bday|uid):(.+)$/i;
	var Re2 = /^([^:;]+);([^:]+):(.+)$/;
	var ReKey = /item\d{1,2}\./;
	var fields = {};

	input.split(/\r\n|\r|\n/).forEach(function(line) {
		var results, key;

		if (Re1.test(line)) {
			results = line.match(Re1);
			key = results[1].toLowerCase();
			fields[key] = results[2];
		} else if (Re2.test(line)) {
			results = line.match(Re2);
			key = results[1].replace(ReKey, '').toLowerCase();

			var meta = {};
			results[2].split(';').map(function(p, i) {
				var match = p.match(/([a-z]+)=(.*)/i);
				if (match) {
					return [ match[1], match[2] ];
				} else {
					return [ "TYPE" + (i === 0 ? "" : i), p ];
				}
			}).forEach(function(p) {
				meta[p[0]] = p[1];
			});

			if (!fields[key]) {
				fields[key] = [];
			}

			fields[key].push({
			meta : meta,
			value : results[3].split(';')
			});
		}
	});

	return fields;
};
*/
/*
                 _     _                                  _        __        __         _
  _ __ ___  __ _(_)___| |_ ___ _ __   ___  ___ _ ____   _(_) ___ __\ \      / /__  _ __| | _____ _ __
 | '__/ _ \/ _` | / __| __/ _ \ '__| / __|/ _ \ '__\ \ / / |/ __/ _ \ \ /\ / / _ \| '__| |/ / _ \ '__|
 | | |  __/ (_| | \__ \ ||  __/ |    \__ \  __/ |   \ V /| | (_|  __/\ V  V / (_) | |  |   <  __/ |
 |_|  \___|\__, |_|___/\__\___|_|    |___/\___|_|    \_/ |_|\___\___| \_/\_/ \___/|_|  |_|\_\___|_|
           |___/
*/
/*
if ('serviceWorker' in navigator) {
	window.addEventListener('load', function() {
		try {
			navigator.serviceWorker.register('/service-worker.js').then(function(registration) {
				console.log('serviceWorker(): registration successful ');
			});
		} catch (e) {
			console.error('serviceWorker(): registration failed: ', err);
		}
	});
}
*/
/*
  _                     _ _        _   _                      ____                    _           _        _ _
 | |__   __ _ _ __   __| | | ___  | | | | ___  _ __ ___   ___|  _ \ __ _  __ _  ___  (_)_ __  ___| |_ __ _| | |
 | '_ \ / _` | '_ \ / _` | |/ _ \ | |_| |/ _ \| '_ ` _ \ / _ \ |_) / _` |/ _` |/ _ \ | | '_ \/ __| __/ _` | | |
 | | | | (_| | | | | (_| | |  __/ |  _  | (_) | | | | | |  __/  __/ (_| | (_| |  __/ | | | | \__ \ || (_| | | |
 |_| |_|\__,_|_| |_|\__,_|_|\___| |_| |_|\___/|_| |_| |_|\___|_|   \__,_|\__, |\___| |_|_| |_|___/\__\__,_|_|_|
                                                                         |___/
*/
/*
window.addEventListener('beforeinstallprompt', function(e) {
	//https://developers.google.com/web/updates/2015/03/increasing-engagement-with-app-install-banners-in-chrome-for-android
	e.userChoice.then(function(choiceResult) {
		applog("webappInstall(): outcome: " + choiceResult.outcome);
		if (choiceResult.outcome == 'dismissed') {
			// send alert to server
			applog('webappInstall(): User cancelled home screen install');
		} else {
			// send sucess to server
			applog('webappInstall(): User added to home screen');
		}
	});
});
*/
var app = angular.module("myApp", [ "ngCookies" ]);

/*
app.config([ "$locationProvider", "$routeProvider", function($locationProvider, $routeProvider) {
	$locationProvider.html5Mode(true);

	$routeProvider.when('/', {
		templateUrl : '/pages/home.php'
	}).when('/signup', {
		templateUrl : '/pages/signup.php',
		controller : 'SignupCtrl'
	}).when('/about', {
		templateUrl : '/pages/about.php',
		controller : 'AboutCtrl'
	}).when('/merch', {
		templateUrl : '/pages/merch.php',
		controller : 'MerchCtrl'
	}).when('/privacy', {
		templateUrl : '/pages/privacy.php',
		controller : 'PrivacyCtrl'
	}).when('/terms', {
		templateUrl : '/pages/terms.php'
	}).otherwise({
		templateUrl : '/pages/404.php'
	});
} ]);
*/