<?php

// error_reporting(E_ALL);
class MI_Logs {
	// 文章信息数据
	private static $file = NULL;
	// 文件信息ID
	private static $f_id = NULL;
	// 错误信息记录
	private static $errorArr = NULL;
	// 记录项目及日记名称
	private static $fileDir = NULL;

	/**
	 * 配置项目名称,记录名称,生成Log文件
	 * @param String $folder
	 */
	public static function init($folder, $note = NULL) {
		try {
			if (!$folder) {
				throw new Exception("参数信息不正确,请传入项目名及记录名称!", 61);
			}
			$note = $note ? '/'. $note : '';
			self::checkAndCreateID(md5(date('YmdH', time())), '/data/logs/' . $folder . $note . '/' . date('Ym',time()));
			return TRUE;
		} catch ( Exception $e ) {
			self::$errorArr[self::$f_id] = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return FALSE;
		}
	}

	/**
	 * 写入文件操作
	 * @param String $type	写入信息的类型
	 * @param String $content  写入的内容
	 * @throws Exception
	 * @return bool
	 */
	public static function writeLog($type, $content) {
		try {
			self::clearError();
			if (!$type || !$content) {
				throw new Exception("参数信息不正确,请传入信息类型及信息内容!", 62);
			}
			if(!self::checkAndCreateID(md5(date('YmdH', time())))) {
				return false;
			}
			if (self::$file[self::$f_id]) {
				$contents = date('Y-m-d H:i:s', time()) . '|' . $type . '|' . $content;
				// 最多尝试写锁定3次.
				for($i = 1; $i <= 3; $i++) {
					if(flock(self::$file[self::$f_id], LOCK_EX)) {
						$res = fputs(self::$file[self::$f_id], $contents . "\r\n");
						flock(self::$file[self::$f_id], LOCK_UN);
						$i = 0;
						break;
					}
				}
				// 写锁定不成功,尝试直接写入
				if ($i > 0) {
					$result = fputs(self::$file[self::$f_id], $contents . "\r\n");
					if ($result === FALSE) {
						throw new Exception('日记写入失败,请检查权限设置!', 63);
					}
					return TRUE;
				}
				return TRUE;
			} else {
				throw new Exception('文件打开失败,请检查/data/logs目录权限!', 64);
			}
		} catch ( Exception $e ) {
			self::$errorArr[self::$f_id] = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return FALSE;
		}

	}

	/**
	 * 在指定的路径位置输出Log文件
	 * @param String $folder
	 * @param String $note
	 * @param String $content
	 * @param String $type
	 * @throws Exception
	 * @return bool
	 */
	public static function wLog($folder, $note, $type, $content)   {
		try {
			if (!$folder || !$note || !$content) {
				throw new Exception("参数信息不正确,请传入正确的路径及信息!", 61);
			}
			$time = time();
			$folder = "/data/logs/". $folder . "/" . $note."/".  date("Ymd",$time);
			if(!self::creatdir($folder)) {
				throw new Exception("无法建立目录!", 66);
			}
			$fname = $folder . '/' . date("Y_m_d_H", $time) . '.log';
			$type = $type ? $type . '|'  : '';
			$contents = date('Y-m-d H:i:s', time()) . '|' . $type . $content;
			$fp = fopen($fname, 'a');
			if ($fp) {
				for($i = 1; $i <= 3; $i++) {
					if(flock($fp, LOCK_EX)) {
						fputs($fp, $contents . "\r\n");
						flock($fp, LOCK_UN);
						$i = 0;
						break;
					}
				}
				if ($i > 0) {
					if (fputs($fp , $contents . "\r\n") === FALSE) {
						throw new Exception('日记写入失败!', 63);
					}
					return TRUE;
				}
				fclose($fp);
				return TRUE;
			} else {
				throw new Exception('文件打开失败,请检查/data/logs目录权限!', 64);
			}

		} catch ( Exception $e ) {
			self::$errorArr[self::$f_id] = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return FALSE;
		}
	}
	/**
	 * 获取错误信息
	 * @return NULL
	 */
	public static function getError() {
		return self::$errorArr[self::$f_id];
	}
	/**
	 * 清除之前产生的错误信息
	 */
	private static function clearError() {
		self::$errorArr[self::$f_id] = NULL;
	}

	private static function creatdir($path) {
		if (!is_dir($path)) {
			if (self::creatdir(dirname($path))) {
				$res = mkdir($path, 0775);
				if ($res === FALSE) {
					return FALSE;
				}
				return true;
			}
		} else {
			return true;
		}
	}

	/**
	 * 检查当前文档是否已经过期(每小时创建一个新的文档来保存信息), 过期则重新指定文件
	 * @param int $time
	 * @throws Exception
	 * @return boolean
	 */
	private static function checkAndCreateID($f_id_new, $forders = NULL ) {
		try {
			if (!$f_id_new) {
				throw new Exception("系统参数出错!", 67);
				return FALSE;
			}
			$is_change = FALSE;
			$old_key = self::$f_id;
			if ($f_id_new != self::$f_id) {
				self::$f_id = $f_id_new;
				$is_change = TRUE;
			}
			if ($forders  &&  self::$fileDir[$old_key] != $forders) {
				self::$fileDir[self::$f_id] = $forders;
				$is_change = TRUE;
			}
			if ($is_change) {
				if(!self::creatdir(self::$fileDir[self::$f_id])) {
					throw new Exception("无法建立目录!", 66);
				}
				$fname = self::$fileDir[self::$f_id] . '/' . date('dH', time()) . '.log';
				self::$file[self::$f_id] = fopen($fname, 'a');
				self::$file[$old_key] = NULL;
				self::$fileDir[$old_key] = NULL;
			}
			return TRUE;
		} catch (Exception $e) {
			self::$errorArr[self::$f_id] = array('code' => $e->getCode(), 'msg' => $e->getMessage());
			return FALSE;
		}

	}
}
