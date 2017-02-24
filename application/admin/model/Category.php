<?php
namespace app\admin\model;
use think\Db;
use think\Model;

/**
 * 分类模型
 * User: Administrator
 * Date: 2016/8/19
 * Time: 14:08
 */
class Category extends Model
{
    private $_category = 'imk_category';

    /**
     * 获取分类(单)
     * @param $condition
     * @return array|false|mixed|\PDOStatement|string|Model
     */
    public function getCategory($condition)
    {
        return Db::table($this->_category)->where($condition)->find();
    }

    /**
     * 获取分类（多）
     * @param $condition
     * @return array|false|mixed|\PDOStatement|string|Model
     */
    public function getCategorys($condition)
    {
        return Db::table($this->_category)->where($condition)->find();
    }

    /**
     * 获取分类（列表）
     * @param array $condition  -条件
     * @param int $start        -起始数据
     * @param int $count        -总数
     * @param string $order     -排序
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCategoryList($condition=array(),$start=1,$count=10,$order='sort desc,id asc')
    {
        return Db::table($this->_category)
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
    public function getCategoryCount($condition=array())
    {
        return Db::table($this->_category)->where($condition)->count();
    }
}