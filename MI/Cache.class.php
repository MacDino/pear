<?php
class MI_Cache
{

	private static $_memObj = array();
	private static $_memObjKey = NULL;

	public static function setCacheConf($config)
	{
		self::$_memObjKey = md5(serialize($config));
		if(!isset(self::$_memObj[self::$_memObjKey]) or !is_object(self::$_memObj[self::$_memObjKey]))
		{
			self::$_memObj[self::$_memObjKey] = new Memcached;
			foreach($config['servers'] as $v)
			{
				self::$_memObj[self::$_memObjKey]->addServer($v['host'], $v['port'], $v['weight']);
			}
			self::$_memObj[self::$_memObjKey]->setOption(Memcached::OPT_DISTRIBUTION, 
				Memcached::DISTRIBUTION_CONSISTENT);

			if(isset($config['compress']))
				self::$_memObj[self::$_memObjKey]->setOption(Memcached::OPT_COMPRESSION, (bool)$option['compress']);
		}
		return self::$_memObj[self::$_memObjKey];
	}


	public static function get( $key)
	{
		return self::$_memObj[self::$_memObjKey]->get($key);
	}

	public static function getMulti( $keys)
	{
		return self::$_memObj[self::$_memObjKey]->getMulti($keys);
	}


	public static function set($key, $value, $expiration = 0)
	{
		return self::$_memObj[self::$_memObjKey]->set($key, $value, $expiration);
	}

	public static function setMulti( $items, $expiration = 0)
	{
		return self::$_memObj[self::$_memObjKey]->set($items, $expiration);
	}

	public static function inc( $key, $offset = 1)
	{
		return self::$_memObj[self::$_memObjKey]->increment($key, $offset);
	}

	public static function dec( $key, $offset = 1)
	{
		$res = self::$_memObj[self::$_memObjKey]->decrement($key, $offset);
		if(self::$_memObj[self::$_memObjKey]->getResultCode == Memcached::RES_NOTFOUND)
		{
			$res = 0;
			self::set($key, $res);
		}
		return $res;
	}

	public static function delete( $key)
	{
		return self::$_memObj[self::$_memObjKey]->delete($key);
	}

	public static function getResultCode()
	{
		return self::$_memObj[self::$_memObjKey]->getResultCode();
	}

	public static function getResultMessage()
	{
		return self::$_memObj[self::$_memObjKey]->getResultMessage();
	}



	
}
