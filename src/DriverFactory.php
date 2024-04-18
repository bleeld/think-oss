<?php
declare (strict_types = 1);
namespace think;


use think\oss\driver\sdk\AliyunDriver;
use think\oss\driver\sdk\QiniuDriver;
use think\oss\driver\sdk\localDriver;
class DriverFactory
{

    //  创建返回机制
    public static function createOssService($type)
    {
        switch ($type) {
            case 'aliyun':
                return new AliyunDriver();
            break;
            case 'qiniu':
                return new QiniuDriver();
            break;
            case 'local':
                return new LocalDriver();
            break;
            default:
                throw new \Exception('Invalid OSS type');
        }
    }



}