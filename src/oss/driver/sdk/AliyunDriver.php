<?php
declare (strict_types = 1);
namespace think\oss\driver\sdk;

// 以SDK的方式链接
use think\oss\interface\SdkInterface;
//  引入SDK
use think\File;
use AliOSS\OssClient;
use AliOSS\Core\OssUtil;
use AliOSS\Core\OssException;

class AliyunDriver implements SdkInterface
{

    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $bucket;
    public $ossClient;
    public $region;
    private $target_path_dir;

    //  初始化OSS实例
    public function __construct ($targetPathDir=null) {
        //  获取配置项，并赋值给对象$config
        $config = config('oss');
        //  获取aliyun-oss默认配置
        $config = $config['aliyun_oss'];
        //  为OSS初始化赋值
        $this->accessKeyId = env('OSS_ALIYUN.ALIYUN_ACCESSID', $config['accessKeyId']);
        $this->accessKeySecret = env("OSS_ALIYUN.ALIYUN_ACCESSSECRET", $config['accessKeySecret']);
        $this->endpoint = env("OSS_ALIYUN.ALIYUN_ENDPOINT", $config['endpoint']);
        $this->bucket = env("OSS_ALIYUN.ALIYUN_BUCKET", $config['bucket']);
        $this->region = env("OSS_ALIYUN.ALIYUN_REGION", $config['region']);
        //  设置存放目标文件的目录
        $this->target_path_dir = $targetPathDir ? $targetPathDir : 'strong';
        //  实例化实例
        $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        
        //  返回实例
        //return $this->ossClient;
    }

    /**
     * @function_name list 获取OSS-Bucket中的文件列表
     * @param string $prefix 需要获取存储文件的目录,比如想列举topic目录下所有文件，就用 topic/
     * @return array||string 返回数据列表或者错误信息
     */
    public function list (string $prefix = null, bool $v2 = false)
    {
        //  处理流程
        try {
            if (!is_null($prefix) && !empty($prefix)) {
                $prefix .= '/';
            }
            if ( $v2 ) {
                $listObjectInfo = $this->ossClient->listObjectsV2($this->bucket, ['prefix' => $prefix]);
            } else {
                $listObjectInfo = $this->ossClient->listObjects($this->bucket, ['prefix' => $prefix]);                    
            }
            $objectList = $listObjectInfo->getObjectList();
            if (!empty($objectList)) {
                //  定义需要返回的数据格式
                $ret = [];
                foreach ($objectList as $objectInfo) {
                    $ret[] = [
                        'retivePath'        =>  $objectInfo->getKey(),
                        'fileCustomPath'    =>  $prefix,
                        'saveFileName'      =>  str_replace('\\', '/', $objectInfo->getKey()),
                        'extName'           =>  pathinfo($objectInfo->getKey(), PATHINFO_EXTENSION),
                    ];
                }
                return $ret;
            }
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @function_name   uploadStr  上传文件流
     * @param string||null $objectName 定义需要存储的文件路径
     * @param string||null $content 需要存储的内容
     * @param array||null $options 条件
     * 
     * @return array||string||null 
     */
    public function uploadFile(string $path = null, File $file = null, string $rule = null, array $options = [])
    {
        if (is_null($file) && empty($file)) {
            return false;
        }
        try {
            $contents = fopen($file->getRealPath(), 'r');
            $savePath   = trim($path . '/' . $file->hashName($rule), '/');
            $savePath = str_replace('\\', '/', $savePath);
            $result = $this->ossClient->uploadStream($this->bucket, $savePath, $contents, $options);
            if (is_resource($contents)) {
                fclose($contents);
            }
            if (is_null($result)) {
                return false;
            }
            return [
                'fullPath'          =>  $result['info']['url'],
                'rootPath'          =>  $this->bucket. '.' . $this->endpoint,
                'fileCustomPath'    =>  $path,
                'retivePath'        =>  $savePath,
                'fileRetiveName'    =>  $file->hashName($rule),
                'saveFileName'      =>  basename($savePath),
            ];
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @method deleteFile
     * @param string|null $filePathName 需要删除的文件,带一级目录（年月日时分秒）
     * 
     * @return bool
     */
    public function deleteFile (string $target_path_dir = null, string $filePathName = null)
    {
        //  判断数据是否存在
        if (!( $filePathName )) { return false; }
        //  定义必要参数
        $filePath = (is_null($target_path_dir) ? $this->target_path_dir : $target_path_dir) . '/' . $filePathName;
        //$filePath = str_replace('\\', '/', $filePath);
        //  处理删除流程
        try{
            $result = $this->ossClient->deleteObject($this->bucket, $filePath);
            return true;
        } catch(OssException $e) {
            return $e->getMessage();
        }
        return true;
    }

}

// 重复上述步骤，为腾讯云COS、华为云OBS、七牛云Kodo创建服务类