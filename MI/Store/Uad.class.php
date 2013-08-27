<?php
class MI_Store_Uad extends MI_Store
{
    private static $_clientId  = 450001;
    private static $_serverUri = 'http://fs.dalasu.com';
    
    public static function uploadFile($file, $params = array()) {
        self::_setConfig();
        $info = parent::uploadFile($file, $params);
        return $info;
    }
    
    private static function _setConfig() {
        $params = array('client_id'  => defined('API_CLIENT_ID') ? API_CLIENT_ID : self::$_clientId,
                        'server_uri' => defined('FS_URL')        ? FS_URL : self::$_serverUri);
        parent::setConfig($params);
    }
}
    
    
    
    
    
    
    
    
    
    
    
    // 

     

// }