<?php
declare (strict_types = 1);
namespace app\controller;

use think\DriverFactory as Oss;

class Demo
{
    public function index()
    {

        // //  定义驱动
        // $oss_type = 'local';

        // //  实例化驱动
        // $ossClient = Oss::createOssService($oss_type);
        
        
        if (request()->isAjax()) {
            $name = request()->get('name/s', null);
            if(request()->file()){
                $file = request()->file($name);
                $path = 'topic';
                $file = '2.jpg';

                //  定义驱动
                $oss_type = 'local';

                //$oss_type = 'aliyun';

                //$oss_type = 'qiniu';


                //  实例化驱动
                $ossClient = Oss::createOssService($oss_type);

                // //  上传文件
                //$res = $ossClient->uploadFile($path, $file);

                //  删除文件
                //$res = $ossClient->deleteFile($path, $file);

                //  获取文件列表
                //$res = $ossClient->list($path);









                halt($res);

                return 'hello thinkphp8';
            }


            halt(20);
        }

        // if (request()->isAjax())
        // {
        //     $name = request()->get('name/s', null);
        //     if(request()->file()){
        //         $data = request()->file($name);
                

        //         halt($name, $data);
            

        //     }
        //     halt(30);
        //     $data = input('post.');


        //     halt($data);



        //     //  定义驱动
        //     $oss_type = 'local';

        //     //  实例化驱动
        //     $ossClient = Oss::createOssService($oss_type);

        //     //  调用实例化后的具体方法
        //     //$ossList = $ossClient->list();
        //     halt($ossClient);

        //     return 'hello thinkphp8';

        // }

        return view();

    }


}