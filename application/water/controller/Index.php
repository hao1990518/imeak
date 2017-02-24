<?php
namespace app\water\controller;

use app\water\model\Water;
use think\captcha\Captcha;
use think\Controller;
use think\Request;
use app\admin\model\Feedback;

class Index extends Controller
{
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isPost()){
            $page = Request::instance()->post('page');
            $key_words = Request::instance()->post('key_words');
            $condition['category_id'] = 1;
            $condition['status'] = 1;
            if(isset($key_words)){
                $condition['title'] = ['LIKE','%'.$key_words.'%'];
            }
            $WaterModel = new Water();
            $data = $WaterModel->getWaterAll($condition,$page);
            if($data){
//                foreach ($data as $k=>$v){
//                    if(!empty($v['picture'])){
//                        $h_arr = pathinfo($v['picture']);
//                        $data[$k]['picture_small'] = $h_arr['dirname'].'/'.$h_arr['filename'].'-500x750.'.$h_arr['extension'];
//
//                        if(!file_exists('./'.$data[$k]['picture_small'])){
//                            $data[$k]['picture_small'] =$v['picture'];
//                        }
//                    }else{
//                        $data[$k]['picture_small'] =$v['picture'];
//                    }
//                }
                $this->assign('data',$data);
                $str = $this->fetch('list');
                $this->success('','',$str);
            }else{
                $this->error('没有更多数据了');
            }
        }
        $this->assign('search',1);
        return $this->fetch();
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function detail($id)
    {
        if(is_numeric($id) && $id > 0){
            $data = Water::get(['id'=>$id]);
//            if(!empty($data['picture'])){
//                $h_arr = pathinfo($data['picture']);
//                $data['picture_small'] = $h_arr['dirname'].'/'.$h_arr['filename'].'-500x750.'.$h_arr['extension'];
//
//                if(!file_exists('./'.$data['picture_small'])){
//                    $data['picture_small'] =$data['picture'];
//                }
//            }else{
//                $data['picture_small'] =$data['picture'];
//            }
            $this->assign('data',$data);
            return $this->fetch();
        }
    }
    
    /**
     * 关于我们
     * @return mixed
     */
    public function about()
    {
        return $this->fetch('index/about');
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
                $data['type'] = "water" ;
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

    /**
     * 验证码
     * @return Captcha
     */
    public function getCode()
    {
        $src = captcha('code',config('captcha'));
        return $src;
    }
}
