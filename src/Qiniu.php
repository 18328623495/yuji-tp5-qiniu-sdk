<?php
namespace TpQiniu;

use think\Cache;
use think\Config;
use think\Exception;
use think\facade\Env;

class Qiniu
{
    private $_accessKey;
    private $_secretKey;
    private $_bucket;

    public function __construct($accessKey = "", $secretKey = "", $bucketName = ""){
        $this->_accessKey=!empty($accessKey)?$accessKey:Config::QINIU_ACCESS_KEY;
        $this->_secretKey=!empty($_secretKey)?$secretKey:Config::QINIU_SECRET_KEY;
        $this->_bucket=!empty($bucketName)?$bucketName:Config::QINIU_BUCKET;
    }

    /**
     * 同步，从指定URL抓取资源，并将该资源存储到指定空间中，每次只抓取一条
     * @param string $url 指定的URL
     * @param string $bucket 目标资源空间
     * @param string $key 目标资源文件名
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1263/fetch
     */
    public function fetch($url, $bucket='', $key = null)
    {
        require_once Env::get('vendor_path') . 'qiniu/php-sdk/autoload.php';
        $auth=new \Qiniu\Auth($this->_accessKey,$this->_secretKey);
        $BucketManager=new \Qiniu\Storage\BucketManager($auth);
        $bucket=!empty($bucket)?$bucket:$this->_bucket;
        return $BucketManager->fetch($url,$bucket,'');
    }

    /**
     * @param $bucketName
     * @return mixed|string
     * @throws Exception
     * 只有设置到配置的bucket才会使用缓存功能
     */
    private function _getUploadToken($bucketName='')
    {
        require_once Env::get('vendor_path') . 'qiniu/php-sdk/autoload.php';
        $upToken = Cache::get('qiniu_upload_token');
        if (!empty($upToken) && empty($bucketName)) {
            return $upToken;
        }else{
            $auth = new \Qiniu\Auth($this->_accessKey, $this->_secretKey);
            $bucket = empty($bucketName)? $this->_bucket:$bucketName;
            if ($bucket === false) {
                throw new Exception('你还没有设置或者传入bucket', 100001);
            }
            $upToken = $auth->uploadToken($bucket);
            Cache::set('qiniu_upload_token', $upToken);
            return $upToken;
        }
    }
}
