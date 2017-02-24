<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\Link as LinkModel;
use think\Session;
use app\admin\model\Upload;
/**
 * 友情链接控制器
 * User: Administrator
 * Date: 2016/8/19
 * Time: 11:38
 */
class Link extends Base
{
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isAjax()) {
            $LinkModel = new LinkModel();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if ($tableInfo['sorting'] == 0) {
                $order = ('id ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 2) {
                $order = ('title ' . $tableInfo['desc']);
            } elseif ($tableInfo['sorting'] == 5) {
                $order = ('status ' . $tableInfo['desc']);
            } else {
                $order = ('sort ' . $tableInfo['desc']);
            }

            $condition = [];//条件
            //搜索管理员条件组合
            if ($key_words = Request::instance()->get('key_words')) {
                $condition['title'] = array('LIKE', '%' . $key_words . '%');
            }

            //一页显示的条数
            $number = (int)@$tableInfo['length'] > 0 ? $tableInfo['length'] : 25;
            //当前的页数
            $start = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
            //获取幻灯片结果数据
            $data['data'] = $LinkModel->getLinkList($condition, $start, $number, $order);
            //总数
            $total = $LinkModel->getLinkCount($condition);

            //组合dataTable数据
            if ($data['data']) {
                foreach ($data['data'] as $k => $v) {
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = "<img class='img_show' src='".$v['picture']."' id='".$v['id']."' style='max-height:16px' >";
                    $data['aaData'][$k][] = $v['title'];
                    $data['aaData'][$k][] = $v['remarks'];
                    $data['aaData'][$k][] = $v['url'];
                    $data['aaData'][$k][] = $v['status']==1?'显示':"<span style='color:red'>隐藏</span>";
                    $data['aaData'][$k][] = $v['sort'];
                    $data['aaData'][$k][] = "
                        <a href='" . url('Link/edit', ['id' => $v['id']]) . "' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                        <a href='javascript:;' onclick='del(" . $v['id'] . ")' did='" . $v['id'] . "' uname='" . $v['title'] . "' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
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
     * 新增幻灯片
     * @return mixed
     */
    public function add()
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            //分类
            if(empty($data['category_id'])){
                $this->error('请选择分类');
            }
            $data['category_id'] = implode(',',$data['category_id']);
            //状态
            if(!empty($data['status']) && $data['status']=="on"){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }

            $url = $this->upload();
            if($url){
                $data['picture'] = $url;
            }
            $data['uid'] = Session::get(config('user_auth_key'));
            if(LinkModel::create($data)){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }
        $LinkModel = new LinkModel();
        $category_list = $LinkModel->getCategorys([]);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }

    /**
     * 编辑幻灯片
     * @return mixed
     */
    public function edit($id)
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            //分类
            if(empty($data['category_id'])){
                $this->error('请选择分类');
            }
            $data['category_id'] = implode(',',$data['category_id']);
            //状态
            if(!empty($data['status']) && $data['status']=="on"){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }
            $url = $this->upload();

            if(!empty($url)){
                $data['picture'] = $url;
            }else{
                unset($data['picture']);
            }
            $id = $data['id'];
            unset($data['id']);
            if(LinkModel::update($data,['id'=>$id])){
                $this->success('编辑成功');
            }else{
                $this->error('编辑失败');
            }
        }

        $data = LinkModel::get(['id'=>$id]);
        $data['category_id'] = explode(',',$data['category_id']);
        $LinkModel = new LinkModel();
        $category_list = $LinkModel->getCategorys([]);
        $this->assign('data',$data);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }


    /**
     * 删除幻灯片
     */
    public function delete()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');

            if($id && is_numeric($id) && $id>0){
                if(LinkModel::destroy(['id'=>$id])){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error('非法操作');
            }
        }
    }

    /********************************************
     * 分类
     * @return mixed
     */
    public function category()
    {
        if(Request::instance()->isAjax()) {
            $LinkModel = new LinkModel();

            $tableInfo = get_table_info(); //获取dataTable数据
            //排序
            if ($tableInfo['sorting'] == 1) {
                $order = ('name ' . $tableInfo['desc']);
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
            $data['data'] = $LinkModel->getCategoryList($condition, $start, $number, $order);
            //总数
            $total = $LinkModel->getCategoryCount($condition);

            //组合dataTable数据
            if ($data['data']) {
                foreach ($data['data'] as $k => $v) {
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = $v['name'];
                    $data['aaData'][$k][] = $v['remarks'];
                    $data['aaData'][$k][] = "
                        <a href='" . url('Link/editCategory', ['id' => $v['id']]) . "' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
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

    //添加分类
    public function addCategory()
    {
        if(Request::instance()->isPost()){

            $data = Request::instance()->post();
            $LinkModel = new LinkModel();
            if(empty($data['name'])){
                $this->error('名称不能为空');
            }elseif($LinkModel->getCategory(['name'=>$data['name']])){
                $this->error('名称已存在');
            }else{
                $data['uid'] = Session::get(config('user_auth_key'));
                if($LinkModel->addCategory($data)){
                    $this->success("新增分类[".$data['name']."]成功");
                }else{
                    $this->error('新增失败');
                }
            }
        }
        return $this->fetch('category_add');
    }

    //编辑分类
    public function editCategory($id)
    {
        $LinkModel = new LinkModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if(empty($data['name'])){
                $this->error('名称不能为空');
            }elseif($LinkModel->getCategory(['name'=>$data['name'],'id'=>array('neq',$data['id'])])){
                $this->error('名称已存在');
            }else{
                $id = $data['id'];
                unset($data['id']);
                if($LinkModel->updateCategory($data,['id'=>$id])){
                    $this->success("编辑分类[".$data['name']."]成功");
                }else{
                    $this->error('编辑失败');
                }
            }
        }
        $Category = $LinkModel->getCategory(['id'=>$id]);
        $this->assign('category',$Category);
        return $this->fetch('category_edit');
    }

    //删除分类
    public function deleteCategory()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            if($id && is_numeric($id) && $id>0){
                $LinkModel = new LinkModel();
//                like '%,1,%' or like '1,%' or ='1' or like '%,1'
                if($LinkModel::get(['category_id'=> [['like','%,'.$id.',%'], ['like',$id.',%'],['like','%,'.$id], ['eq',$id], 'or']])){
                    $this->error('该分类下有幻灯片，不能删除');
                }else{
                    if($LinkModel->deleteCategory(['id'=>$id])){
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
     * 文件上传
     * @return bool
     */
    private function upload()
    {
        $UploadModel = new Upload();
        $files = Request::instance()->file();
        if($files){
            $thumb = false;
//            if(Request::instance()->post('thumb') && Request::instance()->post('thumb')==1){
//                $thumb = true;//是否生成缩略图
//            };
            // 移动到框架应用根目录/public/uploads/ 目录下
            if(is_array($files)){
                foreach($files as $file){
                    $filePath = $UploadModel->execUpload($file,$thumb);
                }
            }else{
                $filePath = $UploadModel->execUpload($files,$thumb);
            }
            if($filePath['status']==1){
                return $filePath['url'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}