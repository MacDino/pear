<?php
require_once('/usr/share/pear/Smarty/Smarty.class.php');

class MI_Template {

	static private $_default_combo = true;
    	static private $_default_template_dir = '/data/www/shop/template';
    	static private $_default_compiled_dir = '/data/www/shop/compiled';
	static private $_template = null;
	static private $_jsArray = array();
	static private $_cssArray = array();
	static private $_instance = null;
	static private $_jsCode = array();
	static private $_scriptholder = array();
	static private $_cssholder = array();
	static private $_jsVars = array();
	/*
	 * 获取类的唯一实例
	 * @return MI_Template 本类的实例
	 */
	public static function instance() {
		if(!is_object(self::$_instance)) self::$_instance = new MI_Template;
		return self::$_instance;
	}

	//{{{ display($templateFile, $assign = array())
	/**
	 * display 
	 *
	 * 根据模板文件生成页面并输出
	 * 
	 * @param	string	$templateFile	模板文件
	 * @param	array	$assign			传递给模板的参数 格式 array(key1 => value1, key2 => value2)
	 * @static
	 * @access public
	 * @return void
	 */
	public static function display($templateFile, $assign = array()) {
		if(is_array($assign)) self::Assign($assign);
		$smarty = self::_getTemplate();
		if(self::$_jsArray) $smarty->assign('javascripts', self::jsToString(self::$_jsArray));
		if(self::$_cssArray) $smarty->assign('csses', self::cssToString(self::$_cssArray));
		if(self::$_jsVars) $smarty->assign('envobj', json_encode(self::$_jsVars));
		$smarty->display($templateFile);
		self::closeTemplate();
	}//}}}

	//{{{ fetch($templateFile, $assign = array())
	/**
	 * fetch 
	 * 
	 * 根据模板文件生成页面并返回
	 *
	 * @param	string	$templateFile	模板文件
	 * @param	array	$assign			可选参数，传递给模板的变量数组 格式 array(key1 => value1, key2 => value2)
	 * @static
	 * @access public
	 * @return	string	页面html代码
	 */
	public static function fetch($templateFile, $assign = array()) {
		if(is_array($assign)) self::Assign($assign);
		$smarty = self::_getTemplate();
		if(self::$_jsArray) $smarty->assign('javascripts', self::jsToString(self::$_jsArray));
		if(self::$_cssArray) $smarty->assign('csses', self::cssToString(self::$_cssArray));
		if(self::$_jsVars) $smarty->assign('envobj', json_encode(self::$_jsVars));
		$content = $smarty->fetch($templateFile);
		self::closeTemplate();
		return $content;
	}//}}}

	//{{{ closeTemplate()
	/**
	 * closeTemplate 
	 *
	 * 关闭当前模板
	 * 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function closeTemplate() {
		self::$_template = null;
	}//}}}

	//{{{ assign($k = null, $v = null)
	/**
	 * assign 
	 * 
	 * 传递页面变量组给smarty
	 *
	 * @param	string/Array	$k	可选参数，当为数组时为变量数组，格式 array(key1 => value1, key2 => value2)，
	 *								当为字符串是表示变量名
	 * @param	string			$v	可选参数，当$k为字符串时，$v为$k对应的变量值
	 * @static
	 * @access public
	 * @return	MI_Template		本类的实例
	 */
	public static function assign($k = null, $v = null) {
		$smarty = self::_getTemplate();
		if(!is_array($k)) {
			$smarty->assign($k, $v);
		} else {
			foreach($k as $key => $value) {
				$smarty->assign($key, $value);
			}
		}
		return self::instance();
	}//}}}

	//{{{ setTitle($title)
	/**
	 * setTitle 
	 * 
	 * 设置页面标题
	 *
	 * @param	string	$title	页面标题
	 * @static
	 * @access	public
	 * @return	本类的实例
	 */
	public static function setTitle($title) {
		self::assign('head_title', $title);
		return self::instance();
	}//}}}

	//{{{ addCss($css = array())
	/**
	 * addCss 
	 * 
	 * 添加css文件到页面
	 *
	 * @param	string/array	$css	单个css文件地址或者css文件地址的一维数组
	 * @static
	 * @access	public
	 * @return	MI_Template		本类的实例
	 */
	public static function addCss($css = array()) {
		if(!is_array($css)) $css = array($css);
		self::$_cssArray = array_unique(array_merge(self::$_cssArray, $css));
		return self::instance();
	}//}}}

