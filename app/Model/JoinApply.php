<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-16 16:28:24
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\JoinApply.php
 * @LastEditTime: 2020-10-16 18:00:20
 * @LastEditors: 罗曼
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;


class JoinApply extends Model
{
    //添加申请记录
    public function addApply($number, $step, $review_status=1, $reviewer='', $remarks='')
    {
        try {
            $data = [
                'number' => $number,
                'step' => $step,
                'review_status' => $review_status,
                'reviewer' => $reviewer,
                'remarks' => $remarks
            ];
            $this->save($data);
            // $this->update(['review_status' => $data['review_status'], 'id' => $data['id']]);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }
    //查询申请进程
    public function selectApplyStep($number){
        //获取该人员最新步骤
        // try {
            $step = $this->where('number',$number)->max('step');
            return $this->where('number',$number)->where('step',$step)->find();
        // } catch (\Exception $e) {
        //     return  $e->getMessage();
        // }

        
    }




    //over
}
