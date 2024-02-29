<?php

// Interface/OssInterface.php
namespace think\oss\interface;

interface SdkInterface
{
    public function list ();
    public function hasBucket (string $bucket = null);
    public function getRegions (string $bucket = null);
    public function getBucketInfo (string $bucket = null);
    public function getBucketMeta (string $bucket = null);
    public function getBucketStat (string $bucket = null);
    public function delBucket (string $bucket = null);
    public function uploadStr (string $filePath = null, string $objectName = null);
    public function uploadFile (string $filePath = null, string $objectName = null, object $file = null);
    public function hasFile (string $objectPath = null);
    public function renameFile (string $oldName = null, string $newName = null);
    public function copyFile (string $sourceFile = null, string $targetFile = null);
    public function deleteFile (array $objectName = null);
    // ... 其他通用方法
}