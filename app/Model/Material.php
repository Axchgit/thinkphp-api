<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-13 17:12:47
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Material.php
 * @LastEditTime: 2020-10-25 20:03:52
 * @LastEditors: 罗曼
 */


namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class Material extends Model
{
    //  添加资料信息
    public function addMaterial($number, $category, $serial_number, $score, $remarks)
    {
        try {
            $data = [
                'number' => $number,
                'category' => $category,
                'serial_number' => $serial_number,
                'score' => $score,
                'remarks' => $remarks
            ];
            $this->save($data);
            // $this->update(['review_status' => $data['review_status'], 'id' => $data['id']]);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }
    //根据学号查询信息
    public function getInfoByNumber($number, $key, $value)
    {
        return $this->where('number', $number)->where($key, $value)->find();
    }

    //根据学号查询所有数据
    public function getAllByNumber($number)
    {
        return $this->where('number', $number)->select();
    }
}
