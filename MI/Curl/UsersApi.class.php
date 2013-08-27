<?php

class MI_Curl_UsersApi extends MI_Curl
{
	protected static $_clientId = 450001;
	protected static $_serverUri = 'http://users.dalasuapi.com';

	public static function sendRequest($interFace, $params = array())
	{
		self::_setConfig();
		$info = parent::sendRequest($interFace, $params);
		$info = json_decode($info, TRUE);
		if(is_array($info) && count($info) && $info['code'] == 0)
		{
			return $info['data'];
		}else{
			parent::clearError();
			parent::setError($info['code'], $info['msg']);
			return FALSE;
		}
	}

	private static function _setConfig()
	{
		$params = array('client_id' => defined('USERS_API_CLIENT_ID')?USERS_API_CLIENT_ID:self::$_clientId,
						'server_uri' => defined('USERS_API_URL')?USERS_API_URL:self::$_serverUri);
		parent::setConfig($params);
	}

}
