<?php
//上传
class MI_Curl_Store
{
    private static $_errorMsg = null;
    
    public static function uploadPic($file, $params = array()) {
        if($file) {
            $res =  self::curlUploadFileApi($file, $params = array());
            $ret = json_decode($res,TRUE);
            if(is_array($ret) && $ret['code'] == 0) {
                if(self::$_errorMsg) {
                    self::$_errorMsg = NULL;
                }
                return $ret['data'];
            } else {
                self::$_errorMsg = $ret['msg'] ? $ret['msg'] : '未知错误';
                return FALSE;
            }
        } else {
            self::$_errorMsg = '缺少文件';
            return FALSE;
        }
    }
    
    /**
     * 将文件上传至$url
     * @param   $url        string  上传地址
     * @param   $file   string  文件信息
     * @param   $params     array   需要顺路上传的参数
     * @return  string|bool json字符串|false
     * **/
    public static function curlUploadFileApi($file, $params = array()) {
        if($file) {
            $url    = 'http://uadapi.mzh.dev.bs.com/upload/uploadPic.php';
            $ci     = curl_init();
            $params['image'] =  '@'.$file['tmp_name'].";type=".$file['type'].";";
            curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ci, CURLOPT_TIMEOUT, 30);
            curl_setopt($ci, CURLOPT_POST, TRUE);
            curl_setopt($ci, CURLOPT_URL, $url);
            curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
            ob_start();
            curl_exec($ci);
            $res    = ob_get_contents();
            ob_end_clean();
            $httpCode = curl_getinfo($ci, CURLINFO_HTTP_CODE);
            curl_close($ci);
            if(200 == $httpCode) {
                return $res;
            }
        }
        return false;
    }
}
