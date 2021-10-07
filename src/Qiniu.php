<?php
namespace TpQiniu;

use Qiniu\Storage\UploadManager;
use think\Config;
use think\Exception;
use think\facade\Cache;
use think\facade\Env;

class Qiniu
{
    private $_accessKey;
    private $_secretKey;
    private $_bucket;
    //空间外链域名
    private $_domain;
    //私有文件下载过期时间
    private $_download_file_expires;

    public function __construct($accessKey = "", $secretKey = "", $bucketName = "",$domain="",$download_file_expires=60){
        $this->_accessKey=!empty($accessKey)?$accessKey:Env::get('QINIU_ACCESS_KEY','');
        $this->_secretKey=!empty($_secretKey)?$secretKey:Env::get('QINIU_SECRET_KEY','');
        $this->_bucket=!empty($bucketName)?$bucketName:Env::get('QINIU_BUCKET','');
        $this->_domain=!empty($domain)?$domain:Env::get('QINBIU_DOMAIN','');
        $this->_download_file_expires=!empty($download_file_expires)?$download_file_expires:Env::get('DOWNLOAD_FILE_EXPIRES','');
        if(empty($this->_accessKey)|| empty($this->_secretKey)||empty($this->_bucket)){
            throw new Exception('七牛云参数配置不能为空！');
        }
    }

    /**
     * 上传单个文件
     * @param $file_path  文件绝对路径
     * @param string $file_name 文件名
     * @param string $bucket
     * @return mixed
     * @throws Exception
     */
    public function upload($file_path,$file_name = '', $bucket = '')
    {
        $token = $this->_getUploadToken($bucket);
        if (!file_exists($file_path)) {
            throw new Exception('文件不存在', 10002);
        }
        $uploadManager = new UploadManager();
        if (empty($file_name)) {
            $file_name = hash_file('sha1',$file_path).time();
        }
        list($ret, $err) = $uploadManager->putFile($token, $file_name,$file_path);
        if ($err !== null) {
            throw new Exception('上传出错'.serialize($err));
        }
        return $ret['key'];
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

    private function getAuth(){
        require_once Env::get('vendor_path') . 'qiniu/php-sdk/autoload.php';
        return new \Qiniu\Auth($this->_accessKey,$this->_secretKey);
    }
    /**
     * 获取下载凭证
     * @param $path 七牛云上传的文件路径
     * @return string
     */
    public function getDownloadToken($path){
        if(empty($this->_domain)|| empty($this->_download_file_expires)){
            throw new Exception('七牛云参数配置不能为空！');
        }
        $auth =$this->getAuth();
        //过期时间
        $expires=$this->_download_file_expires;
        // 私有空间中的外链 http://<domain>/<file_key>
        $baseUrl = 'http://'.$this->_domain.'/'.$path;
        // 对链接进行签名
        $signedUrl = $auth->privateDownloadUrl($path,$expires);
        return $signedUrl;
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
