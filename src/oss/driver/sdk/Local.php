<?php
declare (strict_types = 1);
namespace think\oss\driver\sdk;

// 以SDK的方式链接
use think\oss\interface\SdkInterface;

//  引入SDK
use think\File;
use League\Flysystem\Filesystem as LocalClient;
use League\Flysystem\Local\LocalFilesystemAdapter as LocalClientAdapter;

class Local implements SdkInterface
{
    private $endpoint;
    private $bucket;
    private $target_path_dir;
    public $ossClient;
    public $region;

    
    //  初始化OSS实例
    public function __construct ($targetPathDir=null) {
        //  获取配置项，并赋值给对象$config
        $config = config('filesystem');
        //  获取aliyun-oss默认配置
        $config = $config['disks'][$config['default']];
        //  为OSS初始化赋值
        $this->bucket = env("OSS_ALIYUN.ALIYUN_BUCKET", $config['bucket']);
        $this->region = env("OSS_ALIYUN.ALIYUN_REGION", $config['region']);
        //  设置存放目标文件的目录
        $this->target_path_dir = $targetPathDir ? $targetPathDir : 'strong';
        //  定义适配器
        $adapter = new LocalClientAdapter(
            public_path() . 'storage' . DIRECTORY_SEPARATOR
        );
        //  实例化实例
        $this->ossClient = new LocalClient($adapter);
        //  返回实例
        return $this->ossClient;
    }

    /**
     * @function_name list 获取OSS-Bucket中的文件列表
     * @param string $pathDir 需要获取存储文件的目录
     * @return array||string 返回数据列表或者错误信息
     */
    public function list (string $pathDir = null, bool $v2 = false)
    {
        //  处理流程
        try {
            //  列出目录下的内容
            $listContents = $this->ossClient->listContents($this->target_path_dir);
            if (!empty($objectList)) {
                //  定义需要返回的数据格式
                $ret = [];
                foreach ($objectList as $objectInfo) {
                    $ret[] = [
                        'fileName'   =>  $objectInfo->getKey(),
                        'fileSize'  =>  $objectInfo->getSize(),
                        'fileInfo'  =>  $objectInfo->getLastModified(),
                        'fileType'  =>  pathinfo($objectInfo->getKey(), PATHINFO_EXTENSION),
                    ];
                }
                return $ret;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    //  判断目录是否存在
    public function hasBucket (string $bucket = null)
    {
        //  校验参数是否存在
        if (!$bucket) { return false;}
        //  判断逻辑
        if (is_dir($bucket)) {
            return true;
        } else {
            return false;
        }
    }

    //  获取区域
    public function getRegions (string $bucket = null)
    {
        return 'Local';
    }

    //  上传字符串
    public function uploadStr(string $filePath = null, string $objectName = null)
    {
        //  校验参数是否存在
        if ( !($objectName && $filePath) ) { return false;}
        //  执行流程
        $this->ossClient->writeStream($objectName, $filePath);

    }

    //  上传文件
    public function uploadFile(string $filePath = null, string $objectName = null, object $file = null)
    {
        //  校验参数是否存在
        if ( !($objectName && $filePath) ) { return false;}
        //  执行流程
        $this->ossClient->write($objectName, $filePath);

    }

    //  获取bucket信息
    public function getBucketInfo (string $bucket = null)
    {
        return null;
    }

    //  获取bucketMeta信息
    public function getBucketMeta (string $bucket = null)
    {
        return null;  
    }

    //  获取bucket状态
    public function getBucketStat (string $bucket = null)
    {
        return null;  
    }

    //  删除bucket
    public function delBucket (string $bucket = null)
    {
        $this->ossClient->deleteDirectory($this->bucket);
    }

    //  判断文件是否存在
    public function hasFile (string $objectPath = null)
    {
        $this->ossClient->fileExists($objectPath);
    }

    //  重命名文件
    public function renameFile (string $oldName = null, string $newName = null)
    {
        if (file_exists($oldName) && @rename($oldName, $newName)) {
            return true;
        } 
        return false;
    }

    //  复制文件
    public function copyFile (string $sourceFile = null, string $targetFile = null)
    {
        $this->ossClient->copy($sourceFile, $targetFile);
    }
    public function deleteFile (array $objectName = null)
    {
        if (is_array($objectName) && !empty($objectName)) {
            foreach ($objectName as $v) {
                if(!$this->ossClient->delete($v)){
                    return false;
                };
            }
            return true;
        }
        return false;
    }
}