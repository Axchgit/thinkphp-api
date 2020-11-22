<?php

/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-11-23 01:31:32
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\BulletinRead.php
 * @LastEditTime: 2020-11-23 01:55:06
 * @LastEditors: 罗曼
 */
namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class BulletinRead extends Model
{
    //添加记录
    public function createBulletinTarget($data)
    {
        // $bt_model = new BulletinTargetModel();

        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $this->create($data, ['level', 'title', 'content', 'target_type', 'create_number']);
            switch ($data['target_type']) {
                case 1:

                    # code...
                    break;
                case 2:
                    # code...
                    break;
                case 3:
                    # code...
                    break;
                case 4:
                    # code...
                    break;

                default:
                    return '通知类型不能为空';
                    break;
            }
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }





    //over
}


