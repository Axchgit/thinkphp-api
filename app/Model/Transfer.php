<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-15 12:01:16
 * @LastEditTime: 2020-11-22 03:17:12
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Transfer.php
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;

use app\model\Person as PersonModel;

class Transfer extends Model
{

    //添加申请记录
    public function createApply($data)
    {
        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $this->create($data, ['number', 'contacts_phone', 'receive_organization', 'receive_faculty', 'reason', 'remarks', 'review_status', 'low_reviewer', 'high_reviewer']);
            return true;
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    // 修改人员信息
    public function updateTransfer($data)
    {
        try {
            // return $data;

            // $data = request()->only(['id', 'review_status','review_steps','receive_major', 'out_low_reviewer','in_low_reviewer','high_reviewer']);
            // return $data;
            $this->update($data,['id'=>$data['id']],['review_status','review_steps','receive_major', 'out_low_reviewer','in_low_reviewer','high_reviewer']);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        // $res = $this->save($data);
    }

    //查询进度
    public function selectApplyStep($number)
    {
        return $this->where('number', $number)->where('review_steps', '<>', 3)->find();
    }

    //查询所有申请
    public function getAllApply($list_rows, $config, $post, $post_string, $role, $isSimple = false)
    {
        $person_model = new PersonModel();
        //删除指定键名元素
        $select_post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        $select_post_new = [];
        foreach ($select_post as $k => $v) {
            $select_post_new['person.' . $k] = $v;
            if ($k == 'faculty') {
                $v > 9 ? $select_post_new['person.' . $k] = (string)$v : $select_post_new['person.' . $k] = '0' . (string)$v;
            }
            if ($k == 'review_steps' || $k == 'review_status' || $k == 'receive_organization' || $k == 'reason') {
                unset($select_post_new['person.' . $k]);
                $select_post_new['transfer.' . $k] = $v;
            }
        }
        $list =  Db::view('person')
            ->view('transfer', 'id,contacts_phone,reason,receive_organization,receive_faculty,receive_major,review_steps,review_status,remarks,create_time', 'person.number=transfer.number')
            ->where($select_post_new)
            ->whereRaw($post_string)
            // ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->paginate($list_rows, $isSimple, $config)
            ->each(function ($item, $key) {
                $item['faculty'] = (int)$item['faculty'];
                $item['receive_faculty'] = (int)$item['receive_faculty'];

                // $person_model->getJsonData();
                return $item;
            })->toArray();
        foreach ($list['data'] as $k => $v) {
            if ($v['party_branch'] == 0) {
                $list['data'][$k]['party_branch_label'] = '未选择';
            } else {
                $list['data'][$k]['party_branch_label'] = $person_model->getJsonData('options.json', $v['faculty'], $v['party_branch'], true);
            }
            $list['data'][$k]['receive_organization_label'] = $person_model->getJsonData('options.json', $v['receive_organization'], $v['party_branch'], true);
        }
        return  $list;
    }





    //over
}
