<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-15 12:01:16
 * @LastEditTime: 2020-11-12 12:16:09
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\LoginRecord.php
 */

namespace app\model;

// use PHPExcel_IOFactory;

use think\Model;


class LoginRecord extends Model
{
    //根据学工号查询登录记录
    public function selectRecord($number,int $count = 10){
        return $this->where('uuid',$number)->order('id', 'desc')->limit($count)->select();        
    }

}
