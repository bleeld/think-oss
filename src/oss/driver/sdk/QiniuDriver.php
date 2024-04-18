<?php
declare (strict_types = 1);
namespace think\oss\driver\sdk;

// 以SDK的方式链接
use think\oss\interface\SdkInterface;

require_once(root_path() . "vendor/autoload.php");
//  引入SDK
use think\File;
use QiniuOSS\Auth;
use QiniuOSS\Config;
use QiniuOSS\Storage\UploadManager;
use QiniuOSS\Storage\BucketManager;

class QiniuDriver implements SdkInterface
{

    private $accessKeyId;
    private $accessKeySecret;
    private $bucket;
    private $domain;
    private $target_path_dir;       //  用户需要自己定义的文件目录名称
    private $uploadToken;
    public $authClient;
    public $bucketClient;
    public $uploadClient;

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
        $this->domain = env("OSS_QINIU.REGION", $config['region']);
        //  设置存放目标文件的目录
        $this->target_path_dir = $targetPathDir ? $targetPathDir : 'strong';

        //  实例化实例
        $this->authClient = new Auth($this->accessKeyId, $this->accessKeySecret);
        $this->uploadToken = $this->authClient->uploadToken($this->bucket);

        //  实例化操作实例
        $this->bucketClient = new BucketManager($this->authClient, new Config());
        $this->uploadClient = new UploadManager();

        //  【上传client】
        $this->uploadToken = $this->getUploadToken();


        //list($ret, $error) = $this->uploadFile(public_path().'static/cup.jpg', public_path().'static/cup.jpg');
        // $ret = $this->uploadClient->putFile($this->uploadToken, 'cup.jpg', public_path().'static/cup.jpg');
        
        
        //halt($ret);


        //  返回实例
        //return $this->ossClient;
    }

    //获取上传凭证后表单上传
    private function getUploadToken()
    {
        return $this->authClient->uploadToken($this->bucket);
    }

    /**
     * @method list 列取空间的文件列表
     * @param string $prefix 列举前缀,比如想列举topic目录下所有文件，就用 topic/
     * @param string $marker 列举标识符
     * @param int $limit 单次列举个数限制
     * @param string $delimiter 指定目录分隔符
     *
     * @return array
     * @link  https://developer.qiniu.com/kodo/api/1284/list
     */
    public function list (string $prefix = null, string $marker = null, int $limit = 1000, string $delimiter = null)
    {
        try{
            if (!is_null($prefix) && !empty($prefix)) {
                $prefix .= '/';
            }
            // 列举文件
            list($ret, $err) = $this->bucketClient->listFiles($this->bucket, $prefix, $marker, $limit, $delimiter);
            if ($err !== null) {
                return $err;
            } else {
                $files = [];
                if(!empty($ret['items'])){
                    foreach ($ret['items'] as $item) {
                        $files[] = [
                            'retivePath'    =>  $item['key'],
                            'fileType'      =>  $item['mimeType'],
                            'saveFileName'  =>  $item['key'],
                            'extName'       =>  pathinfo($item['key'], PATHINFO_EXTENSION),
                        ];
                    }
                }
                return $files;
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }


    /**
     * @method uploadFile 直传文件
     * @param string $key 上传后需要保存的文件名
     * @param string $filePath 需要上传的文件的实际路径
     */
    //  上传文件流
    public function uploadFile(string $path = null, File $file = null, string $rule = null, array $options = [])
    {
        if (is_null($file) && empty($file)) {
            return false;
        }
        try {
            $savePath   = trim($path . '/' . $file->hashName($rule), '/');
            $savePath = str_replace('\\', '/', $savePath);
            //halt($savePath, $file->getRealPath(), $file);
            // 调用 UploadManager 的 putFile 方法进行文件的上传。
            list($result, $err) = $this->uploadClient->putFile($this->uploadToken, $savePath, $file->getRealPath(), null, 'application/octet-stream', true, null, 'v2');
            if ($err) {
                return $err;
            } else {
                return [
                    'fullPath'      =>  'http://' . $this->domain . '/' . $result['key'],
                    'rootPath'      =>  'http://' . $this->domain . '/',
                    'fileCustomPath' => $path,
                    'retivePath'    =>  $result['key'],
                    'fileretiveName' => $file->hashName($rule),
                    'saveFileName'  =>  basename($savePath),
                ];
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    //  删除文件
    public function deleteFile (string $target_path_dir = null, string $filePathName = null)
    {
        //  判断参数是否合法
        if (is_null($target_path_dir) || empty($target_path_dir)) {
            $target_path_dir = $this->target_path_dir;
        }
        //  定义必要参数
        $fileRealPath =  $target_path_dir . '/' . $filePathName;
        //  执行操作流程
        list($ret, $err) = $this->bucketClient->delete($this->bucket, $fileRealPath);
        return ($err != null) ? false : true;
    }

}