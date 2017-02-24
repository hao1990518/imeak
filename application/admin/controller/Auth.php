<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use app\admin\model\Auth as AuthModel;
/**
 * 权限控制器
 * User: Administrator
 * Date: 2016/8/15
 * Time: 10:48
 */
class Auth extends Base
{
    /**
     * 权限组（角色）
     */
    public function group()
    {
        if(Request::instance()->isAjax()){
            $AuthModel = new AuthModel();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if($tableInfo['sorting'] == 1){
                $order = ('title '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 2){
                $order = ('status '.$tableInfo['desc']);
            }else{
                $order = ('id '.$tableInfo['desc']);
            }

            $condition=[];//条件
            //搜索管理员条件组合
            if($key_words = Request::instance()->get('key_words')){
                $condition['title'] = array('LIKE','%'.$key_words.'%');
            }

            //一页显示的条数
            $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
            //当前的页数
            $start = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
            //获取管理员结果数据
            $data['data'] = $AuthModel->getAuthGroupList($condition,$start,$number,$order);
            //总数
            $total = $AuthModel->getAuthGroupsCount($condition);

            //组合dataTable数据
            if($data['data']){
                foreach ($data['data'] as $k=>$v){
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = $v['title'];
                    $data['aaData'][$k][] = $v['status']==1?'启用':"<span style='color:red'>禁用</span>";
//                    $data['aaData'][$k][] = $v['rules'];
                    $data['aaData'][$k][] = "
                        <a href='".url('auth/editGroup',['id'=>$v['id']])."' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                        <a href='javascript:;' onclick='del(".$v['id'].")' did='".$v['id']."' uname='".$v['title']."' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
                        ";
                }
                unset($data['data']);
            }
            $data["sEcho"] = $tableInfo['sEcho'];
            $data["iTotalRecords"] = $total;
            $data["iTotalDisplayRecords"] = $total;
            $data["iDisplayLength"] = $number;
            exit(json_encode($data));
        }
        return $this->fetch('group');
    }

    /**
     * 新增权限组（角色）
     * @return mixed
     */
    public function addGroup()
    {
        $AuthModel = new AuthModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if($AuthModel->getAuthGroup(['title'=>$data['title']])){
                $this->error('名称已存在');
            }

            $data['rules'] = is_array($data['rules']) ? implode(',',$data['rules']) : '';
            unset($data['_helper2']);
            if($AuthModel->addAuthGroup($data)){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }
        $rules = $AuthModel->getAuthRules(['status'=>1]); //获取节点列表
        $this->assign('rules',$rules);
        return $this->fetch('add_group');
    }

    /**
     * 编辑权限组（角色）
     * @return mixed
     */
    public function editGroup($id)
    {
        $AuthModel = new AuthModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if($AuthModel->getAuthGroup(['title'=>$data['title'],'id'=>array('neq',$data['id'])])){
//                p(Db::table('imk_auth_group')->getLastSql());
                $this->error('名称已存在');
            }

            $data['rules'] = is_array($data['rules']) ? implode(',',$data['rules']) : '';
            $condition['id'] = $data['id'];
            unset($data['id']);
            if($AuthModel->updateAuthGroup($condition,$data)){
                $this->success('编辑成功');
            }else{
                $this->error('编辑失败');
            }
        }
        if(is_numeric($id) && $id > 0){
            $rules = $AuthModel->getAuthRules(['status'=>1]); //获取节点列表
            $group = $AuthModel->getAuthGroup(['id'=>$id]); //获取节点列表
            $group['rules'] = $group['rules'] ? explode(',',$group['rules']) :[];
            $this->assign('rules',$rules);
            $this->assign('group',$group);
            return $this->fetch('edit_group');
        }

    }
    
    /**
     * 删除权限组（角色）
     */
    public function deleteGroup()
    {
        $id = Request::instance()->post('id');
        if($id && is_numeric($id) && $id > 0){
            $AuthModel = new AuthModel();
            if($AuthModel->deleteAuthGroups(['id'=>$id])){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 节点
     */
    public function rule()
    {
        if(Request::instance()->isAjax()){
            $AuthModel = new AuthModel();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if($tableInfo['sorting'] == 1){
                $order = ('name '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 2){
                $order = ('title '.$tableInfo['desc']);
            }elseif($tableInfo['sorting'] == 3){
                $order = ('status '.$tableInfo['desc']);
            }else{
                $order = ('id '.$tableInfo['desc']);
            }

            $condition=[];//条件
            //搜索管理员条件组合
            if($key_words = Request::instance()->get('key_words')){
                $condition['title'] = array('LIKE','%'.$key_words.'%');
            }

            //一页显示的条数
            $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
            //当前的页数
            $start = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
            //获取管理员结果数据
            $data['data'] = $AuthModel->getAuthRulesList($condition,$start,$number,$order);
            //总数
            $total = $AuthModel->getAuthRulesCount($condition);
            //组合dataTable数据
            if($data['data']){
                foreach ($data['data'] as $k=>$v){
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = $v['name'];
                    $data['aaData'][$k][] = $v['title'];
                    $data['aaData'][$k][] = $v['status']==1?'启用':"<span style='color:red'>禁用</span>";
                    $data['aaData'][$k][] = "
                        <a href='".url('auth/editRule',['id'=>$v['id']])."' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                        <a href='javascript:;' onclick='del(".$v['id'].")' did='".$v['id']."' uname='".$v['name']."' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
                        ";
                }
                unset($data['data']);
            }
            $data["sEcho"] = $tableInfo['sEcho'];
            $data["iTotalRecords"] = $total;
            $data["iTotalDisplayRecords"] = $total;
            $data["iDisplayLength"] = $number;
            exit(json_encode($data));
        }
        return $this->fetch('rule');
    }

    /**
     * 新增节点
     * @return mixed
     */
    public function addRule()
    {
        if(Request::instance()->isPost()){
            $AuthModel = new AuthModel();
            $data = Request::instance()->post();
            if($AuthModel->getAuthRule(['name'=>$data['name']])){
                $this->error('节点已存在');
            }
            if($AuthModel->addAuthRule($data)){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }
        return $this->fetch('add_rule');
    }

    /**
     * 编辑节点
     * @return mixed
     */
    public function editRule($id)
    {
        $AuthModel = new AuthModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if($AuthModel->getAuthRule(['name'=>$data['name'],'id'=>array('neq',$data['id'])])){
                $this->error('名称已存在');
            }
            $condition['id'] = $data['id'];
            unset($data['id']);
            if($AuthModel->updateAuthRule($condition,$data)){
                $this->success('编辑成功');
            }else{
                $this->error('编辑失败');
            }
        }
        if(is_numeric($id) && $id > 0){
            $rule = $AuthModel->getAuthRule(['id'=>$id]); //获取节点列表
//            p($rule);
            $this->assign('data',$rule);
            return $this->fetch('edit_rule');
        }

    }

    /**
     * 删除节点
     */
    public function deleteRule()
    {
        $id = Request::instance()->post('id');
        if($id && is_numeric($id) && $id > 0){
            $AuthModel = new AuthModel();
            if($AuthModel->deleteAuthRule(['id'=>$id])){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            $this->error('非法操作');
        }
    }
}