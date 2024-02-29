<?php
declare (strict_types = 1);
namespace think\oss\driver\sdk;

// 以SDK的方式链接
use think\oss\interface\SdkInterface;

use AliOSS\OssClient;
use AliOSS\Core\OssUtil;
use AliOSS\Core\OssException;

class Aliyun implements SdkInterface
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
        $this->target_path_dir = $targetPathDir ? $targetPathDir : 'upload/';
        //  实例化实例
        $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
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
            if ( $v2 ) {
                $listObjectInfo = $this->ossClient->listObjectsV2($this->bucket, ['prefix' => $pathDir]);
            } else {
                $listObjectInfo = $this->ossClient->listObjects($this->bucket, ['prefix' => $pathDir]);                    
            }
            $objectList = $listObjectInfo->getObjectList();
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
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method hasBucket 判断是否存在指定的bucket
     */
    public function hasBucket (string $bucket = null)
    {
        //  判断参数是否存在
        if (!$bucket) { $bucket = $this->bucket; }
        try {
            //  判断bucket是否存在
            $res = $this->ossClient->doesBucketExist($bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
        return ($res === true) ? true : false;
    }

    /**
     * @method getRegions 判断指定的bucket所在的区域
     */
    public function getRegions (string $bucket = null)
    {
        //  判断参数是否存在
        if (!$bucket) { $bucket = $this->bucket; }
        try {
            //  返回bucket所在的区域
            return $this->ossClient->getBucketLocation($bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method getBucketInfo 获取指定的bucket的信息
     */
    public function getBucketInfo (string $bucket = null)
    {
        //  判断参数是否有效
        if (!$bucket) { $bucket = $this->bucket; }
        try {
            // 获取存储空间的信息，包括存储空间名称（Name）、所在地域（Location）、创建日期（CreateDate）、存储类型（StorageClass）、外网访问域名（ExtranetEndpoint）以及内网访问域名（IntranetEndpoint）等。
            $bucketInfo = $this->ossClient->getBucketInfo($bucket);    
            return [
                'bucket_name'                   =>  $bucketInfo->getName(),
                'bucket_location'               =>  $bucketInfo->getLocation(),
                'bucket_creat_time'             =>  $bucketInfo->getCreateDate(),
                'bucket_storage_class'          =>  $bucketInfo->getStorageClass(),
                'bucket_extranet_endpoint'      =>  $bucketInfo->getExtranetEndpoint(),
                'bucket_intranet_endpoint'      =>  $bucketInfo->getIntranetEndpoint(),
            ];
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method getBucketMeta 获取指定的bucket空间的元数据信息
     */
    public function getBucketMeta (string $bucket = null)
    {
        //  判断参数是否有效
        if (!$bucket) { $bucket = $this->bucket; }
        try {
            return $this->ossClient->getBucketMeta($bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method getBucketStat 获取指定的bucket存储空间信息
     */
    public function getBucketStat (string $bucket = null)
    {
        //  判断参数是否有效
        if (!$bucket) { $bucket = $this->bucket; }
        try {
            $stat = $this->ossClient->getBucketStat($bucket);
            return [
                'current_storage'                   =>  $stat->getStorage(),                            //  获取Bucket的总存储量，单位为字节
                'object_count'                      =>  $stat->getObjectCount(),                        //  获取Bucket中总的Object数量
                'multipart_upload_is'               =>  $stat->getMultipartUploadCount(),               //  获取Bucket中已经初始化但还未完成（Complete）或者还未中止（Abort）的Multipart Upload数量
            ];
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method getBucketStat 删除指定的bucket存储空间
     */
    public function delBucket (string $bucket = null)
    {
        //  判断参数是否有效
        if (!$bucket) { $bucket = $this->bucket; }
        try {
            return $this->ossClient->deleteBucket($bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @function_name   uploadStr  上传字符串
     * @param string||null $objectName 定义需要存储的文件
     * @param string||null $content 需要存储的内容
     * @param array||null $options 条件
     * 
     * @return array||string||null 
     */
    public function uploadStr (string $objectName = null, string $content = null, array $options = null)
    {
        if ( !($objectName && $content) ) { return false; }
        // 上传时可以设置相关的headers，例如设置访问权限为private、自定义元数据等。
        $options = [
            OssClient::OSS_HEADERS => [
                'x-oss-object-acl' => 'public',
                'x-oss-meta-info' => 'fileinfo'                
            ]
        ];
        try{
            return $this->ossClient->putObject($this->bucket, $objectName, $content, $options);
        } catch(OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @function_name   uploadFile  上传文件
     * @param string $filePath  用户目标文件
     * @param string $objectName  定义需要存储的文件
     * @param object $file 文件本身
     * 
     * @return array||string||null 
     */
    public function uploadFile (string $filePath = null, string $objectName = null, object $file = null)
    {
        //  判断数据是否存在
        if (!($filePath && $objectName && $file)) { return false; }

        //  实现阿里云OSS上传逻辑
        try{
            //  定义需要存储的目标文件路径
            $objectName = $this->target_path_dir . $objectName;
            $fileInfo = $this->ossClient->uploadFile($this->bucket, $objectName, $filePath);
        } catch(OssException $e) {
            return $e->getMessage();
        }
        return [
            'callbackUrl'   =>  $this->endpoint . $objectName,
            'fielName'      =>  $file->getOriginalName(),
            'fileAttr'      =>  $file->getOriginalExtension(),
            'fileExtension' =>  $file->extension(),
            'fileInfo'      =>  $fileInfo,
        ];
    }

    /**
     * @method fileDownload
     */
    public function downloadFile (string $objectPath = null, string $localfile = null)
    {
        //  判断数据是否存在
        if ( !($objectPath && $localfile) ) { return false; }
        //  执行流程
        try{
            // 如果未指定本地路径，则下载后的文件默认保存到示例程序所属项目对应本地路径中。
            $options = [
                OssClient::OSS_FILE_DOWNLOAD => $localfile            
            ];
            $this->ossClient->getObject($this->bucket, $objectPath, $options);
        } catch(OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method hasFile 判断文件是否存在
     * @param string|null $objectPath 目标文件
     * 
     * @return boolean
     */
    public function hasFile(string $objectPath = null) {
        //  判断数据是否存在
        if ( !$objectPath ) { return true; }
        //  执行流程
        try{
            return $this->ossClient->doesObjectExist($this->bucket, $objectPath);
        } catch(OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method rename 文件重命名
     * @param $oldName 源文件
     * @param $newName 目标文件
     * 
     * @return bool
     */
    public function renameFile(string $oldName = null, string $newName = null) {
        //  判断数据是否存在
        if (!( $oldName && $newName )) {
            return false;
        }
        //  执行流程
        try {
            $this->ossClient->copyObject($this->bucket, $oldName, $this->bucket, $newName);   //  将srcobject.txt拷贝至同一Bucket下的destobject.txt。
            $this->ossClient->deleteObject($this->bucket, $oldName);                          //  删除源文件
            return true;
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @method copyFile 复制文件
     * @param string|null $sourceFile 源文件
     * @param string|null $targetFile 目标文件
     * @param string|null $from_bucket 源bucket
     * @param string|null $to_bucket 目标bucket
     * 
     * @return bool
     */
    public function copyFile (string $sourceFile = null, string $targetFile = null, string $from_bucket = null, string $to_bucket = null) {
        //  判断数据是否存在
        if (!( $sourceFile && $targetFile )) {
            return false;
        }
        //  判断需要复制的文件是否在同一个bucket
        if ( !($from_bucket && $to_bucket) ) {
            $from_bucket = $to_bucket = $this->bucket;
        }
        //  开始执行复制流程
        try{
            $this->ossClient->copyObject($from_bucket, $sourceFile, $to_bucket, $targetFile);
        } catch(OssException $e) {
            return $e->getMessage();
        }
        return true; 
    }


    /**
     * @method deleteFile
     * @param array|null $objectName 需要删除的文件
     * 
     * @return bool
     */
    public function deleteFile (array $objectName = null)
    {
        //  判断数据是否存在
        if (!( $objectName )) { return false; }
        //  处理删除流程
        try{
            $result = $this->ossClient->deleteObjects($this->bucket, $objectName);
        } catch(OssException $e) {
            return $e->getMessage();
        }
        return true;
    }

}

// 重复上述步骤，为腾讯云COS、华为云OBS、七牛云Kodo创建服务类
