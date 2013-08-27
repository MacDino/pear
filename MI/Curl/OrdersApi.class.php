<?php

class MI_Curl_OrdersApi extends MI_Curl
{
	protected static $_clientId = 450001;
	protected static $_serverUri = 'http://orders.dalasuapi.com';

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
                        if($info == NULL)
                        {
                                parent::setError(-1, "Curl 错误");
				return FALSE;
                        }
                        else
                        {
                                parent::setError($info['code'], $info['msg']);
				return FALSE;
                        }
                }
	}

	private static function _setConfig()
	{
		$params = array('client_id' => defined('ORDERS_API_CLIENT_ID')?ORDERS_API_CLIENT_ID:self::$_clientId,
						'server_uri' => defined('ORDERS_API_URL')?ORDERS_API_URL:self::$_serverUri);
		parent::setConfig($params);
	}
	
	public static function getError()
	{
		return parent::getError();
	}

}
