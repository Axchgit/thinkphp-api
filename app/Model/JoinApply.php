<?php
/*
 * @Description: 
 * @Author: 罗曼
 * @Date: 2020-10-16 16:28:24
 * @FilePath: \testd:\wamp64\www\thinkphp-api\app\Model\JoinApply.php
 * @LastEditTime: 2020-11-16 02:02:34
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
    public function getAllApply($list_rows, $config, $faculty, $post, $role, $isSimple = false)
    {
        $person_model = new PersonModel();
        $material_model = new MaterialModel();

        //知识点:删除指定键名元素
        $post_select = array_diff_key($post, ["list_rows" => 0, "page" => 0, 'faculty' => -1]);
        // return $post;
        // if ($faculty == '') {
        $data = $this->where($post_select)->paginate($list_rows, $isSimple, $config);
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
            if (($role == 4 && $faculty !== $person_info['faculty']) || (!empty($post['faculty']) && $post['faculty'] !== $person_info['faculty'])) {
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
            $data[$k]['faculty'] = (int)($person_info['faculty']);


            //学院
            $found_arr = array_column($json_data, 'value'); //所查询键名组成的数组
            $found_key = array_search($person_info['faculty'], $found_arr); //所查询数据在josn_data数组中的下标
            // $data[$k]['faculty'] = $json_data[$found_key]['label'];
            //党支部
            $found_child_arr = array_column($json_data[$found_key]['children'], 'value'); //所查询键名组成的数组
            $found_child_key = array_search($person_info['party_branch'], $found_child_arr); //所查询数据在josn_data数组中的下标
            $data[$k]['party_branch'] = $json_data[$found_key]['children'][$found_child_key]['label'];

            /************审核资料*/
            $data[$k]['certificate_one'] = !empty($material_path_info[0]['score']) ? $material_path_info[3]['score'] : '未认证';
            $data[$k]['certificate_two'] = !empty($material_path_info[1]['score']) ? $material_path_info[1]['score'] : '未认证';
            $data[$k]['certificate_three'] = !empty($material_path_info[2]['score']) ? $material_path_info[2]['score'] : '未认证';
            $data[$k]['applicationPath'] = !empty($material_path_info[3]['remarks']) ? $material_path_info[3]['remarks'] : '';
        }
        // $data['material'] = $material;
        //重新建立索引
        // $data=array_values($data);
        return $data;
        // }
    }

    //修改/审核申请
    public function updateJoinApply($data)
    {
        try {
            $this->update($data, ['id' => $data['id']], ['step', 'review_status', 'reviewer', 'remarks']);  //只允许第二个参数内的值被修改
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }






    /*********首页charts数据*/

    //查询所有申请人数
    public function countApplyPerson(string $faculty = null)
    {
        if ($faculty === null) {
            return $this->group('number')->count();
        }
        return Db::view('person', 'number,faculty')
            ->view('join_apply', 'number', 'person.number=join_apply.number')
            ->where('faculty', $faculty)
            ->count();
    }
    //查询审核数
    public function countReview(int $review_status = 2, string $faculty = null)
    {
        if ($faculty === null) {
            return $this->where('review_status', $review_status)->count();
        }
        return Db::view('person', 'faculty')
            ->view('join_apply', 'number', 'person.number=join_apply.number')
            ->where('faculty', $faculty)
            ->where('review_status', $review_status)
            ->count();
    }

    //折线图数据
    public function countLineCharts(string $faculty = null)
    {
        $year = (int)date('Y');
        // return $year;
        $list = [];
        for ($i = $year; $i > $year - 10; $i--) {
            if ($faculty !== null) {
                $count = Db::view('person', 'faculty')
                    ->view('join_apply', 'number', 'person.number=join_apply.number')
                    ->where('step', 1)
                    ->whereYear('join_apply.create_time', $year)
                    ->where('faculty', $faculty)
                    // ->group("date_format(join_apply.create_time,'%Y')")
                    ->count();
            }
            $count = Db::view('person', 'faculty')
                ->view('join_apply', 'number', 'person.number=join_apply.number')
                ->where('step', 1)
                ->whereYear('join_apply.create_time', $i)
                // ->where('faculty', $faculty)
                // ->group("date_format(join_apply.create_time,'%Y')")
                ->count();
            $list[$year - $i]['年份'] = (string)$i;
            $list[$year - $i]['新增申请人数'] = $count;
            // $list[$i] = $count;
        }
        return $list;
    }
    //男女占比
    public function countPersonSex(string $faculty = null)
    {
        $list = [];
        for ($i = 1; $i <= 2; $i++) {
            if ($faculty !== null) {
                $count = Db::view('person', 'sex')
                    ->view('join_apply', 'number', 'person.number=join_apply.number')
                    ->where('faculty', $faculty)

                    ->where('step', 1)
                    ->where('sex', $i)
                    ->count();
            }
            $count = Db::view('person', 'sex')
                ->view('join_apply', 'number', 'person.number=join_apply.number')
                ->where('step', 1)
                ->where('sex', $i)
                ->count();
            $list[$i - 1]['性别'] = $i==1?'男':'女';
            $list[$i - 1]['人数'] = $count;
        }
        return $list;
    }










    //over
}
