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
//        $city = $this->getCityId($postObj);
//        $city_code =$city['retData']['cityCode'];
        $ch = curl_init();
//        $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityid='.$city_code;
        $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityname='.urlencode($postObj->Content);
        $header = array(
            'apikey:eabdf5e16bd743ebe901088a9f24bf4f',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        curl_close($ch);

        $arr = json_decode($res,true);
        $content = "城市：".$arr['retData']['city']
            ."\n日期：".$arr['retData']['today']['date']."[今天]"
            ."\n星期：".$arr['retData']['today']['week']
            ."\n当前温度：".$arr['retData']['today']['curTemp']
            ."\npm值：".$arr['retData']['today']['aqi']
            ."\n风向：".$arr['retData']['today']['fengxiang']
            ."\n风力：".$arr['retData']['today']['fengli']
            ."\n最高温度：".$arr['retData']['today']['hightemp']
            ."\n最低温度：".$arr['retData']['today']['lowtemp']
            ."\n天气状态：".$arr['retData']['today']['type'];

        $content.="\n\n预报列表：" ."\n◎◎◎◎◎◎◎◎◎◎◎◎";
        foreach($arr['retData']['forecast'] as $k=>$v){
            $content.= "\n\n日期：".$v['date']
                ."\n星期：".$v['week']
                ."\n风向：".$v['fengxiang']
                ."\n风力：".$v['fengli']
                ."\n最高温度：".$v['hightemp']
                ."\n最低温度：".$v['lowtemp']
                ."\n天气状态：".$v['type'];
            if($k+1 == count($arr['retData']['forecast'])){
                $content.="\n================";
            }else{
                $content.="\n------------";
            }
        }

        $content.="\n\n今日指标列表：" ."\n⊙⊙⊙⊙⊙⊙⊙⊙⊙⊙⊙⊙";
        foreach ($arr['retData']['today']['index'] as $k=>$v){
            $content.= "\n\n指数指标名称：".$v['name']
                ."\n描述：".$v['details']
                ."\n等级：".$v['index'];
            if($k+1 == count($arr['retData']['today']['index'])){
                $content.="\n================";
            }else{
                $content.="\n-----------";
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
        $url = 'http://apis.baidu.com/showapi_open_bus/showapi_joke/joke_text?page='.rand(1,100);
        $header = array(
            'apikey: eabdf5e16bd743ebe901088a9f24bf4f',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $arr = json_decode($res,true);
        $content = '';
        foreach($arr['showapi_res_body']['contentlist'] as $k=>$v){
            if($k<5) {
                $content .= ($k + 1)."：";
                $content .= $v['text'];
                $content .= "\n";
            }
        }
        $Wechat = new Wechat();
        $Wechat->responseText($postObj,$content);
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

