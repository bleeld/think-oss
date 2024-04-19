<?php

return [
    'oss_default'   => 'local',         // 定义默认上传驱动，或者 'aliyun'或者'qiniu',未来会扩展别的驱动
    'oss_type'      => 'driver',        // 定义默认上传驱动类型，或者 'client'（一般不用动）
    'oss_url'       => '',              // 定义最终需要访问的URL域名包含scheme和host部分,此项只是针对本地上传设置的，例如：'http://www.explam.com'

    // 阿里云OSS配置        //  https://ram.console.aliyun.com/manage/ak?spm=a2c8b.20014584.top-nav.dak.6411336a4Vl9Of
    'aliyun_oss' => [
        'accessKeyId'       =>  '',
        'accessKeySecret'   =>  '',
        'endpoint'          =>  '',
        'bucket'            =>  '',			//  存储块名称
        'region'            =>  '',			//  区域
        'domain'            =>  '',			//  最终可以访问的域名
    ],
    // 腾讯云COS配置
    'qcloud_oss' => [
        'secretId'          => '',
        'secretKey'         => '',
        'region'            => '',
        'bucket'            => '',
        'domain'            => '',			//  最终可以访问的域名
    ],
    // 七牛云COS配置
    'qiniu_oss' => [        //  https://portal.qiniu.com/developer/user/key
        'accessKeyId'       => '',
        'accessKeySecret'   => '',
        'region'            => '',			//  区域
        'bucket'            => '',			//  空间
        'domain'            => '',			//  最终可以访问的域名
    ],
    // ... 其他配置
];
