<?php
namespace app\admin\model;
use think\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/18
 * Time: 11:06
 */
class Upload extends Model{
    //允许上传类型
    private $img_ext = ['jpg','gif','png'];
    private $file_ext = ['txt','doc','xls'];

    /**
     * 执行上传
     * @param $files
     * @param bool $thumb //是否生成缩略图
     * @param string $path //保存路径
     * @param array $size //图片尺寸
     * @return array
     */
    public function execUpload($files,$thumb,$size=[],$path="")
    {
        $fileInfo = $files->getInfo(); //获取图片信息

        $nameExt = explode('.',$fileInfo['name']); //获取图片后缀
        $name  = $nameExt[0];
        $ext  = $nameExt[1];
        if(empty($path)){
            if(!in_array(strtolower($ext),$this->img_ext) && !in_array(strtolower($ext),$this->file_ext)){
//                $path = config('upload_path').'/images';
//            }elseif (in_array($ext,$this->file_ext)){
//                $path = config('upload_path').'/files';
//            }else{
                $this->error('文件类型错误');
            }
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
//        $info = $files->move($path,md5($name.time()));
        $path = config('upload_path');
        $info = $files->move($path);

        if($info){
            $filePath = $path.'/'.date('Ymd',time()).'/'.$info->getFilename();
            $filename = $info->getFilename(); //文件名
            $new_file = explode('.',$filename);

            //生成缩略图
            if($thumb){
                $image = \think\Image::open($filePath);
                if($size){
                    if($image->width()>$size['width'] || $image->height()>$size['height']){
                        $image->thumb($size['width'], $size['height'])->save($path.'/'.date('Ymd',time()).'/'.$new_file[0].'-'.$size['width'].'x'.$size['height'].'.'.$ext);
                    }
                }else{
                    if($image->width()>64 || $image->height()>64){
                        $image->thumb(64, 64)->save($path.'/'.date('Ymd',time()).'/'.$new_file[0].'-64x64.'.$ext);
                    }
                    if($image->width()>150 || $image->height()>150){
                        $image->thumb(150, 150)->save($path.'/'.date('Ymd',time()).'/'.$new_file[0].'-150x150.'.$ext);
                    }
                }

            }
            $filePath = ltrim($filePath, ".");
            // 成功上传后 获取上传信息
            return ['status'=>1,'url'=>$filePath];
        }else{
            // 上传失败获取错误信息
            return ['status'=>0,'msg'=>$files->getError()];
        }
    }
}