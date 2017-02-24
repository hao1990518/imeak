<?php
namespace app\wechat\controller;

use app\wechat\model\Wechat;
use think\Controller;
use think\Request;
use app\wechat\model\Port;
/**
 * 测试号
 * Class Index
 * @package app\wechat\controller
 */
class Index extends Controller
{
    public function valid()
    {
        $Wechat = new Wechat();
        $echoStr = Request::instance()->get("echostr");
//        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($Wechat->checkSignature(config('test.token')) && $echoStr){
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
        $Port= new Port();
        $postArr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $this->definedItem();
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
                    )
                );
                $Wechat->responseSubscribe($postObj,$arr);
            }

            if(strtolower($postObj->Event == 'CLICK')){
                if(strtolower($postObj->EventKey) == 'help'){
                    $content = '测试指令：(输入地名查询天气)';
                    $Wechat->responseText($postObj, $content);
                } elseif (strtolower($postObj->EventKey) == '重庆'){
                    $postObj->Content = '重庆';
                    $Port->getWeather($postObj);
                } elseif (strtolower($postObj->EventKey)=='text'){
                    $Port->getJokerText($postObj);
                }elseif (strtolower($postObj->EventKey)=='pic'){
                    $Port->getJokerPic($postObj);
                }elseif (strtolower($postObj->EventKey)=='mb'){
                    $this->sendTemplateMsg();
                }elseif (strtolower($postObj->EventKey)=='qf'){
                    $this->sendMsgAll();
                }
            }
        }

        //发送“tw” 回复单图文
        if(strtolower($postObj->MsgType) == 'text') {
            if (trim($postObj->Content) == 'tw') {
                //图文不超过10个
                $arr = array(
                    array(
                        'title' => 'FirstPic',
                        'description' => 'This is First Picture',
                        'picUrl' => 'http://www.imeak.com/static/admin/img/default.jpg',
                        'url' => 'http://www.imeak.com'
                    ),
                    array(
                        'title' => 'SecondPic',
                        'description' => 'This is Second Picture',
                        'picUrl' => 'http://www.imeak.com/static/admin/img/wenku_logo.png',
                        'url' => 'http://www.imeak.com/water'
                    )
                );

                $Wechat->responseNews($postObj, $arr);
            } elseif ($postObj->Content == '帮助') {
                $content = '测试指令：(输入地名查询天气)';
                $Wechat->responseText($postObj, $content);
            } else {
                $Port->getWeather($postObj);
            }
        }
    }
    /**
     * 获取服务器列表
     * @param $accessToken
     */
    public function getWxServerIp($accessToken)
    {

        $url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$accessToken;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $res = curl_exec($ch);
        if(curl_errno($ch)){
            var_dump(curl_error($ch));
        }

        $arr = json_decode($res,true);

        p($arr);
    }

    private function getCityId($postObj){
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/weatherservice/cityinfo?cityname='.urlencode($postObj->Content);
        $header = array(
            'apikey:eabdf5e16bd743ebe901088a9f24bf4f',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        return (json_decode($res,true));
    }

    /**
     * 定义菜单
     */
    public function definedItem()
    {
        $Wechat = new Wechat();
        $accessToken=$Wechat->getWxAccessToken(1);
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$accessToken['access_token'];
        $postArr = array(
            'button'=>array(
                array(
                    'name'=>urlencode('笑话'),
                    'sub_button'=>array(
                        array(
                            'type'=>"click",
                            'name'=>urlencode("文字"),
                            'key'=>'text'
                        ),
                        array(
                            'type'=>"click",
                            'name'=>urlencode("图片"),
                            'key'=>'pic'
                        ),
                        array(
                            'type'=>"click",
                            'name'=>urlencode("模板消息"),
                            'key'=>'mb'
                        ),
                        array(
                            'type'=>"click",
                            'name'=>urlencode("群发测试"),
                            'key'=>'qf'
                        )
                    )
                ),
                array(
                    'name'=>urlencode('功能'),
                    'sub_button'=>array(
                        array(
                            'type'=>"click",
                            'name'=>urlencode('天气'),
                            'key'=>urlencode('重庆')
                        ),
                        array(
                            'type'=>'view',
                            'name'=>urlencode('搜索'),
                            'url'=>'http://www.soso.com/'
                        ),
                        array(
                            'type'=>'view',
                            'name'=>urlencode('视频'),
                            'url'=>'http://www.iqiyi.com/'
                        ),
                        array(
                            'type'=>'click',
                            'name'=>urlencode('帮助'),
                            'key'=>'help'
                        )
                    )
                ),
                array(
                    'name'=>urlencode('网站'),
                    'sub_button'=>array(
                        array(
                            'type'=>'view',
                            'name'=>urlencode('官网'),
                            'url'=>'http://www.imeak.com/'
                        ),
                        array(
                            'type'=>'view',
                            'name'=>urlencode('净水器'),
                            'url'=>'http://www.imeak.com/water'
                        )
                    )
                )
            ),
        );

        $postJson = urldecode(json_encode($postArr));
        $this->http_curl($url,'post','json',$postJson);
    }

    //模板消息
    public function sendTemplateMsg()
    {
        $Wechat = new Wechat();
        //获取全局access_token
        $accessToken = $Wechat->getWxAccessToken(1);
        //接口数据
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$accessToken['access_token'];
        $arr = array(
            'touser'=>'o63QUt3yJ_SYh-L2BRpQhNp0CWjY',
            'template_id'=>'fJY5P5jkCH5hTWtqvsFfMFMnBqOghGj6vOaBkzq_2H8',
            'url'=>'http://www.imeak.com/',
            'data'=>array(
                'name'=>array('value'=>'hello','color'=>"#173177"),
                'money'=>array('value'=>'100','color'=>"#173177"),
                'time'=>array('value'=>date('Y-m-d H:i:s'),'color'=>"#173177")
            )
        );
        $postJson = json_encode($arr);
        $res=$this->http_curl($url,'post','json',$postJson);
        p($res);

    }

    //群发接口
    public function sendMsgAll(){
        //获取全局access_token
        $Wechat = new Wechat();
        $accessToken = $Wechat->getWxAccessToken(1);
        //组装群发接口数据
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$accessToken['access_token'];
        $arr = array(
            "touser"=>array(
                "o63QUt6eQED-Pl2PtDXyZtFEu008",
                "o63QUt3yJ_SYh-L2BRpQhNp0CWjY"

            ),
            "msgtype"=>"text",

            "text"=>array(
                "content"=>urlencode("群发测试")
            )

        );
        $postJson = urldecode(json_encode($arr));
        $res=$this->http_curl($url,'post','json',$postJson);
        p($res);
    }

    //获取用户列表
    public function getUserList()
    {
        $accessToken = $this->getWxAccessToken(1);
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$accessToken['access_token'];
        $res = $this->http_curl($url);
        p($res);
    }

    //获取用户基本信息
    public function getUser()
    {
        $accessToken = $this->getWxAccessToken(1);
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken['access_token']."&openid=o63QUt3yJ_SYh-L2BRpQhNp0CWjY&lang=zh_CN";
        $res = $this->http_curl($url);
        p($res);
    }

    /**
     * @param $url 接口
     * @param string $type 请求类型
     * @param string $res 返回数据类型
     * @param array $arr 请求参数
     * @return mixed|string
     */
    public function http_curl($url,$type='get',$res='json',$arr=[])
    {
        //初始化curl
        $ch = curl_init();
        //设置参数
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($type == 'post'){
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
        }
        //采集
        $output = curl_exec($ch);
        //关闭
        curl_close($ch);
        if($res =='json'){
//            if(curl_errno($ch)){
//                return curl_error($ch);
//            }else{
                return json_decode($output);
//            }
        }
    }


}
