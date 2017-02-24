<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Loader;

/**
 * 菜单模型
 * User: Administrator
 * Date: 2016/8/11
 * Time: 14:30
 */
class Menu extends Model
{
    private $_menu = 'imk_menu';

    /**
     * 按条件获取菜单(多)
     * @param array $condition 条件
     * @param array $or_condition Or条件
     * @param string $order 排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getMenus($condition=array(),$or_condition=array(),$order='sort desc,id asc')
    {
       return Db::table($this->_menu)->where($condition)->whereOr($or_condition)->order($order)->select();
    }

    /**
     * 按条件获取菜单(列表)
     * @param array $condition 条件
     * @param int $start 起始位置
     * @param int $count 结束位置
     * @param string $order 排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getMenuList($condition=array(),$start=1,$count=10,$order='sort desc,id asc')
    {
        return Db::table($this->_menu)
            ->where($condition)
            ->limit($start,$count)
            ->order($order)
            ->select();
    }

    /**
     * 获取菜单数量
     * @param $condition
     * @return int
     */
    public function getMeunCount($condition)
    {
        return Db::table($this->_menu)->where($condition)->count();
    }

    /**
     * 添加菜单
     * @param $data
     * @return int|string
     */
    public function addMenu($data)
    {
        $validate = Loader::validate('Menu');
        if(!$validate->check($data)){
            return $validate->getError();
        }else{
            unset($data['__token__']);
            return Db::table($this->_menu)->insertGetId($data);
        }
    }

    /**
     * 添加菜单
     * @param $condition
     * @param $data
     * @return int|string
     */
    public function updateMenu($condition,$data)
    {
        $validate = Loader::validate('Menu');
        if(!$validate->check($data)){
            return $validate->getError();
        }else{
            unset($data['__token__']);
            return Db::table($this->_menu)->where($condition)->update($data);
        }
    }
}