<?php
class MI_ErrorLog {
	
	const OPTION = 'errorlog';
	
	private static $errorInfo = array();
	private static $folder = NULL;
	private static $level = "E";
	
	public static function init($folder = NULL, $level = 'E') {
		try {
			if (!$folder) {
				throw new Exception('参数错误,请指定项目的名称!', 81);
			}
			self::$folder = $folder;
			self::$level = $level;
			set_error_handler(array('MI_ErrorLog', '_errorHandler'), E_ALL);
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return false;
		}
	}
	
	public static function _errorHandler($errno, $errstr, $errfile, $errline) {
		try {
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		} catch ( Exception $e ) {	
			if ( $errno > self::_errorLevel(self::$level)) {
				return TRUE;
			}
	
			$backtrace = $e->getTrace();
			$traceInfo = '';			
			$traceInfo .=  "\n" . $errstr . "\nFile: $errfile (Line: " . $errline . ")\n";
			$traceInfo .="HttpArgs: {" . http_build_query($_REQUEST) . "}\n";		
			for($i = count($backtrace) - 1; $i > 0; -- $i) {	
				if(is_array($backtrace[$i]) && $backtrace[$i]['args'] != NULL) {
					$traceInfo .= "Function-Args: ". $backtrace[$i]['function'].'(';
					$traceInfo .= json_encode($backtrace[$i]['args']) . ")\n";
				}	
			}		
			$traceInfo .= "Trace:\n" . $e->getTraceAsString() . "\n";
			MI_Logs::wlog(self::$folder, self::OPTION, self::_errorType($errno), $traceInfo);
			return TRUE;
		}
	}
	
	public static function getError() {
		return self::$errorInfo;
	}
	
	private static function _errorType($type) {
		switch ($type) {
			case E_ERROR : // 1 //
				return 'E_ERROR';
			case E_WARNING : // 2 //
				return 'E_WARNING';
			case E_PARSE : // 4 //
				return 'E_PARSE';
			case E_NOTICE : // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR : // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING : // 32 //
				return 'E_CORE_WARNING';
			case E_CORE_ERROR : // 64 //
				return 'E_COMPILE_ERROR';
			case E_CORE_WARNING : // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR : // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING : // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE : // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT : // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR : // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED : // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED : // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return "";
	}
	
	/**
	 * 返回显示错误的级别
	 * @param String $level
	 * @return int
	 */
	private static function _errorLevel($level) {
		switch ($level) {
			case 'A' : // E_ALL //
				return E_ALL;
			case 'W' : // E_WARNING //
				return E_WARNING;
			case 'E' : // E_ERROR //
				return E_ERROR;
			default :
				return E_ERROR;
		}
	}
	
	private static function _clearError() {
		self::$errorInfo = NULL;
	}
}


