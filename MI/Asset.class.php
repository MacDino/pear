<?php

/**
 * Asset Class
 */
class MI_Asset
{
	static $split = '__';
	const ASSET_MAX = 5;
	const FS_MAX = 5;	

	/**
	 * Get asset url with timestamp
	 *	@param	path		the path of asset.xxx.com/$path
	 *	@return	URL			the url ( domain name & path )
	 */
	static public function getUrl($url, $version = true, $type = 1) {
		if (empty($url)) return '';
		if (preg_match('#^https?://#', $url)) return $url;
		if ($url{0} != '/') $url = '/' . $url;

		// 同一个文件，总会被分配到同一个 n 上。
		$http = 1;
		$server = self::getServer($url, $type, $http);
		if (!$version) return $server . $url;

		$tmp = explode('.', $url);
		$ext = array_pop($tmp);
		$split = self::$split;
		$suffix = defined(ASSET_VERSION)?ASSET_VERSION:0;

		//we use more then one domain name to down load asset in parallel
		return "$server{$url}$split$suffix.$ext";
	}

	/**
	 * Get combo asset url
	 *
	 *	@param array path		array of the path
	 *	@return	string		the url ( domain name & path )
	 */
	static public function getComboUrl($urls, $version = true)
	{
		$prefix = '/combo/';
		$split = self::$split;
		$url = join($split, $urls);
		$url = ltrim($url, '/');
		$server = self::getServer($url);
		if (!$version) return "$server$prefix$url";

		$suffix = defined(ASSET_VERSION)?ASSET_VERSION:0;
		return "$server$prefix$split$suffix/$url";
	}

	/**
	 * Get asset server number
	 *
	 *	@param	path		path of the asset, asset.xxx.com/$path
	 *	@return	int 		the number
	 */
	static public function getServer($url, $type = 1, $http = true)
	{
		if ($type) {

			$n = sprintf('%u', crc32($url));
			$n%= self::ASSET_MAX;
		} else {
			$n = 0;
		}
		try {
			if (ASSET_SUFFIX  and ASSET_PREFIX ) {

				$suffix = ASSET_SUFFIX;
				$prefix = ASSET_PREFIX;
			} else {
				throw new MI_Exception('No config for asset_server.');
			}
		}catch(MI_Exception $e) {
			return '';
		}
		$server = "$prefix$n.$suffix";
		return $http ? "http://$server" : $server;
	}
	
	/*
	 * Get fs url
	 */
	static public function getFsUrl($url)
	{
		$n = sprintf('%u', crc32($url));
		$n %= self::FS_MAX;
		
		$prefix = defined('FS_PREFIX') ? FS_PREFIX : '';
		$suffix = defined('FS_SUFFIX') ? FS_SUFFIX : ''; 
		if($prefix && $suffix){
			$server = "http://".$prefix.$n.".".$suffix;
		}else{
			$server = FS_URL;
		}
		return rtrim($server, "/")."/".ltrim($url, "/");
	}
}
