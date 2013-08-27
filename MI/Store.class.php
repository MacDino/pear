<?php

/*
 * *************************
 * 上传文件类
 * *************************
 */

class MI_Store {  
        private static $_errorCode      = 0;
        private static $_errorMsg       = NULL;
        private static $_data       = NULL;
        
        /*
         * 上传文件
         */
        public static function uploadFile($upfile, $params=array()) {
                $fs_url = defined("FS_URL") ? FS_URL : "http://fs.dalasu.com/";   //fs上传地址
                
                //要上传的文件路径
                $uploadfile = $upfile['tmp_name'];
                //上传的文件是否存在
                if(!file_exists($uploadfile)){
                        self::setError(1, '上传的文件不存在');
                        return FALSE;  
                }
                
                //向文件服务器发送的参数
                $config = array();
                $config['file_ext'] = pathinfo($upfile['name'], PATHINFO_EXTENSION);    //文件扩展名
                $config['file_md5'] = md5_file($uploadfile);                            //文件md5
                $config['file_size'] = filesize($uploadfile);                           //文件大小
                if(isset($params['dimensions'])){
                        $config['dimensions'] = $params['dimensions'];                  //缩略图尺寸大小数组
                }
                if(isset($params['is_watermark'])){
                        $config['is_watermark'] = $params['is_watermark'];              //是否加水印
                }
                
                //发送的参数及文件
                $post_data['config'] = json_encode($config);
                
                //首先单独验证一下上传的文件是否上传过，如果上传过则直接返回成功
                $res = self::sendRequest($fs_url, $post_data); 
                if(self::getRet($res)){
                        return self::$_data;
                }
                
                //上传文件
                $post_data['upload'] = "@".$uploadfile;
                $res = self::sendRequest($fs_url, $post_data);
                
                if(self::getRet($res)){
                        return self::$_data;
                }                
        }
        
        /*
         * 设置返回结果
         */
        private static function getRet($res){
                $ret = json_decode($res,TRUE);
                //如果返回的是不是数组或是空数组
                if(!isset($ret['code']) || !isset($ret['msg']) || !isset($ret['data'])){
                      self::setError(-1, '文件系统返回未知错误或上传地址错误');
                      return FALSE;  
                }
                
                self::setError($ret['code'], $ret['msg'], $ret['data']);
                
                return ($ret['code'] == 0) ? TRUE : FALSE;              
        }
        
        /*
         * 设置错误码
         */
        private static function setError($code, $msg, $data=''){
              self::$_errorCode = $code;
              self::$_errorMsg = $msg;  
              self::$_data = $data;
        }
        
        /*
         * 返回错误码和错误消息
         */
        public static function getError($key=''){
                $arr = array(
                    'code' => self::$_errorCode,
                    'msg' => self::$_errorMsg,
                    'data' => self::$_data
                );
                
                return (!empty($key) && isset($arr[$key])) ? $arr[$key] : $arr;
        }
        
        
        /*
         * 发送HTTP请求
         */
        public static function sendRequest($url, $post_data=array()) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                $ret = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if($httpCode == 200){
                        return $ret;
                }
        }
}

?>
