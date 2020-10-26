<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-16 16:28:24
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\JoinApply.php
 * @LastEditTime: 2020-10-26 19:32:46
 * @LastEditors: 罗曼
 */

namespace app\model;

// use PHPExcel_IOFactory;

// use think\Db;
use think\Model;
use think\facade\Db;

use app\model\Person as PersonModel;
use app\model\Material as MaterialModel;


class JoinApply extends Model
{
    //添加申请记录
    public function addApply($number, $step, $review_status = 1, $reviewer = '', $remarks = '')
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
    public function selectApplyStep($number)
    {
        //获取该人员最新步骤
        // try {
        $step = $this->where('number', $number)->max('step');
        return $this->where('number', $number)->where('step', $step)->find();
        // } catch (\Exception $e) {
        //     return  $e->getMessage();
        // }


    }

    //获取人员信息,分页显示

    public function getAllApply($list_rows, $isSimple = false, $config, $faculty, $post)
    {
        $person_model = new PersonModel();
        $material_model = new MaterialModel();

        //知识点:删除指定键名元素
        $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        // return $post;
        // if ($faculty == '') {
        $data = $this->where($post)->paginate($list_rows, $isSimple = false, $config);
        // } else {
        //     $data = $this->where($post)->where('faculty', $faculty)->paginate($list_rows, $isSimple = false, $config);
        // }
        //判断是否有值
        // if ($data->isEmpty()) {
        //     return $data;
        // } else {
        $fileName = config('app.json_path') . '/options.json';
        $string = file_get_contents($fileName);
        $json_data = json_decode($string, true);
        // $material = [];
        foreach ($data as $k => $v) {
            $person_info = $person_model->getAllInfoByNumber($data[$k]['number']);  //获取人员信息
            for ($i = 0; $i <= 3; $i++) {
                $material_path_info[$i] = $material_model->getInfoByNumber($data[$k]['number'], 'category', $i + 1); //获取审核资料
            }
            //二级管理员查看时剔除非本学院人员信息
            if ($faculty == 4 && $faculty == $person_info['faculty']) {
                unset($data[$k]);
                continue;
            }
            /************个人信息*/
            $data[$k]['name'] = $person_info['name'];
            $data[$k]['sex'] = $person_info['sex'];
            $data[$k]['post'] = $person_info['post'];
            $data[$k]['nation'] = $person_info['nation'];
            $data[$k]['email'] = $person_info['email'];
            $data[$k]['role'] = $person_info['role'];


            //学院
            $found_arr = array_column($json_data, 'value'); //所查询键名组成的数组
            $found_key = array_search($person_info['faculty'], $found_arr); //所查询数据在josn_data数组中的下标
            $data[$k]['faculty'] = $json_data[$found_key]['label'];
            //党支部
            $found_child_arr = array_column($json_data[$found_key]['children'], 'value'); //所查询键名组成的数组
            $found_child_key = array_search($person_info['party_branch'], $found_child_arr); //所查询数据在josn_data数组中的下标
            $data[$k]['party_branch'] = $json_data[$found_key]['children'][$found_child_key]['label'];

            /************审核资料*/
            $data[$k]['certificate_one'] = !empty($material_path_info[0]['score']) ? $material_path_info[3]['score'] : '';
            $data[$k]['certificate_two'] = !empty($material_path_info[1]['score']) ? $material_path_info[1]['score'] : '';
            $data[$k]['certificate_three'] = !empty($material_path_info[2]['score']) ? $material_path_info[2]['score'] : '';
            $data[$k]['applicationPath'] = !empty($material_path_info[3]['remarks']) ? $material_path_info[3]['remarks'] : '';
        }
        // $data['material'] = $material;
        //重新建立索引
        // $data=array_values($data);
        return $data;
        // }
    }


    public function getAllApplyOneStep($list_rows, $isSimple = false, $config, $faculty, $post)
    {
        //知识点:删除指定键名元素
        $post = array_diff_key($post, ["list_rows" => 0, "page" => 0]);
        // return $post;
        // if ($faculty == '') {
        $data = $this->where($post)->paginate($list_rows, $isSimple = false, $config);
        // } else {
        //     $data = $this->where($post)->where('faculty', $faculty)->paginate($list_rows, $isSimple = false, $config);
        // }
        //判断是否有值
        // if ($data->isEmpty()) {
        //     return $data;
        // } else {
        // $fileName = config('app.json_path') . '/options.json';
        // $string = file_get_contents($fileName);
        // $json_data = json_decode($string, true);
        foreach ($data as $k => $v) {
            // $data[$k]['number']


            // PHP数组查询
            //学院
            // $found_arr = array_column($json_data, 'value'); //所查询键名组成的数组
            // $found_key = array_search($v['faculty'], $found_arr); //所查询数据在josn_data数组中的下标
            // $data[$k]['faculty'] = $json_data[$found_key]['label'];
            // //党支部
            // $found_child_arr = array_column($json_data[$found_key]['children'], 'value'); //所查询键名组成的数组
            // $found_child_key = array_search($v['party_branch'], $found_child_arr); //所查询数据在josn_data数组中的下标
            // $data[$k]['party_branch'] = $json_data[$found_key]['children'][$found_child_key]['label'];
        }

        return $data;
        // }
    }








    //over
}
