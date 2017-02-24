<?php
namespace app\admin\controller;
use app\admin\model\Admin;
use think\Request;
use think\Session;
use app\admin\model\Setting as SettingModel;
use app\admin\model\Upload;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/16
 * Time: 16:03
 */
class Setting extends Base
{
    /**
     * 网站设置
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            $url = $this->upload();
            if($url){
                $data['logo'] = $url;
            }else{
                unset($data['logo']);
            }

            if($result = SettingModel::get(1)){
                if(SettingModel::update($data,['id'=>$result->id])){
                    $this->success('编辑成功');
                }else{
                    $this->error('编辑失败');
                }
            }else{
                if(SettingModel::create($data)){
                    $this->success('编辑成功');
                }else{
                    $this->error('编辑失败');
                }
            }
        }
        $data = SettingModel::get(1);
        $this->assign('data',$data);
        return $this->fetch();
    }
    /**
     * 修改个人信息
     * @return mixed
     */
    public function personal()
    {
        $Admin = new Admin();
        $uid = Session::get(config('user_auth_key'));
        if(Request::instance()->isPost()){
            $data= Request::instance()->Post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['uid'] = $uid;
            if($result = $Admin->getAdminInfo(['uid'=>$uid])){
                $data['id']=$result['uid'];
            }
            if($Admin->saveAdminInfo($data)){
                $this->success('编辑成功');
            }else{
                $this->success('编辑失败');
            }
        }
        $data = $Admin->getAdminInfos(['imk_admin.id'=>$uid]);
        $data['birthday'] = date('Y-m-d',$data['birthday']);
        $this->assign('data',$data);
        return $this->fetch('personal');
    }

    /**
     * 修改个人密码
     * @return mixed
     */
    public function password()
    {
        if(Request::instance()->isPost()){
            $pwd = Request::instance()->post('pwd');
            $new_pwd = Request::instance()->post('new_pwd');
            $re_pwd = Request::instance()->post('re_pwd');
            if($new_pwd!=$re_pwd){
                $this->error('两次输入密码不一致');
            }
            $admin = Admin::get(['id'=>Session::get(config('user_auth_key'))]);
            $pwd = sha1($admin->username.$pwd.'imk');
            if(Admin::get(['id'=>Session::get(config('user_auth_key')),'password'=>$pwd])){
                $password = sha1($admin->username.$new_pwd.'imk');
                if(Admin::update(['password'=>$password],['id'=>$admin['id']])){
                    $this->success('密码修改成功');
                }else{
                    $this->error('密码修改失败');
                }
            }else{
                $this->error('原始密码输入错误');
            }
        }
        return $this->fetch('password');
    }

    /**
     * 文件上传
     * @return bool
     */
    private function upload()
    {
        $UploadModel = new Upload();
        $files = Request::instance()->file();
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
                return $filePath['url'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}