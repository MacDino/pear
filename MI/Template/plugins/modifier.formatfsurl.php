<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @author Rejoy.li
 */


/**
 * Smarty formatfsurl modifier plugin
 *
 * Type:     modifier<br>
 * Name:     formatfsurl<br>
 * Date:     JUNE 11, 2008
 * Purpose:  Format url as purpose
 * Input:    string to format
 * Example:  {$var|formatfsurl}
 * @version 1.0
 * @param 	(String)	$url		| 要格式化的网址
 * @param 	(Boolean)	$mtime		| 可选,文件创建时间
 * @return 	(String)	格式化后的网址
 */
function smarty_modifier_formatfsurl( $url , $mtime=true , $type=1)
{
	return MI_Asset::getFsUrl($url);
}
