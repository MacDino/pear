<?php
//用于标准的发起CURL操作
class MI_Curl
{
    private static      $_version       = '1.0';
    private static      $_timeout       = '3';
    private static      $_connectTimeOut = '3';
    private static      $_keepAlive     = TRUE;
    private static      $_clientId      = 999999;
    private static      $_serverUri     = 'http://uadapi.dalasu.com';
    private static      $_logServer     = '/data/logs/nginx/interface/interface.[split].log';
    private static      $_logSplit      = 'YmdH';
    private static      $_methodPost    = TRUE;
    private static      $_userAgent     = "";
    private static      $_httpHeader    = "";
    private static      $_httpInfo      = "";
    private static      $_errorCode     = 0;
    private static      $_errorMsg      = '';
    private static      $_interFaceExt  = '.php';

    //设置参数
    public static function setConfig($configParams)
    {
        if(isset($configParams['time_out']))self::$_timeout = $configParams['time_out'];
        if(isset($configParams['connect_time_out']))self::$_connectTimeOut = $configParams['connect_time_out'];
        if(isset($configParams['client_id']))self::$_clientId = $configParams['client_id'];
        if(isset($configParams['server_uri']))self::$_serverUri = $configParams['server_uri'];
        if(isset($configParams['method_post']))self::$_methodPost = $configParams['method_post'];
        if(isset($configParams['user_agent']))self::$_userAgent = $configParams['user_agent'];
        if(isset($configParams['http_header']))self::$_httpHeader = $configParams['http_header'];
        if(isset($configParams['inter_face_ext']))self::$_interFaceExt = $configParams['inter_face_ext'];
    }
    //发送请求
    public static function sendRequest($interface, $params)
    {
        if(!is_array($params))return FALSE;
        $params = self::_getCurlValue($params);
        $uri    = self::_getCurlUri($interface, $params);
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(self::$_methodPost)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$_connectTimeOut);
        if(self::$_userAgent)curl_setopt($ch, CURLOPT_USERAGENT, self::$_userAgent);
        if(self::$_httpHeader)curl_setopt($ch, CURLOPT_HTTPHEADER, self::$_httpHeader);
        $data               = curl_exec($ch);
        self::$_httpInfo    = curl_getinfo($ch);
        curl_close($ch);
        if(self::$_httpInfo['http_code'] == 200)
        {
            return $data;
        }else{
            return FALSE;
        }
    }
    //检查参数
    private static function _checkParams($params)
    {
        if(!isset($params['client_id']))$params['client_id'] = self::$_clientId;
        if(!isset($params['version']))$params['version'] = self::$_version;    
        return $params;
    }
    //获取CURL的HTTPINFO
    public static function getHttpInfo()
    {
        return self::$_httpInfo;
    }

    //组装数据
    private static function _getCurlValue($params)
    {
        $params = self::_checkParams($params);
        $params = http_build_query($params);
        return $params;
    }
    //获取请求的地地址
    private static function _getCurlUri($interface, $params)
    {
        $uri = self::$_serverUri."/".$interface.self::$_interFaceExt;
        if(!self::$_methodPost)
        {
            return $uri."?".$params;
        }
        return $uri;
    }

    public static function setError($code = 0, $msg = "")
    {
        self::$_errorCode = $code;
        self::$_errorMsg  = $msg;

    }

    public static function clearError()
    {
        self::$_errorCode = 0;
        self::$_errorMsg  = "";
    }

    public static function getError()
    {
        return array('errorCode' => self::$_errorCode,
                     'errorMsg'  => self::$_errorMsg);
    }
}
