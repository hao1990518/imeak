<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\admin\model\Upload as UploadModel;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/17
 * Time: 14:49
 */
class Upload extends Controller
{

    /**
     * 文件上传
     */
    public function upload()
    {
        $UploadModel = new UploadModel();
        $files = Request::instance()->file('upload_file');
        if($files){
            $thumb = false;
            if(Request::instance()->post('thumb') && Request::instance()->post('thumb')==1){
                $thumb = true;//是否生成缩略图
            };
            // 移动到框架应用根目录/public/uploads/ 目录下
            if(is_array($files)){
                foreach($files as $file){
                    $filePath = $UploadModel->execUpload($file,$thumb);
                }
            }else{
                $filePath = $UploadModel->execUpload($files,$thumb);
            }
            if($filePath['status']==1){
                $this->success('上传成功','',$filePath['url']);
            }else{
                $this->error($filePath['msg']);
            }
        }else{
            echo '上传数据有误';
        }
    }

    /**
     * 头像上传
     */
    public function head()
    {
        $UploadModel = new UploadModel();
        $files = Request::instance()->file('upload_file');
        if($files){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $path = config('upload_path').'/head';
            $filePath = $UploadModel->execUpload($files,true,$path,['width'=>64,'height'=>64]);
            if($filePath['status']==1){
                echo json_encode(['code'=>1,'msg'=>'上传成功','url'=>$filePath['url']]);
            }else{
                echo json_encode(['code'=>0,'msg'=>$filePath['msg']]);
            }
        }else{
            echo json_encode(['code'=>0,'msg'=>'上传数据有误']);
        }
    }
}