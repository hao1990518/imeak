<?php
namespace app\admin\controller;
use think\Request;

/**
 * 文件管理控制器
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/23
 * Time: 14:47
 */
class Files extends Base
{
    public function index()
    {
        if(Request::instance()->isPost()){
            $this->get_folder();
        }

        //获取文件夹最小值和最大值
        $dir_list = $this->getDir(config('upload_path'));
//        $min = date('Y-m-d',strtotime($dir_list[0]));
//        $max = date('Y-m-d',strtotime(array_reverse($dir_list)[0]));

        $min = time()-(6*24*3600); //七天前
        $min = date('Y-m-d',$min);
        $max = date('Y-m-d',time());
        $data['min_folder'] = $min;
        $data['max_folder'] = $max;
        if(strtotime($min) > strtotime($max)){
            $data['min_folder'] = $max;
            $data['max_folder'] = $min;
        }
        $this->assign('data',$data);
        return $this->fetch();
    }
    /**
     * 删除文件
     */
    public function delete(){
        $url = Request::instance()->post('url');
        $url =iconv("utf-8","gb2312",$url);
        if(unlink($url)){
            $this->success('删除成功');
        }else {
            $this->error('删除失败');
        }
    }

    /**
     * 删除文件
     */
    public function deleteFolder(){
        $url = Request::instance()->post('name');
        $dir = config('upload_path').'/'.$url;
        if($this->isEmpty($dir)){
            if(rmdir($dir)){
                $this->success('删除成功');
            }else {
                $this->error('删除失败');
            }
        }else{
            $this->error('删除失败,文件夹中有文件');
        }
    }

    /**
     * 获取文件列表
     */
    public function getFile() {
        $name = Request::instance()->post('name');//文件夹名

        $dir = config('upload_path').'/'.$name;
        $fileArray[]=NULL;
        $dir =iconv("utf-8","gb2312",$dir);
        if (false != ($handle = opendir ( $dir ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                $file=iconv("gb2312","utf-8",$file);
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&strpos($file,".")) {
                    $fileArray[$i]['url']=$dir.'/'.$file;
                    $fileArray[$i]['name'] = $file; //文件名称
                    if(!is_image($fileArray[$i]['url'])){ //判断是否是图片
                        $fileArray[$i]['is_img']=false;
                    }else{
                        $fileArray[$i]['is_img']=true;
                    }
                    $file =iconv("utf-8","gb2312",$file);
                    $fileArray[$i]['time'] = filectime($dir.'/'.$file); //文件创建时间

                    if($i==100){
                        break;
                    }
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        if(array_filter($fileArray)){
            $str = $this->fetch('list',['data'=>$fileArray]);
        }else{
            $this->error('没有找到相关文件');
        }
        $this->success('','',$str);
    }


    /**
     * 获取文件目录
     */
    private function get_folder(){
        $data = Request::instance()->post();
        if(!empty($data['start'])){
            $start = strtotime($data['start']);
        }else{
            $start = 0;
        }

        if(!empty($data['end'])){
            $end = strtotime($data['end']);
        }else{
            $end = time();
        }

        $folders = $this->getDir(config('upload_path'));
        $folder = array();
        foreach($folders as $v){
//            $time = filectime($this->_dir.'/'.$v); //文件夹创建时间
            $time = strtotime($v);
            if($time>=$start && $time<=$end){
                $folder[] =$v;
            }
            unset ($v);
        }
        rsort($folder);//倒序
        $this->success('','',$folder);
    }

    //获取文件目录列表,该方法返回数组
    private function getDir($dir) {
        $dirArray[]=NULL;
        if (false != ($handle = opendir ( $dir ))) {
            $i=0;
            while ( false !== ($file = readdir ( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".."&&!strpos($file,".")) {
                    $dirArray[$i]=$file;
                    $i++;
                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        return $dirArray;
    }

    //检查文件夹是否为空
    private function isEmpty($dir){
        if($handle = opendir($dir)){
            while($item = readdir($handle)){
                if ($item != '.' && $item != '..')return false;
            }
        }
        return true;
    }
}