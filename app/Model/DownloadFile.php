<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-11-25 15:19:46
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\DownloadFile.php
 * @LastEditTime: 2020-11-26 15:14:06
 * @LastEditors: 罗曼
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class DownloadFile extends Model
{
    //添加记录
    public function createFileInfo($data)
    {
        // $bt_model = new BulletinTargetModel();

        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $this->create($data, ['uploader_number', 'file_category', 'file_name', 'file_path', 'file_remarks']);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }


    //获取
    public function selectFileList($list_rows, $config, $post,  $isSimple = false)
    {
        $select_post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        try {
            $list = $this->where($select_post)->paginate($list_rows, $isSimple, $config);
            return [true ,$list];
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }
    }

    //删除
    public function deleteFileById($id){
        $file_path = $this->where('id',$id)->value('file_path');
        try {
            $this->destroy($id);
            unlink('./storage/'.$file_path);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }





    //over
}
