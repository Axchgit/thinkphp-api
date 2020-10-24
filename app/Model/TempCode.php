<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-15 12:01:16
 * @LastEditTime: 2020-10-22 20:22:33
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\TempCode.php
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class TempCode extends Model
{
    //保存验证码
    public function saveCode($uuid,$code){
        try {
            $this->save(['uuid'=>$uuid,'code'=>$code]);
            // $this->where('uuid', $uuid)->delete();
            return true;
        } catch (\Exception  $e) {
            return $e->getMessage();
        }
    }
    //获取验证码
    public function getCode($uuid){
        try {
            return $this->where('uuid', $uuid)->value('code');            
        } catch (\Exception  $e) {
            return false;
        }
    }
    //删除验证码
    public function deleteCode($uuid)
    {
        try {
            $this->where('uuid', $uuid)->delete();
            return true;
        } catch (\Exception  $e) {
            return $e->getMessage();
        }
    }
}
