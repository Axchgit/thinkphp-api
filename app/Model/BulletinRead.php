<?php

/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-11-23 01:31:32
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\BulletinRead.php
 * @LastEditTime: 2020-11-23 12:50:11
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
    public function createBulletinRead($data)
    {
        // $bt_model = new BulletinTargetModel();

        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $this->create($data, ['bulletin_id', 'target_number']);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }





    //over
}


