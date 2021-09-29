##thinkphp5 七牛云文件上传
####使用说明
一、配置使用

1.1配置：

在配置文件src/Config.php中配置七牛云的配置参数

    /**
    * 七牛云 AccessKey
    */
    const QINIU_ACCESS_KEY='xxx';
    /**
    * 七牛云 SecretKey
    */
    const QINIU_SECRET_KEY='xxxx';
    /**
    * 七牛云 空间名称
    */
    const QINIU_BUCKET='xxxx';

1.2使用：

    $qiniu=new Qiniu();
    return  $qiniu->fetch('http://xx.xx.xx');
