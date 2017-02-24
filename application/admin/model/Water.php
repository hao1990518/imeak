<?php
namespace app\admin\model;
use think\Db;
use think\Model;

/**
 * 净水器管理模型
 * User: Administrator
 * Date: 2016/8/19
 * Time: 16:12
 */

class Water extends Model
{
    private $_water = 'imk_water';
    private $_category = 'imk_water_category';//分类表

    /**
     * 获取管理员（列表）
     * @param array $condition -条件
     * @param int $start       -起始位置
     * @param int $count       -显示数量
     * @param string $order    -排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getWaterList($condition=array(),$start=1,$count=10,$order="id desc")
    {
        return Db::table($this->_water)
            ->field('imk_water.*,imk_admin.username,imk_admin_info.nickname')
            ->join('imk_admin','imk_admin.id = imk_water.uid','left')
            ->join('imk_admin_info','imk_admin_info.uid = imk_water.uid','left')
            ->where($condition)
            ->order($order)
            ->limit($start,$count)
            ->select();
    }

    /**
     * 获取文章数量
     * @param $condition
     * @return int
     */
    public function getWaterCount($condition)
    {
        return Db::table($this->_water)->where($condition)->count();
    }

    /**
     * 删除文章
     * @param $condition
     * @return int
     */
    public function deleteWater($condition)
    {
        return Db::table($this->_water)->where($condition)->delete();
    }

    /**
     * 获取分类(单)
     * @param $condition
     * @return int|string
     */
    public function getCategory($condition)
    {
        return Db::table($this->_category)->where($condition)->find();
    }

    /**
     * 获取分类(多)
     * @param $condition
     * @return int|stringe
     */
    public function getCategorys($condition)
    {
        return Db::table($this->_category)->where($condition)->select();
    }

    /**
     * 获取分类（列表）
     * @param array $condition  -条件
     * @param int $start        -起始数据
     * @param int $count        -总数
     * @param string $order     -排序
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCategoryList($condition=array(),$start=1,$count=10,$order='id desc')
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

    /**
     * 新增分类
     * @param $data
     * @return int|string
     */
    public function addCategory($data)
    {
        return Db::table($this->_category)->insert($data);
    }

    /**
     * 更新分类
     * @param $condition
     * @param $data
     * @return int|string
     */
    public function updateCategory($data,$condition)
    {
        return Db::table($this->_category)->where($condition)->update($data);
    }

    /**
     * 删除分类
     * @param $condition
     * @return int
     */
    public function deleteCategory($condition)
    {
        return Db::table($this->_category)->where($condition)->delete();
    }
}