<?php
if (!defined("LL_NONE")) {
	define("LL_NONE", 0);
	define("LL_SYSTEM", 0);
	define("LL_SYS", 0);
	define("LL_ERROR", 1);
	define("LL_ERR", 1);
	define("LL_WRN", 2);
	define("LL_WARN", 2);
	define("LL_WARNING", 2);
	define("LL_INF", 3);
	define("LL_INFO", 3);
	define("LL_DBG", 4);
	define("LL_DEBUG", 4);
	define("LL_EDBG", 5);
	define("LL_EDEBUG", 5);
	define("LL_XDBG", 6);
	define("LL_XDEBUG", 6);
}

if (!function_exists("mkpath")) {

	function mkpath($path) {
		$dirs = array();
		$path = preg_replace('/(\/){2,}|(\\\){1,}/', '/', $path); // only forward-slash
		$dirs = explode("/", $path);
		$path = "";
		foreach ($dirs as $element) {
			$path .= $element . "/";
			if (!is_dir($path)) {
				if (!@mkdir($path)) {
					echo "mkpath(): something went wrong at : " . $path . "\n";
					return false;
				}
			}
		}
		// echo("<B>".$path."</B> successfully created");
		return true;
	}
}

class Logger {

	function __construct($path = "/tmp/logs", $app_name = "") {
		$this->setLevel(LL_ERR);
		$this->strings = array();
		$this->strings[LL_SYS] = "SYS";
		$this->strings[LL_ERROR] = "ERR";
		$this->strings[LL_WARNING] = "WRN";
		$this->strings[LL_INFO] = "INF";
		$this->strings[LL_DEBUG] = "DBG";
		$this->strings[LL_EDEBUG] = "EDBG";
		$this->strings[LL_XDEBUG] = "XDBG";
		$this->log2String(true);
		$this->_fp = null;
		$this->_fn = null;
		$this->_path = null;

		if ($path !== null && $app_name !== null) {
			if (strlen($app_name)) {
				$path .= "/" . $app_name;
			}
			// echo "Logger starting at $path\n";

			$this->_path = $path;
			if ($this->_path) {
				mkpath($this->_path);
				@chmod($this->_path, 0777);
				$this->_fn = date("Ymd") . ".txt";
				$logfile = $path . "/" . $this->_fn;
				@touch($logfile);
				@chmod($logfile, 0666);
				$this->_fp = @fopen($path . "/" . $this->_fn, "a");
			}
		}

		if ($this->_path && !$this->_fp) {
			echo "<!-- Logger unable to write to file '" . $path . "/" . $this->_fn . "' -->\n";
		}
	}

	function clearLogs($days = 7) {
		// echo "Dir: " . $this->_path . "\n";
		$inc = array();
		// $inc [] = dirname ( __FILE__ ) . "/config.php";
		// $inc [] = dirname ( __FILE__ ) . "/config_override.php";
		$inc = array_merge($inc, includeDirectory($this->_path, "txt"));
		$tnow = time();
		foreach ($inc as $file) {
			if (file_exists($file) && !is_dir($file)) {
				$tfile = filemtime($file);
				$delta = $tnow - $tfile;
				if ($delta > numDays($days)) {
					unlink($file);
					// echo " deleted $file: (".durationFormat($delta)." old)\n";
					// } else {
					// echo " ignoring $file: (".durationFormat($delta)." old)\n";
				}
			}
		}
	}

	function setLevel($level) {
		$this->_level = $level;
		// echo "Logger::setLevel(".$this->_level.")\n";
		// debug_print_backtrace();
		// echo $this->_fn."- logging level: ".$this->_level."<br />";
	}

	function getLevel() {
		return $this->_level;
	}

	function log($level, $str) {
		// echo "Logger::log()\n";
		// echo " level: ".$level."\n";
		// echo " above: ".$this->_level."\n";
		if ($level > $this->_level) {
			// echo " skipping\n";
			// echo $this->_fn."- logging request too low level<br />";
			return false;
		}

		$lev = $this->strings[$level];
		$ts = date("H:i:s");
		// $un = "";
		// if (class_exists("tUsers")) {
		// $un = tUsers :: getLoggedInUser();
		// }
		// if (strlen($un) == 0) {
		// $un = "_NONE_";
		// }

		$str = str_replace("\n", "\r\n", $str);
		// $str = $ts . " ; " . $lev . " ; " . $un . " ; " . $str . "\r\n";
		$str = $ts . " ; " . $lev . " ; " . $str . "\r\n";
		echo trim($str) . "\n";

		if ($this->_fp) {
			fwrite($this->_fp, $str);
		}

		if ($this->log_to_string) {
			$this->log_string .= $str;
		}
	}

	function log2String($enable = true) {
		$this->log_to_string = $enable;
		if ($enable) {
			$this->log_string = "";
		}
	}

	function getString() {
		return $this->log_string;
	}

	function toString() {
		return $this->getString();
	}
}

global $log_dir, $log_level, $app_name;
if (strlen(@$log_dir) == 0) {
	$log_dir = "/tmp/logs";
}
$log_dir = null;
$logger = new Logger($log_dir, @$app_name);
$logger->setLevel($log_level);

// $logger->setLevel ( @ $log_level );
function logger($level, $str) {
	global $logger;
	// foreach ($logger as $ilog) {
	$logger->log($level, $str);
	// debug_print_backtrace();
	// }
}
