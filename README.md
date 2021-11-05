##thinkphp5 七牛云文件上传
####使用说明
一、配置使用

1.1配置：

在.env文件中，配置七牛云的配置参数

    #七牛云参数配置
    QINIU_ACCESS_KEY=xxx
    QINIU_SECRET_KEY=xxxx
    #空间名称
    QINIU_BUCKET=xxx
    #空间外链域名 
    QINBIU_DOMAIN=xxxxx
    #私有空间文件下载过期时间
    DOWNLOAD_FILE_EXPIRES=60

1.2使用：

    更新依赖：composer update

    远程图片上传：
    $qiniu=new Qiniu();
    return  $qiniu->fetch('http://xx.xx.xx');
