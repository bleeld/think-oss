# thinkphp6.0及以上OSS扩展
Thinkphp6.0 and above OSS extensions
- 本插件经thinkphp官方推荐认证扩展，请放心使用
- 开发者基本礼仪，star一下
## 安装
> composer require lx3gp/think-oss

## 更新
> composer update lx3gp/think-oss

## 卸载
> composer remove lx3gp/think-oss

## 兼容版本
- thinkphp 6.0及以上版本

## 配置
```
// oss配置
[
    'oss_default' => 'aliyun', // 或者 'qcloud'或者'huawei'或者'qiniu'
    'oss_type' => 'driver', // 或者 'client'

    // 阿里云OSS配置
    'aliyun_oss' => [
        'accessKeyId'       =>  '',
        'accessKeySecret'   =>  '',
        'endpoint'          =>  '',
        'bucket'            =>  '',                     //  存储块名称
        'region'            =>  '',                     //  区域
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
        'secretId'       => '',
        'secretKey'      => '',
        'region'         => '',
        'bucket'         => '',
    ],
    // ... 其他配置
],
```