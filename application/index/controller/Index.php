<?php
namespace app\index\controller;

use think\Controller;
use app\admin\model\Feedback;
use think\Request;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch('index-video');
    }
    /**
     * 联系我们
     * @return mixed
     */
    public function contact()
    {
        if(Request::instance()->isPost()){
            $data =  Request::instance()->post();
            if(!$data['name']){$this->error('称呼不能为空');}
            if(!$data['tel']){$this->error('电话不能为空');}
            if(!$data['content']){$this->error('内容不能为空');}
            if(!$data['verify_code']){$this->error('验证码不能为空');}

            if(!captcha_check($data['verify_code'],'code')){
                //验证失败
                $this->error('验证码错误');
            }else{
                $data['type'] = "timex" ;
                $data['create_dt'] = time();
                unset($data['verify_code']);
                if(Feedback::create($data)){
                    $this->success('感谢您的反馈和建议');
                }else{
                    $this->error('遇到未知错误，请稍后再试');
                }
            }
        }
        return $this->fetch('index/contact');
    }
}
