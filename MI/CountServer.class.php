<?php

error_reporting(E_ALL);
class CountServer {
	// 初始化计数器时指定的数值
	const COUNTER_INIT_VAL = 0;
	
	private static $redis  = NULL;
	private static $res_id = NULL;
	private static $errorInfo = array('code' => NULL, 'port' => NULL);
	
	public static function init($REDIS_CONFIG_INFO = NULL) {
		try {
			$tempConfig = array();
			$tempConfig = $REDIS_CONFIG_INFO;
			if (!is_array($REDIS_CONFIG_INFO) or !$REDIS_CONFIG_INFO["host"] or !$REDIS_CONFIG_INFO["port"]) {
                                throw new Exception('您传递的参数不正确!', 51);
                        }
			self::$res_id = md5($REDIS_CONFIG_INFO['host'] . $REDIS_CONFIG_INFO['port']);
					
			if (isset(self::$redis[self::$res_id]) ||!is_object(self::$redis[self::$res_id])) {
				self::$redis[self::$res_id] = new Redis();
				$conn = self::$redis[self::$res_id]->connect($tempConfig['host'], $tempConfig['port']);
				if ($conn === FALSE) {
					self::$redis[self::$res_id] = NULL;
					throw new Exception(self::$redis[self::$res_id]->getLastError(), 52);
				}
			}
			self::$redis[self::$res_id]->clearLastError();
			return self::$redis[self::$res_id];
			
		} catch (Exception $e){
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}		
	}
	
	public static function getCountByKey($count_key, $auto_create = TRUE) {
		try {
			if (!$count_key) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->get($count_key);
			if ($val === FALSE && !$auto_create) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			} else if (!$val) {
				if (self::setCountByKey($count_key, self::COUNTER_INIT_VAL)) {
					$val = self::COUNTER_INIT_VAL;
				} else {
					return FALSE;
				}
			}
			return $val;		
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	
	}
	
	public static function mgetCountByArr($arr = NULL) {
		try {
			if (is_null($arr) || !is_array($arr)) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->mGet($arr);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return $val;
		} catch (Exception $e) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
		
	}
	
	public static function setCountByKey($count_key, $value) {
		try {
			if (!$count_key || intval($value) < 0) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->set($count_key, $value);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return TRUE;
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	}
	
	public static function msetCountByArr($arr = NULL) {
		try {
			if (is_null($arr) || !is_array($arr)) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->mset($arr);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return TRUE;
		} catch (Exception $e) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
		
	}
	
	public static function incCountBykey($count_key) {
		try {
			if (!$count_key) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->incr($count_key);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return $val;
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	}
	
	public static function incByCountBykey($count_key, $offset = 1) {
		try {
			if (!$count_key && intval($offset) <= 0) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->incrBy($count_key);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return $val;
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	}
	
	public static function decCountByKey($count_key, $offset = 1) {
		try {
			if (!$count_key) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->decr($count_key);			
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return $val;
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	}
	
	public static function decByCountByKey($count_key, $offset = 1) {
		try {
			if (!$count_key || !is_numeric($offset) || intval($offset) < 0) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->incrBy($count_key);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return $val;
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	}
	
	public static function delCountByKey($count_key) {
		try {
			if (!$count_key) {
				throw new Exception('您传递的参数不正确!', 51);
			}
			
			$val = self::$redis[self::$res_id]->del($count_key);
			if ($val === FALSE) {
				throw new Exception(self::$redis[self::$res_id]->getLastError(), 53);
			}
			return $val;
		} catch ( Exception $e ) {
			self::$errorInfo = array('code' => $e->getCode(), 'port' => $e->getMessage());
			return FALSE;
		}
	}
	
	public static function resetCountByKey($count_key) {
		return self::setCountBykey($count_key, self::COUNTER_INIT_VAL);
	}
	
	public static function getError() {
		return self::$errorInfo;
	}
}
