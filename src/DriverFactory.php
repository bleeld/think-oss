<?php
declare (strict_types = 1);
namespace think;


use think\oss\driver\sdk\Aliyun;


class DriverFactory
{

    //  创建返回机制
    public static function createOssService($type)
    {
        switch ($type) {
            case 'aliyun':
                return new Aliyun();
            break;
            default:
                throw new \Exception('Invalid OSS type');
        }
    }



}