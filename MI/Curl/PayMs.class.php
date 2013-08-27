<?php
//与支付平台通信
class MI_Curl_PayMs extends MI_Curl
{
	private static $_serverUri = 'http://www.bspp.com:8080';

	public static function sendRequest($interFace, $params)
	{
		error_log($interFace, 3, '/tmp/xg.log');
		self::_setConfig();
		$info = parent::sendRequest($interFace, $params);
		error_log(print_r($info, true), 3, '/tmp/xg.log');
		if($info)
		{
			$info = json_decode($info, TRUE);
			if(is_array($info) && count($info) && $info['code'] == 0)
			{
				return $info['data'];
			}else{
				parent::setError($info['code'], $info['msg']);
				return FALSE;
			}
		}else{
			parent::setError(999, "httpCode:".parent::getHttpInfo()['http_code']);
			return FALSE;
		}
		
	}

	private static function _setConfig()
	{
		$params = array('server_uri' => defined('PAY_MS_URL')?PAY_MS_URL:self::$_serverUri,
						'inter_face_ext' => '',
						'method_post' => TRUE);
		parent::setConfig($params);
	}
}
