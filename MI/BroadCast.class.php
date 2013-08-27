<?php
error_reporting(E_ALL);
/*  
 * 消息广播, 功能分配
 */
class MI_BroadCast { 
   
    private static $workerList = NULL;
    private static $gmclient   = NULL;
    private static $host = '';
    private static $port = 0;  
	
	// 处理分发请求的入口
    public static function broadCast($params = NULL) {
        // 配置Gearman信息
        self::init($params); 
        // 查看分发信息的有效性
        if (!$params['type']) {
            throw new Exception("操作标识不是不效的标识", 1001);            
        } 
        $result = self::$gmclient->doNormal("doBroadCast", json_encode($params));
        return $result; 
    }
    
    private static function init($params) {
        
        if (is_null(self::$gmclient)) {
            // 建立对象
            self::$gmclient= new GearmanClient(); 
            if (!self::$gmclient) {                
                throw new Exception("无法创建CearmanClient对象!", 1002);     
            } 
            // 配置服务器的信息
            self::$host = isset($params['host']) && $params['host'] ? $params['host'] : '127.0.0.1';
            self::$port = isset($params['port']) && $params['port'] ? $params['port'] : '4730';
            $rest_addS  = self::$gmclient->addServer(self::$host, self::$port);
            if (!$rest_addS) {
                throw new Exception("无法添加Gearman的Server服务器!", 1003);     
            }
        } 
        return TRUE;       
   } 
}



