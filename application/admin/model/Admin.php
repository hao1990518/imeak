<?php
namespace app\admin\model;
use think\Db;
use think\Loader;

/**
 * 管理员模型
 * User: Administrator
 * Date: 2016/8/10
 * Time: 15:59
 */
class Admin extends Base
{
    private $_admin = 'imk_admin';
    private $_admin_info = 'imk_admin_info';

    /**
     * 获取管理员详细信息
     * @param $condition
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
    public function getAdminInfos($condition)
    {
        return Db::table($this->_admin)
            ->field('imk_admin_info.*,imk_admin.username,imk_admin.status,imk_admin.create_dt,imk_admin.login_dt,
            imk_admin.login_num,imk_admin.login_ip,imk_admin.remarks')
            ->join('imk_admin_info','imk_admin_info.uid = imk_admin.id','left')
            ->where($condition)
            ->find();
    }
    
    /**
     * 获取管理员（列表）
     * @param array $condition -条件
     * @param int $start       -起始位置
     * @param int $count       -显示数量
     * @param string $order    -排序
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getAdminList($condition=array(),$start=1,$count=10,$order="id desc")
    {
        return Db::table($this->_admin)
            ->where($condition)
            ->order($order)
            ->limit($start,$count)
            ->select();
    }

    /**
     * 获取管理员数量
     * @param array $condition -条件
     * @return int
     */
    public function getAdminCount($condition=array())
    {
        return Db::table($this->_admin)->where($condition)->count();
    }

    /**
     * 新增管理员
     * @param $data
     * @return int|string
     */
    public function addAdmin($data)
    {
        $validate = Loader::validate('Admin');
        if(!$validate->scene('add')->check($data)){
            return $validate->getError();
        }else{
            $data['password'] = sha1($data['username'].$data['password'].'imk');
            $data['create_dt'] = time();
            unset($data['repassword']);
            unset($data['__token__']);
            return Db::table($this->_admin)->insertGetId($data);
        }
    }

    /**
     * 更新管理员数据
     * @param $condition
     * @param $data
     * @return int|string
     */
    public function updateAdmin($condition,$data)
    {
        return Db::table($this->_admin)->where($condition)->update($data);
    }

    /**
     * 获取用户详情
     * @param $condition
     * @return int|string
     */
    public function getAdminInfo($condition)
    {
        return Db::table($this->_admin_info)->where($condition)->find();
    }

    /**
     * 保存用户详情
     * @param $data
     * @return int|string
     */
    public function saveAdminInfo($data)
    {
        if(!empty($data['id'])){
            $id=$data['id'];
            unset($data['id']);
            return Db::table($this->_admin_info)->where(['id'=>$id])->update($data);
        }else{
            return Db::table($this->_admin_info)->insertGetId($data);
        }
    }

    /**
     * 删除详情
     * @param $condition
     * @return int
     */
    public function deleteAdminInfo($condition)
    {
        return Db::table($this->_admin_info)->where($condition)->delete();
    }
}