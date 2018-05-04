<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp;

/*
	Sample:
		trigger_error("error_message", E_USER_NOTICE);
		throw new Exception("error_message", E_USER_ERROR);
*/

/**
 * Class ErrorHandler
 */
class ErrorHandler {
	private $handler = [];

	public function __construct($handler) {
		set_exception_handler(array($this, 'exception_handler'));
		set_error_handler(array($this, 'error_handler'));
		register_shutdown_function(array($this, 'fatal_handler'));

		if ( $handler ) {
			$handler($this);
		}
	}

	public function push($handler) {
		$this->handler[] = $handler;
	}

	private function errno_str($errno) {
		switch ($errno) {
			case E_ERROR:               $errtype = 'E_ERROR';             break;
			case E_WARNING:             $errtype = 'E_WARNING';           break;
			case E_PARSE:               $errtype = 'E_PARSE';             break;
			case E_NOTICE:              $errtype = 'E_NOTICE';            break;
			case E_CORE_ERROR:          $errtype = 'E_CORE_ERROR';        break;
			case E_CORE_WARNING:        $errtype = 'E_CORE_WARNING';      break;
			case E_COMPILE_ERROR:       $errtype = 'E_COMPILE_ERROR';     break;
			case E_COMPILE_WARNING:     $errtype = 'E_COMPILE_WARNING';   break;
			case E_USER_ERROR:          $errtype = 'E_USER_ERROR';        break;
			case E_USER_WARNING:        $errtype = 'E_USER_WARNING';      break;
			case E_USER_NOTICE:         $errtype = 'E_USER_NOTICE';       break;
			case E_STRICT:              $errtype = 'E_STRICT';            break;
			case E_RECOVERABLE_ERROR:   $errtype = 'E_RECOVERABLE_ERROR'; break;
			case E_DEPRECATED:          $errtype = 'E_DEPRECATED';        break;
			case E_USER_DEPRECATED:     $errtype = 'E_USER_DEPRECATED';   break;
			default:                    $errtype = 'E_EXCEPTION';         break;
		}
		return $errtype;
	}

	private function logstring($errno, $errstr, $errfile, $errline) {
		$errtype = $this->errno_str($errno);
		$time    = date('Y-m-d H:i:s');
		return "$time - $errtype $errstr [$errfile] on line $errline\n";
	}
	
	public function exception_handler($e) {
		$this->log($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
	}

	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
		$errtrace = debug_backtrace();
		array_shift($errtrace);
		array_shift($errtrace);

		$this->log($errno, $errstr, $errfile, $errline, $errtrace);
	}

	public function fatal_handler() {
		$error = error_get_last();
		if ( $error !== NULL ) {
			$errno   = $error["type"];
			$errfile = $error["file"];
			$errline = $error["line"];
			$errstr  = $error["message"];
			$this->log($errno, $errstr, $errfile, $errline, '');
			sos_exit();
		}
	}

	private function log($errno, $errstr, $errfile, $errline, $trace) {
		$msg = $this->logstring($errno, $errstr, $errfile, $errline);
		foreach ( $this->handler as $handler ) {
			$handler->log($errno, $errstr, $errfile, $errline, $trace, $msg);
		}

		// Error no to exit
		if ( $errno == E_USER_ERROR || $errno == E_ERROR ) {
			sos_exit();
		}
	}
}