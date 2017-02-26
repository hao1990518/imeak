<?php
namespace app\wechat\model;
/**
 * 微信功能
 * User: Administrator
 * Date: 2016/9/1
 * Time: 14:06
 */

class Port {
    /**
     * 查询天气
     * @param $postObj
     */
    public function getWeather($postObj)
    {
        $url = "http://wthrcdn.etouch.cn/weather_mini?city={$postObj->Content}";
//        $url = "http://wthrcdn.etouch.cn/WeatherApi?city={$postObj}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        curl_close($ch);

        $arr = json_decode($res,true);

        $content = "城市：{$arr['data']['city']}"
            ."\n日期：{$arr['data']['forecast'][0]['date']}"
            ."\n当前温度：{$arr['data']['wendu']}"
            ."\npm值：{$arr['data']['aqi']}"
            ."\n风向：{$arr['data']['forecast'][0]['fengxiang']}"
            ."\n风力：{$arr['data']['forecast'][0]['fengli']}"
            ."\n最高温度：{$arr['data']['forecast'][0]['high']}"
            ."\n最低温度：{$arr['data']['forecast'][0]['low']}"
            ."\n天气状态：{$arr['data']['forecast'][0]['type']}"
            ."\n温馨提示：{$arr['data']['ganmao']}";

        unset($arr['data']['forecast'][0]);
        $content.="\n\n预报列表：" ."\n◎◎◎◎◎◎◎◎◎◎◎◎";
        foreach($arr['data']['forecast'] as $k=>$v){
            $content.= "\n\n日期：".$v['date']
                ."\n风向：".$v['fengxiang']
                ."\n风力：".$v['fengli']
                ."\n最高温度：".$v['high']
                ."\n最低温度：".$v['low']
                ."\n天气状态：".$v['type'];
            if($k == count($arr['data']['forecast'])){
                $content.="\n================";
            }else{
                $content.="\n------------";
            }
        }
        $Wechat = new Wechat();
        $Wechat->responseText($postObj,$content);
    }

    /**
     * 笑话
     * @param $postObj
     */
    public function getJokerText($postObj)
    {
        $ch = curl_init();

        $url = "http://api.xiaoliaoba.cn/Index/duanzi.html?key=436263d0bc4f3d7a6a73655a01d42a40&count=10&page=".rand(1,6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $arr = json_decode($res,true);
        $content = '';
        foreach($arr['result'] as $k=>$v){
            if($k<5) {
                $content .= ($k + 1)." {$v['title']}：";
                $content .= $v['content'];
                $content .= "\n";
            }
        }
        $Wechat = new Wechat();
        $Wechat->responseText($postObj,htmlspecialchars_decode($content));
    }

    public function getJokerPic($postObj)
    {
        $ch = curl_init();
        $url = 'http://apis.baidu.com/showapi_open_bus/showapi_joke/joke_pic?page='.rand(1,100);
        $header = array(
            'apikey: eabdf5e16bd743ebe901088a9f24bf4f',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $data = json_decode($res,true);
        $arr = [];
        foreach($data['showapi_res_body']['contentlist'] as $k=>$v){
            if($k<6){
                $arr[$k] = [
                    'title'=>$v['title'],
                    'description'=>$v['title'],
                    'picUrl'=>$v['img'],
                    'url'=>$v['img']
                ];
            }

        }
        $Wechat = new Wechat();
        $Wechat->responseNews($postObj,$arr);
    }
}

