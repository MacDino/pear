<?php
class MI_Performance {
	
	const OPTION = 'runtimelogs';
	const NOTE = 'RUNTIMELOG';
	
	private static $startTime = NULL;
	private static $url = NULL;
	private static $errorinfo = array();
	private static $folder = '';
	
	/**
	 * 挂载执行时间监控
	 * @param String $forder
	 * @throws Exception
	 * @return boolean
	 */
	public static function init($folder = NULL) {
		try {
			if (!$folder) {
				throw new Exception('参数错误,请指定项目的名称!', 71);
			}
			self::$folder = $folder;
			self::$startTime = microtime(true);
			self::$url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . http_build_query($_REQUEST);
			register_shutdown_function(array('MI_Performance' , '_end'));
			return TRUE;
		} catch ( Exception $e ) {
			self::$errorinfo = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return FALSE;
		}	
	}
	/**
	 * 获取错误信息
	 * @return Array:
	 */
	public static function getError() {
		return self::$errorinfo;
	}
	
	public static function _end() {
		try {
			$runTime = microtime(true) - self::$startTime;
			$content = self::$url . "|" . $runTime . "|" . self::getResponLevel($runTime);				
			if (!MI_Logs::init(self::$folder, self::OPTION) || !MI_Logs::writeLog(self::NOTE, $content)) {
				throw new Exception('写入日记文件失败!', 72);
			}
			return TRUE;
		} catch ( Exception $e ) {	
			self::$errorinfo = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return FALSE;
		}
	}
	
	private static function getResponLevel($rtime) {
		
		if ($rtime <= 0.01)
			return "A";
		
		if ($rtime <= 0.02)
			return "B";
		
		if ($rtime <= 0.04)
			return "C";
		
		if ($rtime <= 0.05)
			return "D";
		
		if ($rtime > 0.05)
			return "E";
	
	}

}


