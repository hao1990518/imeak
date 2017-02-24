<?php
namespace app\wechat\controller;
use think\Controller;
use app\wechat\model\Wechat;
use app\wechat\model\Port;
use think\Request;
/**
 * 正式订阅号
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/1
 * Time: 11:39
 */
class Online extends Controller
{
    public function valid()
    {
        $Wechat = new Wechat();
        $echoStr = Request::instance()->get("echostr");
//        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($Wechat->checkSignature(config('online.token')) && $echoStr){
            echo $echoStr;
            exit;
        }else{
            $this->responseMsg();
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments

        $Wechat = new Wechat();
        $Port = new Port();
        $postArr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postArr);

        //判断该数据包是否是订阅的事件推送
        if(strtolower($postObj->MsgType) == 'event'){
            if(strtolower($postObj->Event == 'subscribe')){
                //回复用户消息
                $arr = array(
                    array(
                        'title'=>'欢迎关注Imeak的微信公众号',
                        'description'=>'测试指令：(输入地名查询天气)',
                        'picUrl'=>'http://www.imeak.com/static/admin/img/default.jpg',
                        'url'=>'http://www.imeak.com'
                    ),
                    array(
                        'title'=>'净水器',
                        'description'=>'净水器预览',
                        'picUrl'=>'http://www.imeak.com/uploads/20160901/d18e4ea0d077e1e12f74036860927d97-500x750.png',
                        'url'=>'http://www.imeak.com/water'
                    )
                );
                $Wechat->responseSubscribe($postObj,$arr);
            }
        }

        //发送“tw” 回复单图文
        if(strtolower($postObj->MsgType) == 'text') {
            if (trim($postObj->Content) == 'tw') {
                //图文不超过10个
                $arr = array(
                    array(
                        'title' => 'IMEAK',
                        'description' => 'This is First Picture',
                        'picUrl' => 'http://www.imeak.com/static/admin/img/default.jpg',
                        'url' => 'http://www.imeak.com'
                    ),
                    array(
                        'title' => '净水器',
                        'description' => 'This is Second Picture',
                        'picUrl' => 'http://www.imeak.com/static/admin/img/wenku_logo.png',
                        'url' => 'http://www.imeak.com/water'
                    )
                );

                $Wechat->responseNews($postObj, $arr);
            } elseif ($postObj->Content == '帮助') {
                $content = "测试指令：(输入地名查询天气)\n输入净水器获取网站链接";
                $Wechat->responseText($postObj, $content);
            } elseif($postObj->Content == '净水器') {
                $arr = array(
                    array(
                        'title' => '净水器',
                        'description' => '净水器预览',
                        'picUrl'=>'http://www.imeak.com/uploads/20160901/d18e4ea0d077e1e12f74036860927d97-500x750.png',
                        'url'=>'http://www.imeak.com/water'
                    )
                );
                $Wechat->responseNews($postObj,$arr);
            } elseif($postObj->Content == 'jt') {
                $Port->getJokerText($postObj);
            } elseif($postObj->Content == 'jp') {
                $Port->getJokerPic($postObj);
            } else {
                $Port->getWeather($postObj);
            }
        }
    }
}