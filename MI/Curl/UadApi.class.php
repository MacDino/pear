<?php

class MI_Curl_UadApi extends MI_Curl
{
	protected static $_clientId = 450001;
	protected static $_serverUri = 'http://uad.dalasuapi.com';

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
		$params = array('client_id' => defined('UAD_API_CLIENT_ID')?UAD_API_CLIENT_ID:self::$_clientId,
						'server_uri' => defined('UAD_API_URL')?UAD_API_URL:self::$_serverUri);
		parent::setConfig($params);
	}

}
