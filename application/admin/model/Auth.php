<?php
namespace app\admin\model;
use think\Db;
use think\Model;

/**
 * 权限模型
 * User: Administrator
 * Date: 2016/8/11
 * Time: 14:52
 */
class Auth extends Model
{
    private $_auth_group = 'imk_auth_group'; //权限组
    private $_auth_group_access = 'imk_auth_group_access'; //用户&&权限组表
    private $_auth_rule = 'imk_auth_rule'; //权限节点

    /**
     * 添加权限组
     * @param $data
     * @return int|string
     */
    public function addAuthGroup($data)
    {
        return Db::table($this->_auth_group)->insertGetId($data);
    }

    /**
     * 编辑权限组
     * @param $condition
     * @param $data
     * @return int|string
     */
    public function updateAuthGroup($condition,$data)
    {
        return Db::table($this->_auth_group)->where($condition)->update($data);
    }

    /**
     * 获取权限组（单）
     * @param $condition
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthGroup($condition)
    {
        return Db::table($this->_auth_group)->where($condition)->find();
    }

    /**
     * 获取权限组（多）
     * @param $condition
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthGroups($condition)
    {
        return Db::table($this->_auth_group)->where($condition)->select();
    }

    /**
     * 获取权限组（列表）
     * @param array $condition -条件
     * @param int $start       -起始位置
     * @param int $count       -显示数量
     * @param string $order    -排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthGroupList($condition=array(),$start=1,$count=10,$order="id desc")
    {
        return Db::table($this->_auth_group)
            ->where($condition)
            ->limit($start,$count)
            ->order($order)
            ->select();
    }

    /**
     * 获取权限组（数量）
     * @param $condition
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthGroupsCount($condition)
    {
        return Db::table($this->_auth_group)->where($condition)->count();
    }

    /**
     * 删除权限组
     * @param $condition
     * @return int
     */
    public function deleteAuthGroups($condition)
    {
        return Db::table($this->_auth_group)->where($condition)->delete();
    }

    /**********************************************
     * 添加权限节点
     * @param $data
     * @return int|string
     */
    public function addAuthRule($data)
    {
        return Db::table($this->_auth_rule)->insertGetId($data);
    }

    /**
     * 编辑权限节点
     * @param $condition
     * @param $data
     * @return int|string
     */
    public function updateAuthRule($condition,$data)
    {
        return Db::table($this->_auth_rule)->where($condition)->update($data);
    }

    /**
     * 获取权限节点（多）
     * @param $condition
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthRules($condition)
    {
        return Db::table($this->_auth_rule)->where($condition)->select();
    }

    /**
     * 获取权节点（单）
     * @param $condition
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthRule($condition)
    {
        return Db::table($this->_auth_rule)->where($condition)->find();
    }

    /**
     * 获取权限节点（列表）
     * @param array $condition -条件
     * @param int $start       -起始位置
     * @param int $count       -显示数量
     * @param string $order    -排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthRulesList($condition=array(),$start=1,$count=10,$order="id desc")
    {
        return Db::table($this->_auth_rule)
            ->where($condition)
            ->limit($start,$count)
            ->order($order)
            ->select();
    }

    /**
     * 获取权限节点（数量）
     * @param $condition
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAuthRulesCount($condition)
    {
        return Db::table($this->_auth_rule)->where($condition)->count();
    }

    /**
     * 删除权限组
     * @param $condition
     * @return int
     */
    public function deleteAuthRule($condition)
    {
        return Db::table($this->_auth_rule)->where($condition)->delete();
    }

    /***************************************************
     * 查询用户权限（单）
     * @param $condition
     * @return int|string
     */
    public function getAuthGroupAccess($condition)
    {
        return Db::table($this->_auth_group_access)->where($condition)->find();
    }

    /**
     * 插入用户权限（单）
     * @param $data
     * @return int|string
     */
    public function addAuthGroupAccess($data)
    {
        return Db::table($this->_auth_group_access)->insert($data);
    }
    /**
     * 插入用户权限（多）
     * @param $data
     * @return int|string
     */
    public function addAuthGroupAccesses($data)
    {
        return Db::table($this->_auth_group_access)->insertAll($data);
    }

    /**
     * 删除用户权限
     * @param $condition
     * @return int|string
     */
    public function deleteAuthGroupAccess($condition)
    {
        return Db::table($this->_auth_group_access)->where($condition)->delete();
    }
}