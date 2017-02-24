<?php
namespace app\admin\controller;
use think\Request;
use app\admin\model\Feedback as FeedbackModel;
/**
 * 用户反馈
 * User: Administrator
 * Date: 2016/8/29
 * Time: 15:05b
 */
class Feedback extends Base
{
    public function index()
    {
        if(Request::instance()->isAjax()) {
            $FeedbackModel = new FeedbackModel();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if ($tableInfo['sorting'] == 1) {
                $order = ('name ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 2) {
                $order = ('type ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 3) {
                $order = ('tel ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 4) {
                $order = ('email ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 5) {
                $order = ('qq ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 7) {
                $order = ('create_dt ' . $tableInfo['desc']);
            } else {
                $order = ('id ' . $tableInfo['desc']);
            }

            $condition = [];//条件
            //搜索管理员条件组合
            if ($key_words = Request::instance()->get('key_words')) {
                $condition['name'] = array('LIKE', '%' . $key_words . '%');
            }

            //一页显示的条数
            $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
            //当前的页数
            $start = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
            //获取管理员结果数据
            $data['data'] = $FeedbackModel->getList($condition, $start, $number, $order);
            //总数
            $total = $FeedbackModel->getCount($condition);

            //组合dataTable数据
            if ($data['data']) {
                foreach ($data['data'] as $k => $v) {
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = $v['name'];
                    $data['aaData'][$k][] = $v['type'];
                    $data['aaData'][$k][] = $v['tel'];
                    $data['aaData'][$k][] = $v['email'];
                    $data['aaData'][$k][] = $v['qq'];
                    $data['aaData'][$k][] = $v['content'];
                    $data['aaData'][$k][] = date('Y-m-d H:i:s', $v['create_dt']);
                    $data['aaData'][$k][] = "<a href='javascript:;' onclick='del(" . $v['id'] . ")' did='" . $v['id'] . "' uname='" . $v['name'] . "' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>";
                }
                unset($data['data']);
            }
            $data["sEcho"] = $tableInfo['sEcho'];
            $data["iTotalRecords"] = $total;
            $data["iTotalDisplayRecords"] = $total;
            $data["iDisplayLength"] = $number;
            exit(json_encode($data));
        }
        return $this->fetch();
    }

    /**
     * 删除留言
     */
    public function delete()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            if($id && is_numeric($id) && $id>0){
                if(FeedbackModel::destroy(['id'=>$id])){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error('非法操作');
            }
        }
    }
}