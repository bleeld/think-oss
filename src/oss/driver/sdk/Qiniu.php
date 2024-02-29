<?php
declare (strict_types = 1);
namespace think\oss\driver\sdk;

// 以SDK的方式链接
use think\oss\interface\SdkInterface;

require_once(root_path() . "vendor/autoload.php");

use QiniuOSS\Auth;
use QiniuOSS\Config;
use QiniuOSS\Storage\UploadManager;
use QiniuOSS\Storage\BucketManager;
use QiniuOSS\Processing\PersistentFop;

class Qiniu implements SdkInterface
{

    private $accessKeyId;
    private $accessKeySecret;
    private $bucket;
    private $domain;
    private $target_path_dir;
    private $uploadToken;
    public $ossClient;
    public $bucketClient;

    //  初始化OSS实例
    public function __construct ($targetPathDir=null) {
        //  获取配置项，并赋值给对象$config
        $config = config('oss');
        //  获取qiniu_oss默认配置
        $config = $config['qiniu_oss'];
        //  为OSS初始化赋值
        $this->accessKeyId = env('OSS_QINIU.ACCESSKEYID', $config['accessKeyId']);
        $this->accessKeySecret = env("OSS_QINIU.ACCESSKEYSECRET", $config['accessKeySecret']);
        $this->bucket = env("OSS_QINIU.BUCKET", $config['bucket']);
        $this->domain = env("OSS_QINIU.REGION", $config['domain']);
        //  设置存放目标文件的目录
        $this->target_path_dir = $targetPathDir ? $targetPathDir : 'upload/';
        //  实例化实例
        $this->ossClient = new Auth($this->accessKeyId, $this->accessKeySecret);
        $this->uploadToken = $this->ossClient->uploadToken($this->bucket);
        $this->bucketClient = new BucketManager($this->ossClient, new Config());

        //  返回实例
        //return $this->ossClient;
    }

    //获取上传凭证后表单上传
    private function getUploadToken()
    {
        return $this->ossClient->uploadToken($this->bucket);
    }

    //  获取空间文件列表情况
    public function list ()
    {
        //  执行获取流程
        try{
            return $this->bucketClient->listFiles($this->bucket);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function hasBucket (string $bucket = null)
    {
        //  执行逻辑
        try{
            return true;
        }catch(\Exception $e){
            return $e->getMessage();            
        }
    }

    //  获取bucket所在的地区
    public function getRegions (string $bucket = null)
    {
        return null;
    }

    //  获取空间信息
    public function getBucketInfo (string $bucket = null)
    {
        return $this->bucketClient->getBucketQuota($this->bucket);
    }

    //  获取bucket的Meta信息
    public function getBucketMeta (string $bucket = null)
    {
        return $this->bucketClient->bucketInfo($this->bucket);
    }

    
    public function getBucketStat (string $bucket = null)
    {
        return $this->bucketClient->buckets($this->bucket);
    }

    //  删除bucket
    public function delBucket (string $bucket = null)
    {
        return $this->bucketClient->deleteBucket($this->bucket);        
    }

    //  上传字符串
    public function uploadStr (string $filePath = null, string $objectName = null)
    {
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 put 方法进行字符串的上传。
        list($ret, $err) = $uploadMgr->put($this->uploadToken, null, $filePath);
        if ($err !== null) {
            return $err;
        } else {
            return $ret;
        }
    }

    //  上传文件
    public function uploadFile (string $filePath = null, string $objectName = null, object $file = null)
    {
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($this->uploadToken, $objectName, $filePath, null, 'application/octet-stream', true, null, 'v2');
        if ($err !== null) {
            return $err;
        } else {
            return $ret;
        }  
    }

    //  判断文件是否存在
    public function hasFile (string $objectPath = null)
    {
        list($fileInfo, $err) = $this->bucketClient->stat($this->bucket, $objectPath);
        if ($err) {
            return $err;
        } else {
            return $fileInfo;
        }
    }

    //  修改文件
    public function renameFile (string $oldName = null, string $newName = null)
    {
        $err = $this->bucketClient->rename($this->bucket, $oldName, $this->bucket, $newName);
        if ($err) {
            return $err;
        }
    }

    //  复制文件
    public function copyFile (string $sourceFile = null, string $targetFile = null)
    {
        $err = $this->bucketClient->copy($this->bucket, $sourceFile, $this->bucket, $targetFile, true);
        if ($err) {
            return $err;
        }
    }

    //  删除文件
    public function deleteFile (array $objectName = null)
    {
        $err = $this->bucketClient->delete($this->bucket, $objectName);
        if ($err) {
            print_r($err);
        }
    }

}