	//{{{ addJs($js = array())
	/**
	 * addJs 
	 * 
	 * 添加js文件到页面
	 *
	 * @param	string/array	$js		单个js文件地址或者js文件地址的一维数组
	 * @param array $js 
	 * @static
	 * @access public
	 * @return	MI_Template		本类的实例
	 */
	public static function addJs($js = array()) {
		if(!is_array($js)) $js = array(
			$js
		);
		self::$_jsArray = array_unique(array_merge(self::$_jsArray, $js));
		return self::instance();
	}//}}}

	//{{{ addJsCode($jsCode)
	/**
	 * addJsCode
	 *
	 * 添加js代码到页尾加载完javascripts的代码后的位置 
	 * 
	 * @param string $jsCode 
	 * @static
	 * @access public
	 * @return 本类的一个实例
	 */
	public static function addJsCode($jsCode) {
		$str = '';
		if (!is_array($jsCode)) $jsCode = array($jsCode);
		self::$_jsCode = array_merge(self::$_jsCode, $jsCode);
		$str .= '<script type="text/javascript">' . "\r\n";
		$str .= implode("\r\n", self::$_jsCode);
		$str .= "\r\n</script>";
		self::assign('js_codes', $str);
		return self::instance();
	}//}}}

	//{{{ addMeta($metas, $multi = false)
	/**
	 * addMeta 
	 * 
	 * 添加meta标签，支持批量
	 *
	 * @param	array	$metas  key-value数组
	 * @param	boolean	$multi  是否是多个meta标签
	 * @static
	 * @access	public
	 * @return	MI_Template		本类的实例
	 */
	public static function addMeta($metas, $multi = false)
	{
		static $str = '';
		
		if($multi) {
			foreach ($metas as $meta) {
				$str .= self::metaToString($meta);
			}
		} else {
			$str .= self::metaToString($metas);
		}
		
		self::assign('metas', rtrim($str, "\r\n"));
		return self::instance();
	}//}}}

	//{{{ jsToString($js)
	/**
	 * jsToString 
	 * 
	 * 由js文件列表返回html页面引用的字符串
	 *
	 * @param	array	$js		js文件一维数组
	 * @static
	 * @access public
	 * @return	string	html页面引用的字符串
	 */
	public static function jsToString($js) {
		if(empty($js)) return '';
		$cnt = count($js);
		if(self::isCombo()) {
			$str = '<script type="text/javascript" src="' 
				. ($cnt > 1 ? MI_Asset::getComboUrl($js) : MI_Asset::getUrl($js[0]))
				. '"></script>';
		} else {
			$str = '';
			for($i = 0; $i < $cnt; ++$i) {
				$str.= '<script type="text/javascript" src="' . MI_Asset::getUrl($js[$i]) . "\"></script>\n";
			}
		}
		return $str;
	}//}}}

	//{{{ cssToString($css)
	/**
	 * cssToString 
	 * 
	 * 由css文件列表返回html页面引用的字符串
	 *
	 * @param	array	$css	css文件一维数组
	 * @static
	 * @access	public
	 * @return	string	html页面引用的字符串
	 */
	public static function cssToString($css) {
		if(empty($css)) return '';
		$cnt = count($css);
		if(self::isCombo()) {
			$str = '<link href="'
				. (count($css) > 1 ? MI_Asset::getComboUrl($css) : MI_Asset::getUrl($css[0]))
				. '" rel="stylesheet" type="text/css" />';
		} else {
			$str = '';
			for($i = 0; $i < $cnt; ++$i) {
				$str .= '<link href="' . MI_Asset::getUrl($css[$i]) . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
			}
		}
		return $str;
	}//}}}

	//{{{ metaToString
	/**
	 * metaToString 
	 *
	 * 生成 <meta ... />
	 * 
	 * @param array $meta 
	 * @static
	 * @access public
	 * @return string
	 */
	public static function metaToString(array $meta)
	{
		$str = '<meta ';
		$tmp = array();
		
		foreach ($meta as $k => $v) {
			$tmp[] = $k . '="' . addslashes($v) . '"';
		}
		
		$str .= implode(' ', $tmp);
		$str .= " />\r\n";
		
		return $str;
	}//}}}

