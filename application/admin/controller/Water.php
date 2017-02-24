<?php
namespace app\admin\controller;
use think\Request;
use app\admin\model\Water as WaterModel;
use app\admin\model\Upload;
use think\Session;

/**
 * 净水器控制器
 * User: Administrator
 * Date: 2016/8/19
 * Time: 11:35
 */
class Water extends Base
{
    /**
     * 文章列表
     * @return mixed
     */
    public function index()
    {
        if(Request::instance()->isAjax()){
            $this->getList();
        }
        return $this->fetch();
    }

    /**
     * 新增文章
     * @return mixed
     */
    public function add()
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            //分类
            if(empty($data['category_id'])){
                $this->error('请选择栏目');
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
                $h_arr = pathinfo($url);
                $data['picture'] = $h_arr['dirname'].'/'.$h_arr['filename'].'-500x750.'.$h_arr['extension'];
                if(!file_exists($data['picture']))
                {
                    $data['picture'] =$url;
                }else{
                    unlink('./'.$url);
                }

            }
            $data['uid'] = Session::get(config('user_auth_key'));
            $data['release_dt'] = strtotime($data['release_dt']);
            $data['update_dt'] = time();
            $data['create_dt'] = time();

            if(WaterModel::create($data)){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }
        $WaterModel = new WaterModel();
        $category_list = $WaterModel->getCategorys([]);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }

    /**
     * 编辑文章
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            //分类
            if(empty($data['category_id'])){
                $this->error('请选择栏目');
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
                $h_arr = pathinfo($url);
                $data['picture'] = $h_arr['dirname'].'/'.$h_arr['filename'].'-500x750.'.$h_arr['extension'];
                if(!file_exists($data['picture']))
                {
                    $data['picture'] =$url;
                }else{
                    unlink('./'.$url);
                }
            }else{
                unset($data['picture']);
            }
            $data['release_dt'] = strtotime($data['release_dt']);
            $data['update_dt'] = time();
            $id = $data['id'];
            unset($data['id']);
            if(WaterModel::update($data,['id'=>$id])){
                $this->success('编辑成功');
            }else{
                $this->error('编辑失败');
            }
        }

        $data = WaterModel::get(['id'=>$id]);
        $data['category_id'] = explode(',',$data['category_id']);

        if(!empty($data['picture'])){
            $h_arr = pathinfo($data['picture']);
            $data['picture_small'] = $h_arr['dirname'].'/'.$h_arr['filename'].'-500x750.'.$h_arr['extension'];

            if(!file_exists('./'.$data['picture_small'])){
                $data['picture_small'] =$data['picture'];
            }
        }else{
            $data['picture_small'] =$data['picture'];
        }

        $WaterModel = new WaterModel();
        $category_list = $WaterModel->getCategorys([]);
        $this->assign('data',$data);
        $this->assign('category_list',$category_list);
        return $this->fetch();
    }

    /**
     * 删除文章
     */
    public function delete()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            if($id && is_numeric($id) && $id>0){
                $WaterModel = new WaterModel();
                if($WaterModel->deleteWater(['id'=>$id])){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error('非法操作');
            }

        }
    }
    
    /**
     * 获取文章列表
     */
    private function getList(){
        $WaterModel = new WaterModel();

        $tableInfo = get_table_info(); //获取dataTable数据
        //排序
        if($tableInfo['sorting'] == 1){
            $order = ('title '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 2){
            $order = ('category_id '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 3){
            $order = ('uid '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 4){
            $order = ('status '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 5){
            $order = ('sort '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 6){
            $order = ('click_num '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 7){
            $order = ('praise_num '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 8){
            $order = ('update_dt '.$tableInfo['desc']);
        }elseif($tableInfo['sorting'] == 9){
            $order = ('create_dt '.$tableInfo['desc']);
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
        $page = (int)@$tableInfo['start'] > 0 ? $tableInfo['start'] : 0;
        //获取文章结果数据
        $data['data'] = $WaterModel->getWaterList($condition,$page,$number,$order);

        //总数
        $total = $WaterModel->getWaterCount($condition);

        //组合dataTable数据
        if($data['data']){
            foreach ($data['data'] as $k=>$v){
                $category_list = $WaterModel->getCategorys(['id'=>array('in',explode(',',$v['category_id']))]);
                $category = [];
                foreach ($category_list as $vv){
                    $category[] = $vv['name'];
                }

                $data['aaData'][$k][] = $v['id'];
                $data['aaData'][$k][] = $v['title'];
                $data['aaData'][$k][] = implode(',',$category);
                $data['aaData'][$k][] = $v['nickname']?$v['nickname']:$v['username'];
                $data['aaData'][$k][] = $v['status']==1?'显示':"<span style='color:red'>隐藏</span>";
                $data['aaData'][$k][] = $v['sort'];
                $data['aaData'][$k][] = $v['click_num'];
                $data['aaData'][$k][] = $v['praise_num'];
                $data['aaData'][$k][] = date('Y-m-d H:i:s',$v['update_dt']);
                $data['aaData'][$k][] = date('Y-m-d H:i:s',$v['create_dt']);
                $data['aaData'][$k][] = "
                    <a href='".url('Water/edit',['id'=>$v['id']])."' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
                    <a href='javascript:;' onclick='del(".$v['id'].")' did='".$v['id']."' uname='".$v['title']."' alt='删除' title='删除' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-trash-o'></i></a>
                    ";
//                <a href='#' alt='图片集' title='图片集' style='color:#676A6C;margin-left: 5px;'><i class='fa fa-image'></i></a>

            }
            unset($data['data']);
        }
        $data["sEcho"] = $tableInfo['sEcho'];
        $data["iTotalRecords"] = $total;
        $data["iTotalDisplayRecords"] = $total;
        $data["iDisplayLength"] = $number;
        exit(json_encode($data));
    }
    /********************************************
     * 分类
     * @return mixed
     */
    public function category()
    {
        if(Request::instance()->isAjax()) {
            $WaterModel = new WaterModel();

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
            $data['data'] = $WaterModel->getCategoryList($condition, $start, $number, $order);
            //总数
            $total = $WaterModel->getCategoryCount($condition);

            //组合dataTable数据
            if ($data['data']) {
                foreach ($data['data'] as $k => $v) {
                    $data['aaData'][$k][] = $v['id'];
                    $data['aaData'][$k][] = $v['name'];
                    $data['aaData'][$k][] = $v['remarks'];
                    $data['aaData'][$k][] = "
                        <a href='" . url('Slide/editCategory', ['id' => $v['id']]) . "' alt='编辑' title='编辑' style='color:#676A6C'><i class='fa fa-edit'></i></a>
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
            $WaterModel = new WaterModel();
            if(empty($data['name'])){
                $this->error('名称不能为空');
            }elseif($WaterModel->getCategory(['name'=>$data['name']])){
                $this->error('名称已存在');
            }else{
                $data['uid'] = Session::get(config('user_auth_key'));
                if($WaterModel->addCategory($data)){
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
        $WaterModel = new WaterModel();
        if(Request::instance()->isPost()){
            $data = Request::instance()->post();
            if(empty($data['name'])){
                $this->error('名称不能为空');
            }elseif($WaterModel->getCategory(['name'=>$data['name'],'id'=>array('neq',$data['id'])])){
                $this->error('名称已存在');
            }else{
                $id = $data['id'];
                unset($data['id']);
                if($WaterModel->updateCategory($data,['id'=>$id])){
                    $this->success("编辑分类[".$data['name']."]成功");
                }else{
                    $this->error('编辑失败');
                }
            }
        }
        $Category = $WaterModel->getCategory(['id'=>$id]);
        $this->assign('category',$Category);
        return $this->fetch('category_edit');
    }

    //删除分类
    public function deleteCategory()
    {
        if(Request::instance()->isPost()){
            $id = Request::instance()->post('id');
            if($id && is_numeric($id) && $id>0){
                $WaterModel = new WaterModel();
//                like '%,1,%' or like '1,%' or ='1' or like '%,1'
                if($WaterModel::get(['category_id'=> [['like','%,'.$id.',%'], ['like',$id.',%'],['like','%,'.$id], ['eq',$id], 'or']])){
                    $this->error('该分类下有幻灯片，不能删除');
                }else{
                    if($WaterModel->deleteCategory(['id'=>$id])){
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

            $thumb = true;
//            if(Request::instance()->post('thumb') && Request::instance()->post('thumb')==1){
//                $thumb = true;//是否生成缩略图
//            };
            // 移动到框架应用根目录/public/uploads/ 目录下
            if(is_array($files)){
                foreach($files as $file){
                    $filePath = $UploadModel->execUpload($file,$thumb,['width'=>500,'height'=>750]);
                }
            }else{
                $filePath = $UploadModel->execUpload($files,$thumb,[['width'=>500,'height'=>750]]);
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