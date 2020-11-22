<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-11-23 01:30:43
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Bulletin.php
 * @LastEditTime: 2020-11-23 02:25:15
 * @LastEditors: 罗曼
 */


namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;
use app\model\BulletinTarget as BulletinTargetModel;


class Bulletin extends Model
{
    //添加记录
    public function createBulletin($data)
    {
        $bt_model = new BulletinTargetModel();

        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $create_res = $this->create($data, ['level', 'title', 'content', 'target_type', 'create_number']);
            $data['bulletin_id'] = $create_res->id;
            switch ($data['target_type']) {
                case 2:
                    $target_person_arr = explode('-', $data['target_person']);
                    // return $target_person_arr[0];
                    for ($i = 0; $i < count($target_person_arr); $i++) {
                        $data['target_person'] = $target_person_arr[$i];
                        $bt_model->createBulletinTarget($data);
                    }
                    // return $data;
                    break;
                case 4:
                    return true;
                    break;
                default:
                    $bt_model->createBulletinTarget($data);
                    break;
            }
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }




    //over
}