	//{{{ scriptHolder($params, $content)
	/**
	 * scriptHolder 
	 * 
	 * 将js代码加入后写入数组中
	 *
	 * @param string $params 
	 * @param string $content 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function scriptHolder($params, $content) {
		if(empty($content))
			return;

		if(empty($params))
			$params = array('place' => 'scriptassembly');

		$assign = $params['place'];
		if(!isset(self::$_scriptholder[$assign]) or empty(self::$_scriptholder[$assign]))
			self::$_scriptholder[$assign] = $content;
		else
			self::$_scriptholder[$assign].= $content;
	}//}}}

	//{{{ getScriptHolder($params)
	/**
	 * getScriptHolder 
	 *
	 * 获取后写入js代码
	 * 
	 * @param string $params 
	 * @static
	 * @access public
	 * @return void
	 */
	static public function getScriptHolder($params) {
		if(!isset($params['place']))
			$params = array('place' => 'scriptassembly');
		$assign = $params['place'];

		$str = isset(self::$_scriptholder[$assign]) ? self::$_scriptholder[$assign] : '';
		echo preg_replace('/((<|\<\/)script.*?>)|(<script.*)|(.*><\\/script.*)/', '', $str);
		return '';
	}//}}}

	//{{{ getTemplateDir()
	/**
	 * getTemplateDir 
	 * 
	 * 获取模板目录
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function getTemplateDir(){
		return defined('TPL_TEMPLATE_DIR') ? TPL_TEMPLATE_DIR: self::_default_template_dir;
	}//}}}

	//{{{ getCompliedDir()
	/**
	 * getCompliedDir
	 * 
	 * 获取编译临时目录
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function getCompliedDir(){
		return defined('TPL_COMPILED_DIR') ? TPL_COMPILED_DIR: self::_default_compiled_dir;
	}//}}}

	//{{{ cssHolder($cssUrl = "")
	/**
	 * cssHolder 
	 * 
	 * 为cssholder插件做的封装函数
	 *
	 * @param string $cssUrl 
	 * @static
	 * @access public
	 * @return void
	 */
	static public function cssHolder($cssUrl = "") {
		if(empty($cssUrl)) 
			return empty(self::$_cssholder) ? '' : self::cssToString(array_keys(self::$_cssholder));

		self::$_cssholder[$cssUrl] = 1;
	}//}}}

	//{{{ addJsVars($vars, $value = array())
	/**
	 * addJsVars 
	 * 
	 * 向页面添加 js 输出变量
	 *
	 * Example:
	 * <code>
	 * MI_Template::addJsVars("target", 6);
	 * MI_Template::addJsVars(array("target" => 7, "next" => true, "list" => array( 1, 2, 3, 4)));
	 * </code>
	 *
	 * @param string|array $js			变量名或变量数组
	 * @param boolean $value			变量值
	 * @static
	 * @access public
	 * @return void
	 */
	static public function addJsVars($vars, $value = array())
	{
		if (is_array($vars)) {
			foreach ($vars as $key => $value) {
				self::$_jsVars[$key] = $value;
			}
		}
		else {
			self::$_jsVars[$vars] = $value;
		}

		return self::instance();
	}//}}}

	//{{{ _getTemplate()
	/**
	 * _getTemplate 
	 *
	 * 获取smarty实例
	 * 
	 * @static
	 * @access private
	 * @return	Smarty	$smarty	smarty实例 
	 */
	private static function _getTemplate() {
		if(self::$_template) return self::$_template;
		$smarty = new Smarty();
		$smarty->template_dir = self::getTemplateDir();
		$smarty->use_sub_dirs = defined('TPL_SUB_DIRS') ? TPL_SUB_DIRS : false;
		$smarty->compile_dir = self::getCompliedDir();
		if (defined('TPL_PLUGINS_DIR')) $smarty->addPluginsDir(TPL_PLUGINS_DIR);
		$smarty->addPluginsDir(dirname(__FILE__) . '/Template/plugins');
		$smarty->left_delimiter = '<{';
		$smarty->right_delimiter = '}>';
		$smarty->loadFilter('output', 'cssholder');
		self::$_template = $smarty;
		return self::$_template;
	}//}}}

	//{{{ isCombo()
	/**
	 * isCombo 
	 *
	 * 判断是否需要组合静态文件地址
	 *
	 * @static
	 * @access public
	 * @return boolean
	 */
	public static function isCombo() {
		return defined('ASSET_COMBO') ? ASSET_COMBO : self::$_default_combo;
	}//}}}
}
