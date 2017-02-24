<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\Menu as MenuModel;
use app\admin\model\Auth;
/**
 * 菜单控制器
 * User: Administrator
 * Date: 2016/8/16
 * Time: 9:24
 */
class Menu extends Base
{

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        $MenuModel = new MenuModel();
        $menus = $MenuModel->getMenus();
        $menus = format_tree($menus);
        $this->assign('data',$menus);
        return $this->fetch();
    }

    /**
     * 新增
     * @return mixed
     */
    public function add()
    {
        $MenuModel = new MenuModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if($status = $MenuModel->addMenu($data)){
                if(is_numeric($status) && $status >0 ){
                    $this->success('添加成功，前往列表查看或继续添加');
                }else{
                    $this->error($status);
                }
            }else{
                $this->error('添加失败，请稍后再试或联系管理员');
            }
        }
        //获取菜单
        $AuthModel = new Auth();
        $menus = $MenuModel->getMenus();
        $menus = format_tree($menus);
        //获取节点
        $rules = $AuthModel->getAuthRules(['status'=>1]);
        $this->assign('menus',$menus);
        $this->assign('rules',$rules);
        return $this->fetch('add');
    }

    /**
     * 编辑
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $MenuModel = new MenuModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            $id = $data['id'];
            unset($data['id']);
            if($status = $MenuModel->updateMenu(['id'=>$id],$data)){
                if(is_numeric($status) && $status >0 ){
                    $this->success('编辑成功，前往列表查看或继续编辑');
                }else{
                    $this->error($status);
                }
            }else{
                $this->error('编辑失败，请稍后再试或联系管理员');
            }
        }elseif(is_numeric($id) && $id >0){
            //获取菜单
            $AuthModel = new Auth();
            $menus = $MenuModel->getMenus();
            $menus = format_tree($menus);
            //获取节点
            $rules = $AuthModel->getAuthRules(['status'=>1]);

            $data = MenuModel::get(['id'=>$id]);

            $this->assign('menus',$menus);
            $this->assign('rules',$rules);
            $this->assign('data',$data);
            return $this->fetch('edit');
        }else{
            $this->error('非法操作');
        }

    }

    /**
     * 删除菜单
     */
    public function delete()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            if(is_numeric($id) && $id > 0 ){
                if(MenuModel::destroy(['id'=>$id])){
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