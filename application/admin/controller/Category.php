<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\Category as CategoryModel;
use app\admin\model\Article;
use think\Session;

/**
 * 分类控制器
 * User: Administrator
 * Date: 2016/8/19
 * Time: 11:38
 */
class Category extends Base
{
    /**
     * 分类首页
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isAjax()) {
            $CategoryModel = new CategoryModel();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if ($tableInfo['sorting'] == 1) {
                $order = ('name ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 2) {
                $order = ('parent_id ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 3) {
                $order = ('type ' . $tableInfo['desc']);
            } else {
                $order = ('id ' . $tableInfo['desc']);
            }

            $condition = [];//条件
            //搜索管理员条件组合
            if ($key_words = Request::instance()->get('key_words')) {
                $condition['name'] = array('LIKE', '%' . $key_words . '%');
            }

            //一页显示的条数
            $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
            //当前的页数
            $start = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
            //获取管理员结果数据
            $data['data'] = $CategoryModel->getCategoryList($condition, $start, $number, $order);
            //总数
            $total = $CategoryModel->getCategoryCount($condition);

            //组合dataTable数据
            if ($data['data']) {
                foreach ($data['data'] as $k => $v) {
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = $v['name'];
                    $data['aaData'][$k][] = $v['parent_id'];
                    $data['aaData'][$k][] = $this->changeType($v['type']);
                    $data['aaData'][$k][] = "
                        <a href='" . url('Category/edit', ['id' => $v['id']]) . "' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                        <a href='javascript:;' onclick='del(" . $v['id'] . ")' did='" . $v['id'] . "' uname='" . $v['name'] . "' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
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
        return $this->fetch();
    }

    /**
     * 新增分类
     * @return mixed
     */
    public function add()
    {
        if(Request::instance()->isPost()){

            $data = Request::instance()->post();

            if(empty($data['name'])){
                $this->error('名称不能为空');
            }elseif(CategoryModel::get(['name'=>$data['name'],'parent_id'=>$data['parent_id']])){
                $this->error('名称已存在');
            }else{
                $data['uid'] = Session::get(config('user_auth_key'));
                if(CategoryModel::create($data)){
                    $this->success("新增分类[".$data['name']."]成功");
                }else{
                    $this->error('新增失败');
                }
            }
        }
        $data = CategoryModel::all();
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 编辑分类
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if(empty($data['name'])){
                $this->error('名称不能为空');
            }elseif(CategoryModel::get(['name'=>$data['name'],'id'=>array('neq',$data['id'])])){
                $this->error('名称已存在');
            }else{
                $id = $data['id'];
                unset($data['id']);
                if(CategoryModel::update($data,['id'=>$id])){
                    $this->success("编辑分类[".$data['name']."]成功");
                }else{
                    $this->error('编辑失败');
                }
            }
        }
        $Category = CategoryModel::get(['id'=>$id]);
        $data = CategoryModel::all();
        $this->assign('category',$Category);
        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 删除分类
     */
    public function delete()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            if($id && is_numeric($id) && $id>0){
                if(Article::get(['category_id'=>[['like','%,'.$id.',%'], ['like',$id.',%'],['like','%,'.$id], ['eq',$id], 'or']])){
                    $this->error('改分类下有内容，不能删除');
                }else{
                    if(CategoryModel::destroy(['id'=>$id])){
                        $this->success('删除成功');
                    }else{
                        $this->error('删除失败');
                    }
                }
            }else{
                $this->error('非法操作');
            }
        }
    }
    
    /**
     * 替换类型
     * @param $type
     * @return string
     */
    private function changeType($type)
    {
        switch ($type) {
            case 1 :
                $name = "文章";
                break;
            case 2 :
                $name = "图片";
                break;
            default :
                $name = "未知";
        }

        return $name;
    }
}