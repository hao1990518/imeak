<?php

if (!function_exists('get_table_info')) {
    /**
     * 获取到的datatable传入参数说明，这些参数将传入model进行数据库查找
     *
     * @access public
     * @param  start   - 起始数据位置
     * @param  length  - 每页数据长度,通常和start一起运用进行ajax分页
     * @param  search  - 用于搜索得到的关键字
     * @param  sorting - 表示针对某一列进行排序，从0开始，例如sorting=1表示按第二列排序
     * @param  desc    - 前台传入的排序方式，值为"asc"或者"desc"
     * @param  sEcho   - 用于传回的值，可忽略
     * @return array
     */
    function get_table_info()
    {
        $info = array();
        $dt_iDisplayLength = @\think\Request::instance()->get('dt_iDisplayLength');
        $info["start"] = (@\think\Request::instance()->get("iDisplayStart") ? \think\Request::instance()->get("iDisplayStart") : 0);
        $info["length"] = !empty($dt_iDisplayLength) ? $dt_iDisplayLength : (@\think\Request::instance()->get("iDisplayLength") ? @\think\Request::instance()->get("iDisplayLength") : 10);
        $info["search"] = @\think\Request::instance()->get("sSearch") ? @\think\Request::instance()->get("sSearch") : null;
        $info["sorting"] = @\think\Request::instance()->get('iSortCol_0');
        $info["desc"] = @\think\Request::instance()->get('sSortDir_0');
        $info["sEcho"] = @\think\Request::instance()->get('sEcho');
        return $info;
    }
}

if (!function_exists('format_tree')) {
    /**
     * 格式化成树型结构
     * @param $array    => 数组
     * @param int $pid  => 第一级父id
     * @return array
     */
    function format_tree($array,$pid = 0)
    {
        $arr = array();
        $tem = array();
        foreach ($array as $v) {
            if ($v['parent_id'] == $pid) {
                $tem = format_tree($array, $v['id']);
                //判断是否存在子数组
                $tem && $v['son'] = $tem;
//                if(!empty($v['son']) || !empty($v['url'])){
                    $arr[] = $v;
//                }

            }
        }
        return $arr;
    }
}
if (!function_exists('is_image')) {
    /**
     * 格式化成树型结构
     * @param $filename    => 文件地址
     * @return array
     */
    function is_image($filename){
        $filename =iconv("utf-8","gb2312",$filename);
        $types = '.gif|.jpeg|.png|.bmp';//定义检查的图片类型
        if(file_exists($filename)){
            $info = getimagesize($filename);
            $ext = image_type_to_extension($info['2']);
            return stripos($types,$ext);
        }else{
            return false;
        }
    }
}


