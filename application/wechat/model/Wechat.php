<?php
namespace app\wechat\model;
use think\Session;
use think\Request;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/31
 * Time: 16:28
 */
class Wechat {

    /**
     * 验证token
     * @param $token
     * @return bools
     */
    public function checkSignature($token)
    {
        // you must define TOKEN by yourself
        if (!$token) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = Request::instance()->get("signature");
        $timestamp = Request::instance()->get("timestamp");
        $nonce = Request::instance()->get("nonce");

        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取ACCESSTOKEN
     * @param $type 0正式环境，1测试环境
     * @return mixed
     */
    public function getWxAccessToken($type=0)
    {
        $accessToken = Session::get('accessToken');
        if(!$accessToken || $accessToken['expires_time']<time()){
            if($type==1){
                $app_id = config('test.app_id');
                $app_secret = config('test.app_secret');
            }else{
                $app_id = config('online.app_id');
                $app_secret = config('online.app_secret');
            }
            //请求地址
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_id."&secret=".$app_secret;
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            $res = curl_exec($ch);
            curl_close($ch);
//            if(curl_errno($ch)){
//                p(curl_error($ch));
//            }
            $arr = json_decode($res,true);
            $arr['expires_time'] = time()+$arr['expires_in'];
            Session::set('accessToken',$arr);
            return $arr;
        }else{
            return $accessToken;
        }
    }

    //回复多图文
    public function responseNews($postObj,$arr)
    {
        //回复用户消息
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $time = time();

        $template = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <ArticleCount>".count($arr)."</ArticleCount>
                    <Articles>";
        foreach ($arr as $k=>$v){
            $template .="<item>
                    <Title><![CDATA[{$v['title']}]]></Title>
                    <Description><![CDATA[{$v['description']}]]></Description>
                    <PicUrl><![CDATA[{$v['picUrl']}]]></PicUrl>
                    <Url><![CDATA[{$v['url']}]]></Url>
                    </item>";
        }

        $template .="</Articles>
                    </xml>";
        echo sprintf($template,$toUser,$fromUser,$time,'news');
    }

    //回复文本
    public function responseText($postObj,$content)
    {
        $template = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        //回复用户消息
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;
        $time = time();
        $msgType = 'text';

        echo sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
    }

    //回复关注
    public function responseSubscribe($postObj,$arr)
    {
        $this->responseNews($postObj,$arr);
    }
}