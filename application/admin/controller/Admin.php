<?php
namespace app\admin\controller;

use app\admin\model\Admin as AdminModel;
use app\admin\model\Auth;
use think\Request;
use think\Session;

/**
 * 管理员控制器
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Base
{
    /**
     * 管理员列表
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isAjax()){

            $AdminModel = new AdminModel();
            $Auth = new \auth\Auth();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if($tableInfo['sorting'] == 1){
                $order = ('username '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 3){
                $order = ('status '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 4){
                $order = ('login_dt '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 5){
                $order = ('login_ip '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 6){
                $order = ('login_num '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 7){
                $order = ('create_dt '.$tableInfo['desc']);
            }else{
                $order = ('id '.$tableInfo['desc']);
            }

            $condition=[];//条件
            //搜索管理员条件组合
            if($key_words = Request::instance()->get('key_words')){
                $condition['username'] = array('LIKE','%'.$key_words.'%');
            }

            //一页显示的条数
            $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
            //当前的页数
            $page = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
            //获取管理员结果数据
            $data['data'] = $AdminModel->getAdminList($condition,$page,$number,$order);
            //总数
            $total = $AdminModel->getAdminCount($condition);

            //组合dataTable数据
            if($data['data']){
                foreach ($data['data'] as $k=>$v){
                    if($v['id'] == 1){
                        $identity = '超级管理员';
                    }else{
                        $groups = $Auth->getGroups($v['id']); //获取权限
                        $arr = [];
                        foreach ($groups as $kk=>$vv){
                            $arr[$kk] = $vv['title'];
                        }
                        $identity = implode(',',$arr); //组合多个权限
                    }
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = '<a href="javascript:;" style="color:#23C6C8" onclick="get_detail('.$v['id'].')" title="点击查看详情" >'.$v['username'].'</a>';
                    $data['aaData'][$k][] = $identity;
                    $data['aaData'][$k][] = $v['status']==1?'启用':"<span style='color:red'>禁用</span>";
                    $data['aaData'][$k][] = !empty($v['login_dt']) ? date('Y-m-d H:i:s',$v['login_dt']) :'还未登录过';
                    $data['aaData'][$k][] = $v['login_ip'];
                    $data['aaData'][$k][] = $v['login_num'];
                    $data['aaData'][$k][] = date('Y-m-d H:i:s',$v['create_dt']);
                    if($v['id']!=1){
                        $data['aaData'][$k][] = "
                        <a href='".url('admin/edit',['id'=>$v['id']])."' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                        <a href='javascript:;' onclick='reset_pwd(".$v['id'].")' did='".$v['id']."' uname='".$v['username']."' alt='重置密码' title='重置密码' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-repeat'></i></a>
                        <a href='javascript:;' onclick='del(".$v['id'].")' did='".$v['id']."' uname='".$v['username']."' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
                        ";
                    }else{
                        $data['aaData'][$k][] = "<small>[最高权限]</small>";
                    }

                }
                unset($data['data']);
            }
            $data["sEcho"] = $tableInfo['sEcho'];
            $data["iTotalRecords"] = $total;
            $data["iTotalDisplayRecords"] = $total;
            $data["iDisplayLength"] = $number;
            exit(json_encode($data));
        }
        return $this->fetch('index');
    }

    /**
     * 新增管理员
     * @return mixed
     */
    public function add()
    {
        $AuthModel = new Auth();
        //新增数据
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            $AdminModel = new AdminModel();

            if($AdminModel::get(['username'=>$data['username']])){
                $this->error('用户名已存在');
            }
            $auth_list = explode(',',$data['auth']);//权限数据
            unset($data['auth']);
            if($status = $AdminModel->addAdmin($data)){
                if(is_numeric($status) && $status >0 ){
                    //添加权限数据
                    if(is_array($auth_list)){
                        foreach ($auth_list as $k=>$v){
                            $arr[$k]['uid'] = $status;
                            $arr[$k]['group_id'] = $v;
                        }
                    }else{
                        $arr['uid'] = $status;
                        $arr['group_id'] = $auth_list;
                    }
                    $AuthModel->addAuthGroupAccesses($arr);
                    $this->success('添加成功，前往列表查看或继续添加');
                }else{
                    $this->error($status);
                }
            }else{
                $this->error('添加失败，请稍后再试或联系管理员');
            }
        }

        //新增页面视图
        $auth = $AuthModel->getAuthGroups(['status'=>1]);
        $this->assign('auth',$auth);
        return $this->fetch('add');
    }

    /**
     * 编辑管理员
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $AuthModel = new Auth();
        $AdminModel = new AdminModel();

        //编辑数据
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            $id = $data['id'];
            if($id == 1){
                $this->error('非法操作');
            }
            $auth_list = explode(',',$data['auth']);//权限数据
            unset($data['id']);
            unset($data['auth']);
            if($AdminModel::update($data,['id'=>$id])){
                //删除权限数据
                $AuthModel->deleteAuthGroupAccess(['uid'=>$id]);
                //添加权限数据
                if(is_array($auth_list)){
                    foreach ($auth_list as $k=>$v){
                        $arr[$k]['uid'] = $id;
                        $arr[$k]['group_id'] = $v;
                    }
                }else{
                    $arr['uid'] = $id;
                    $arr['group_id'] = $auth_list;
                }
                $AuthModel->addAuthGroupAccesses($arr);

                $this->success('编辑成功，前往列表查看或继续编辑');
            }else{
                $this->error('编辑失败，请稍后再试或联系管理员');
            }
        }elseif(is_numeric($id) && $id >0){
            $Auth = new \auth\Auth();
            //编辑页面视图
            $result = $AdminModel::get(['id'=>$id]); //获取管理员信息
            $groups = $Auth->getGroups($id); //获取权限数据
            $arr = [];
            foreach ($groups as $v){
                $arr[] = $v['group_id'];
            }
            $result['groups'] =$arr; //当前用户权限组
            $auth = $AuthModel->getAuthGroups(['status'=>1]); //获取权限组列表
            $this->assign('data',$result);
            $this->assign('auth',$auth);
            return $this->fetch('edit');
        }else{
            $this->error('非法操作');
        }

    }

    /**
     * 删除管理员
     */
    public function delete()
    {
        $id = Request::instance()->post('id');
        if($id && is_numeric($id) && $id > 0){
            if ($id == 1) {
                $this->error('超级管理员不能被删除');
            }else{
                if(AdminModel::destroy(['id'=>$id])){
                    $AuthModel = new Auth();
                    $AuthModel->deleteAuthGroupAccess(['uid'=>$id]);
                    $AdminModel = new AdminModel();
                    $AdminModel->deleteAdminInfo(['uid'=>$id]);
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 获取管理员详情
     */
    public function detail()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            $AdminModel = new AdminModel();
            $data = $AdminModel->getAdminInfos(['imk_admin.id'=>$id]);
            if($data){
                if($data['id'] == 1){
                    $data['identity'] = '超级管理员';
                }else{
                    $Auth = new \auth\Auth();
                    $groups = $Auth->getGroups($id); //获取权限
                    $arr = [];
                    foreach ($groups as $kk=>$vv){
                        $arr[$kk] = $vv['title'];
                    }
                    $data['identity'] = implode(',',$arr); //组合多个权限
                }
                $data['birthday'] = date('Y-m-d',$data['birthday']);
                $this->assign('data',$data);
                $str = $this->fetch('detail');
                $this->success('成功','',$str);
            }else{
                $this->error('未找到该用户');
            }
        }
    }
    
    /**
     * 验证口令密码
     */
    public function checkResetPassword()
    {
        if(Request::instance()->isPost() && (Session::get(config('user_auth_key')) == 1) && Session::get(config('admin_auth_key'))){
            $id = Request::instance()->post('id');
            $pwd = Request::instance()->post('pwd');
            $type= Request::instance()->post('type'); //是否重置密码

            if(config('check_password') == $pwd){
                if($type==1 && $id ){
                    $AdminModel = new AdminModel();
                    $user = AdminModel::get(['id'=>$id]);
                    $password = $user->username.$user->username.'666imk';
                    if($AdminModel->updateAdmin(['id'=>$id],['password'=>sha1($password)])){
                        $this->success('重置密码成功');
                    }else{
                        $this->error('重置密码失败');
                    }
                }
                $this->success('验证成功');
            }else{
                $this->error('口令错误');
            }
        }else{
            $this->error('权限不足');
        }
    }
}
