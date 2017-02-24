<?php
namespace app\admin\model;
use think\Model;
use think\Db;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/25
 * Time: 16:23
 */
class Feedback extends Model
{
    private $_feedback = 'imk_feedback';
    /**
     * 获取分类（列表）
     * @param array $condition  -条件
     * @param int $start        -起始数据
     * @param int $count        -总数
     * @param string $order     -排序
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($condition=array(),$start=1,$count=10,$order='id desc')
    {
        return Db::table($this->_feedback)
            ->where($condition)
            ->limit($start,$count)
            ->order($order)
            ->select();
    }

    /**
     * 获取分类总数
     * @param array $condition
     * @return int
     */
    public function getCount($condition=array())
    {
        return Db::table($this->_feedback)->where($condition)->count();
    }
}