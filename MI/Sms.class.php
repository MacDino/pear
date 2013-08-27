<?php

/*
 * *************************
 * 发送短信接口类
 * *************************
 */

class MI_Sms {    
    private static $_serveruri = '';                                        //短信平台地址
    private static $_serverInterface1 = 'sendsms.php';                      //短信平台接口（1对1和n对n）
    private static $_serverInterface2 = 'sendsmsmass.php';                  //短信平台接口（n对1）
    
    private static $_returnCode = '';                                       //返回的代码
    private static $_returnMsg = '';                                        //返回的信息
    private static $_returnData = '';                                       //返回的数据

    /*
     * 初始化设置
     */
    private static function initialize($params = array()) {
        self::$_serveruri = isset($params['sms_serveruri']) ? $params['sms_serveruri'] : 'http://sms.dalasuapi.com';
    }

    /*
     * 设置curl参数
     */
    private static function setCurlConfig() {
        $curl_config = array(
            'server_uri' => self::$_serveruri,
            'inter_face_ext'=> ''
        );
        MI_Curl::setConfig($curl_config);
    }
    
    /*
     * curl发送http请求
     */
    private static function sendRequest($interface = '', $params = array()) {
        //curl配置
        self::setCurlConfig();       
        //curl发送http请求
        return MI_Curl::sendRequest($interface, $params);
    }
    
    /*
     * 验证参数
     */
    private static function checkParams($params) {
        if(!isset($params['username']) || empty($params['username'])){
            self::$_returnCode = 2;
            self::$_returnMsg = '用户名不能为空';
            return FALSE;
        }
        if(!isset($params['password']) || empty($params['password'])){
            self::$_returnCode = 3;
            self::$_returnMsg = '密码不能为空';
            return FALSE;
        }
        if(!isset($params['channel']) || empty($params['channel'])){
            self::$_returnCode = 4;
            self::$_returnMsg = '主通道号不能为空';
            return FALSE;
        }
        return TRUE;
    }


    /*
     * 发送短信 1对1或n对n（可以1个手机号对应1条短信；n个手机号对应n条短信）
     */
    public static function sendSms($params = array()) {
        //验证参数
        if(!self::checkParams($params)){
            return FALSE;
        }
        
        //验证短信参数
        if(!is_array($params['sms_list'])){
            self::$_returnCode = 5;
            self::$_returnMsg = '短信参数错误';
            return FALSE;
        }
        //每次发送短信的条数限制
        if(count($params['sms_list'])>50){
            self::$_returnCode = 6;
            self::$_returnMsg = '每次发送短信最多50条';
            return FALSE;
        }
        
        //初始化参数
        self::initialize($params);
        //发送短信
        $arr = json_decode(self::sendRequest(self::$_serverInterface1, $params), true);
        //返回的代码和数据
        self::setError($arr);
        
        //返回的code码为0时，代表发送成功
        if(self::$_returnCode == 0){
            return TRUE;
        }
        return FALSE;
    }
    
    /*
     * 发送短信 n对1（可以n个手机号对应1条短信）
     */
    public static function sendSmsMass($params = array()) {
        //验证参数
        if(!self::checkParams($params)){
            return FALSE;
        }
        
        //验证短信参数
        if(!is_array($params['sms_list']['mobiles'])){
            self::$_returnCode = 5;
            self::$_returnMsg = '短信参数错误';
            return FALSE;
        }
        //每次发送短信的条数限制
        if(count($params['sms_list']['mobiles'])>50){
            self::$_returnCode = 6;
            self::$_returnMsg = '每次发送短信最多50条';
            return FALSE;
        }
        
        //初始化参数
        self::initialize($params);
        //发送短信
        $arr = json_decode(self::sendRequest(self::$_serverInterface2, $params), true);
        //返回的代码和数据
        self::setError($arr);
        
        //返回的code码为0时，代表发送成功
        if(self::$_returnCode == 0){
            return true;
        }
        return false;
    }


    /*
     * 获取返回数据
     */
    public static function getError() {
        return array(
            'code'=>self::$_returnCode,
            'msg'=>self::$_returnMsg,
            'data'=>self::$_returnData,
        );
    }
    
    /*
     * 设置错误信息
     */
    private static function setError($arr) {
        self::$_returnCode = isset($arr['code']) ? $arr['code'] : (1);
        self::$_returnMsg = isset($arr['msg']) ? $arr['msg'] : 'curl请求失败';
        self::$_returnData = isset($arr['data']) ? $arr['data'] : '';
    }
}

?>
