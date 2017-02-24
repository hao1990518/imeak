<?php
namespace app\admin\model;
use think\Db;
use think\Model;

/**
 * 幻灯片模型
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/24
 * Time: 17:01
 */
class Slide extends Model
{
    private $_slide = 'imk_slide';//幻灯片表
    private $_category = 'imk_slide_category';//幻灯片分类表

    /**
     * 获取幻灯片（列表）
     * @param array $condition  -条件
     * @param int $start        -起始数据
     * @param int $count        -总数
     * @param string $order     -排序
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSlideList($condition=array(),$start=1,$count=10,$order='id desc')
    {
        return Db::table($this->_slide)
            ->where($condition)
            ->limit($start,$count)
            ->order($order)
            ->select();
    }

    /**
     * 获取幻灯片总数
     * @param array $condition
     * @return int
     */
    public function getSlideCount($condition=array())
    {
        return Db::table($this->_slide)->where($condition)->count();
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