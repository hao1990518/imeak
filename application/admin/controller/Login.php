<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\admin\model\Admin;
use think\Session;

/**
 * Created by PhpStorm.
 * 登录控制器
 * User: Administrator
 * Date: 2016/8/10
 * Time: 12:58
 */
class Login extends Controller{

    /**
     * 登录
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if(!captcha_check($data['verify_code'])){
                //验证失败
                $this->error('验证码错误');
            }else{
                $password = $data['username'].$data['password'].'imk';
                if($result = Admin::get(['username'=>$data['username'],'password'=>sha1($password)])){
                    if(empty($result['status'])){
                        $this->error('用户名密码错误或账户被禁用');
                    }
                    $result['username'] == config('super_admin')?Session::set('admin_auth_key',true):Session::set('admin_auth_key',false);
                    Session::set(config('user_auth_key'),$result['id']);
                    Session::set('username',$result['username']);

                    $logData = [
                        'login_ip'=>Request::instance()->ip(),
                        'login_dt'=>time(),
                        'login_num'=>$result['login_num']+1
                    ];

                    Admin::update($logData,['id'=>$result['id']]);
                    $this->success('登录成功',url('Index/index'));
                }else{
                    $this->error('用户名密码错误或账户被禁用');
                }
            }
        }else{
            return $this->fetch();
        }

    }

    /**
     * 登出
     */
    public function logOut()
    {
        Session::clear();
        $this->redirect(url('Login/index'));
    }

    public function noAuth()
    {
        $title = Request::instance()->param('title');
        $remarks = Request::instance()->param('remarks');
        $content = Request::instance()->param('content');
        $data['title'] = empty($title) ? "404" : $title;
        $data['remarks'] = empty($remarks) ? "页面未找到！" : $remarks;
        $data['content'] = empty($content) ? "抱歉，页面好像去火星了~" : $content;
        $this->assign('data',$data);
        return $this->fetch('404');
    }
}