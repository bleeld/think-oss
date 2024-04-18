<?php
declare (strict_types = 1);
namespace think\oss\driver\sdk;

// 以SDK的方式链接
use think\oss\interface\SdkInterface;

//  引入SDK
use think\File;
use think\facade\Filesystem;

class LocalDriver implements SdkInterface
{
    private $rootPath;
    private $bucket;
    private $target_path_dir;   //  用户需要自己定义的目录名称
    public $ossClient;
    
    //  初始化OSS实例
    public function __construct ($targetPathDir=null) {
        //  获取配置项，并赋值给对象$config
        $config = config('filesystem');
        //  定义区块
        $this->bucket = $config['default'];
        //  实例化实例
        $this->ossClient = Filesystem::disk($this->bucket);
        //  设置存放目标文件的目录(这个是前端要指定文件夹所传递的文件夹名称)
        $this->target_path_dir = $targetPathDir ? $targetPathDir : 'topic';
        //  定义本地文件操作的根目录
        $this->rootPath = $config['disks'][$this->bucket]['root'];
    }


    /**
     * @function_name list 获取指定目录下的所有文件
     * @param string $path 需要获取存储文件的目录
     * @param bool $isDeep 是否需要穿透
     * 
     * @return array||string 返回数据列表或者错误信息
     */
    public function list(string $path = null, bool $isDeep = false)
    {
        if (is_null($path) || empty($path)) {
            $path = $this->target_path_dir;
        }
        $realPath = $this->rootPath . DIRECTORY_SEPARATOR . $path . '/';
        //halt($realPath);
        $files = $this->getFolderOrFileinfo($realPath, $isDeep);
        if (!empty($files)) {
            $__files__ = [];
            foreach ($files as $file) {
                $__files__[] = [
                    'fileFullPath' => $file,
                    'fileRootPath' => $this->rootPath,
                    'fileCustomPath' => $path,
                    'fileretiveName' => ltrim(str_replace('\\', '/', str_ireplace($this->rootPath, '', $file)), '/'),
                    'fileName'      =>  basename($file),
                ];
            }
            $files = $__files__;
        }
        return $files;
    }

    //  上传文件(图片、视频、文件)
    public function uploadFile(string $path = null, File $file = null, string $rule = null,  array $options = [])
    {
        try{
            //  判断参数是否合法
            if(is_null($file)){ return false; }
            if(is_null($path) || empty($path)){ 
                $path = $this->target_path_dir;
            }
            //  定义必要参数
            $savePath = $file->hashName($rule);
            $savePath = str_replace('\\', '/', $savePath);
            //  执行上传流程
            $filePath = $this->ossClient->putFileAs($path, $file, $savePath);
            if ($filePath){
                return [
                    'fullPath' =>    $filePath,
                    'rootPath' =>    $this->rootPath,
                    'fileCustomPath' => $path,
                    'retivePath'    =>  $savePath,
                    'fileretiveName' => $file->hashName($rule),
                    'saveFileName'  =>  basename($savePath),
                ];
            }
            return false;
        }catch(\Exception $e){
            return false; 
        }
    }

    //  删除文件(图片、视频、文件)
    public function deleteFile(string $target_path_dir = null, string $filePathName = null)
    {
        try{
            //  判断参数是否合法
            if (is_null($target_path_dir) || empty($target_path_dir)) {
                $target_path_dir = $this->target_path_dir;
            }
            //  定义必要参数
            $fileRealPath =  $this->rootPath . $target_path_dir . $filePathName;
            //  执行上传流程
            if (file_exists($fileRealPath)) {
                @unlink($fileRealPath);
            }
            return true;
        }catch(\Exception $e){
            return false; 
        }
    }

    //	获取指定文件夹下文件
    private function getFolderOrFileinfo(string $dir = null, bool $includeFilePath = true, bool $includeSubdirs = false)
    {
        if (is_null($dir)) { $dir = $this->target_path_dir; }
        $files = [];
        // 使用 RecursiveDirectoryIterator 遍历目录
        $iterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        if ($includeSubdirs) {
            $recursiveIterator = new \RecursiveIteratorIterator($iterator);
        } else {
            $recursiveIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::LEAVES_ONLY);
        }
        // 遍历目录中的每个文件
        foreach ($recursiveIterator as $file) {
            // 确保是文件而不是目录
            if ($file->isFile()) {
                $files[] = $includeFilePath ? basename($file->getPathname()) : $file->getPathname();
            }
        }
        return $files;
    }

	/**
	 * 将字节转换为可读文本 sizeFormatBytes
	 * @param int    $size      大小
	 * @param string $delimiter 分隔符
	 * @param int    $precision 小数位数
	 * @return string
	 */
	private function sizeFormatBytes($size, $delimiter = '', $precision = 2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
		for ($i = 0; $size >= 1024 && $i < 6; $i++) {
			$size /= 1024;
		}
		return round($size, $precision) . $delimiter . $units[$i];
	}

}