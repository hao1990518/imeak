<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;

/**
 * 公用类
 * Class Base
 * @package app\admin\controller
 */
class Base extends Controller
{
    public function _initialize () {
        $this->checkAuth();
    }

    /**
     * 权限验证
     */
    private function checkAuth(){
        $AUTH = new \think\Auth;
        //类库位置应该位于ThinkPHP\Library\Think\
        $id = Session::get(config('user_auth_key'));
        if(!$id){
            $this->redirect(url('Login/index'));
        } elseif(!$AUTH->check($this->request->module().'/'.$this->request->controller().'/'.$this->request->action(),$id) && !Session::get('admin_auth_key')){
//            p($this->request->module().'/'.$this->request->controller().'/'.$this->request->action().'<br/>无权限');
            if(Request::instance()->isAjax()){
                $this->error('没有访问权限，请联系超级管理员!');
            }else{
                $this->redirect(url('login/noAuth',['title'=>'900','remarks'=>'没有访问权限','content'=>'请联系管理员提升权限~']));
            }
        }
    }


}