<?php

return [
    'oss_default' => 'aliyun', // 或者 'qcloud'或者'huawei'或者'qiniu'
    'oss_type' => 'driver', // 或者 'client'

    // 阿里云OSS配置
    'aliyun_oss' => [
        'accessKeyId'       =>  '',
        'accessKeySecret'   =>  '',
        'endpoint'          =>  '',
        'bucket'            =>  '',                   //  存储块名称
        'region'            =>  '',                       //  区域
    ],
    // 腾讯云COS配置
    'qcloud_oss' => [
        'secretId'       => '',
        'secretKey'      => '',
        'region'         => '',
        'bucket'         => '',
    ],
    // 华为云COS配置
    'huawei_oss' => [
        'secretId'       => '',
        'secretKey'      => '',
        'region'         => '',
        'bucket'         => '',
    ],
    // 七牛云COS配置
    'qiniu_oss' => [
        'accessKeyId'		=> '',
        'accessKeySecret'	=> '',
        'bucket'			=> '',
        'domain'			=> '',
    ],
    // ... 其他配置
];
