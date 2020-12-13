<?php
/*
 * @Author: 罗曼
 * @Date: 2020-08-15 12:01:16
 * @LastEditTime: 2020-12-13 16:56:52
 * @LastEditors: 罗曼
 * @Description: 
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\Transfer.php
 */

namespace app\model;

use think\model\concern\SoftDelete;

use think\Model;
use think\facade\Db;

use app\model\Person as PersonModel;

class Transfer extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    //添加申请记录
    public function createApply($data)
    {
        try {
            // Transfer::create($data,['number', 'contacts_phone','receive_organization','reason','remarks','review_status','reviewer']);
            $this->create($data, ['number', 'contacts_phone', 'leave_faculty', 'leave_major', 'leave_organization', 'receive_organization', 'receive_faculty', 'reason', 'remarks', 'review_status', 'low_reviewer', 'high_reviewer']);
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
            $this->update($data, ['id' => $data['id']], ['review_status', 'review_steps', 'receive_major', 'out_low_reviewer', 'in_low_reviewer', 'high_reviewer']);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        // $res = $this->save($data);
    }

    //查询进度
    public function selectApplyStep($number)
    {
        return $this->where('number', $number)->where('review_steps', '<>', 4)->find();
    }

    //个人浏览历史记录
    public function getHistroyByNumber($number)
    {
        $person_model = new PersonModel();
        $list =  $this->where('number', $number)->where('review_steps', 4)->select();
        // return $list[0];
        foreach ($list as $k => $v) {
            $list[$k]['reason'] = $v['reason'] === 1 ? '转专业' : ($v['reason'] === 2 ? '分配错误修正' : '其他');
            $list[$k]['review_steps'] = '转出成功';


            $list[$k]['leave_organization'] = $v['leave_organization'] == 0 ? '未选择' : $v['leave_organization'];
            $list[$k]['leave_faculty_label'] = $person_model->getJsonData('options.json', $v['leave_faculty']);
            $list[$k]['leave_organization_label'] = $person_model->getJsonData('options.json', $v['leave_faculty'], $v['leave_organization'], true);
            $list[$k]['leave_label'] = $v['leave_faculty_label'] . $v['leave_organization_label'];


            $list[$k]['receive_faculty_label'] = $person_model->getJsonData('options.json', $v['receive_faculty']);
            $list[$k]['receive_organization_label'] = $person_model->getJsonData('options.json', $v['receive_faculty'], $v['receive_organization'], true);
            $list[$k]['receive_label'] = $v['receive_faculty_label'] . $v['receive_organization_label'];
        }


        // if ($list['leave_organization'] == 0) {
        //     $list['leave_organization_label'] = '未选择';
        // } else {
        //     $list['leave_organization_label'] = $person_model->getJsonData('options.json', $list['faculty'], $list['leave_organization'], true);
        // }
        return $list;
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
            ->view('transfer', '*', 'person.number=transfer.number')
            ->where($select_post_new)
            ->whereRaw($post_string)
            // ->whereRaw("faculty='$faculty' or '$faculty' =''")
            ->paginate($list_rows, $isSimple, $config)
            ->each(function ($item, $key) {
                $item['leave_faculty'] = (int)$item['leave_faculty'];
                $item['receive_faculty'] = (int)$item['receive_faculty'];

                // $person_model->getJsonData();
                return $item;
            })->toArray();
        foreach ($list['data'] as $k => $v) {
            if ($v['leave_organization'] == 0) {
                $list['data'][$k]['leave_organization_label'] = '未选择';
            } else {
                $list['data'][$k]['leave_organization_label'] = $person_model->getJsonData('options.json', $v['faculty'], $v['leave_organization'], true);
            }
            $list['data'][$k]['receive_organization_label'] = $person_model->getJsonData('options.json', $v['receive_faculty'], $v['receive_organization'], true);
        }
        return  $list;
    }

    //删除申请信息
    public function deleteTransfer($data)
    {
        try {
            $this->destroy($data['id'],true);  //只允许第二个参数内的值被修改
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }





    //over
}
